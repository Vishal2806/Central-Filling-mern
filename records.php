<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$db = getDb();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'ALL';

$sql = 'SELECT id, case_no, case_year, advocate_name, current_status, latest_remark, filing_date, filing_time, case_nature, case_type_code, paperbook_sets FROM records WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (case_no ILIKE :search OR advocate_name ILIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if ($status !== 'ALL') {
    $sql .= ' AND current_status = :status';
    $params[':status'] = $status;
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$statusOptions = ['ALL' => 'All', 'SUBMITTED' => 'Submitted', 'PENDING' => 'Pending', 'RETURNED' => 'Returned', 'RESUBMITTED' => 'Resubmitted', 'APPROVED' => 'Approved'];

require_once __DIR__ . '/includes/header.php';
?>
    <section class="page-header">
        <h1>Filing Records</h1>
        <p>Search, filter, and manage official filing registry entries.</p>
    </section>

    <section class="panel">
        <form method="get" class="filter-row">
            <div class="field-group">
                <label>Search</label>
                <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Case number or advocate name" />
            </div>
            <div class="field-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $value === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="button button-secondary">Filter</button>
                <a class="button button-primary" href="add_record.php">Add Record</a>
            </div>
        </form>

        <?php if (count($records) === 0): ?>
            <p class="empty-state">No records match the current filters.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case No</th>
                            <th>Year</th>
                            <th>Nature</th>
                            <th>Advocate</th>
                            <th>Status</th>
                            <th>Paperbook Sets</th>
                            <th>Filing</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['case_no']); ?></td>
                                <td><?php echo htmlspecialchars($record['case_year']); ?></td>
                                <td><?php echo htmlspecialchars($record['case_nature'] . ' / ' . $record['case_type_code']); ?></td>
                                <td><?php echo htmlspecialchars($record['advocate_name']); ?></td>
                                <td><span class="status-badge status-<?php echo htmlspecialchars(strtolower($record['current_status'])); ?>"><?php echo htmlspecialchars($record['current_status']); ?></span></td>
                                <td><?php echo htmlspecialchars($record['paperbook_sets']); ?></td>
                                <td><?php echo htmlspecialchars($record['filing_date']); ?></td>
                                <td><a href="record_details.php?id=<?php echo urlencode($record['id']); ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php require_once __DIR__ . '/includes/footer.php';
