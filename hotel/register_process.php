<?php
// hotel/register_process.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Security token expired. Please refresh the page.');
    }

    $db = Database::getInstance()->getConnection();

    $hotel_name  = trim($_POST['hotel_name'] ?? '');
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $phone       = trim($_POST['phone'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $owner_name  = trim($_POST['owner_name'] ?? '');
    $owner_phone = trim($_POST['owner_phone'] ?? '');
    $owner_email = trim($_POST['owner_email'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($hotel_name) || empty($email) || empty($phone) || empty($location) || empty($owner_name) || empty($owner_phone)) {
        throw new Exception('Please fill in all mandatory registration fields.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid hotel email address.');
    }
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long.');
    }

    $stmt = $db->prepare("SELECT id FROM hotels WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('This hotel email is already registered. Please log in.');
    }

    $db->beginTransaction();

    // Format: TT/XXX/HLT/YY (YY = 2-digit joining year)
    $hotel_registered_id = generateHotelId($db);

    $stmt = $db->prepare("
        INSERT INTO hotels (hotel_registered_id, hotel_name, email, phone, location, owner_name, owner_phone, owner_email, status, is_onboarded) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0)
    ");
    $stmt->execute([$hotel_registered_id, $hotel_name, $email, $phone, $location, $owner_name, $owner_phone, $owner_email]);
    $hotel_id = $db->lastInsertId();

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO hotel_owners (hotel_id, username, password, registration_completed) VALUES (?, ?, ?, 0)");
    $stmt->execute([$hotel_id, $email, $hashed]);
    $owner_id = $db->lastInsertId();

    $db->commit();

    $_SESSION['hotel_owner_id'] = $owner_id;
    $_SESSION['hotel_id'] = $hotel_id;
    $_SESSION['hotel_username'] = $email;
    $_SESSION['registration_completed'] = 0;

    $response['success'] = true;
    $response['message'] = 'Registration successful! Directing to Phase 1 setup...';
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
