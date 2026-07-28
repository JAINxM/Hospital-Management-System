<?php
// ============================================================
// GLOBAL UTILITY FUNCTIONS
// ============================================================

/**
 * Sanitize raw string input
 */
function sanitize(string $input): string {
    return trim(stripslashes($input));
}

/**
 * Escape HTML output for XSS protection
 */
function escape(?string $input): string {
    return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message in session
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Render and consume flash message
 */
function getFlash(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = escape($flash['type']);
        $msg = escape($flash['message']);
        
        $icon = match($type) {
            'success' => '<svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
            'danger' => '<svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
            'warning' => '<svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            default => '<svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };

        return "<div class='alert alert-{$type} fade-in'>
            {$icon}
            <span>{$msg}</span>
            <button type='button' class='alert-close' onclick='this.parentElement.remove();'>&times;</button>
        </div>";
    }
    return '';
}

/**
 * Format currency with INR (₹) symbol
 */
function formatCurrency(float|int|string|null $amount): string {
    $num = (float)($amount ?? 0);
    return '₹ ' . number_format($num, 2, '.', ',');
}

/**
 * Calculate difference in days between two date strings (YYYY-MM-DD)
 */
function calculateDays(string $checkIn, string $checkOut): int {
    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    $diff = $d1->diff($d2);
    $days = $diff->days;
    return ($days > 0) ? $days : 1; // Minimum 1 day stay
}

/**
 * Check if a room is available between two dates
 */
function isRoomAvailable(PDO $pdo, int $roomId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool {
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = :room_id 
            AND status IN ('Confirmed', 'Checked In')
            AND (
                (check_in_date < :check_out AND check_out_date > :check_in)
            )";
    
    if ($excludeBookingId) {
        $sql .= " AND id != :exclude_id";
    }

    $stmt = $pdo->prepare($sql);
    $params = [
        ':room_id' => $roomId,
        ':check_in' => $checkIn,
        ':check_out' => $checkOut,
    ];
    if ($excludeBookingId) {
        $params[':exclude_id'] = $excludeBookingId;
    }

    $stmt->execute($params);
    return ($stmt->fetchColumn() == 0);
}

/**
 * Handle file upload securely
 */
function uploadFile(array $file, string $targetSubdir, array $allowedExts = ['jpg', 'jpeg', 'png', 'webp']): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/' . trim($targetSubdir, '/') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($file['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        return null;
    }

    $newFileName = uniqid($targetSubdir . '_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFileName;
    }

    return null;
}
