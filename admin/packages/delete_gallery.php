<?php
// admin/packages/delete_gallery.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED: Added missing auth.php inclusion

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // CSRF Protection
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('Invalid ID provided');
    }

    $db = Database::getInstance()->getConnection();

    // Fetch the file name
    $stmt = $db->prepare("SELECT media FROM package_galleries WHERE id = ?");
    $stmt->execute([$id]);
    $gallery = $stmt->fetch();

    if (!$gallery) {
        throw new Exception('Gallery item not found');
    }

    // Delete the file from the server
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/packages/gallery/' . $gallery['media'];
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }

    // Delete the record from the database
    $stmt = $db->prepare("DELETE FROM package_galleries WHERE id = ?");
    $stmt->execute([$id]);

    $response['success'] = true;
    $response['message'] = 'Gallery item deleted successfully';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
