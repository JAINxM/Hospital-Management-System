<?php
// ============================================================
// INDEX REDIRECTOR
// ============================================================

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
