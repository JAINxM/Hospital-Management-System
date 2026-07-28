<?php
// ============================================================
// EDIT ROOM MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Edit Room Details';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

$roomId = (int)($_GET['id'] ?? 0);
if ($roomId <= 0) {
    setFlash('danger', 'Invalid room ID.');
    header("Location: view_rooms.php");
    exit();
}

// Fetch existing room details
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch();

if (!$room) {
    setFlash('danger', 'Room not found.');
    header("Location: view_rooms.php");
    exit();
}

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
        setFlash('danger', 'Please complete all required fields.');
    } else {
        try {
            // Check if room number already exists elsewhere
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number AND id != :id");
            $checkStmt->execute([':room_number' => $room_number, ':id' => $roomId]);
            if ($checkStmt->fetchColumn() > 0) {
                setFlash('danger', "Room number '{$room_number}' is already assigned to another room.");
            } else {
                $imageName = $room['image'];
                if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
                    $newImage = uploadFile($_FILES['room_image'], 'room');
                    if ($newImage) {
                        // Unlink old image if existed
                        if (!empty($room['image']) && file_exists(__DIR__ . '/uploads/room/' . $room['image'])) {
                            @unlink(__DIR__ . '/uploads/room/' . $room['image']);
                        }
                        $imageName = $newImage;
                    }
                }

                $updateStmt = $pdo->prepare("UPDATE rooms SET room_number = :room_number, room_type = :room_type, ac_type = :ac_type, 
                                             floor_number = :floor_number, price_per_night = :price_per_night, status = :status, image = :image 
                                             WHERE id = :id");
                $updateStmt->execute([
                    ':room_number' => $room_number,
                    ':room_type' => $room_type,
                    ':ac_type' => $ac_type,
                    ':floor_number' => $floor_number,
                    ':price_per_night' => $price_per_night,
                    ':status' => $status,
                    ':image' => $imageName,
                    ':id' => $roomId
                ]);

                setFlash('success', "Room {$room_number} details updated successfully.");
                header("Location: view_rooms.php");
                exit();
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Error updating room: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Room <?= escape($room['room_number']) ?></h1>
        <p class="page-subtitle">Update pricing, type, or maintenance status</p>
    </div>
    <div>
        <a href="view_rooms.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Inventory</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 800px;">
    <div class="form-header">
        <h3 class="form-title">Edit Room Parameters</h3>
    </div>
    
    <form action="edit_room.php?id=<?= $roomId ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Room Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="room_number">Room Number <span class="required">*</span></label>
                        <input type="text" id="room_number" name="room_number" class="form-control" value="<?= escape($room['room_number']) ?>" required>
                    </div>
                </div>

                <!-- Floor Number -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="floor_number">Floor Number <span class="required">*</span></label>
                        <input type="number" id="floor_number" name="floor_number" class="form-control" min="1" max="50" value="<?= escape($room['floor_number']) ?>" required>
                    </div>
                </div>

                <!-- Room Type -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="room_type">Room Category <span class="required">*</span></label>
                        <select id="room_type" name="room_type" class="form-select" required>
                            <option value="Single" <?= ($room['room_type'] === 'Single') ? 'selected' : '' ?>>Single</option>
                            <option value="Double" <?= ($room['room_type'] === 'Double') ? 'selected' : '' ?>>Double</option>
                            <option value="Deluxe" <?= ($room['room_type'] === 'Deluxe') ? 'selected' : '' ?>>Deluxe</option>
                            <option value="Suite" <?= ($room['room_type'] === 'Suite') ? 'selected' : '' ?>>Suite</option>
                        </select>
                    </div>
                </div>

                <!-- AC / Non AC -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="ac_type">AC Specification <span class="required">*</span></label>
                        <select id="ac_type" name="ac_type" class="form-select" required>
                            <option value="AC" <?= ($room['ac_type'] === 'AC') ? 'selected' : '' ?>>AC (Air Conditioned)</option>
                            <option value="Non AC" <?= ($room['ac_type'] === 'Non AC') ? 'selected' : '' ?>>Non AC</option>
                        </select>
                    </div>
                </div>

                <!-- Price Per Night -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="price_per_night">Price Per Night (₹) <span class="required">*</span></label>
                        <input type="number" step="0.01" id="price_per_night" name="price_per_night" class="form-control" value="<?= escape($room['price_per_night']) ?>" required>
                    </div>
                </div>

                <!-- Room Status -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="status">Room Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="Available" <?= ($room['status'] === 'Available') ? 'selected' : '' ?>>Available</option>
                            <option value="Booked" <?= ($room['status'] === 'Booked') ? 'selected' : '' ?>>Booked</option>
                            <option value="Occupied" <?= ($room['status'] === 'Occupied') ? 'selected' : '' ?>>Occupied</option>
                            <option value="Maintenance" <?= ($room['status'] === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="room_image">Update Photo</label>
                        <input type="file" id="room_image" name="room_image" class="form-control" accept="image/*" data-preview="roomPhotoPreview">
                        <span class="form-help">Leave empty to keep existing room image.</span>
                        <?php if (!empty($room['image']) && file_exists(__DIR__ . '/uploads/room/' . $room['image'])): ?>
                            <img id="roomPhotoPreview" src="uploads/room/<?= escape($room['image']) ?>" class="file-upload-preview" style="display:block;" alt="Room Photo">
                        <?php else: ?>
                            <img id="roomPhotoPreview" class="file-upload-preview" alt="Room Photo">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_rooms.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Room Record</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
