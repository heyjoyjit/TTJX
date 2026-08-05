<?php
// admin/packages/toggle_status.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE package_details SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    $response['success'] = true;
    $response['message'] = $status ? 'Package is now visible.' : 'Package is now hidden.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
