<?php
// ============================================================
// MY PROFILE MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'My Profile';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

$user = getLoggedInUser();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user['id']]);
$userData = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif (empty($full_name) || empty($email)) {
        setFlash('danger', 'Full name and email address are required.');
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id");
            $updateStmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':id' => $user['id']
            ]);

            // Update session values
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            setFlash('success', 'Profile updated successfully.');
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            setFlash('danger', 'Error updating profile: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Profile</h1>
        <p class="page-subtitle">Manage your account information and preferences</p>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 650px;">
    <div class="form-header">
        <h3 class="form-title">Account Details</h3>
    </div>
    
    <form action="profile.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= escape($userData['username']) ?>" disabled>
                        <span class="form-help">Username cannot be modified.</span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?= escape($userData['full_name']) ?>" required>
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= escape($userData['email']) ?>" required>
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= escape($userData['phone']) ?>">
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= strtoupper(escape($userData['role'])) ?>" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary">Save Profile Changes</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
