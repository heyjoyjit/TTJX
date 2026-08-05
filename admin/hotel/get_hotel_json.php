<?php
// admin/hotel/get_hotel_json.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $hotel_id = intval($_GET['id']);

    // Fetch hotel and address data
    $stmt = $pdo->prepare("
        SELECT h.hotel_name, h.email, h.phone, h.location, ha.city 
        FROM hotels h
        LEFT JOIN hotel_addresses ha ON h.id = ha.hotel_id
        WHERE h.id = ? AND h.status = 1
    ");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hotel) {
        echo json_encode($hotel);
        exit;
    }
}

echo json_encode(['error' => 'Hotel not found']);
exit;
