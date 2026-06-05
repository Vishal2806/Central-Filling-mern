<?php
require_once __DIR__ . '/config.php';
$db = getDb();

try {
    // 1. Fetch Live Summary Counts in Real-Time
    $countStmt = $db->query('SELECT current_status, COUNT(*) as total FROM records GROUP BY current_status');
    $statusCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Calculate aggregated summary values safely
    $totalRecords = array_sum($statusCounts);
    $pendingCount = $statusCounts['PENDING'] ?? 0;
    
    // Combine both explicit advocate and central filing return metrics
    $returnedCount = ($statusCounts['RETURNED'] ?? 0) + 
                     ($statusCounts['RETURNED TO ADVOCATE '] ?? 0) + 
                     ($statusCounts['RETURNED TO CENTRAL FILING'] ?? 0);
                     
    $approvedCount = $statusCounts['APPROVED'] ?? 0;

    // 2. Fetch the 2 Most Recent Case Entries Live
    $latestStmt = $db->query('SELECT id, case_no, case_year, advocate_name, current_status, filing_date FROM records ORDER BY created_at DESC LIMIT 3');
    $latestRecords = $latestStmt->fetchAll();

} catch (Exception $e) {
    // Fail-safe defaults if the database schema is empty or installing
    $totalRecords = $pendingCount = $returnedCount = $approvedCount = 0;
    $latestRecords = [];
}

require_once __DIR__ . '/includes/header.php';
?>

    <main class="container">
        
        <section class="page-header">
            <div>
                <h1>Registry Dashboard</h1>
                <p>Live metrics monitor filing movement, recent case entries, and registry status parameters.</p>
            </div>
        </section>

        <!-- Fixed: Re-linked to class tokens to force correct horizontal grid layouts -->
        <div class="grid cards-grid">
            <article class="card card-total" style="border-top: 4px solid var(--navy);">
                <h2><?php echo $totalRecords; ?></h2>
                <p>Total Records</p>
            </article>
            <article class="card card-pending" style="border-top: 4px solid #d97706;">
                <h2><?php echo $pendingCount; ?></h2>
                <p>Pending</p>
            </article>
            <article class="card card-returned" style="border-top: 4px solid #dc2626;">
                <h2><?php echo $returnedCount; ?></h2>
                <p>Returned</p>
            </article>
            <article class="card card-approved" style="border-top: 4px solid #16a34a;">
                <h2><?php echo $approvedCount; ?></h2>
                <p>Approved</p>
            </article>
        </div>

        <section class="panel">
            <div class="panel-header">
                <h2>Latest Records Log</h2>
                <a class="button button-secondary" href="records.php">View all records</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case No</th>
                            <th>Year</th>
                            <th>Advocate Particulars</th>
                            <th>Status</th>
                            <th>Filing Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($latestRecords) === 0): ?>
                            <tr>
                                <td colspan="6" class="empty-state" style="border: none;">No filing data recorded inside database tables yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($latestRecords as $row): ?>
                                <?php 
                                    // Normalized to flawlessly trigger your styles.css dynamic badge highlights
                                    $slugStatus = strtolower(str_replace(' ', '-', trim($row['current_status']))); 
                                ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--ink);"><?php echo htmlspecialchars($row['case_no']); ?></td>
                                    <td style="font-weight: 500; color: var(--muted);"><?php echo htmlspecialchars($row['case_year']); ?></td>
                                    <td style="font-weight: 500; color: var(--ink);"><?php echo htmlspecialchars($row['advocate_name']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $slugStatus; ?>">
                                            <?php echo htmlspecialchars($row['current_status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--muted); font-size: 0.88rem; font-weight: 500;"><?php echo htmlspecialchars($row['filing_date']); ?></td>
                                    <td style="text-align: right;">
                                        <a href="record_details.php?id=<?php echo urlencode($row['id']); ?>" style="font-weight: 600; text-decoration: underline;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>            
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 
