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
    $params[':status'] = trim($status);
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$statusOptions = ['ALL' => 'All', 'SUBMITTED' => 'Submitted', 'PENDING' => 'Pending', 'RETURNED' => 'Returned', 'RESUBMITTED' => 'Resubmitted', 'APPROVED' => 'Approved'];

require_once __DIR__ . '/includes/header.php';
?>
    <!-- Fixed: Wrapped page layout elements inside a master responsive container shell -->
    <main class="container">

        <!-- Fixed: Content now sits inside container boundaries to align margins across screen resolutions -->
        <section class="page-header">
            <div>
                <h1>Filing Records</h1>
                <p>Search, filter, and manage official filing registry entries.</p>
            </div>
        </section>

        <section class="panel">
            <form method="get" class="filter-row">
                <div class="field-group">
                    <label for="search-filter">Search</label>
                    <input type="search" id="search-filter" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Case number or advocate name" />
                </div>
                <div class="field-group">
                    <label for="status-filter">Status</label>
                    <select id="status-filter" name="status">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo trim($value) === trim($status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="button button-secondary">Search</button>
                    <a class="button button-primary" href="add_record.php" style="text-decoration: none;">Add Record</a>
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
                                <th style="text-align: center;">Sets</th>
                                <th>Filing Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <?php 
                                    $cleanStatusClass = strtolower(str_replace(' ', '-', trim($record['current_status']))); 
                                ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--ink);"><?php echo htmlspecialchars($record['case_no']); ?></td>
                                    <td style="font-weight: 500; color: var(--muted);"><?php echo htmlspecialchars($record['case_year']); ?></td>
                                    <td style="font-weight: 600; color: var(--ink);"><?php echo htmlspecialchars($record['case_nature'] . ' / ' . $record['case_type_code']); ?></td>
                                    <td style="font-weight: 500; color: var(--ink);"><?php echo htmlspecialchars($record['advocate_name']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $cleanStatusClass; ?>">
                                            <?php echo htmlspecialchars(trim($record['current_status'])); ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; text-align: center; color: var(--ink);"><?php echo htmlspecialchars($record['paperbook_sets']); ?></td>
                                    <td style="color: var(--muted); font-size: 0.88rem; font-weight: 500;"><?php echo htmlspecialchars($record['filing_date']); ?></td>
                                    <td style="text-align: right;">
                                        <a href="record_details.php?id=<?php echo urlencode($record['id']); ?>" style="font-weight: 600; text-decoration: underline;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
