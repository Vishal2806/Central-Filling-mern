<?php
require_once __DIR__ . '/config.php';
$db = getDb();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: records.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $remark = trim($_POST['remark'] ?? '');
    $signaturePath = '';
    $uploadError = '';

    // Handle signature upload for RETURNED statuses
    $isReturnedStatus = ($status === 'RETURNED TO CENTRAL FILING' || $status === 'RETURNED TO ADVOCATE' || $status === 'RETURNED TO ADVOCATE ');
    if ($isReturnedStatus && isset($_FILES['signature']) && $_FILES['signature']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/signatures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Validate file type
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowedExt)) {
                $filename = 'sig_' . $id . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['signature']['tmp_name'], $uploadPath)) {
                    $signaturePath = 'uploads/signatures/' . $filename;
                    chmod($uploadPath, 0644);
                } else {
                    $uploadError = 'Failed to move uploaded file. Check folder permissions.';
                }
            } else {
                $uploadError = 'Invalid file type. Only image files allowed.';
            }
        } else {
            $uploadError = 'File upload error: ' . $_FILES['signature']['error'];
        }
    }

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

            try {
                $historyStmt = $db->prepare('INSERT INTO record_history (record_id, status, remark, signature_path, updated_by) VALUES (:record_id, :status, :remark, :signature_path, :updated_by)');
                $historyStmt->execute([
                    ':record_id' => $id,
                    ':status' => $status,
                    ':remark' => $remark,
                    ':signature_path' => $signaturePath,
                    ':updated_by' => 'User ID: ' . ($_SESSION['user_id'] ?? 'Unknown'),
                ]);
            } catch (Exception $e) {
                $uploadError = 'Database Error: ' . $e->getMessage() . '. Make sure signature_path column exists in record_history table.';
            }
        }
    }

    if ($uploadError) {
        $_SESSION['upload_error'] = $uploadError;
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

$historyStmt = $db->prepare('SELECT id, status, remark, signature_path, created_at FROM record_history WHERE record_id = :record_id ORDER BY created_at ASC');
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

        <?php if (isset($_SESSION['upload_error'])): ?>
            <div class="alert-error">
                <strong>Upload Error:</strong> <?php echo htmlspecialchars($_SESSION['upload_error']); ?>
            </div>
            <?php unset($_SESSION['upload_error']); ?>
        <?php endif; ?>

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
            <form method="post" class="form-grid" enctype="multipart/form-data">
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
                <div class="field-group full-width" id="signature-field" style="display: none;">
<input type="file" id="signature-input" name="signature" accept="image/*" hidden>

<label for="signature-input" class="signature-upload-btn">
    📄 Choose Signature
</label>

<span id="signature-file-name">No file selected</span>

<small style="color: var(--muted); margin-top: 4px; display: block;">
    Upload signature image (PNG, JPG, etc.)
</small>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="button button-primary">Save Update</button>
                </div>
            </form>
        </section>

        <script>
            const statusSelect = document.getElementById('status-select');
            const signatureField = document.getElementById('signature-field');
            const signatureInput = document.getElementById('signature-input');
            const returnedStatuses = ['RETURNED TO CENTRAL FILING', 'RETURNED TO ADVOCATE ', 'RETURNED TO ADVOCATE'];

            function toggleSignatureField() {
                const selectedStatus = statusSelect.value;
                if (returnedStatuses.includes(selectedStatus)) {
                    signatureField.style.display = 'block';
                    signatureInput.required = true;
                } else {
                    signatureField.style.display = 'none';
                    signatureInput.required = false;
                    signatureInput.value = '';
                }
            }

            statusSelect.addEventListener('change', toggleSignatureField);
            toggleSignatureField();

            function printHistoryTimeline() {
                const body = document.body;
                body.classList.add('print-history-only');

                window.print();

                window.onafterprint = function () {
                    body.classList.remove('print-history-only');
                    window.onafterprint = null;
                };
            }
        </script>

        <section class="panel" id="history-panel">
            <div class="panel-header">
                <h2>History Timeline</h2>
                <div class="print-controls">
                    <button type="button" class="button button-secondary" onclick="printHistoryTimeline()">
                        Print / Download PDF
                    </button>
                </div>
            </div>
            <?php if (count($history) === 0): ?>
                <p class="empty-state">No timeline entries have been recorded.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Status</th>
                                <th>Remarks</th>
                                <th style="width: 120px;">Signature</th>
                                <th style="width: 160px; text-align: right;">Updated At</th>
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
                                    <td style="text-align: center;">
                                        <?php if (!empty($item['signature_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($item['signature_path']); ?>" target="_blank" style="display: inline-block;">
                                                <img src="<?php echo htmlspecialchars($item['signature_path']); ?>" alt="Signature" style="max-height: 50px; max-width: 100px; border: 1px solid var(--line); border-radius: 4px; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" />
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--muted); font-size: 0.85rem;">—</span>
                                        <?php endif; ?>
                                    </td>
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
