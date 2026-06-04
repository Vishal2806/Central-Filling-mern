<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$db = getDb();

$caseNature = $_POST['caseNature'] ?? 'Civil';
$caseTypeCode = $_POST['caseTypeCode'] ?? '';
$caseNo = $_POST['caseNo'] ?? '';
$caseYear = $_POST['caseYear'] ?? '';
$advocateName = $_POST['advocateName'] ?? '';
$advocateContact = $_POST['advocateContact'] ?? '';
$paperbookSets = $_POST['paperbookSets'] ?? '1';
$status = $_POST['status'] ?? 'SUBMITTED';
$remark = $_POST['remark'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($caseNo === '' || $caseYear === '' || $advocateName === '' || $remark === '') {
        $error = 'Please complete all required fields.';
    } else {
        try {
            $filingDate = $_POST['filingDate'] ?? date('Y-m-d');
            $filingTime = $_POST['filingTime'] ?? date('H:i:s');
            $totalReturns = $status === 'RETURNED' ? 1 : 0;

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

$statusOptions = ['SUBMITTED' => 'Submitted', 'PENDING' => 'Pending', 'RETURNED' => 'Returned', 'RESUBMITTED' => 'Resubmitted', 'APPROVED' => 'Approved'];
$caseNatureOptions = ['Civil', 'Criminal', 'Writ'];

require_once __DIR__ . '/includes/header.php';
?>
    <section class="page-header">
        <h1>New Filing Record</h1>
        <p>Create an official filing registry entry with case and advocate details.</p>
    </section>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form-panel">
        <div class="field-group">
            <label>Case Nature</label>
            <select name="caseNature">
                <?php foreach ($caseNatureOptions as $nature): ?>
                    <option value="<?php echo htmlspecialchars($nature); ?>" <?php echo $nature === $caseNature ? 'selected' : ''; ?>><?php echo htmlspecialchars($nature); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Case Type Code</label>
            <input type="text" name="caseTypeCode" value="<?php echo htmlspecialchars($caseTypeCode); ?>" placeholder="Enter case type code" />
        </div>

        <div class="field-group">
            <label>Case No</label>
            <input type="text" name="caseNo" value="<?php echo htmlspecialchars($caseNo); ?>" required />
        </div>

        <div class="field-group">
            <label>Case Year</label>
            <input type="text" name="caseYear" value="<?php echo htmlspecialchars($caseYear); ?>" required />
        </div>

        <div class="field-group">
            <label>Advocate Name</label>
            <input type="text" name="advocateName" value="<?php echo htmlspecialchars($advocateName); ?>" required />
        </div>

        <div class="field-group">
            <label>Advocate Contact</label>
            <input type="text" name="advocateContact" value="<?php echo htmlspecialchars($advocateContact); ?>" />
        </div>

        <div class="field-group">
            <label>Paperbook Sets</label>
            <input type="number" min="1" name="paperbookSets" value="<?php echo htmlspecialchars($paperbookSets); ?>" required />
        </div>

        <div class="field-group">
            <label>Status</label>
            <select name="status">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $value === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group full-width">
            <label>Remark</label>
            <textarea name="remark" rows="4" required><?php echo htmlspecialchars($remark); ?></textarea>
        </div>

        <button type="submit" class="button button-primary">Save Record</button>
    </form>
<?php require_once __DIR__ . '/includes/footer.php';
