<?php
// ============================================================
// DELETE CUSTOMER MODULE
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$customerId = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Invalid security token.');
    header("Location: view_customers.php");
    exit();
}

if ($customerId <= 0) {
    setFlash('danger', 'Invalid customer ID.');
    header("Location: view_customers.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute([':id' => $customerId]);
    $customer = $stmt->fetch();

    if ($customer) {
        if (!empty($customer['photo']) && file_exists(__DIR__ . '/uploads/customer/' . $customer['photo'])) {
            @unlink(__DIR__ . '/uploads/customer/' . $customer['photo']);
        }

        $deleteStmt = $pdo->prepare("DELETE FROM customers WHERE id = :id");
        $deleteStmt->execute([':id' => $customerId]);

        setFlash('success', "Customer '{$customer['full_name']}' deleted successfully.");
    } else {
        setFlash('danger', 'Customer not found.');
    }
} catch (PDOException $e) {
    setFlash('danger', 'Error deleting customer: ' . $e->getMessage());
}

header("Location: view_customers.php");
exit();
