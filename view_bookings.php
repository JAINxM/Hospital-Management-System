<?php
// ============================================================
// VIEW BOOKINGS & GLOBAL SEARCH API MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Check if request is AJAX Global Search API call from navbar
if (isset($_GET['search_api'])) {
    header('Content-Type: application/json');
    $query = sanitize($_GET['search_api']);
    $results = [];

    if (strlen($query) >= 2) {
        $likeQuery = "%{$query}%";

        // 1. Search Customers
        $cStmt = $pdo->prepare("SELECT id, full_name, phone FROM customers WHERE full_name LIKE :q OR phone LIKE :q OR email LIKE :q LIMIT 3");
        $cStmt->execute([':q' => $likeQuery]);
        while ($r = $cStmt->fetch()) {
            $results[] = [
                'type' => 'Customer',
                'type_class' => 'primary',
                'title' => $r['full_name'],
                'subtitle' => 'Phone: ' . $r['phone'],
                'url' => 'view_customers.php?search=' . urlencode($r['full_name'])
            ];
        }

        // 2. Search Rooms
        $rStmt = $pdo->prepare("SELECT id, room_number, room_type, status FROM rooms WHERE room_number LIKE :q LIMIT 3");
        $rStmt->execute([':q' => $likeQuery]);
        while ($r = $rStmt->fetch()) {
            $results[] = [
                'type' => 'Room',
                'type_class' => 'available',
                'title' => 'Room ' . $r['room_number'],
                'subtitle' => $r['room_type'] . ' (' . $r['status'] . ')',
                'url' => 'view_rooms.php?search=' . urlencode($r['room_number'])
            ];
        }

        // 3. Search Bookings
        $bStmt = $pdo->prepare("SELECT b.id, b.booking_number, c.full_name FROM bookings b JOIN customers c ON b.customer_id = c.id WHERE b.booking_number LIKE :q OR c.full_name LIKE :q LIMIT 3");
        $bStmt->execute([':q' => $likeQuery]);
        while ($r = $bStmt->fetch()) {
            $results[] = [
                'type' => 'Booking',
                'type_class' => 'warning',
                'title' => $r['booking_number'],
                'subtitle' => 'Guest: ' . $r['full_name'],
                'url' => 'view_bookings.php?search=' . urlencode($r['booking_number'])
            ];
        }

        // 4. Search Invoices
        $iStmt = $pdo->prepare("SELECT id, invoice_number, grand_total FROM invoices WHERE invoice_number LIKE :q LIMIT 3");
        $iStmt->execute([':q' => $likeQuery]);
        while ($r = $iStmt->fetch()) {
            $results[] = [
                'type' => 'Invoice',
                'type_class' => 'paid',
                'title' => $r['invoice_number'],
                'subtitle' => 'Total: ₹' . number_format($r['grand_total'], 2),
                'url' => 'bill.php?invoice_id=' . $r['id']
            ];
        }
    }

    echo json_encode($results);
    exit();
}

// Normal View Bookings Page Request
$pageTitle = 'All Bookings & Reservations';
$extra_css = ['tables.css', 'forms.css'];

include __DIR__ . '/includes/header.php';

$search = sanitize($_GET['search'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$date_filter = sanitize($_GET['date'] ?? '');

$sql = "SELECT b.*, c.full_name as customer_name, c.phone as customer_phone, r.room_number, r.room_type, r.price_per_night 
        FROM bookings b 
        JOIN customers c ON b.customer_id = c.id 
        JOIN rooms r ON b.room_id = r.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.booking_number LIKE :search OR c.full_name LIKE :search OR c.phone LIKE :search OR r.room_number LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if (!empty($status_filter)) {
    $sql .= " AND b.status = :status";
    $params[':status'] = $status_filter;
}
if (!empty($date_filter)) {
    $sql .= " AND (b.check_in_date = :date OR b.check_out_date = :date)";
    $params[':date'] = $date_filter;
}

$sql .= " ORDER BY b.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash('danger', 'Error loading bookings: ' . $e->getMessage());
    $bookings = [];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Reservations & Stay Directory</h1>
        <p class="page-subtitle">Track, filter, check-in, and manage guest bookings</p>
    </div>
    <div>
        <a href="booking.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ New Booking</span>
        </a>
    </div>
</div>

<div class="table-card fade-in">
    <!-- Filter Toolbar -->
    <form action="view_bookings.php" method="GET" class="table-toolbar">
        <div class="table-search-box">
            <svg class="table-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" placeholder="Search Booking #, Guest, Room..." value="<?= escape($search) ?>">
        </div>

        <div class="table-filters">
            <!-- Filter Status -->
            <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Confirmed" <?= ($status_filter === 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                <option value="Checked In" <?= ($status_filter === 'Checked In') ? 'selected' : '' ?>>Checked In</option>
                <option value="Completed" <?= ($status_filter === 'Completed') ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <!-- Filter Date -->
            <input type="date" name="date" class="form-control" style="width: auto;" value="<?= escape($date_filter) ?>" onchange="this.form.submit()">

            <?php if (!empty($search) || !empty($status_filter) || !empty($date_filter)): ?>
                <a href="view_bookings.php" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer Name</th>
                    <th>Room Assigned</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total / Advance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            No bookings matching criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong><?= escape($b['booking_number']) ?></strong></td>
                            <td>
                                <div><strong><?= escape($b['customer_name']) ?></strong></div>
                                <div style="font-size:0.775rem; color:var(--text-muted);"><?= escape($b['customer_phone']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-primary">Room <?= escape($b['room_number']) ?></span>
                                <div style="font-size:0.775rem; color:var(--text-muted);"><?= escape($b['room_type']) ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($b['check_in_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($b['check_out_date'])) ?></td>
                            <td>
                                <div><strong><?= formatCurrency($b['booking_amount']) ?></strong></div>
                                <div style="font-size:0.775rem; color:var(--success);">Adv: <?= formatCurrency($b['advance_payment']) ?></div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = match($b['status']) {
                                    'Confirmed' => 'badge-confirmed',
                                    'Checked In' => 'badge-checked-in',
                                    'Completed' => 'badge-available',
                                    'Cancelled' => 'badge-cancelled',
                                    default => 'badge-pending'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= escape($b['status']) ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <?php if ($b['status'] === 'Confirmed'): ?>
                                        <a href="checkin.php?booking_id=<?= $b['id'] ?>" class="btn btn-success btn-sm" title="Check-In Guest">
                                            Check-In
                                        </a>
                                    <?php elseif ($b['status'] === 'Checked In'): ?>
                                        <a href="checkout.php?booking_id=<?= $b['id'] ?>" class="btn btn-warning btn-sm" title="Check-Out Guest">
                                            Check-Out
                                        </a>
                                    <?php elseif ($b['status'] === 'Completed'): ?>
                                        <a href="bill.php?booking_id=<?= $b['id'] ?>" class="action-btn btn-bill" title="Print Invoice">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
