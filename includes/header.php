<?php
require_once __DIR__ . '/../config.php';
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
    <header class="topbar" style="background-color: #0f172a; border-bottom: 3px solid #4f46e5; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div class="container nav-inner" style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; padding-bottom: 14px; width: 100%; max-width: 1820px; margin: 0 auto; padding-left: 24px; padding-right: 24px; box-sizing: border-box;">
            
            <a class="brand" href="index.php" aria-label="<?php echo htmlspecialchars(APP_NAME); ?>" style="display: inline-flex; align-items: center; text-decoration: none;">
                <span class="brand-text" style="display: flex; flex-direction: column;">
                    <span class="brand-title" style="color: #ffffff; font-size: 1.2rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.2;"><?php echo htmlspecialchars(APP_NAME); ?></span>
                    <span class="brand-subtitle" style="color: #94a3b8; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px;">Central Filing Office</span>
                </span>
            </a>
            
            <nav class="main-nav" style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                <a href="index.php" style="color: #cbd5e1; font-size: 0.9rem; font-weight: 600; padding: 8px 16px; border-radius: 6px; transition: all 0.15s ease; text-decoration: none;" onmouseover="this.style.color='#ffffff'; this.style.backgroundColor='rgba(255,255,255,0.06)';" onmouseout="this.style.color='#cbd5e1'; this.style.backgroundColor='transparent';">Dashboard</a>
                <a href="records.php" style="color: #cbd5e1; font-size: 0.9rem; font-weight: 600; padding: 8px 16px; border-radius: 6px; transition: all 0.15s ease; text-decoration: none;" onmouseover="this.style.color='#ffffff'; this.style.backgroundColor='rgba(255,255,255,0.06)';" onmouseout="this.style.color='#cbd5e1'; this.style.backgroundColor='transparent';">Records</a>
                <a href="add_record.php" style="color: #cbd5e1; font-size: 0.9rem; font-weight: 600; padding: 8px 16px; border-radius: 6px; transition: all 0.15s ease; text-decoration: none;" onmouseover="this.style.color='#ffffff'; this.style.backgroundColor='rgba(255,255,255,0.06)';" onmouseout="this.style.color='#cbd5e1'; this.style.backgroundColor='transparent';">Add Record</a>
            </nav>

        </div>
    </header>
