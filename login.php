<?php
require_once __DIR__ . '/config.php';

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
            $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
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
        <p>Sign in to access the e-filing registry.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="form-grid">
            <label>
                Email
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
            </label>

            <label>
                Password
                <input type="password" name="password" required />
            </label>

            <button type="submit" class="button button-primary">Login</button>
        </form>

        <div class="demo-credentials">
            <p><strong>Demo credentials</strong></p>
            <p>Email: admin@test.com</p>
            <p>Password: 123456</p>
        </div>
    </div>
</body>
</html>
