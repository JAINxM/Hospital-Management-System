<?php
// ============================================================
// VIEW CUSTOMERS MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Manage Customers';
$extra_css = ['tables.css', 'forms.css'];

include __DIR__ . '/includes/header.php';

$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT * FROM customers WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (full_name LIKE :search OR phone LIKE :search OR email LIKE :search OR id_number LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash('danger', 'Error loading customers: ' . $e->getMessage());
    $customers = [];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Customer Directory</h1>
        <p class="page-subtitle">View and manage registered hotel guests</p>
    </div>
    <div>
        <a href="add_customer.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            <span>+ Add Customer</span>
        </a>
    </div>
</div>

<div class="table-card fade-in">
    <!-- Filter Toolbar -->
    <form action="view_customers.php" method="GET" class="table-toolbar">
        <div class="table-search-box">
            <svg class="table-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" placeholder="Search Name, Phone, ID..." value="<?= escape($search) ?>">
        </div>
        
        <?php if (!empty($search)): ?>
            <a href="view_customers.php" class="btn btn-secondary btn-sm">Clear Search</a>
        <?php endif; ?>
    </form>

    <!-- Customers Table -->
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Full Name</th>
                    <th>Contact Info</th>
                    <th>ID Proof</th>
                    <th>City / State</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            No customers found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>
                                <?php if (!empty($c['photo']) && file_exists(__DIR__ . '/uploads/customer/' . $c['photo'])): ?>
                                    <img src="uploads/customer/<?= escape($c['photo']) ?>" class="table-img" alt="Customer Photo">
                                <?php else: ?>
                                    <div class="table-img" style="background: var(--primary-light); color: var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.1rem;">
                                        <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= escape($c['full_name']) ?></strong>
                                <div style="font-size:0.775rem; color:var(--text-muted);"><?= escape($c['gender']) ?></div>
                            </td>
                            <td>
                                <div><strong><?= escape($c['phone']) ?></strong></div>
                                <div style="font-size:0.775rem; color:var(--text-muted);"><?= escape($c['email'] ?: 'No email') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= escape($c['id_proof_type']) ?></span>
                                <div style="font-size:0.785rem; font-family:monospace; margin-top:2px;"><?= escape($c['id_number']) ?></div>
                            </td>
                            <td><?= escape($c['city'] ?: '-') ?>, <?= escape($c['state'] ?: '-') ?></td>
                            <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="booking.php?customer_id=<?= $c['id'] ?>" class="action-btn btn-bill" title="New Booking for Customer">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    </a>

                                    <a href="edit_customer.php?id=<?= $c['id'] ?>" class="action-btn btn-edit" title="Edit Customer">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>

                                    <a href="delete_customer.php?id=<?= $c['id'] ?>&csrf_token=<?= generateCsrfToken() ?>" 
                                       class="action-btn btn-delete" 
                                       title="Delete Customer" 
                                       onclick="return confirm('Are you sure you want to delete guest record for <?= escape($c['full_name']) ?>?');">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </a>
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
