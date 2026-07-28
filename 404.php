<?php
// ============================================================
// 404 PAGE NOT FOUND
// ============================================================

require_once __DIR__ . '/includes/db.php';
$pageTitle = '404 Page Not Found';

include __DIR__ . '/includes/header.php';
?>

<div style="text-align: center; padding: 4rem 1rem;" class="fade-in">
    <div style="font-size: 5rem; font-weight: 800; color: var(--primary); line-height: 1;">404</div>
    <h2 style="font-size: 1.75rem; font-weight: 700; margin: 1rem 0 0.5rem 0;">Page Not Found</h2>
    <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 2rem auto;">
        The requested resource or page could not be located on the server. It may have been moved or removed.
    </p>
    <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
