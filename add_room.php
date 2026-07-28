<?php
// ============================================================
// ADD ROOM MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Add New Room';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = sanitize($_POST['room_number'] ?? '');
    $room_type = sanitize($_POST['room_type'] ?? '');
    $ac_type = sanitize($_POST['ac_type'] ?? '');
    $floor_number = (int)($_POST['floor_number'] ?? 1);
    $price_per_night = (float)($_POST['price_per_night'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'Available');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif (empty($room_number) || empty($room_type) || empty($ac_type) || $price_per_night <= 0) {
        setFlash('danger', 'Please fill in all required room details correctly.');
    } else {
        try {
            // Check if room number already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
            $checkStmt->execute([':room_number' => $room_number]);
            if ($checkStmt->fetchColumn() > 0) {
                setFlash('danger', "Room number '{$room_number}' already exists.");
            } else {
                // Process image upload
                $imageName = null;
                if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
                    $imageName = uploadFile($_FILES['room_image'], 'room');
                }

                $insertStmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, ac_type, floor_number, price_per_night, status, image) 
                                             VALUES (:room_number, :room_type, :ac_type, :floor_number, :price_per_night, :status, :image)");
                $insertStmt->execute([
                    ':room_number' => $room_number,
                    ':room_type' => $room_type,
                    ':ac_type' => $ac_type,
                    ':floor_number' => $floor_number,
                    ':price_per_night' => $price_per_night,
                    ':status' => $status,
                    ':image' => $imageName
                ]);

                setFlash('success', "Room {$room_number} added successfully!");
                header("Location: view_rooms.php");
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
        <h1 class="page-title">Add New Room</h1>
        <p class="page-subtitle">Register a new room into the hotel inventory system</p>
    </div>
    <div>
        <a href="view_rooms.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Rooms</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 800px;">
    <div class="form-header">
        <h3 class="form-title">Room Specifications</h3>
    </div>
    
    <form action="add_room.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Room Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="room_number">Room Number <span class="required">*</span></label>
                        <input type="text" id="room_number" name="room_number" class="form-control" placeholder="e.g. 105" required>
                    </div>
                </div>

                <!-- Floor Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="floor_number">Floor Number <span class="required">*</span></label>
                        <input type="number" id="floor_number" name="floor_number" class="form-control" min="1" max="50" value="1" required>
                    </div>
                </div>

                <!-- Room Type -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="room_type">Room Type <span class="required">*</span></label>
                        <select id="room_type" name="room_type" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="Single">Single</option>
                            <option value="Double">Double</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                </div>

                <!-- AC / Non AC -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="ac_type">AC Specification <span class="required">*</span></label>
                        <select id="ac_type" name="ac_type" class="form-select" required>
                            <option value="AC">AC (Air Conditioned)</option>
                            <option value="Non AC">Non AC</option>
                        </select>
                    </div>
                </div>

                <!-- Price Per Night -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="price_per_night">Price Per Night (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" id="price_per_night" name="price_per_night" class="form-control" placeholder="e.g. 3500.00" required>
                    </div>
                </div>

                <!-- Room Status -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="status">Initial Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Booked">Booked</option>
                            <option value="Occupied">Occupied</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Room Image Upload -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="room_image">Room Photo</label>
                        <input type="file" id="room_image" name="room_image" class="form-control" accept="image/*" data-preview="roomPhotoPreview">
                        <span class="form-help">Supported formats: JPG, PNG, WEBP. Max size 5MB.</span>
                        <img id="roomPhotoPreview" class="file-upload-preview" alt="Room Preview">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_rooms.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Room Record</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
