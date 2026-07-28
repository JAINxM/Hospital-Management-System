<?php
// ============================================================
// EDIT CUSTOMER MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Edit Customer Profile';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

$customerId = (int)($_GET['id'] ?? 0);
if ($customerId <= 0) {
    setFlash('danger', 'Invalid customer ID.');
    header("Location: view_customers.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    setFlash('danger', 'Customer profile not found.');
    header("Location: view_customers.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $dob = !empty($_POST['dob']) ? sanitize($_POST['dob']) : null;
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $country = sanitize($_POST['country'] ?? 'India');
    $id_proof_type = sanitize($_POST['id_proof_type'] ?? 'Aadhar');
    $id_number = sanitize($_POST['id_number'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif (empty($full_name) || empty($phone) || empty($id_number)) {
        setFlash('danger', 'Full name, phone, and ID number are required.');
    } else {
        try {
            // Check phone uniqueness
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE phone = :phone AND id != :id");
            $checkStmt->execute([':phone' => $phone, ':id' => $customerId]);
            if ($checkStmt->fetchColumn() > 0) {
                setFlash('danger', "Phone number '{$phone}' belongs to another customer.");
            } else {
                $photoName = $customer['photo'];
                if (isset($_FILES['customer_photo']) && $_FILES['customer_photo']['error'] === UPLOAD_ERR_OK) {
                    $newPhoto = uploadFile($_FILES['customer_photo'], 'customer');
                    if ($newPhoto) {
                        if (!empty($customer['photo']) && file_exists(__DIR__ . '/uploads/customer/' . $customer['photo'])) {
                            @unlink(__DIR__ . '/uploads/customer/' . $customer['photo']);
                        }
                        $photoName = $newPhoto;
                    }
                }

                $updateStmt = $pdo->prepare("UPDATE customers SET full_name = :full_name, gender = :gender, dob = :dob, 
                                             phone = :phone, email = :email, address = :address, city = :city, 
                                             state = :state, country = :country, id_proof_type = :id_proof_type, 
                                             id_number = :id_number, photo = :photo WHERE id = :id");
                $updateStmt->execute([
                    ':full_name' => $full_name,
                    ':gender' => $gender,
                    ':dob' => $dob,
                    ':phone' => $phone,
                    ':email' => $email,
                    ':address' => $address,
                    ':city' => $city,
                    ':state' => $state,
                    ':country' => $country,
                    ':id_proof_type' => $id_proof_type,
                    ':id_number' => $id_number,
                    ':photo' => $photoName,
                    ':id' => $customerId
                ]);

                setFlash('success', "Customer record for '{$full_name}' updated successfully.");
                header("Location: view_customers.php");
                exit();
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Database error: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Customer Profile</h1>
        <p class="page-subtitle">Update contact info, address, or ID document</p>
    </div>
    <div>
        <a href="view_customers.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Directory</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 850px;">
    <div class="form-header">
        <h3 class="form-title">Customer Details</h3>
    </div>
    
    <form action="edit_customer.php?id=<?= $customerId ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Full Name -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?= escape($customer['full_name']) ?>" required>
                    </div>
                </div>

                <!-- Gender -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="Male" <?= ($customer['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($customer['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($customer['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- DOB -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" value="<?= escape($customer['dob']) ?>">
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= escape($customer['phone']) ?>" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= escape($customer['email']) ?>">
                    </div>
                </div>

                <!-- ID Proof Type -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="id_proof_type">Identity Proof Type <span class="required">*</span></label>
                        <select id="id_proof_type" name="id_proof_type" class="form-select" required>
                            <option value="Aadhar" <?= ($customer['id_proof_type'] === 'Aadhar') ? 'selected' : '' ?>>Aadhar Card</option>
                            <option value="PAN" <?= ($customer['id_proof_type'] === 'PAN') ? 'selected' : '' ?>>PAN Card</option>
                            <option value="Passport" <?= ($customer['id_proof_type'] === 'Passport') ? 'selected' : '' ?>>Passport</option>
                            <option value="Driving License" <?= ($customer['id_proof_type'] === 'Driving License') ? 'selected' : '' ?>>Driving License</option>
                        </select>
                    </div>
                </div>

                <!-- ID Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="id_number">ID Number <span class="required">*</span></label>
                        <input type="text" id="id_number" name="id_number" class="form-control" value="<?= escape($customer['id_number']) ?>" required>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="address">Street Address</label>
                        <textarea id="address" name="address" class="form-textarea"><?= escape($customer['address']) ?></textarea>
                    </div>
                </div>

                <!-- City -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?= escape($customer['city']) ?>">
                    </div>
                </div>

                <!-- State -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="state">State</label>
                        <input type="text" id="state" name="state" class="form-control" value="<?= escape($customer['state']) ?>">
                    </div>
                </div>

                <!-- Country -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="<?= escape($customer['country']) ?>">
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="customer_photo">Update Photograph</label>
                        <input type="file" id="customer_photo" name="customer_photo" class="form-control" accept="image/*" data-preview="customerPhotoPreview">
                        <span class="form-help">Leave empty to retain current photograph.</span>
                        <?php if (!empty($customer['photo']) && file_exists(__DIR__ . '/uploads/customer/' . $customer['photo'])): ?>
                            <img id="customerPhotoPreview" src="uploads/customer/<?= escape($customer['photo']) ?>" class="file-upload-preview" style="display:block;" alt="Customer Photo">
                        <?php else: ?>
                            <img id="customerPhotoPreview" class="file-upload-preview" alt="Customer Photo">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_customers.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
