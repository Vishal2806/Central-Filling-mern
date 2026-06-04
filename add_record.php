<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$db = getDb();

// Core static code options framework initialization parameters
$civilOptions = ['OS', 'CA', 'WP', 'OP'];
$criminalOptions = ['CC', 'CRA', 'BA', 'MC'];

$caseNature = $_POST['caseNature'] ?? 'Civil';
$caseTypeCode = $_POST['caseTypeCode'] ?? '';
$caseNo = $_POST['caseNo'] ?? '';
$caseYear = $_POST['caseYear'] ?? '';
$advocateName = $_POST['advocateName'] ?? '';
$advocateContact = $_POST['advocateContact'] ?? '';
$paperbookSets = $_POST['paperbookSets'] ?? '1';
$status = trim($_POST['status'] ?? 'SUBMITTED');
$remark = $_POST['remark'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($caseNo === '' || $caseYear === '' || $advocateName === '' || $remark === '') {
        $error = 'Please complete all required fields.';
    } else {
        try {
            $filingDate = $_POST['filingDate'] ?? date('Y-m-d');
            $filingTime = $_POST['filingTime'] ?? date('H:i:s');
            
            // Robust logic matrix matching central styling sheet status overrides
            $checkStatus = trim($status);
            $isReturnedStatus = ($checkStatus === 'RETURNED' || $checkStatus === 'RETURNED TO CENTRAL FILING' || $checkStatus === 'RETURNED TO ADVOCATE');
            $totalReturns = $isReturnedStatus ? 1 : 0;

            $stmt = $db->prepare(
                'INSERT INTO records (case_no, case_year, advocate_name, advocate_contact, current_status, total_returns, latest_remark, filing_date, filing_time, case_nature, case_type_code, paperbook_sets) VALUES (:case_no, :case_year, :advocate_name, :advocate_contact, :current_status, :total_returns, :latest_remark, :filing_date, :filing_time, :case_nature, :case_type_code, :paperbook_sets) RETURNING id'
            );

            $stmt->execute([
                ':case_no' => $caseNo,
                ':case_year' => $caseYear,
                ':advocate_name' => $advocateName,
                ':advocate_contact' => $advocateContact ?: null,
                ':current_status' => $status,
                ':total_returns' => $totalReturns,
                ':latest_remark' => $remark,
                ':filing_date' => $filingDate,
                ':filing_time' => $filingTime,
                ':case_nature' => $caseNature,
                ':case_type_code' => $caseTypeCode ?: null,
                ':paperbook_sets' => (int) $paperbookSets,
            ]);

            $newRecord = $stmt->fetch();
            $recordId = $newRecord['id'];

            $historyStmt = $db->prepare('INSERT INTO record_history (record_id, status, remark, updated_by) VALUES (:record_id, :status, :remark, :updated_by)');
            $historyStmt->execute([
                ':record_id' => $recordId,
                ':status' => $status,
                ':remark' => $remark,
                ':updated_by' => 'User ID: ' . $_SESSION['user_id'],
            ]);

            header('Location: record_details.php?id=' . urlencode($recordId));
            exit;
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$statusOptions = [
    'SUBMITTED' => 'Submitted', 
    'PENDING' => 'Pending', 
    'RETURNED TO CENTRAL FILING' => 'Returned to Central Filing',
    'RETURNED TO ADVOCATE' => 'Returned to Advocate',
    'RESUBMITTED' => 'Resubmitted', 
    'APPROVED' => 'Approved'
];
$caseNatureOptions = ['Civil', 'Criminal', 'Writ'];

require_once __DIR__ . '/includes/header.php';
?>
    <!-- Fixed: Wrapped page layout elements inside a master responsive container shell -->
    <main class="container">

        <!-- Fixed: Wrapped title structure inside structured page-header tags to align margins perfectly -->
        <section class="page-header">
            <div>
                <h1>New Filing Record</h1>
                <p>Create an official filing registry entry with case and advocate details.</p>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" class="form-panel">
            <div class="field-group">
                <label for="caseNature">Case Nature</label>
                <select id="caseNature" name="caseNature">
                    <?php foreach ($caseNatureOptions as $nature): ?>
                        <option value="<?php echo htmlspecialchars($nature); ?>" <?php echo $nature === $caseNature ? 'selected' : ''; ?>><?php echo htmlspecialchars($nature); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <label for="caseTypeCode">Case Type Code</label>
                <select id="caseTypeCode" name="caseTypeCode">
                    <option value="">Select Case Type</option>
                    <optgroup label="Civil Cases">
                        <?php foreach ($civilOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $caseTypeCode === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Criminal Cases">
                        <?php foreach ($criminalOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $caseTypeCode === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <div class="field-group">
                <label for="caseNo">Case No</label>
                <input type="text" id="caseNo" name="caseNo" value="<?php echo htmlspecialchars($caseNo); ?>" required />
            </div>

            <div class="field-group">
                <label for="caseYear">Case Year</label>
                <input type="text" id="caseYear" name="caseYear" value="<?php echo htmlspecialchars($caseYear); ?>" required />
            </div>

            <div class="field-group">
                <label for="advocateName">Advocate Name</label>
                <input type="text" id="advocateName" name="advocateName" value="<?php echo htmlspecialchars($advocateName); ?>" required />
            </div>

            <div class="field-group">
                <label for="advocateContact">Advocate Contact</label>
                <input type="text" id="advocateContact" name="advocateContact" value="<?php echo htmlspecialchars($advocateContact); ?>" />
            </div>

            <div class="field-group">
                <label for="paperbookSets">Paperbook Sets</label>
                <input type="number" min="1" id="paperbookSets" name="paperbookSets" value="<?php echo htmlspecialchars($paperbookSets); ?>" required />
            </div>

            <div class="field-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo trim($value) === trim($status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group full-width">
                <label for="remark">Remark</label>
                <textarea id="remark" name="remark" rows="4" required><?php echo htmlspecialchars($remark); ?></textarea>
            </div>

            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-start; margin-top: 4px;">
                <button type="submit" class="button button-primary">Save Record</button>
            </div>
        </form>

    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
