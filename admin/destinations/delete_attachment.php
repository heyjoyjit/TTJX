<?php
// admin/destinations/delete_attachment.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    $id = intval($_POST['id']);
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT file_name FROM destination_attachments WHERE id = ?");
    $stmt->execute([$id]);
    $a = $stmt->fetch();
    if (!$a) throw new Exception('Attachment not found');
    $path = BASE_PATH . 'uploads/destinations/attachments/' . $a['file_name'];
    if (file_exists($path)) unlink($path);
    $stmt = $db->prepare("DELETE FROM destination_attachments WHERE id = ?");
    $stmt->execute([$id]);
    $response['success'] = true;
    $response['message'] = 'Attachment deleted';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
