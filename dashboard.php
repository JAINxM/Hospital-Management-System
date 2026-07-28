<?php
// ============================================================
// ADMIN DASHBOARD MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Dashboard Overview';
$extra_css = ['dashboard.css', 'tables.css'];

include __DIR__ . '/includes/header.php';

// Fetch Real-time Metrics
try {
    // 1. Rooms Overview
    $totalRooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    $availableRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Available'")->fetchColumn();
    $occupiedRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Occupied'")->fetchColumn();
    $bookedRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Booked'")->fetchColumn();

    // 2. Today's Operations
    $todayCheckins = $pdo->query("SELECT COUNT(*) FROM bookings WHERE (DATE(actual_check_in_time) = CURRENT_DATE() OR check_in_date = CURRENT_DATE()) AND status = 'Checked In'")->fetchColumn();
    $todayCheckouts = $pdo->query("SELECT COUNT(*) FROM bookings WHERE (DATE(actual_check_out_time) = CURRENT_DATE() OR check_out_date = CURRENT_DATE()) AND status = 'Completed'")->fetchColumn();

    // 3. Customer & Booking Totals
    $totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    // 4. Financial Revenues
    $revenueToday = $pdo->query("SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE DATE(created_at) = CURRENT_DATE()")->fetchColumn();
    $revenueMonth = $pdo->query("SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();

    // 5. Recent Bookings List
    $recentStmt = $pdo->query("SELECT b.*, c.full_name as customer_name, c.phone as customer_phone, r.room_number, r.room_type 
                               FROM bookings b 
                               JOIN customers c ON b.customer_id = c.id 
                               JOIN rooms r ON b.room_id = r.id 
                               ORDER BY b.id DESC LIMIT 8");
    $recentBookings = $recentStmt->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading metrics: " . escape($e->getMessage()) . "</div>";
    $totalRooms = $availableRooms = $occupiedRooms = $bookedRooms = 0;
    $todayCheckins = $todayCheckouts = $totalCustomers = $totalBookings = 0;
    $revenueToday = $revenueMonth = 0;
    $recentBookings = [];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Welcome Back, <?= escape($_SESSION['full_name'] ?? 'Admin') ?>!</h1>
        <p class="page-subtitle">Here is the real-time operational status of Grand Royale Hotel today.</p>
    </div>
    <div>
        <a href="booking.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ New Reservation</span>
        </a>
    </div>
</div>

<!-- KPI Metric Cards Grid (10 Cards) -->
<div class="dashboard-metrics-grid fade-in">
    <!-- Total Rooms -->
    <div class="metric-card metric-primary">
        <div class="metric-info">
            <span class="metric-label">Total Rooms</span>
            <span class="metric-value"><?= number_format($totalRooms) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
        </div>
    </div>

    <!-- Available Rooms -->
    <div class="metric-card metric-success">
        <div class="metric-info">
            <span class="metric-label">Available Rooms</span>
            <span class="metric-value"><?= number_format($availableRooms) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
    </div>

    <!-- Occupied Rooms -->
    <div class="metric-card metric-info-theme">
        <div class="metric-info">
            <span class="metric-label">Occupied Rooms</span>
            <span class="metric-value"><?= number_format($occupiedRooms) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
    </div>

    <!-- Booked Rooms -->
    <div class="metric-card metric-warning">
        <div class="metric-info">
            <span class="metric-label">Booked Rooms</span>
            <span class="metric-value"><?= number_format($bookedRooms) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
    </div>

    <!-- Today's Check-ins -->
    <div class="metric-card metric-success">
        <div class="metric-info">
            <span class="metric-label">Today's Check-ins</span>
            <span class="metric-value"><?= number_format($todayCheckins) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
        </div>
    </div>

    <!-- Today's Check-outs -->
    <div class="metric-card metric-danger">
        <div class="metric-info">
            <span class="metric-label">Today's Check-outs</span>
            <span class="metric-value"><?= number_format($todayCheckouts) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="metric-card metric-primary">
        <div class="metric-info">
            <span class="metric-label">Total Customers</span>
            <span class="metric-value"><?= number_format($totalCustomers) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="metric-card metric-warning">
        <div class="metric-info">
            <span class="metric-label">Total Bookings</span>
            <span class="metric-value"><?= number_format($totalBookings) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
    </div>

    <!-- Revenue Today -->
    <div class="metric-card metric-success">
        <div class="metric-info">
            <span class="metric-label">Revenue Today</span>
            <span class="metric-value"><?= formatCurrency($revenueToday) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="metric-card metric-primary">
        <div class="metric-info">
            <span class="metric-label">Revenue Month</span>
            <span class="metric-value"><?= formatCurrency($revenueMonth) ?></span>
        </div>
        <div class="metric-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="dashboard-section fade-in">
    <div class="section-header">
        <h3 class="section-title">Quick Operations</h3>
    </div>
    <div class="quick-actions-grid">
        <a href="booking.php" class="quick-action-btn">
            <div class="action-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </div>
            <span>New Booking</span>
        </a>

        <a href="checkin.php" class="quick-action-btn">
            <div class="action-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
            </div>
            <span>Process Check-In</span>
        </a>

        <a href="checkout.php" class="quick-action-btn">
            <div class="action-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </div>
            <span>Process Check-Out</span>
        </a>

        <a href="add_room.php" class="quick-action-btn">
            <div class="action-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            </div>
            <span>Add New Room</span>
        </a>

        <a href="add_customer.php" class="quick-action-btn">
            <div class="action-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            </div>
            <span>Add Customer</span>
        </a>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="table-card fade-in">
    <div class="table-toolbar">
        <h3 class="section-title">Recent Reservations</h3>
        <a href="view_bookings.php" class="btn btn-secondary btn-sm">View All Bookings &rarr;</a>
    </div>
    
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 2rem; color: var(--text-muted);">
                            No recent bookings found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td><strong><?= escape($b['booking_number']) ?></strong></td>
                            <td>
                                <div><strong><?= escape($b['customer_name']) ?></strong></div>
                                <div style="font-size:0.775rem; color:var(--text-muted);"><?= escape($b['customer_phone']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-primary">Room <?= escape($b['room_number']) ?></span>
                                <span style="font-size:0.785rem; color:var(--text-muted);">(<?= escape($b['room_type']) ?>)</span>
                            </td>
                            <td><?= date('d M Y', strtotime($b['check_in_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($b['check_out_date'])) ?></td>
                            <td><strong><?= formatCurrency($b['booking_amount']) ?></strong></td>
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
                                        <a href="checkin.php?booking_id=<?= $b['id'] ?>" class="action-btn btn-edit" title="Process Check-In">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline></svg>
                                        </a>
                                    <?php elseif ($b['status'] === 'Checked In'): ?>
                                        <a href="checkout.php?booking_id=<?= $b['id'] ?>" class="action-btn btn-bill" title="Process Check-Out & Bill">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        </a>
                                    <?php elseif ($b['status'] === 'Completed'): ?>
                                        <a href="bill.php?booking_id=<?= $b['id'] ?>" class="action-btn" title="View Final Invoice">
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
