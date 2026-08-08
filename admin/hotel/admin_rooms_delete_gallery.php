<?php
// admin/hotel/admin_rooms_delete_gallery.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF Security Token');

    $id = intval($_POST['id'] ?? 0);
    if (!$id) throw new Exception('Invalid media item ID');

    $db = Database::getInstance()->getConnection();

    // Fetch file name for disk removal
    $stmt = $db->prepare("SELECT media FROM room_galleries WHERE id = ?");
    $stmt->execute([$id]);
    $g = $stmt->fetch();

    if (!$g) throw new Exception('Gallery item not found');

    $filePath = BASE_PATH . 'uploads/hotel/gallery/' . $g['media'];
    if (file_exists($filePath) && is_file($filePath)) {
        @unlink($filePath);
    }

    $delStmt = $db->prepare("DELETE FROM room_galleries WHERE id = ?");
    $delStmt->execute([$id]);

    $response['success'] = true;
    $response['message'] = 'Media item deleted successfully.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
