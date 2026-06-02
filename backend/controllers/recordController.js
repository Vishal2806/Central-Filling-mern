import pool from "../config/db.js";

// Add Record
export const addRecord = async (req, res) => {
    try {
        const {
            caseNo,
            caseYear,        
            advocateName,
            advocateContact, 
            status,
            remark,
            filingDate,      
            filingTime,      
            caseNature,   
            caseTypeCode,
            paperbookSets // <-- Extracted from request payload
        } = req.body;

        // Automatically capture current server Date (YYYY-MM-DD) and Time (HH:MM:SS) if not provided manually
        const autoFilingDate = filingDate || new Date().toISOString().split('T')[0];
        const autoFilingTime = filingTime || new Date().toTimeString().split(' ')[0];

        // Insert Main Record including paperbook_sets
        const newRecord = await pool.query(
            `
            INSERT INTO records
            (
                case_no,
                case_year,
                advocate_name,
                advocate_contact,
                current_status,
                total_returns,
                latest_remark,
                filing_date,
                filing_time,
                case_nature,
                case_type_code,
                paperbook_sets
            )
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)
            RETURNING *
            `,
            [
                caseNo,
                caseYear,
                advocateName,
                advocateContact || null,
                status,
                status === "RETURNED" ? 1 : 0,
                remark,
                autoFilingDate,
                autoFilingTime,
                caseNature || "Civil", 
                caseTypeCode || null,
                parseInt(paperbookSets, 10) || 1 // <-- Appended parameter mapping to $12
            ]
        );

        const record = newRecord.rows[0];

        // Insert Initial History
        await pool.query(
            `
            INSERT INTO record_history
            (
                record_id,
                status,
                remark
            )
            VALUES ($1, $2, $3)
            `,
            [record.id, status, remark]
        );

        res.status(201).json({
            success: true,
            message: "Record added and saved successfully",
            data: record,
        });
    } catch (error) {
        console.log(error);
        res.status(500).json({
            success: false,
            message: "Server Error",
        });
    }
};

// Get All Records
export const getRecords = async (req, res) => {
    try {
        const records = await pool.query(`
            SELECT
                id,
                case_no AS "caseNo",
                case_year AS "caseYear",
                advocate_name AS "advocateName",
                advocate_contact AS "advocateContact",
                current_status AS "status",
                total_returns AS "totalReturns",
                latest_remark AS "latestRemark",
                filing_date AS "filingDate",
                filing_time AS "filingTime",
                case_nature AS "caseNature",     
                case_type_code AS "caseTypeCode", 
                paperbook_sets AS "paperbookSets", -- <-- Selected and aliased to camelCase
                created_at AS "createdAt"
            FROM records
            ORDER BY created_at DESC
        `);

        res.status(200).json({
            success: true,
            data: records.rows,
        });
    } catch (error) {
        console.log(error);
        res.status(500).json({
            success: false,
            message: "Server Error",
        });
    }
};

// Get Single Record + History
export const getRecordById = async (req, res) => {
    try {
        const { id } = req.params;

        const recordResult = await pool.query(
            `
            SELECT
                id,
                case_no AS "caseNo",
                case_year AS "caseYear",
                advocate_name AS "advocateName",
                advocate_contact AS "advocateContact",
                current_status AS "status",
                total_returns AS "totalReturns",
                latest_remark AS "latestRemark",
                filing_date AS "filingDate",
                filing_time AS "filingTime",
                case_nature AS "caseNature",     
                case_type_code AS "caseTypeCode", 
                paperbook_sets AS "paperbookSets", -- <-- Selected and aliased to camelCase
                created_at AS "createdAt"
            FROM records
            WHERE id = $1
            `,
            [id]
        );

        if (recordResult.rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: "Record not found",
            });
        }

        // History Fetch
        const historyResult = await pool.query(
            `
            SELECT
                id,
                status,
                remark,
                created_at AS date
            FROM record_history
            WHERE record_id = $1
            ORDER BY created_at ASC
            `,
            [id]
        );

        const record = recordResult.rows[0];
        record.history = historyResult.rows;

        res.status(200).json({
            success: true,
            data: record,
        });
    } catch (error) {
        console.log(error);
        res.status(500).json({
            success: false,
            message: "Server Error",
        });
    }
};

// Update Record
export const updateRecord = async (req, res) => {
    try {
        const { id } = req.params;
        const { status, remark } = req.body;
        const updatedBy = req.user.id;

        // Get Existing Record
        const existingRecord = await pool.query(
            `
            SELECT *
            FROM records
            WHERE id = $1
            `,
            [id]
        );

        if (existingRecord.rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: "Record not found",
            });
        }

        const record = existingRecord.rows[0];

        // Update Main Record
        const updatedRecord = await pool.query(
            `
            UPDATE records
            SET
                current_status = $1,
                latest_remark = $2,
                total_returns = $3
            WHERE id = $4
            RETURNING *
            `,
            [
                status,
                remark,
                status === "RETURNED"
                    ? record.total_returns + 1
                    : record.total_returns,
                id,
            ]
        );

        // Insert History Log Track
        await pool.query(
            `
            INSERT INTO record_history
            (
                record_id,
                status,
                remark,
                updated_by
            )
            VALUES ($1, $2, $3 , $4)
            `,
            [
                id,
                status,
                remark,
                `User ID: ${updatedBy}`,
            ]
        );

        res.status(200).json({
            success: true,
            message: "Record updated successfully",
            data: updatedRecord.rows[0],
        });
    } catch (error) {
        console.log(error);
        res.status(500).json({
            success: false,
            message: "Server Error",
        });
    }
};