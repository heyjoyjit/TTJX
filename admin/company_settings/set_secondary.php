<?php
// admin/company_settings/set_secondary.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $target_id = intval($_POST['id']);

    try {
        $pdo->beginTransaction();

        // Remove secondary status from all addresses
        $pdo->query("UPDATE company_addresses SET is_secondary = 0");

        // Set the selected address as secondary
        $stmt = $pdo->prepare("UPDATE company_addresses SET is_secondary = 1 WHERE id = ?");
        $stmt->execute([$target_id]);

        $pdo->commit();
        header("Location: index.php?success=Secondary Address Updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error updating secondary address: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
