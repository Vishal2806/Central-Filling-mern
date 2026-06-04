<?php
require_once __DIR__ . '/config.php';

// Check if user session initialization exists
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        try {
            $db = getDb();
            // Case-insensitive authentication lookup framework guardrail
            $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid email or password.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        /* Contextual adjustments for auth view centering without cluttering global stylesheet */
        body.auth-page {
            display: grid;
            place-items: center;
            padding: 40px 16px;
            background: var(--surface-soft);
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-md);
        }
        .auth-identity {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }
        .auth-identity strong {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .auth-identity span {
            display: block;
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 2px;
        }
        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--navy);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
            letter-spacing: -0.01em;
        }
        .auth-card h1 {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }
        .auth-card p.auth-subtitle {
            color: var(--muted);
            font-weight: 500;
            font-size: 0.92rem;
            margin-bottom: 24px;
        }
        .auth-card .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
    </style>
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-identity">
            <span class="brand-mark" aria-hidden="true">CF</span>
            <div>
                <strong><?php echo htmlspecialchars(APP_NAME); ?></strong>
                <span>Central Filing Office</span>
            </div>
        </div>
        
        <h1>Officer Login</h1>
        <p class="auth-subtitle">Sign in to access the e-filing registry.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="form-grid">
            <div class="field-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="username" />
            </div>

            <div class="field-group">
                <label style="display: flex; justify-content: space-between;"絨 for="password">
                    <span>Password</span>
                </label>
                <input type="password" id="password" name="password" required autocomplete="current-password" />
            </div>

            <button type="submit" class="button button-primary" style="width: 100%; margin-top: 4px;">Login</button>
        </form>

        <div class="demo-credentials">
            <p style="margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--navy);">System Admin Credentials</p>
            <p style="font-weight: 500; margin: 0;">Email: <span style="font-weight: 600; color: var(--ink);">admin@test.com</span></p>
            <p style="font-weight: 500; color: var(--muted); margin: 0;">Password: 123456</p>
        </div>
    </div>

</body>
</html>