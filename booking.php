<?php
// ============================================================
// NEW BOOKING MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Create New Reservation';
$extra_css = ['forms.css'];
$extra_js = ['booking.js'];

include __DIR__ . '/includes/header.php';

// Fetch all registered customers
$customers = $pdo->query("SELECT id, full_name, phone, id_number FROM customers ORDER BY full_name ASC")->fetchAll();

// Fetch available rooms (or all rooms if editing)
$rooms = $pdo->query("SELECT id, room_number, room_type, ac_type, price_per_night, status FROM rooms ORDER BY room_number ASC")->fetchAll();

$preselectedCustomerId = (int)($_GET['customer_id'] ?? 0);
$preselectedRoomId = (int)($_GET['room_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $room_id = (int)($_POST['room_id'] ?? 0);
    $check_in_date = sanitize($_POST['check_in_date'] ?? '');
    $check_out_date = sanitize($_POST['check_out_date'] ?? '');
    $adults = (int)($_POST['adults'] ?? 1);
    $children = (int)($_POST['children'] ?? 0);
    $special_requests = sanitize($_POST['special_requests'] ?? '');
    $advance_payment = (float)($_POST['advance_payment'] ?? 0);
    $payment_mode = sanitize($_POST['payment_mode'] ?? 'Cash');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif ($customer_id <= 0 || $room_id <= 0 || empty($check_in_date) || empty($check_out_date)) {
        setFlash('danger', 'Please select a customer, room, and stay dates.');
    } elseif ($check_out_date <= $check_in_date) {
        setFlash('danger', 'Check-out date must be after check-in date.');
    } else {
        try {
            // Prevent Double Booking Validation
            if (!isRoomAvailable($pdo, $room_id, $check_in_date, $check_out_date)) {
                setFlash('danger', 'Selected room is not available for the specified date range. Please choose different dates or another room.');
            } else {
                // Fetch room price per night
                $roomStmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE id = :id");
                $roomStmt->execute([':id' => $room_id]);
                $pricePerNight = (float)$roomStmt->fetchColumn();

                $days = calculateDays($check_in_date, $check_out_date);
                $booking_amount = $days * $pricePerNight;
                $balance_amount = max(0, $booking_amount - $advance_payment);

                $booking_number = 'HMS-BK-' . rand(1000, 9999) . rand(10, 99);

                $insertStmt = $pdo->prepare("INSERT INTO bookings (booking_number, customer_id, room_id, check_in_date, check_out_date, adults, children, special_requests, booking_amount, advance_payment, balance_amount, payment_mode, status) 
                                             VALUES (:booking_number, :customer_id, :room_id, :check_in_date, :check_out_date, :adults, :children, :special_requests, :booking_amount, :advance_payment, :balance_amount, :payment_mode, 'Confirmed')");
                $insertStmt->execute([
                    ':booking_number' => $booking_number,
                    ':customer_id' => $customer_id,
                    ':room_id' => $room_id,
                    ':check_in_date' => $check_in_date,
                    ':check_out_date' => $check_out_date,
                    ':adults' => $adults,
                    ':children' => $children,
                    ':special_requests' => $special_requests,
                    ':booking_amount' => $booking_amount,
                    ':advance_payment' => $advance_payment,
                    ':balance_amount' => $balance_amount,
                    ':payment_mode' => $payment_mode
                ]);

                // Update Room Status to 'Booked' if check-in is not today or 'Booked' status
                $updateRoom = $pdo->prepare("UPDATE rooms SET status = 'Booked' WHERE id = :id AND status = 'Available'");
                $updateRoom->execute([':id' => $room_id]);

                setFlash('success', "Reservation {$booking_number} created successfully!");
                header("Location: view_bookings.php");
                exit();
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Database error creating booking: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">New Room Reservation</h1>
        <p class="page-subtitle">Book a room for a registered guest</p>
    </div>
    <div>
        <a href="view_bookings.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>View All Bookings</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 900px;">
    <div class="form-header">
        <h3 class="form-title">Booking Parameters</h3>
    </div>
    
    <form action="booking.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Select Customer -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="customer_id">Select Guest / Customer <span class="required">*</span></label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">-- Choose Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($c['id'] == $preselectedCustomerId) ? 'selected' : '' ?>>
                                    <?= escape($c['full_name']) ?> (<?= escape($c['phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-help">Customer not listed? <a href="add_customer.php" target="_blank">+ Register new customer</a></span>
                    </div>
                </div>

                <!-- Select Room -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="bookingRoomSelect">Select Room <span class="required">*</span></label>
                        <select id="bookingRoomSelect" name="room_id" class="form-select" required>
                            <option value="">-- Choose Room --</option>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>" 
                                        data-price="<?= $r['price_per_night'] ?>" 
                                        <?= ($r['id'] == $preselectedRoomId) ? 'selected' : '' ?>>
                                    Room <?= escape($r['room_number']) ?> - <?= escape($r['room_type']) ?> (<?= escape($r['ac_type']) ?>) - ₹<?= number_format($r['price_per_night'], 2) ?>/night [<?= escape($r['status']) ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Check-in Date -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="checkInDate">Check-In Date <span class="required">*</span></label>
                        <input type="date" id="checkInDate" name="check_in_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Check-out Date -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="checkOutDate">Check-Out Date <span class="required">*</span></label>
                        <input type="date" id="checkOutDate" name="check_out_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                </div>

                <!-- Stay Duration Display badge -->
                <div class="col-12" style="background: var(--primary-light); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 600; color: var(--primary);">Calculated Duration of Stay:</span>
                    <span id="totalDaysDisplay" style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">1 Night</span>
                </div>

                <!-- Adults -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="adults">Adults <span class="required">*</span></label>
                        <input type="number" id="adults" name="adults" class="form-control" min="1" max="10" value="1" required>
                    </div>
                </div>

                <!-- Children -->
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label" for="children">Children</label>
                        <input type="number" id="children" name="children" class="form-control" min="0" max="10" value="0">
                    </div>
                </div>

                <!-- Payment Mode -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="payment_mode">Payment Mode</label>
                        <select id="payment_mode" name="payment_mode" class="form-select">
                            <option value="Cash">Cash</option>
                            <option value="Card">Credit/Debit Card</option>
                            <option value="UPI">UPI / Net Banking</option>
                        </select>
                    </div>
                </div>

                <!-- Total Amount (Auto Calculated) -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="bookingAmountInput">Total Booking Amount (₹)</label>
                        <input type="text" id="bookingAmountInput" name="booking_amount" class="form-control" style="font-weight:700; color:var(--primary);" readonly value="0.00">
                    </div>
                </div>

                <!-- Advance Payment -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="advancePaymentInput">Advance Payment (₹)</label>
                        <input type="number" step="0.01" id="advancePaymentInput" name="advance_payment" class="form-control" placeholder="0.00" value="0.00">
                    </div>
                </div>

                <!-- Balance Amount (Auto Calculated) -->
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="balanceAmountInput">Balance Amount (₹)</label>
                        <input type="text" id="balanceAmountInput" class="form-control" style="font-weight:700; color:var(--danger);" readonly value="0.00">
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="special_requests">Special Requests / Remarks</label>
                        <textarea id="special_requests" name="special_requests" class="form-textarea" placeholder="e.g. Airport shuttle required, high floor preference, late arrival"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_bookings.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Confirm & Create Reservation</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
