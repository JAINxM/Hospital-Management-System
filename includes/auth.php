<?php
// ============================================================
// AUTHENTICATION & SESSION MANAGEMENT
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Enforce authentication for protected pages
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}

/**
 * Get current logged in user array from session
 */
function getLoggedInUser(): array {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'User',
        'full_name' => $_SESSION['full_name'] ?? 'System User',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'staff',
    ];
}

/**
 * Generate CSRF Token for form security
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify submitted CSRF Token
 */
function verifyCsrfToken(?string $token): bool {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
