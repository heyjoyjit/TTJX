<?php
// hotel/profile/address_save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireHotelOwner();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token. Please refresh the page.');
    }

    $hotel_id      = $_SESSION['hotel_id'];
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city          = trim($_POST['city'] ?? '');
    $state         = trim($_POST['state'] ?? '');
    $country       = trim($_POST['country'] ?? '');
    $zip           = trim($_POST['zip'] ?? '');

    if (empty($address_line1) || empty($city) || empty($state) || empty($country) || empty($zip)) {
        throw new Exception('All required address fields must be filled.');
    }

    // Hidden internal IP Geolocation Fetch
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ip_location = '';
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city", false, $ctx);

    if ($data) {
        $json = json_decode($data, true);
        if ($json && isset($json['status']) && $json['status'] === 'success') {
            $ip_location = $json['city'] . ', ' . $json['regionName'] . ', ' . $json['country'] . ' (IP: ' . $ip . ')';
        }
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id FROM hotel_addresses WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE hotel_addresses SET address_line1=?, address_line2=?, city=?, state=?, country=?, zip=?, ip_location=? WHERE hotel_id=?");
        $stmt->execute([$address_line1, $address_line2, $city, $state, $country, $zip, $ip_location, $hotel_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO hotel_addresses (hotel_id, address_line1, address_line2, city, state, country, zip, ip_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hotel_id, $address_line1, $address_line2, $city, $state, $country, $zip, $ip_location]);
    }

    $response['success'] = true;
    $response['message'] = 'Address saved successfully! Moving to Phase 2 (Rooms)...';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
