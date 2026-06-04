<?php
require_once __DIR__ . '/../auth.php';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <header class="topbar">
        <div class="container nav-inner">
            <a class="brand" href="index.php" aria-label="<?php echo htmlspecialchars(APP_NAME); ?>">
                <span class="brand-mark" aria-hidden="true">CF</span>
                <span class="brand-text">
                    <span class="brand-title"><?php echo htmlspecialchars(APP_NAME); ?></span>
                    <span class="brand-subtitle">Central Filing Office</span>
                </span>
            </a>
            <?php if ($user): ?>
                <nav class="main-nav">
                    <a href="records.php">Records</a>
                    <a href="add_record.php">Add Record</a>
                    <a href="logout.php">Logout</a>
                </nav>
                <div class="user-chip">Signed in as <?php echo htmlspecialchars($user['name']); ?></div>
            <?php endif; ?>
        </div>
    </header>
    <main class="container">
