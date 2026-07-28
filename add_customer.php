<?php
// ============================================================
// ADD CUSTOMER MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Add New Customer';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

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
        setFlash('danger', 'Please enter customer full name, phone number, and ID proof number.');
    } else {
        try {
            // Check if phone number already registered
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE phone = :phone");
            $checkStmt->execute([':phone' => $phone]);
            if ($checkStmt->fetchColumn() > 0) {
                setFlash('danger', "Customer with phone number '{$phone}' is already registered.");
            } else {
                $photoName = null;
                if (isset($_FILES['customer_photo']) && $_FILES['customer_photo']['error'] === UPLOAD_ERR_OK) {
                    $photoName = uploadFile($_FILES['customer_photo'], 'customer');
                }

                $insertStmt = $pdo->prepare("INSERT INTO customers (full_name, gender, dob, phone, email, address, city, state, country, id_proof_type, id_number, photo) 
                                             VALUES (:full_name, :gender, :dob, :phone, :email, :address, :city, :state, :country, :id_proof_type, :id_number, :photo)");
                $insertStmt->execute([
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
                    ':photo' => $photoName
                ]);

                setFlash('success', "Customer '{$full_name}' registered successfully!");
                header("Location: view_customers.php");
                exit();
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Database error adding customer: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Register New Customer</h1>
        <p class="page-subtitle">Add guest profile and identity records into system</p>
    </div>
    <div>
        <a href="view_customers.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Customers</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 850px;">
    <div class="form-header">
        <h3 class="form-title">Customer Information</h3>
    </div>
    
    <form action="add_customer.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Full Name -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>
                </div>

                <!-- Gender -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control">
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. guest@example.com">
                    </div>
                </div>

                <!-- Identity Proof Type -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="id_proof_type">Identity Proof Type <span class="required">*</span></label>
                        <select id="id_proof_type" name="id_proof_type" class="form-select" required>
                            <option value="Aadhar">Aadhar Card</option>
                            <option value="PAN">PAN Card</option>
                            <option value="Passport">Passport</option>
                            <option value="Driving License">Driving License</option>
                        </select>
                    </div>
                </div>

                <!-- Identity Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="id_number">ID Number <span class="required">*</span></label>
                        <input type="text" id="id_number" name="id_number" class="form-control" placeholder="Enter ID Document Number" required>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="address">Street Address</label>
                        <textarea id="address" name="address" class="form-textarea" placeholder="Enter residential address"></textarea>
                    </div>
                </div>

                <!-- City -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" placeholder="e.g. New Delhi">
                    </div>
                </div>

                <!-- State -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="state">State</label>
                        <input type="text" id="state" name="state" class="form-control" placeholder="e.g. Delhi">
                    </div>
                </div>

                <!-- Country -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="India">
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="customer_photo">Customer Photograph</label>
                        <input type="file" id="customer_photo" name="customer_photo" class="form-control" accept="image/*" data-preview="customerPhotoPreview">
                        <span class="form-help">Supported formats: JPG, PNG. Max size 5MB.</span>
                        <img id="customerPhotoPreview" class="file-upload-preview" alt="Customer Photo Preview">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_customers.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Customer Profile</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
