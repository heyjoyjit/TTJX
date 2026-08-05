<?php
// admin/packages/delete_highlight.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    $id = intval($_POST['id']);
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM package_highlights WHERE id = ?");
    $stmt->execute([$id]);
    $response['success'] = true;
    $response['message'] = 'Highlight deleted';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
