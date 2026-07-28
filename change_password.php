<?php
// ============================================================
// CHANGE PASSWORD MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Change Password';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

$user = getLoggedInUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        setFlash('danger', 'All fields are required.');
    } elseif ($new_password !== $confirm_password) {
        setFlash('danger', 'New password and confirmation do not match.');
    } elseif (strlen($new_password) < 6) {
        setFlash('danger', 'New password must be at least 6 characters long.');
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($current_password, $currentHash)) {
                setFlash('danger', 'Current password is incorrect.');
            } else {
                $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->execute([':password' => $newHash, ':id' => $user['id']]);

                setFlash('success', 'Password updated successfully!');
                header("Location: change_password.php");
                exit();
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Error updating password: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Change Password</h1>
        <p class="page-subtitle">Update your account security password</p>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 550px;">
    <div class="form-header">
        <h3 class="form-title">Security Credentials</h3>
    </div>
    
    <form action="change_password.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min 6 chars)" required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
