<?php
// admin/hotel/admin_rooms_save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid Security Token');

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $room_id = intval($_POST['room_id'] ?? 0);
    $room_title = trim($_POST['room_title'] ?? '');
    $beds = intval($_POST['beds'] ?? 1);
    $adults = intval($_POST['adults'] ?? 1);
    $children = intval($_POST['children'] ?? 0);
    $tv_price = floatval($_POST['tv_price'] ?? 0);
    $ac_price = floatval($_POST['ac_price'] ?? 0);
    $wifi_price = floatval($_POST['wifi_price'] ?? 0);

    if (!$hotel_id || empty($room_title) || $beds < 1 || $adults < 1) {
        throw new Exception('Hotel ID, Room Title, Beds, and Max Adults are required.');
    }

    // Save or Update Core Room Record
    if ($room_id === 0) {
        $stmt = $db->prepare("INSERT INTO hotel_rooms (hotel_id, room_title, beds, adults, children, tv_price, ac_price, wifi_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hotel_id, $room_title, $beds, $adults, $children, $tv_price, $ac_price, $wifi_price]);
        $room_id = $db->lastInsertId();
    } else {
        $stmt = $db->prepare("UPDATE hotel_rooms SET room_title=?, beds=?, adults=?, children=?, tv_price=?, ac_price=?, wifi_price=? WHERE id=? AND hotel_id=?");
        $stmt->execute([$room_title, $beds, $adults, $children, $tv_price, $ac_price, $wifi_price, $room_id, $hotel_id]);

        $stmt = $db->prepare("DELETE FROM room_amenities WHERE room_id = ?");
        $stmt->execute([$room_id]);
    }

    // Process Custom Amenities
    if (isset($_POST['amenity_title']) && is_array($_POST['amenity_title'])) {
        $titles = $_POST['amenity_title'];
        $prices = $_POST['amenity_price'];
        for ($i = 0; $i < count($titles); $i++) {
            if (!empty($titles[$i])) {
                $price = floatval($prices[$i] ?? 0);
                $stmt = $db->prepare("INSERT INTO room_amenities (room_id, title, price) VALUES (?, ?, ?)");
                $stmt->execute([$room_id, trim($titles[$i]), $price]);
            }
        }
    }

    // Upload Unlimited Photos & Videos across Categories
    $galleryDir = BASE_PATH . 'uploads/hotel/gallery/';
    if (!is_dir($galleryDir)) mkdir($galleryDir, 0777, true);

    $categories = ['beds', 'tv', 'ac', 'balcony', 'fridge', 'inner_view', 'outer_view', 'others', 'amenities'];
    foreach ($categories as $cat) {
        $fieldName = 'gallery_' . $cat;
        if (isset($_FILES[$fieldName]) && is_array($_FILES[$fieldName]['name'])) {
            $files = $_FILES[$fieldName];
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
                    if (strpos($mime, 'video') !== false) {
                        $mediaType = 'video';
                    } elseif (strpos($mime, 'gif') !== false) {
                        $mediaType = 'gif';
                    }

                    $fileName = uploadFile($file, $galleryDir, [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                        'video/mp4',
                        'video/webm',
                        'video/ogg',
                        'video/quicktime'
                    ]);

                    $stmt = $db->prepare("INSERT INTO room_galleries (room_id, category, media, media_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$room_id, $cat, $fileName, $mediaType]);
                }
            }
        }
    }

    $db->commit();
    $response['success'] = true;
    $response['message'] = 'Room details and media galleries saved successfully.';
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
