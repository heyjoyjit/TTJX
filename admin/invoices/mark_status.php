<?php
// admin/invoices/mark_status.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status']; // 'paid', 'cancelled', 'sent'

    // Validate allowed statuses
    if (!in_array($status, ['paid', 'cancelled', 'sent', 'draft'])) {
        die("Invalid Status");
    }

    try {
        $stmt = $pdo->prepare("UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        header("Location: view.php?id=" . $id . "&success=Status Updated");
        exit;
    } catch (PDOException $e) {
        die("Error updating status: " . $e->getMessage());
    }
}
header("Location: index.php");
exit;
