<?php
// ============================================================
// BILLING & INVOICE MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Tax Invoice / Bill';
$extra_css = ['bill.css'];
$extra_js = ['bill.js'];

include __DIR__ . '/includes/header.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);
$invoiceId = (int)($_GET['invoice_id'] ?? 0);

if ($bookingId <= 0 && $invoiceId <= 0) {
    setFlash('danger', 'Invoice or Booking ID is required to display bill.');
    header("Location: view_bookings.php");
    exit();
}

try {
    if ($invoiceId > 0) {
        $stmt = $pdo->prepare("SELECT i.*, b.booking_number, b.check_in_date, b.check_out_date, b.adults, b.children, 
                                      c.full_name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address, c.id_proof_type, c.id_number, 
                                      r.room_number, r.room_type, r.price_per_night 
                               FROM invoices i 
                               JOIN bookings b ON i.booking_id = b.id 
                               JOIN customers c ON b.customer_id = c.id 
                               JOIN rooms r ON b.room_id = r.id 
                               WHERE i.id = :id");
        $stmt->execute([':id' => $invoiceId]);
    } else {
        $stmt = $pdo->prepare("SELECT i.*, b.booking_number, b.check_in_date, b.check_out_date, b.adults, b.children, 
                                      c.full_name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address, c.id_proof_type, c.id_number, 
                                      r.room_number, r.room_type, r.price_per_night 
                               FROM invoices i 
                               JOIN bookings b ON i.booking_id = b.id 
                               JOIN customers c ON b.customer_id = c.id 
                               JOIN rooms r ON b.room_id = r.id 
                               WHERE i.booking_id = :b_id 
                               ORDER BY i.id DESC LIMIT 1");
        $stmt->execute([':b_id' => $bookingId]);
    }

    $inv = $stmt->fetch();

    if (!$inv) {
        setFlash('danger', 'Invoice record not found. Check-out may not be completed yet.');
        header("Location: view_bookings.php");
        exit();
    }

    $stayDays = calculateDays($inv['check_in_date'], $inv['check_out_date']);

} catch (PDOException $e) {
    setFlash('danger', 'Error loading invoice: ' . $e->getMessage());
    header("Location: view_bookings.php");
    exit();
}
?>

<div class="invoice-actions-bar">
    <a href="view_bookings.php" class="btn btn-secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        <span>Back to Bookings</span>
    </a>

    <button type="button" id="printInvoiceBtn" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        <span>Print Tax Invoice</span>
    </button>
</div>

<!-- Invoice Template Card -->
<div class="invoice-wrapper fade-in">
    <!-- Invoice Header -->
    <div class="invoice-header">
        <div class="hotel-branding">
            <div class="invoice-hotel-logo">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v5m-6 0h6"></path></svg>
            </div>
            <div class="hotel-details">
                <h2>Grand Royale Hotel & Suites</h2>
                <p>
                    100 Royale Boulevard, Connaught Place<br>
                    New Delhi - 110001, India<br>
                    Phone: +91 11 2345 6789 | Email: billing@grandroyale.com<br>
                    <strong>GSTIN: 07AAAAA0000A1Z5</strong>
                </p>
            </div>
        </div>

        <div class="invoice-meta-top">
            <div class="invoice-badge-title">TAX INVOICE</div>
            <div class="invoice-number-tag">Invoice #: <strong><?= escape($inv['invoice_number']) ?></strong></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                Date: <?= date('d M Y, h:i A', strtotime($inv['created_at'])) ?>
            </div>
        </div>
    </div>

    <!-- Customer & Room Info Grid -->
    <div class="invoice-info-grid">
        <div class="info-block">
            <h4>Billed To (Guest Details)</h4>
            <p>
                <strong><?= escape($inv['customer_name']) ?></strong><br>
                Phone: <?= escape($inv['customer_phone']) ?><br>
                Email: <?= escape($inv['customer_email'] ?: 'N/A') ?><br>
                ID: <?= escape($inv['id_proof_type']) ?> (<?= escape($inv['id_number']) ?>)<br>
                <?= escape($inv['customer_address'] ?: '') ?>
            </p>
        </div>

        <div class="info-block">
            <h4>Stay & Reservation Summary</h4>
            <p>
                Booking Ref: <strong><?= escape($inv['booking_number']) ?></strong><br>
                Room Assigned: <strong>Room <?= escape($inv['room_number']) ?> (<?= escape($inv['room_type']) ?>)</strong><br>
                Check-In: <?= date('d M Y', strtotime($inv['check_in_date'])) ?><br>
                Check-Out: <?= date('d M Y', strtotime($inv['check_out_date'])) ?><br>
                Guests: <?= escape($inv['adults']) ?> Adult(s), <?= escape($inv['children']) ?> Child(ren)
            </p>
        </div>
    </div>

    <!-- Itemized Charges Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center;">Qty / Nights</th>
                <th style="text-align: right;">Unit Rate (₹)</th>
                <th style="text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Room Stay Charges</strong><br>
                    <span style="font-size:0.8rem; color:var(--text-muted);">Room <?= escape($inv['room_number']) ?> - <?= escape($inv['room_type']) ?></span>
                </td>
                <td style="text-align: center;"><?= $stayDays ?></td>
                <td style="text-align: right;"><?= number_format($inv['price_per_night'], 2) ?></td>
                <td style="text-align: right;"><strong><?= number_format($inv['room_charges'], 2) ?></strong></td>
            </tr>

            <?php if ($inv['extra_food'] > 0): ?>
                <tr>
                    <td>Room Service & Dining Charges</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;"><?= number_format($inv['extra_food'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($inv['extra_food'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if ($inv['extra_laundry'] > 0): ?>
                <tr>
                    <td>Laundry & Dry Cleaning Services</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;"><?= number_format($inv['extra_laundry'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($inv['extra_laundry'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if ($inv['extra_minibar'] > 0): ?>
                <tr>
                    <td>Mini Bar Consumables</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;"><?= number_format($inv['extra_minibar'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($inv['extra_minibar'], 2) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Calculation Summary Box -->
    <div class="invoice-summary-box">
        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td><?= formatCurrency($inv['subtotal']) ?></td>
            </tr>
            <tr>
                <td>GST (<?= escape($inv['gst_percent']) ?>%):</td>
                <td><?= formatCurrency($inv['gst_amount']) ?></td>
            </tr>
            <?php if ($inv['discount'] > 0): ?>
                <tr>
                    <td style="color:var(--danger);">Discount:</td>
                    <td style="color:var(--danger);">-<?= formatCurrency($inv['discount']) ?></td>
                </tr>
            <?php endif; ?>
            <tr class="grand-total-row">
                <td>Grand Total:</td>
                <td><?= formatCurrency($inv['grand_total']) ?></td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td style="color:var(--success);"><?= formatCurrency($inv['paid_amount']) ?></td>
            </tr>
            <tr>
                <td>Balance Due:</td>
                <td><?= formatCurrency($inv['due_amount']) ?></td>
            </tr>
            <tr>
                <td>Payment Status:</td>
                <td>
                    <span class="badge <?= ($inv['status'] === 'Paid') ? 'badge-paid' : 'badge-unpaid' ?>">
                        <?= escape($inv['status']) ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Invoice Footer Notes -->
    <div class="invoice-footer-note">
        <p>Thank you for staying with <strong>Grand Royale Hotel & Suites</strong>!</p>
        <p style="margin-top: 0.25rem;">This is a computer-generated tax invoice and requires no physical signature.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
