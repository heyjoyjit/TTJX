<?php
// admin/destinations/update.php
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
    $place_name = trim($_POST['place_name']);
    $banner_title = trim($_POST['banner_title']);
    $banner_subtitle = trim($_POST['banner_subtitle'] ?? '');
    $is_top = isset($_POST['is_top_destination']) ? 1 : 0;
    $top_sort = $is_top ? intval($_POST['top_sort_order'] ?? 0) : null;

    $db = Database::getInstance()->getConnection();

    // Fetch existing to keep old media if not replaced
    $stmt = $db->prepare("SELECT main_media, sub_media FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();

    // Main media replacement
    $mainName = $old['main_media'];
    if (isset($_FILES['main_media']) && $_FILES['main_media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['main_media'];
        $mime = mime_content_type($file['tmp_name']);
        $type = 'image';
        if (strpos($mime, 'video') !== false) $type = 'video';
        elseif (strpos($mime, 'gif') !== false) $type = 'gif';
        $path = BASE_PATH . 'uploads/destinations/main/';
        $mainName = uploadFile($file, $path);
        // Delete old file
        if ($old['main_media'] && file_exists($path . $old['main_media'])) {
            unlink($path . $old['main_media']);
        }
    }

    // Sub media
    $subName = $old['sub_media'];
    if (isset($_FILES['sub_media']) && $_FILES['sub_media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['sub_media'];
        $mime = mime_content_type($file['tmp_name']);
        $type = 'image';
        if (strpos($mime, 'video') !== false) $type = 'video';
        elseif (strpos($mime, 'gif') !== false) $type = 'gif';
        $path = BASE_PATH . 'uploads/destinations/sub/';
        $subName = uploadFile($file, $path);
        if ($old['sub_media'] && file_exists($path . $old['sub_media'])) {
            unlink($path . $old['sub_media']);
        }
    }

    // Update destination
    $stmt = $db->prepare("UPDATE destinations SET place_name = ?, banner_title = ?, banner_subtitle = ?, main_media = ?, sub_media = ?, is_top_destination = ?, top_sort_order = ? WHERE id = ?");
    $stmt->execute([$place_name, $banner_title, $banner_subtitle, $mainName, $subName, $is_top, $top_sort, $id]);

    // Add new gallery items
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $galleryPath = BASE_PATH . 'uploads/destinations/gallery/';
        $files = $_FILES['gallery'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                $mime = mime_content_type($file['tmp_name']);
                $mediaType = 'image';
                if (strpos($mime, 'video') !== false) $mediaType = 'video';
                elseif (strpos($mime, 'gif') !== false) $mediaType = 'gif';
                $fileName = uploadFile($file, $galleryPath);
                $stmtGall = $db->prepare("INSERT INTO destination_galleries (destination_id, media, media_type, sort_order) VALUES (?, ?, ?, ?)");
                $stmtGall->execute([$id, $fileName, $mediaType, $i]);
            }
        }
    }

    // Add new attachments
    if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
        $attachPath = BASE_PATH . 'uploads/destinations/attachments/';
        $files = $_FILES['attachments'];
        $allowedDocs = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                if (!in_array(mime_content_type($file['tmp_name']), $allowedDocs)) {
                    continue;
                }

                // FIXED: Passed $allowedDocs to override the default image/video restriction
                $fileName = uploadFile($file, $attachPath, $allowedDocs);

                $stmtAtt = $db->prepare("INSERT INTO destination_attachments (destination_id, file_name, original_name, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                $stmtAtt->execute([$id, $fileName, $file['name'], mime_content_type($file['tmp_name']), $file['size']]);
            }
        }
    }

    $response['success'] = true;
    $response['message'] = 'Destination updated successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
