<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$db = getDb();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: records.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $remark = trim($_POST['remark'] ?? '');

    if ($status !== '' && $remark !== '') {
        $recordStmt = $db->prepare('SELECT total_returns FROM records WHERE id = :id');
        $recordStmt->execute([':id' => $id]);
        $existing = $recordStmt->fetch();

        if ($existing) {
            $checkStatus = trim($status);
            $isReturned = ($checkStatus === 'RETURNED' || $checkStatus === 'RETURNED TO CENTRAL FILING' || $checkStatus === 'RETURNED TO ADVOCATE');
            $newReturns = $isReturned ? $existing['total_returns'] + 1 : $existing['total_returns'];
            
            $updateStmt = $db->prepare('UPDATE records SET current_status = :status, latest_remark = :remark, total_returns = :total_returns WHERE id = :id');
            $updateStmt->execute([
                ':status' => $status,
                ':remark' => $remark,
                ':total_returns' => $newReturns,
                ':id' => $id,
            ]);

            $historyStmt = $db->prepare('INSERT INTO record_history (record_id, status, remark, updated_by) VALUES (:record_id, :status, :remark, :updated_by)');
            $historyStmt->execute([
                ':record_id' => $id,
                ':status' => $status,
                ':remark' => $remark,
                ':updated_by' => 'User ID: ' . $_SESSION['user_id'],
            ]);
        }
    }

    header('Location: record_details.php?id=' . urlencode($id));
    exit;
}

$recordStmt = $db->prepare('SELECT id, case_no, case_year, advocate_name, advocate_contact, current_status, total_returns, latest_remark, filing_date, filing_time, case_nature, case_type_code, paperbook_sets FROM records WHERE id = :id');
$recordStmt->execute([':id' => $id]);
$record = $recordStmt->fetch();

if (!$record) {
    header('Location: records.php');
    exit;
}

$historyStmt = $db->prepare('SELECT id, status, remark, created_at FROM record_history WHERE record_id = :record_id ORDER BY created_at ASC');
$historyStmt->execute([':record_id' => $id]);
$history = $historyStmt->fetchAll();

$statusOptions = ['RETURNED TO CENTRAL FILING' => 'RETURNED TO CENTRAL FILING','RETURNED TO ADVOCATE ' => 'RETURNED TO ADVOCATE', 'RESUBMITTED' => 'RESUBMITTED', 'APPROVED' => 'APPROVED', 'PENDING' => 'PENDING'];

require_once __DIR__ . '/includes/header.php';
?>
    <main class="container">

        <section class="page-header">
            <div>
                <h1>Record Details</h1>
                <p>Review filing particulars, status history, and registry updates.</p>
            </div>
        </section>

        <section class="panel">
            <div class="record-details-grid">
                <div>
                    <strong>Case Nature</strong>
                    <p><?php echo htmlspecialchars($record['case_nature']); ?> / <?php echo htmlspecialchars($record['case_type_code']); ?></p>
                </div>
                <div>
                    <strong>Case No</strong>
                    <p><?php echo htmlspecialchars($record['case_no']); ?></p>
                </div>
                <div>
                    <strong>Year</strong>
                    <p><?php echo htmlspecialchars($record['case_year']); ?></p>
                </div>
                <div>
                    <strong>Advocate</strong>
                    <p><?php echo htmlspecialchars($record['advocate_name']); ?></p>
                </div>
                <div>
                    <strong>Status</strong>
                    <p>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', trim($record['current_status']))); ?>">
                            <?php echo htmlspecialchars($record['current_status']); ?>
                        </span>
                    </p>
                </div>
                <div>
                    <strong>Returns</strong>
                    <p><?php echo htmlspecialchars($record['total_returns']); ?></p>
                </div>
                <div>
                    <strong>Filing Date</strong>
                    <p><?php echo htmlspecialchars($record['filing_date']); ?></p>
                </div>
                <div>
                    <strong>Filing Time</strong>
                    <p><?php echo htmlspecialchars($record['filing_time']); ?></p>
                </div>
                <div>
                    <strong>Paperbook Sets</strong>
                    <p><?php echo htmlspecialchars($record['paperbook_sets']); ?></p>
                </div>
                <div class="full-width remarks-panel">
                    <strong>Latest Remark</strong>
                    <p class="remark-text"><?php echo nl2br(htmlspecialchars($record['latest_remark'])); ?></p>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Update Record</h2>
            <form method="post" class="form-grid">
                <div class="field-group">
                    <label for="status-select">Status</label>
                    <select id="status-select" name="status" required>
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group full-width">
                    <label for="remark-input">Remark</label>
                    <textarea id="remark-input" name="remark" rows="4" required></textarea>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="button button-primary">Save Update</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <h2>History Timeline</h2>
            <?php if (count($history) === 0): ?>
                <p class="empty-state">No timeline entries have been recorded.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th style="width: 280px;">Status</th>
                                <th>Remarks</th>
                                <th style="width: 180px; text-align: right;">Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', trim($item['status']))); ?>">
                                            <?php echo htmlspecialchars($item['status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--ink); font-weight: 500; font-size: 0.92rem; line-height: 1.6;"><?php echo htmlspecialchars($item['remark']); ?></td>
                                    <td style="text-align: right; color: var(--muted); font-weight: 600; font-size: 0.88rem;"><?php echo htmlspecialchars($item['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>