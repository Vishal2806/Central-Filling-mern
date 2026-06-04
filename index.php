<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$db = getDb();

$totalResult = $db->query('SELECT COUNT(*) AS total FROM records')->fetch();
$pendingResult = $db->query("SELECT COUNT(*) AS total FROM records WHERE current_status = 'PENDING'")->fetch();
$returnedResult = $db->query("SELECT COUNT(*) AS total FROM records WHERE current_status = 'RETURNED'")->fetch();
$approvedResult = $db->query("SELECT COUNT(*) AS total FROM records WHERE current_status = 'APPROVED'")->fetch();
$latestRecords = $db->query('SELECT id, case_no, case_year, advocate_name, current_status, filing_date FROM records ORDER BY created_at DESC LIMIT 5')->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
    <section class="page-header">
        <h1>Registry Dashboard</h1>
        <p>Monitor filing movement, recent case entries, and registry status.</p>
    </section>

    <div class="grid cards-grid">
        <article class="card">
            <h2><?php echo htmlspecialchars($totalResult['total'] ?? 0); ?></h2>
            <p>Total Records</p>
        </article>
        <article class="card">
            <h2><?php echo htmlspecialchars($pendingResult['total'] ?? 0); ?></h2>
            <p>Pending</p>
        </article>
        <article class="card">
            <h2><?php echo htmlspecialchars($returnedResult['total'] ?? 0); ?></h2>
            <p>Returned</p>
        </article>
        <article class="card">
            <h2><?php echo htmlspecialchars($approvedResult['total'] ?? 0); ?></h2>
            <p>Approved</p>
        </article>
    </div>

    <section class="panel">
        <div class="panel-header">
            <h2>Latest Records</h2>
            <a class="button button-secondary" href="records.php">View all records</a>
        </div>

        <?php if (count($latestRecords) === 0): ?>
            <p class="empty-state">No records found yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case No</th>
                            <th>Year</th>
                            <th>Advocate</th>
                            <th>Status</th>
                            <th>Filing Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['case_no']); ?></td>
                                <td><?php echo htmlspecialchars($record['case_year']); ?></td>
                                <td><?php echo htmlspecialchars($record['advocate_name']); ?></td>
                                <td><span class="status-badge status-<?php echo htmlspecialchars(strtolower($record['current_status'])); ?>"><?php echo htmlspecialchars($record['current_status']); ?></span></td>
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
