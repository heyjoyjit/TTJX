<?php
// admin/hotel/onboard_save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF token');

    $id = intval($_POST['id']);
    if (!$id) throw new Exception('Invalid Hotel ID.');

    // Phase 1 Address Fields
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    if (empty($address_line1) || empty($city) || empty($state) || empty($country) || empty($zip)) {
        throw new Exception('All Phase 1 mandatory fields must be filled.');
    }

    $db = Database::getInstance()->getConnection();

    // 1. Save or Update Phase 1 Address Record
    $addrCheck = $db->prepare("SELECT id FROM hotel_addresses WHERE hotel_id = ?");
    $addrCheck->execute([$id]);

    if ($addrCheck->fetch()) {
        $stmt = $db->prepare("UPDATE hotel_addresses SET address_line1=?, address_line2=?, city=?, state=?, country=?, zip=? WHERE hotel_id=?");
        $stmt->execute([$address_line1, $address_line2, $city, $state, $country, $zip, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO hotel_addresses (hotel_id, address_line1, address_line2, city, state, country, zip) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $address_line1, $address_line2, $city, $state, $country, $zip]);
    }

    // 2. Recalculate Onboarding Progress (Check both Address & Rooms)
    $roomCheck = $db->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = ? LIMIT 1");
    $roomCheck->execute([$id]);
    $hasRooms = $roomCheck->fetch();

    $hotelStmt = $db->prepare("SELECT hotel_name, email FROM hotels WHERE id = ?");
    $hotelStmt->execute([$id]);
    $hotel = $hotelStmt->fetch();

    if ($hasRooms) {
        $db->prepare("UPDATE hotels SET is_onboarded = 1 WHERE id = ?")->execute([$id]);
        $db->prepare("UPDATE hotel_owners SET registration_completed = 1 WHERE hotel_id = ?")->execute([$id]);

        // Send Email Notification
        $subject = "Your Traveltara Hotel Profile is Live!";
        $message = "
            <h1 style='color: #1e293b; font-size: 24px; text-align: center;'>Onboarding Complete! 🎉</h1>
            <p style='color: #475569; text-align: center;'>Hello Team <strong>{$hotel['hotel_name']}</strong>,</p>
            <p style='color: #475569; text-align: center;'>Our administration team has completed your property onboarding. Your hotel is now live on Traveltara.</p>
        ";
        try {
            sendCustomEmail($hotel['email'], $subject, $message, $hotel['hotel_name']);
        } catch (Exception $m) {
        }
    }

    $response['success'] = true;
    $response['message'] = 'Phase 1 Address saved successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
