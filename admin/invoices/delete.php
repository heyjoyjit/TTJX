<?php
// admin/invoices/delete.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // Optional: Prevent deleting 'paid' invoices for accounting compliance
        $check = $pdo->prepare("SELECT status FROM invoices WHERE id = ?");
        $check->execute([$id]);
        $status = $check->fetchColumn();

        if ($status === 'paid') {
            die("Cannot delete an invoice that has already been paid. Please mark as cancelled instead.");
        }

        $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: index.php?success=Invoice deleted successfully");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
header("Location: index.php");
exit;
