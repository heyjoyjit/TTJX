<?php
// hotel/profile/rooms_delete_gallery.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED

requireHotelOwner();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF');

    $id = intval($_POST['id']);
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT media FROM room_galleries WHERE id = ?");
    $stmt->execute([$id]);
    $g = $stmt->fetch();
    if (!$g) throw new Exception('Gallery item not found');

    $path = BASE_PATH . 'uploads/hotels/gallery/' . $g['media'];
    if (file_exists($path) && is_file($path)) unlink($path);

    $stmt = $db->prepare("DELETE FROM room_galleries WHERE id = ?");
    $stmt->execute([$id]);
    $response['success'] = true;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
