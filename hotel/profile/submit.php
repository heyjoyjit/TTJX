<?php
// hotel/profile/submit.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireHotelOwner();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF token');

    $hotel_id = $_SESSION['hotel_id'];
    $db = Database::getInstance()->getConnection();

    // Guard 1: Verify Phase 1 Address
    $stmt = $db->prepare("SELECT id FROM hotel_addresses WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);
    if (!$stmt->fetch()) throw new Exception('Address proof missing. Please complete Phase 1.');

    // Guard 2: Verify Phase 2 Rooms
    $stmt = $db->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = ? LIMIT 1");
    $stmt->execute([$hotel_id]);
    if (!$stmt->fetch()) throw new Exception('At least one room category is required before submitting.');

    // Finalize Registration
    $stmt = $db->prepare("UPDATE hotel_owners SET registration_completed = 1 WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);

    $stmt = $db->prepare("UPDATE hotels SET is_onboarded = 1 WHERE id = ?");
    $stmt->execute([$hotel_id]);

    $_SESSION['registration_completed'] = 1;

    $response['success'] = true;
    $response['message'] = 'Profile onboarding finalized! Welcome to your live partner dashboard.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
