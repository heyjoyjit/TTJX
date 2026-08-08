<?php
// admin/packages/save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token.');
    }

    $category_id = intval($_POST['category_id'] ?? 0); // This is now the Pricing Category
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $prev_price = !empty($_POST['prev_price']) ? floatval($_POST['prev_price']) : null;

    if (!$category_id || empty($title) || $price <= 0) {
        throw new Exception('Pricing Category, Title, and Base Price are required.');
    }

    // --- Offer System Logic ---
    $offer_type = $_POST['offer_type'] ?? 'none';
    $offer_value = null;
    $upto_limit = null;

    if ($offer_type !== 'none') {
        $offer_value = floatval($_POST['offer_value'] ?? 0);

        $preset = $_POST['upto_limit_preset'] ?? '';
        if ($preset === 'custom') {
            $upto_limit = trim($_POST['upto_limit_custom'] ?? '');
        } else {
            $upto_limit = $preset;
        }

        if ($offer_value <= 0 || empty($upto_limit)) {
            throw new Exception('If an offer is active, the Offer Value and Limit must be provided.');
        }
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Handle PDF Upload
    $pdfName = null;
    if (isset($_FILES['details_pdf']) && $_FILES['details_pdf']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['details_pdf'];
        $allowedDocs = ['application/pdf'];
        if (in_array(mime_content_type($file['tmp_name']), $allowedDocs)) {
            $pdfDir = BASE_PATH . 'uploads/packages/pdfs/';
            if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
            $pdfName = uploadFile($file, $pdfDir, $allowedDocs);
        }
    }

    // Insert Core Package Details
    $stmt = $db->prepare("INSERT INTO package_details (package_category_id, title, subtitle, price, offer_type, offer_value, upto_limit, previous_price, details_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $title, $subtitle, $price, $offer_type, $offer_value, $upto_limit, $prev_price, $pdfName]);
    $detailId = $db->lastInsertId();

    // Process Gallery
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $galleryDir = BASE_PATH . 'uploads/packages/gallery/';
        if (!is_dir($galleryDir)) mkdir($galleryDir, 0777, true);
        $files = $_FILES['gallery'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                $mime = mime_content_type($file['tmp_name']);
                $mediaType = strpos($mime, 'video') !== false ? 'video' : (strpos($mime, 'gif') !== false ? 'gif' : 'image');
                $fileName = uploadFile($file, $galleryDir);
                $db->prepare("INSERT INTO package_galleries (package_detail_id, media, media_type, sort_order) VALUES (?, ?, ?, ?)")->execute([$detailId, $fileName, $mediaType, $i]);
            }
        }
    }

    // Process Highlights
    if (isset($_POST['highlight_icon']) && is_array($_POST['highlight_icon'])) {
        foreach ($_POST['highlight_icon'] as $idx => $icon) {
            $text = trim($_POST['highlight_text'][$idx] ?? '');
            if (!empty($icon) && !empty($text)) {
                $db->prepare("INSERT INTO package_highlights (package_detail_id, icon, text, sort_order) VALUES (?, ?, ?, ?)")->execute([$detailId, $icon, $text, $idx]);
            }
        }
    }

    // Process Guidelines
    if (isset($_POST['guideline_icon']) && is_array($_POST['guideline_icon'])) {
        foreach ($_POST['guideline_icon'] as $idx => $icon) {
            $g_title = trim($_POST['guideline_title'][$idx] ?? '');
            $g_desc = trim($_POST['guideline_desc'][$idx] ?? '');
            if (!empty($icon) && !empty($g_title) && !empty($g_desc)) {
                $db->prepare("INSERT INTO package_guidelines (package_detail_id, icon, title, description, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$detailId, $icon, $g_title, $g_desc, $idx]);
            }
        }
    }

    // Process Days
    if (isset($_POST['day_number']) && is_array($_POST['day_number'])) {
        foreach ($_POST['day_number'] as $idx => $dayNum) {
            $d_title = trim($_POST['day_title'][$idx] ?? '');
            $d_desc = trim($_POST['day_desc'][$idx] ?? '');
            if (!empty($d_title) && !empty($d_desc)) {
                $db->prepare("INSERT INTO package_days (package_detail_id, day_number, title, description, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$detailId, intval($dayNum), $d_title, $d_desc, $idx]);
            }
        }
    }

    $db->commit();
    $response['success'] = true;
    $response['message'] = 'Package published successfully!';
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
