<?php
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

    $category_id = intval($_POST['category_id']);
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;

    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle'] ?? '');
    $price = floatval($_POST['price']);
    $discount = !empty($_POST['discount']) ? floatval($_POST['discount']) : null;
    $previous_price = !empty($_POST['previous_price']) ? floatval($_POST['previous_price']) : null;

    $db = Database::getInstance()->getConnection();

    // Handle PDF upload
    $pdfFileName = null;
    if (isset($_FILES['details_pdf']) && $_FILES['details_pdf']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['details_pdf']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            throw new Exception('Invalid PDF file');
        }
        $pdfPath = BASE_PATH . 'uploads/packages/';
        if (!is_dir($pdfPath)) mkdir($pdfPath, 0755, true);
        $pdfFileName = uploadFile($_FILES['details_pdf'], $pdfPath);
        // If updating, delete old PDF
        if ($package_id) {
            $stmt = $db->prepare("SELECT details_pdf FROM package_details WHERE id = ?");
            $stmt->execute([$package_id]);
            $old = $stmt->fetch();
            if ($old && $old['details_pdf'] && file_exists($pdfPath . $old['details_pdf'])) {
                unlink($pdfPath . $old['details_pdf']);
            }
        }
    } else {
        // If editing, keep existing
        if ($package_id) {
            $stmt = $db->prepare("SELECT details_pdf FROM package_details WHERE id = ?");
            $stmt->execute([$package_id]);
            $old = $stmt->fetch();
            if ($old) $pdfFileName = $old['details_pdf'];
        }
    }

    // Save package detail
    if ($package_id) {
        // Update
        $stmt = $db->prepare("UPDATE package_details SET title=?, subtitle=?, price=?, discount=?, previous_price=?, details_pdf=? WHERE id=?");
        $stmt->execute([$title, $subtitle, $price, $discount, $previous_price, $pdfFileName, $package_id]);
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO package_details (category_id, title, subtitle, price, discount, previous_price, details_pdf) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$category_id, $title, $subtitle, $price, $discount, $previous_price, $pdfFileName]);
        $package_id = $db->lastInsertId();
    }

    // Process Gallery uploads
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $galleryPath = BASE_PATH . 'uploads/packages/gallery/';
        if (!is_dir($galleryPath)) mkdir($galleryPath, 0755, true);
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
                $stmt = $db->prepare("INSERT INTO package_galleries (package_detail_id, media, media_type, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$package_id, $fileName, $mediaType, $i]);
            }
        }
    }

    // Process Highlights
    // First delete existing highlights if updating
    if ($package_id) {
        $stmt = $db->prepare("DELETE FROM package_highlights WHERE package_detail_id = ?");
        $stmt->execute([$package_id]);
    }
    if (isset($_POST['highlights']['icon']) && is_array($_POST['highlights']['icon'])) {
        $icons = $_POST['highlights']['icon'];
        $texts = $_POST['highlights']['text'];
        foreach ($icons as $index => $icon) {
            if (!empty($icon) && !empty($texts[$index])) {
                $text = trim($texts[$index]);
                if (strlen($text) > 150) $text = substr($text, 0, 150);
                $stmt = $db->prepare("INSERT INTO package_highlights (package_detail_id, icon, text) VALUES (?, ?, ?)");
                $stmt->execute([$package_id, $icon, $text]);
            }
        }
    }

    // Process Guidelines
    if ($package_id) {
        $stmt = $db->prepare("DELETE FROM package_guidelines WHERE package_detail_id = ?");
        $stmt->execute([$package_id]);
    }
    if (isset($_POST['guidelines']['icon']) && is_array($_POST['guidelines']['icon'])) {
        $icons = $_POST['guidelines']['icon'];
        $titles = $_POST['guidelines']['title'];
        $descs = $_POST['guidelines']['description'];
        foreach ($icons as $index => $icon) {
            if (!empty($icon) && !empty($titles[$index]) && !empty($descs[$index])) {
                $stmt = $db->prepare("INSERT INTO package_guidelines (package_detail_id, icon, title, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$package_id, $icon, $titles[$index], $descs[$index]]);
            }
        }
    }

    // Process Days
    if ($package_id) {
        $stmt = $db->prepare("DELETE FROM package_days WHERE package_detail_id = ?");
        $stmt->execute([$package_id]);
    }
    if (isset($_POST['days']['day_number']) && is_array($_POST['days']['day_number'])) {
        $dayNumbers = $_POST['days']['day_number'];
        $titles = $_POST['days']['title'];
        $descs = $_POST['days']['description'];
        foreach ($dayNumbers as $index => $dayNum) {
            if (!empty($dayNum) && !empty($titles[$index]) && !empty($descs[$index])) {
                $stmt = $db->prepare("INSERT INTO package_days (package_detail_id, day_number, title, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$package_id, intval($dayNum), $titles[$index], $descs[$index]]);
            }
        }
    }

    $response['success'] = true;
    $response['message'] = 'Package saved successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
