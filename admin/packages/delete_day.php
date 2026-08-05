<?php
// admin/packages/delete.php
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

    // Fetch associated files to delete
    $stmt = $db->prepare("SELECT details_pdf FROM package_details WHERE id = ?");
    $stmt->execute([$id]);
    $detail = $stmt->fetch();

    if ($detail && $detail['details_pdf']) {
        $pdfPath = BASE_PATH . 'uploads/packages/pdfs/' . $detail['details_pdf'];
        if (file_exists($pdfPath) && is_file($pdfPath)) unlink($pdfPath);
    }

    // Delete gallery media files
    $stmt = $db->prepare("SELECT media FROM package_galleries WHERE package_detail_id = ?");
    $stmt->execute([$id]);
    $galleries = $stmt->fetchAll();

    foreach ($galleries as $g) {
        $file = BASE_PATH . 'uploads/packages/gallery/' . $g['media'];
        if (file_exists($file) && is_file($file)) unlink($file);
    }

    // Delete the package detail (cascades will remove highlights, guidelines, days, and gallery db records)
    $stmt = $db->prepare("DELETE FROM package_details WHERE id = ?");
    $stmt->execute([$id]);

    $response['success'] = true;
    $response['message'] = 'Package deleted successfully';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
