<?php
// admin/company_settings/delete_address.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Prevent deleting the primary address directly (force them to assign a new primary first)
    $stmt = $pdo->prepare("SELECT is_primary FROM company_addresses WHERE id = ?");
    $stmt->execute([$id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($address && $address['is_primary'] == 1) {
        die("Error: You cannot delete the Primary Address. Set another address as Primary first.");
    }

    try {
        $del = $pdo->prepare("DELETE FROM company_addresses WHERE id = ?");
        $del->execute([$id]);
        header("Location: index.php?success=Address Deleted");
        exit;
    } catch (PDOException $e) {
        die("Error deleting address: " . $e->getMessage());
    }
}
header("Location: index.php");
exit;
