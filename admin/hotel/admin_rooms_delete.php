<?php
// admin/hotel/admin_rooms_delete.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF Security Token.');
    }

    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('Invalid Room ID provided.');
    }

    $db = Database::getInstance()->getConnection();

    // 1. Fetch gallery media files to remove physical files from storage disk
    $stmt = $db->prepare("SELECT media FROM room_galleries WHERE room_id = ?");
    $stmt->execute([$id]);
    $galleries = $stmt->fetchAll();

    foreach ($galleries as $g) {
        if (!empty($g['media'])) {
            $filePath = BASE_PATH . 'uploads/hotel/gallery/' . $g['media'];
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    // 2. Delete the room entry (ON DELETE CASCADE in MySQL removes foreign records in room_amenities and room_galleries)
    $delStmt = $db->prepare("DELETE FROM hotel_rooms WHERE id = ?");
    $delStmt->execute([$id]);

    $response['success'] = true;
    $response['message'] = 'Room profile and all associated gallery media were successfully deleted.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
