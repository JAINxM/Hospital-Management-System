<?php
// ============================================================
// VIEW ROOMS MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Manage Rooms';
$extra_css = ['tables.css', 'forms.css'];

include __DIR__ . '/includes/header.php';

// Filter & Search Parameters
$search = sanitize($_GET['search'] ?? '');
$type_filter = sanitize($_GET['room_type'] ?? '');
$ac_filter = sanitize($_GET['ac_type'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');

$sql = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND room_number LIKE :search";
    $params[':search'] = "%{$search}%";
}
if (!empty($type_filter)) {
    $sql .= " AND room_type = :room_type";
    $params[':room_type'] = $type_filter;
}
if (!empty($ac_filter)) {
    $sql .= " AND ac_type = :ac_type";
    $params[':ac_type'] = $ac_filter;
}
if (!empty($status_filter)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY floor_number ASC, room_number ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash('danger', 'Error fetching rooms: ' . $e->getMessage());
    $rooms = [];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Room Inventory</h1>
        <p class="page-subtitle">View, search, and manage all hotel rooms</p>
    </div>
    <div>
        <a href="add_room.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Add New Room</span>
        </a>
    </div>
</div>

<div class="table-card fade-in">
    <!-- Filter Toolbar -->
    <form action="view_rooms.php" method="GET" class="table-toolbar">
        <div class="table-search-box">
            <svg class="table-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" placeholder="Search Room Number..." value="<?= escape($search) ?>">
        </div>

        <div class="table-filters">
            <!-- Filter Type -->
            <select name="room_type" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="Single" <?= ($type_filter === 'Single') ? 'selected' : '' ?>>Single</option>
                <option value="Double" <?= ($type_filter === 'Double') ? 'selected' : '' ?>>Double</option>
                <option value="Deluxe" <?= ($type_filter === 'Deluxe') ? 'selected' : '' ?>>Deluxe</option>
                <option value="Suite" <?= ($type_filter === 'Suite') ? 'selected' : '' ?>>Suite</option>
            </select>

            <!-- Filter AC -->
            <select name="ac_type" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All AC Types</option>
                <option value="AC" <?= ($ac_filter === 'AC') ? 'selected' : '' ?>>AC</option>
                <option value="Non AC" <?= ($ac_filter === 'Non AC') ? 'selected' : '' ?>>Non AC</option>
            </select>

            <!-- Filter Status -->
            <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Available" <?= ($status_filter === 'Available') ? 'selected' : '' ?>>Available</option>
                <option value="Booked" <?= ($status_filter === 'Booked') ? 'selected' : '' ?>>Booked</option>
                <option value="Occupied" <?= ($status_filter === 'Occupied') ? 'selected' : '' ?>>Occupied</option>
                <option value="Maintenance" <?= ($status_filter === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
            </select>

            <?php if (!empty($search) || !empty($type_filter) || !empty($ac_filter) || !empty($status_filter)): ?>
                <a href="view_rooms.php" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Rooms Table -->
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Room #</th>
                    <th>Category</th>
                    <th>AC Spec</th>
                    <th>Floor</th>
                    <th>Price / Night</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            No rooms match your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td>
                                <?php if (!empty($room['image']) && file_exists(__DIR__ . '/uploads/room/' . $room['image'])): ?>
                                    <img src="uploads/room/<?= escape($room['image']) ?>" class="table-img" alt="Room Photo">
                                <?php else: ?>
                                    <div class="table-img" style="background: var(--primary-light); color: var(--primary); display:flex; align-items:center; justify-center:center; font-weight:700;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong>Room <?= escape($room['room_number']) ?></strong></td>
                            <td><?= escape($room['room_type']) ?></td>
                            <td>
                                <span class="badge <?= ($room['ac_type'] === 'AC') ? 'badge-confirmed' : 'badge-pending' ?>">
                                    <?= escape($room['ac_type']) ?>
                                </span>
                            </td>
                            <td>Floor <?= escape($room['floor_number']) ?></td>
                            <td><strong><?= formatCurrency($room['price_per_night']) ?></strong></td>
                            <td>
                                <?php
                                $statusBadge = match($room['status']) {
                                    'Available' => 'badge-available',
                                    'Occupied' => 'badge-occupied',
                                    'Booked' => 'badge-booked',
                                    'Maintenance' => 'badge-maintenance',
                                    default => 'badge-pending'
                                };
                                ?>
                                <span class="badge <?= $statusBadge ?>"><?= escape($room['status']) ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit_room.php?id=<?= $room['id'] ?>" class="action-btn btn-edit" title="Edit Room">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>

                                    <a href="delete_room.php?id=<?= $room['id'] ?>&csrf_token=<?= generateCsrfToken() ?>" 
                                       class="action-btn btn-delete" 
                                       title="Delete Room" 
                                       onclick="return confirm('Are you sure you want to delete Room <?= escape($room['room_number']) ?>? This action cannot be undone.');">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </a>

                                    <?php if ($room['status'] === 'Available'): ?>
                                        <a href="booking.php?room_id=<?= $room['id'] ?>" class="action-btn btn-bill" title="Book This Room">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
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
