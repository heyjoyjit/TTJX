<?php
// hotel/profile/rooms_get.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED

requireHotelOwner();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF');

    $id = intval($_POST['id']);
    $hotel_id = $_SESSION['hotel_id'];
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT * FROM hotel_rooms WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotel_id]);
    $room = $stmt->fetch();
    if (!$room) throw new Exception('Room not found');

    $stmtAm = $db->prepare("SELECT id, title, price FROM room_amenities WHERE room_id = ?");
    $stmtAm->execute([$id]);
    $amenities = $stmtAm->fetchAll();

    $stmtGal = $db->prepare("SELECT id, category, media, media_type FROM room_galleries WHERE room_id = ?");
    $stmtGal->execute([$id]);
    $gallery = $stmtGal->fetchAll();

    $response['success'] = true;
    $response['data'] = [
        'room_title' => $room['room_title'],
        'beds' => $room['beds'],
        'adults' => $room['adults'],
        'children' => $room['children'],
        'tv_price' => $room['tv_price'],
        'ac_price' => $room['ac_price'],
        'wifi_price' => $room['wifi_price'],
        'amenities' => $amenities,
        'gallery' => $gallery
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
