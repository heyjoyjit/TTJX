<?php
// admin/packages/update.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // Included missing auth.php

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $id = intval($_POST['id']);
    $category_id = intval($_POST['category_id']);
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $price = floatval($_POST['price']);
    $prev_price = !empty($_POST['prev_price']) ? floatval($_POST['prev_price']) : null;

    if (!$id || !$category_id || empty($title) || $price <= 0) {
        throw new Exception('Missing required fields.');
    }

    // --- Offer System Logic ---
    $offer_type = $_POST['offer_type'] ?? 'none';
    $offer_value = null;
    $upto_limit = null;

    if ($offer_type !== 'none') {
        $offer_value = floatval($_POST['offer_value'] ?? 0);
        $preset = $_POST['upto_limit_preset'] ?? '';

        $upto_limit = ($preset === 'custom') ? trim($_POST['upto_limit_custom'] ?? '') : $preset;

        if ($offer_value <= 0 || empty($upto_limit)) {
            throw new Exception('If an offer is active, the Offer Value and Limit must be provided.');
        }
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT details_pdf FROM package_details WHERE id = ?");
    $stmt->execute([$id]);
    $oldPdf = $stmt->fetchColumn();
    $pdfName = $oldPdf;

    if (isset($_FILES['details_pdf']) && $_FILES['details_pdf']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['details_pdf'];
        $allowedDocs = ['application/pdf'];
        if (in_array(mime_content_type($file['tmp_name']), $allowedDocs)) {
            $pdfDir = BASE_PATH . 'uploads/packages/pdfs/';
            if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
            $pdfName = uploadFile($file, $pdfDir, $allowedDocs);
            if ($oldPdf && file_exists($pdfDir . $oldPdf) && is_file($pdfDir . $oldPdf)) unlink($pdfDir . $oldPdf);
        }
    }

    // Update Core Package Details
    $stmt = $db->prepare("UPDATE package_details SET package_category_id=?, title=?, subtitle=?, price=?, offer_type=?, offer_value=?, upto_limit=?, previous_price=?, details_pdf=? WHERE id=?");
    $stmt->execute([$category_id, $title, $subtitle, $price, $offer_type, $offer_value, $upto_limit, $prev_price, $pdfName, $id]);

    // Process Gallery
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $galleryPath = BASE_PATH . 'uploads/packages/gallery/';
        if (!is_dir($galleryPath)) mkdir($galleryPath, 0777, true);
        $files = $_FILES['gallery'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                $mime = mime_content_type($file['tmp_name']);
                $mediaType = strpos($mime, 'video') !== false ? 'video' : (strpos($mime, 'gif') !== false ? 'gif' : 'image');
                $fileName = uploadFile($file, $galleryPath);
                $db->prepare("INSERT INTO package_galleries (package_detail_id, media, media_type, sort_order) VALUES (?, ?, ?, ?)")->execute([$id, $fileName, $mediaType, $i]);
            }
        }
    }

    // Overwrite Highlights
    $db->prepare("DELETE FROM package_highlights WHERE package_detail_id = ?")->execute([$id]);
    if (isset($_POST['highlight_icon']) && is_array($_POST['highlight_icon'])) {
        foreach ($_POST['highlight_icon'] as $i => $icon) {
            if (!empty($icon) && !empty($_POST['highlight_text'][$i])) {
                $db->prepare("INSERT INTO package_highlights (package_detail_id, icon, text, sort_order) VALUES (?, ?, ?, ?)")->execute([$id, trim($icon), trim($_POST['highlight_text'][$i]), $i]);
            }
        }
    }

    // Overwrite Guidelines
    $db->prepare("DELETE FROM package_guidelines WHERE package_detail_id = ?")->execute([$id]);
    if (isset($_POST['guideline_icon']) && is_array($_POST['guideline_icon'])) {
        foreach ($_POST['guideline_icon'] as $i => $icon) {
            if (!empty($icon) && !empty($_POST['guideline_title'][$i]) && !empty($_POST['guideline_desc'][$i])) {
                $db->prepare("INSERT INTO package_guidelines (package_detail_id, icon, title, description, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$id, trim($icon), trim($_POST['guideline_title'][$i]), trim($_POST['guideline_desc'][$i]), $i]);
            }
        }
    }

    // Overwrite Days
    $db->prepare("DELETE FROM package_days WHERE package_detail_id = ?")->execute([$id]);
    if (isset($_POST['day_number']) && is_array($_POST['day_number'])) {
        foreach ($_POST['day_number'] as $i => $dayNum) {
            if (!empty($dayNum) && !empty($_POST['day_title'][$i]) && !empty($_POST['day_desc'][$i])) {
                $db->prepare("INSERT INTO package_days (package_detail_id, day_number, title, description, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$id, intval($dayNum), trim($_POST['day_title'][$i]), trim($_POST['day_desc'][$i]), $i]);
            }
        }
    }

    $db->commit();
    $response['success'] = true;
    $response['message'] = 'Package updated successfully!';
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
