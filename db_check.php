<?php
require_once __DIR__ . '/config.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$results = [];
$databases = [];
$tables = [];

try {
    $maintenanceDb = getDb(DB_MAINTENANCE_NAME);
    $results[] = ['ok', 'Connected to maintenance database "' . DB_MAINTENANCE_NAME . '".'];

    $stmt = $maintenanceDb->query('SELECT datname FROM pg_database ORDER BY datname');
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array(DB_NAME, $databases, true)) {
        $results[] = ['ok', 'Database "' . DB_NAME . '" exists on this PostgreSQL server.'];
    } else {
        $results[] = ['error', 'Database "' . DB_NAME . '" does not exist on this PostgreSQL server.'];
    }
} catch (Exception $e) {
    $results[] = ['error', 'Could not connect to maintenance database: ' . $e->getMessage()];
}

try {
    $db = getDb();
    $results[] = ['ok', 'Connected to application database "' . DB_NAME . '".'];

    $stmt = $db->query(
        "SELECT table_name
         FROM information_schema.tables
         WHERE table_schema = 'public'
         ORDER BY table_name"
    );
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $results[] = ['error', 'Could not connect to application database: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Database Check | <?php echo h(APP_NAME); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #1f2937; padding: 32px; }
        main { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; }
        h1 { margin-top: 0; }
        code { background: #eef2f7; padding: 2px 6px; border-radius: 4px; }
        .row { display: grid; grid-template-columns: 180px 1fr; gap: 12px; padding: 8px 0; border-bottom: 1px solid #edf2f7; }
        .ok { color: #166534; }
        .error { color: #b91c1c; }
        ul { margin-top: 8px; }
    </style>
</head>
<body>
    <main>
        <h1>Database Check</h1>

        <h2>PHP is using this connection</h2>
        <div class="row"><strong>Host</strong><code><?php echo h(DB_HOST); ?></code></div>
        <div class="row"><strong>Port</strong><code><?php echo h(DB_PORT); ?></code></div>
        <div class="row"><strong>User</strong><code><?php echo h(DB_USER); ?></code></div>
        <div class="row"><strong>App database</strong><code><?php echo h(DB_NAME); ?></code></div>
        <div class="row"><strong>Maintenance DB</strong><code><?php echo h(DB_MAINTENANCE_NAME); ?></code></div>

        <h2>Result</h2>
        <ul>
            <?php foreach ($results as [$type, $message]): ?>
                <li class="<?php echo h($type); ?>"><?php echo h($message); ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($databases): ?>
            <h2>Databases visible to PHP</h2>
            <p><?php echo h(implode(', ', $databases)); ?></p>
        <?php endif; ?>

        <?php if ($tables): ?>
            <h2>Tables in <?php echo h(DB_NAME); ?></h2>
            <p><?php echo h(implode(', ', $tables)); ?></p>
        <?php endif; ?>

        <p><a href="setup.php">Run setup</a> | <a href="login.php">Go to login</a></p>
    </main>
</body>
</html>
