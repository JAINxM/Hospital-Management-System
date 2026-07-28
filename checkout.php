<?php
// ============================================================
// CHECK-OUT & BILLING PROCESSING MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Process Customer Check-Out';
$extra_css = ['forms.css'];
$extra_js = ['bill.js'];

include __DIR__ . '/includes/header.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);

// Fetch all currently checked in bookings
$checkedInBookings = $pdo->query("SELECT b.*, c.full_name as customer_name, r.room_number, r.room_type 
                                  FROM bookings b 
                                  JOIN customers c ON b.customer_id = c.id 
                                  JOIN rooms r ON b.room_id = r.id 
                                  WHERE b.status = 'Checked In' 
                                  ORDER BY b.id DESC")->fetchAll();

$selectedBooking = null;
$stayDays = 1;
$roomCharges = 0;

if ($bookingId > 0) {
    $stmt = $pdo->prepare("SELECT b.*, c.full_name as customer_name, c.phone as customer_phone, r.room_number, r.room_type, r.price_per_night 
                           FROM bookings b 
                           JOIN customers c ON b.customer_id = c.id 
                           JOIN rooms r ON b.room_id = r.id 
                           WHERE b.id = :id AND b.status = 'Checked In'");
    $stmt->execute([':id' => $bookingId]);
    $selectedBooking = $stmt->fetch();

    if ($selectedBooking) {
        $stayDays = calculateDays($selectedBooking['check_in_date'], date('Y-m-d'));
        $roomCharges = $stayDays * (float)$selectedBooking['price_per_night'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postBookingId = (int)($_POST['booking_id'] ?? 0);
    $extra_food = (float)($_POST['extra_food'] ?? 0);
    $extra_laundry = (float)($_POST['extra_laundry'] ?? 0);
    $extra_minibar = (float)($_POST['extra_minibar'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        setFlash('danger', 'Invalid CSRF token.');
    } elseif ($postBookingId <= 0) {
        setFlash('danger', 'Please select an occupied room booking.');
    } else {
        try {
            // Fetch booking details
            $bStmt = $pdo->prepare("SELECT b.*, r.price_per_night FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = :id AND b.status = 'Checked In'");
            $bStmt->execute([':id' => $postBookingId]);
            $bData = $bStmt->fetch();

            if (!$bData) {
                setFlash('danger', 'Selected booking is not currently checked in.');
            } else {
                $pdo->beginTransaction();

                // Compute billing amounts
                $actualCheckOutDate = date('Y-m-d');
                $days = calculateDays($bData['check_in_date'], $actualCheckOutDate);
                $calculatedRoomCharges = $days * (float)$bData['price_per_night'];

                $subtotal = $calculatedRoomCharges + $extra_food + $extra_laundry + $extra_minibar;
                $gstPercent = 12.00; // 12% GST
                $gstAmount = ($subtotal * $gstPercent) / 100;
                $grandTotal = max(0, ($subtotal + $gstAmount) - $discount);

                // Advance payment deduction
                $paidAmount = min($grandTotal, (float)$bData['advance_payment'] + ($grandTotal - (float)$bData['advance_payment']));
                $dueAmount = max(0, $grandTotal - $paidAmount);
                $invoiceStatus = ($dueAmount <= 0) ? 'Paid' : 'Partial';

                $invoiceNumber = 'HMS-INV-' . rand(1000, 9999) . rand(10, 99);

                // 1. Insert Invoice
                $invStmt = $pdo->prepare("INSERT INTO invoices (invoice_number, booking_id, room_charges, extra_food, extra_laundry, extra_minibar, subtotal, gst_percent, gst_amount, discount, grand_total, paid_amount, due_amount, status) 
                                          VALUES (:inv_num, :booking_id, :room_charges, :extra_food, :extra_laundry, :extra_minibar, :subtotal, :gst_percent, :gst_amount, :discount, :grand_total, :paid_amount, :due_amount, :status)");
                $invStmt->execute([
                    ':inv_num' => $invoiceNumber,
                    ':booking_id' => $postBookingId,
                    ':room_charges' => $calculatedRoomCharges,
                    ':extra_food' => $extra_food,
                    ':extra_laundry' => $extra_laundry,
                    ':extra_minibar' => $extra_minibar,
                    ':subtotal' => $subtotal,
                    ':gst_percent' => $gstPercent,
                    ':gst_amount' => $gstAmount,
                    ':discount' => $discount,
                    ':grand_total' => $grandTotal,
                    ':paid_amount' => $paidAmount,
                    ':due_amount' => $dueAmount,
                    ':status' => $invoiceStatus
                ]);

                // 2. Update Booking status to Completed
                $uBk = $pdo->prepare("UPDATE bookings SET status = 'Completed', actual_check_out_time = NOW() WHERE id = :id");
                $uBk->execute([':id' => $postBookingId]);

                // 3. Update Room status to Available
                $uRm = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = :room_id");
                $uRm->execute([':room_id' => $bData['room_id']]);

                $pdo->commit();

                setFlash('success', "Check-out completed and Invoice {$invoiceNumber} generated!");
                header("Location: bill.php?booking_id={$postBookingId}");
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Check-out error: ' . $e->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Process Guest Check-Out</h1>
        <p class="page-subtitle">Calculate final stay charges, add extra services, and issue invoice</p>
    </div>
    <div>
        <a href="view_bookings.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Bookings</span>
        </a>
    </div>
</div>

<div class="form-card fade-in" style="max-width: 850px;">
    <div class="form-header">
        <h3 class="form-title">Check-Out Billing Counter</h3>
    </div>
    
    <form action="checkout.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-body">
            <div class="form-grid">
                <!-- Select Occupied Booking -->
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="booking_id">Select Checked-In Guest <span class="required">*</span></label>
                        <select id="booking_id" name="booking_id" class="form-select" onchange="window.location.href='checkout.php?booking_id=' + this.value" required>
                            <option value="">-- Choose Checked-In Booking --</option>
                            <?php foreach ($checkedInBookings as $cb): ?>
                                <option value="<?= $cb['id'] ?>" <?= ($cb['id'] == $bookingId) ? 'selected' : '' ?>>
                                    Room <?= escape($cb['room_number']) ?> - Guest: <?= escape($cb['customer_name']) ?> (<?= escape($cb['booking_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($selectedBooking): ?>
                    <!-- Room & Stay Details Summary -->
                    <div class="col-12" style="background: var(--primary-light); padding: 1rem 1.25rem; border-radius: var(--radius-md); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong>Room <?= escape($selectedBooking['room_number']) ?> (<?= escape($selectedBooking['room_type']) ?>)</strong><br>
                            <span style="font-size:0.85rem; color:var(--text-secondary);">Rate: ₹<?= number_format($selectedBooking['price_per_night'], 2) ?>/night &bull; Checked In: <?= date('d M Y', strtotime($selectedBooking['check_in_date'])) ?></span>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:0.8rem; text-transform:uppercase; font-weight:700; color:var(--primary);">Calculated Stay</span><br>
                            <strong style="font-size:1.1rem; color:var(--primary);"><?= $stayDays ?> <?= ($stayDays === 1) ? 'Night' : 'Nights' ?></strong>
                        </div>
                    </div>

                    <!-- Room Charges (ReadOnly) -->
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label" for="billRoomCharges">Room Charges (₹)</label>
                            <input type="text" id="billRoomCharges" name="room_charges" class="form-control" value="<?= number_format($roomCharges, 2, '.', '') ?>" readonly style="font-weight:700;">
                        </div>
                    </div>

                    <!-- Advance Paid -->
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Advance Already Paid (₹)</label>
                            <input type="text" class="form-control" value="<?= number_format($selectedBooking['advance_payment'], 2) ?>" readonly style="color:var(--success); font-weight:700;">
                        </div>
                    </div>

                    <!-- Extra Food -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="billExtraFood">Food & Beverage (₹)</label>
                            <input type="number" step="0.01" id="billExtraFood" name="extra_food" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <!-- Extra Laundry -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="billExtraLaundry">Laundry Charges (₹)</label>
                            <input type="number" step="0.01" id="billExtraLaundry" name="extra_laundry" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <!-- Extra Mini Bar -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="billExtraMinibar">Mini Bar Charges (₹)</label>
                            <input type="number" step="0.01" id="billExtraMinibar" name="extra_minibar" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <!-- Subtotal -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Subtotal Amount (₹)</label>
                            <input type="text" id="billSubtotal" class="form-control" readonly style="font-weight:600;">
                        </div>
                    </div>

                    <!-- GST 12% -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">GST 12% Tax (₹)</label>
                            <input type="text" id="billGstAmount" class="form-control" readonly style="font-weight:600;">
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="billDiscount">Special Discount (₹)</label>
                            <input type="number" step="0.01" id="billDiscount" name="discount" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="col-12" style="background: #0f172a; color:#ffffff; padding: 1.25rem; border-radius: var(--radius-md); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span style="text-transform:uppercase; font-size:0.8rem; letter-spacing:0.05em; color:var(--text-muted);">Grand Total Invoice Payable</span>
                        </div>
                        <div>
                            <input type="text" id="billGrandTotal" class="form-control" style="background:transparent; border:none; color:#38bdf8; font-size:1.75rem; font-weight:800; text-align:right; width:220px;" readonly value="0.00">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-footer">
            <a href="view_bookings.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" <?= (!$selectedBooking) ? 'disabled' : '' ?>>
                Generate Invoice & Complete Check-Out
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
