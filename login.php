<?php
// ============================================================
// LOGIN PAGE MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'Please log in to access the system.';
}
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    setFlash('success', 'You have been successfully logged out.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . escape($user['full_name']) . '!');
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database query failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Grand Royale Hotel Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-card fade-in">
        <div class="login-header">
            <div class="login-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v5m-6 0h6"></path></svg>
            </div>
            <h1 class="login-title">Grand Royale Hotel</h1>
            <p class="login-subtitle">Management Control Center</p>
        </div>

        <?= getFlash(); ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger fade-in">
                <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span><?= escape($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-icon-wrapper">
                    <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autofocus value="admin">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-icon-wrapper">
                    <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required value="admin123">
                </div>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" checked>
                    <span>Remember Login</span>
                </label>
                <button type="button" class="forgot-pass-btn" onclick="alert('Default Administrator Credentials:\nUsername: admin\nPassword: admin123\n\nIf you forgot your custom password, please re-import database/hotel.sql in phpMyAdmin.');">Forgot Password?</button>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <span>Sign In to Dashboard</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

        <div class="demo-credentials-box">
            🔑 <strong>Default Login Credentials:</strong><br>
            Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
        </div>
    </div>
</div>

<script src="js/validation.js"></script>
</body>
</html>
