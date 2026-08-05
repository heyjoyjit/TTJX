<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];
try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF');
    $id = intval($_POST['id']);
    $db = Database::getInstance()->getConnection();
    // Delete (cascade will remove child records)
    $stmt = $db->prepare("DELETE FROM package_details WHERE id = ?");
    $stmt->execute([$id]);
    $response['success'] = true;
    $response['message'] = 'Package deleted';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
