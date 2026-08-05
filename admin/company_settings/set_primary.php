<?php
// admin/company_settings/set_primary.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $target_id = intval($_POST['id']);

    try {
        $pdo->beginTransaction();

        // Remove primary status from all addresses
        $pdo->query("UPDATE company_addresses SET is_primary = 0");

        // Set the selected address as primary
        $stmt = $pdo->prepare("UPDATE company_addresses SET is_primary = 1 WHERE id = ?");
        $stmt->execute([$target_id]);

        $pdo->commit();
        header("Location: index.php?success=Primary Address Updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error updating primary address: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
