<?php
// ============================================================
// DELETE ROOM MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$roomId = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Invalid security token.');
    header("Location: view_rooms.php");
    exit();
}

if ($roomId <= 0) {
    setFlash('danger', 'Invalid room ID.');
    header("Location: view_rooms.php");
    exit();
}

try {
    // Check if room is currently booked or occupied
    $checkBookingStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = :id AND status IN ('Confirmed', 'Checked In')");
    $checkBookingStmt->execute([':id' => $roomId]);

    if ($checkBookingStmt->fetchColumn() > 0) {
        setFlash('danger', 'Cannot delete this room because it currently has an active or confirmed booking.');
        header("Location: view_rooms.php");
        exit();
    }

    // Fetch room for image cleanup
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch();

    if ($room) {
        if (!empty($room['image']) && file_exists(__DIR__ . '/uploads/room/' . $room['image'])) {
            @unlink(__DIR__ . '/uploads/room/' . $room['image']);
        }

        $deleteStmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
        $deleteStmt->execute([':id' => $roomId]);

        setFlash('success', "Room {$room['room_number']} deleted successfully.");
    } else {
        setFlash('danger', 'Room not found.');
    }
} catch (PDOException $e) {
    setFlash('danger', 'Database error deleting room: ' . $e->getMessage());
}

header("Location: view_rooms.php");
exit();
