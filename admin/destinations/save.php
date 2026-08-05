<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $db = Database::getInstance()->getConnection();

    // Basic fields
    $place_name = trim($_POST['place_name']);
    $banner_title = trim($_POST['banner_title']);
    $banner_subtitle = trim($_POST['banner_subtitle'] ?? '');
    $is_top = isset($_POST['is_top_destination']) ? 1 : 0;
    $top_sort = $is_top ? intval($_POST['top_sort_order'] ?? 0) : null;

    // Upload main media
    if (!isset($_FILES['main_media']) || $_FILES['main_media']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Main media is required');
    }
    $mainFile = $_FILES['main_media'];
    $mainMime = mime_content_type($mainFile['tmp_name']);
    $mainType = 'image';
    if (strpos($mainMime, 'video') !== false) $mainType = 'video';
    elseif (strpos($mainMime, 'gif') !== false) $mainType = 'gif';
    $mainPath = BASE_PATH . 'uploads/destinations/main/';
    $mainName = uploadFile($mainFile, $mainPath);

    // Upload sub media if provided
    $subName = null;
    $subType = null;
    if (isset($_FILES['sub_media']) && $_FILES['sub_media']['error'] === UPLOAD_ERR_OK) {
        $subFile = $_FILES['sub_media'];
        $subMime = mime_content_type($subFile['tmp_name']);
        $subType = 'image';
        if (strpos($subMime, 'video') !== false) $subType = 'video';
        elseif (strpos($subMime, 'gif') !== false) $subType = 'gif';
        $subPath = BASE_PATH . 'uploads/destinations/sub/';
        $subName = uploadFile($subFile, $subPath);
    }

    // Insert destination
    $stmt = $db->prepare("INSERT INTO destinations (place_name, banner_title, banner_subtitle, main_media, main_media_type, sub_media, sub_media_type, is_top_destination, top_sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$place_name, $banner_title, $banner_subtitle, $mainName, $mainType, $subName, $subType, $is_top, $top_sort]);
    $destId = $db->lastInsertId();

    // Process gallery (multiple files)
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
                $stmtGallery = $db->prepare("INSERT INTO destination_galleries (destination_id, media, media_type, sort_order) VALUES (?, ?, ?, ?)");
                $stmtGallery->execute([$destId, $fileName, $mediaType, $i]);
            }
        }
    }

    // Process attachments
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
                    continue; // skip invalid
                }

                // FIXED: Passed $allowedDocs to override the default image/video restriction
                $fileName = uploadFile($file, $attachPath, $allowedDocs);

                $stmtAttach = $db->prepare("INSERT INTO destination_attachments (destination_id, file_name, original_name, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                $stmtAttach->execute([$destId, $fileName, $file['name'], mime_content_type($file['tmp_name']), $file['size']]);
            }
        }
    }

    $response['success'] = true;
    $response['message'] = 'Destination added successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
