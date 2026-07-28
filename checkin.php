<?php
// ============================================================
// CHECK-IN PROCESSING MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Process Customer Check-In';
$extra_css = ['forms.css'];

include __DIR__ . '/includes/header.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);

// Fetch confirmed bookings eligible for check-in
$pendingBookings = $pdo->query("SELECT b.*, c.full_name as customer_name, c.phone as customer_phone, r.room_number, r.room_type 
                                FROM bookings b 
                                JOIN customers c ON b.customer_id = c.id 
                                JOIN rooms r ON b.room_id = r.id 
                                WHERE b.status = 'Confirmed' 
                                ORDER BY b.check_in_date ASC")->fetchAll();

$selectedBooking = null;
if ($bookingId > 0) {
    $stmt = $pdo->prepare("SELECT b.*, c.full_name as customer_name, c.phone as customer_phone, c.id_proof_type, c.id_number, r.room_number, r.room_type, r.price_per_night 
                           FROM bookings b 
                           JOIN customers c ON b.customer_id = c.id 
                           JOIN rooms r ON b.room_id = r.id 
                           WHERE b.id = :id AND b.status = 'Confirmed'");
    $stmt->execute([':id' => $bookingId]);
    $selectedBooking = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postBookingId = (int)($_POST['booking_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif ($postBookingId <= 0) {
        setFlash('danger', 'Please select a valid booking for check-in.');
    } else {
        try {
            // Fetch booking & room details
            $bStmt = $pdo->prepare("SELECT room_id, booking_number FROM bookings WHERE id = :id AND status = 'Confirmed'");
            $bStmt->execute([':id' => $postBookingId]);
            $bData = $bStmt->fetch();

            if (!$bData) {
                setFlash('danger', 'Booking not found or already checked in.');
            } else {
                $pdo->beginTransaction();

                // 1. Update Booking status to Checked In & log time
                $uBk = $pdo->prepare("UPDATE bookings SET status = 'Checked In', actual_check_in_time = NOW() WHERE id = :id");
                $uBk->execute([':id' => $postBookingId]);

                // 2. Update Room status to Occupied
                $uRm = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = :room_id");
                $uRm->execute([':room_id' => $bData['room_id']]);

                $pdo->commit();

                setFlash('success', "Check-in completed successfully for Booking #{$bData['booking_number']}! Room is now set to Occupied.");
                header("Location: view_bookings.php");
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Check-in error: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Customer Check-In desk</h1>
        <p class="page-subtitle">Verify guest documents and assign keycard to occupied room</p>
    </div>
    <div>
        <a href="view_bookings.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Bookings</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 800px;">
    <div class="form-header">
        <h3 class="form-title">Check-In Desk Verification</h3>
    </div>
    
    <form action="checkin.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Select Booking -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="booking_id">Select Confirmed Reservation <span class="required">*</span></label>
                        <select id="booking_id" name="booking_id" class="form-select" onchange="window.location.href='checkin.php?booking_id=' + this.value" required>
                            <option value="">-- Choose Confirmed Booking --</option>
                            <?php foreach ($pendingBookings as $pb): ?>
                                <option value="<?= $pb['id'] ?>" <?= ($pb['id'] == $bookingId) ? 'selected' : '' ?>>
                                    <?= escape($pb['booking_number']) ?> - Guest: <?= escape($pb['customer_name']) ?> (Room <?= escape($pb['room_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($selectedBooking): ?>
                    <!-- Verification Summary Card -->
                    <div class="col-12" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">Guest Verification Card</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                            <div>
                                <span style="color:var(--text-muted);">Guest Name:</span><br>
                                <strong><?= escape($selectedBooking['customer_name']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);">Phone Number:</span><br>
                                <strong><?= escape($selectedBooking['customer_phone']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);">ID Proof:</span><br>
                                <strong><?= escape($selectedBooking['id_proof_type']) ?> - <?= escape($selectedBooking['id_number']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);">Room Assigned:</span><br>
                                <strong style="color:var(--primary);">Room <?= escape($selectedBooking['room_number']) ?> (<?= escape($selectedBooking['room_type']) ?>)</strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);">Check-In Date:</span><br>
                                <strong><?= date('d M Y', strtotime($selectedBooking['check_in_date'])) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted);">Check-Out Date:</span><br>
                                <strong><?= date('d M Y', strtotime($selectedBooking['check_out_date'])) ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_bookings.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success" <?= (!$selectedBooking) ? 'disabled' : '' ?>>
                Confirm & Check-In Guest
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
