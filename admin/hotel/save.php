<?php
// admin/hotel/save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid Security Token.');
    }

    $destination_id = intval($_POST['destination_id'] ?? 0);
    $hotel_name     = trim($_POST['hotel_name'] ?? '');
    $email          = strtolower(trim($_POST['email'] ?? ''));
    $phone          = trim($_POST['phone'] ?? '');
    $location       = trim($_POST['location'] ?? '');
    $owner_name     = trim($_POST['owner_name'] ?? '');
    $owner_phone    = trim($_POST['owner_phone'] ?? '');
    $owner_email    = trim($_POST['owner_email'] ?? '');

    if (!$destination_id || empty($hotel_name) || empty($email) || empty($phone) || empty($location) || empty($owner_name) || empty($owner_phone)) {
        throw new Exception('Destination and all mandatory fields are required.');
    }

    $db = Database::getInstance()->getConnection();

    // Check if email already registered
    $stmt = $db->prepare("SELECT id FROM hotels WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('A hotel with this email address is already registered.');
    }

    $db->beginTransaction();

    // Format: TT/XXX/HLT/YY (YY = 2-digit joining year)
    $hotel_registered_id = generateHotelId($db);
    $setup_token = bin2hex(random_bytes(32));
    $token_expires = date('Y-m-d H:i:s', strtotime('+72 hours'));

    // 1. Create Hotel Record
    $stmt = $db->prepare("
        INSERT INTO hotels (hotel_registered_id, destination_id, hotel_name, email, phone, location, owner_name, owner_phone, owner_email, status, is_onboarded) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)
    ");
    $stmt->execute([$hotel_registered_id, $destination_id, $hotel_name, $email, $phone, $location, $owner_name, $owner_phone, $owner_email]);
    $hotel_id = $db->lastInsertId();

    // 2. Create Partner Account with Setup Token
    $temp_password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $stmtOwner = $db->prepare("
        INSERT INTO hotel_owners (hotel_id, username, password, registration_completed, reset_token, reset_expires) 
        VALUES (?, ?, ?, 0, ?, ?)
    ");
    $stmtOwner->execute([$hotel_id, $email, $temp_password, $setup_token, $token_expires]);

    $db->commit();

    // Send Onboarding Email
    $setup_link = BASE_URL . "hotel/reset_password.php?token=" . $setup_token;
    $subject = "Welcome to TravelTara! Complete Your Property Setup";
    $message = "
        <h1 style='color: #1e293b; font-size: 22px; text-align: center; margin-top: 0;'>Welcome Aboard! 🏨</h1>
        <p style='color: #475569; text-align: center; font-size: 15px;'>Hello <strong>{$owner_name}</strong>,</p>
        <p style='color: #475569; text-align: center; font-size: 15px;'>Your property <strong>{$hotel_name}</strong> (Registered ID: <code>{$hotel_registered_id}</code>) has been registered on TravelTara.</p>
        
        <div style='background-color: #f0f9ff; border-left: 4px solid #2563eb; padding: 16px; margin: 25px 0; border-radius: 6px;'>
            <p style='color: #1e3a8a; margin: 0; font-size: 14px;'><strong>Next Step:</strong> Click below to set your account password and begin Phase 1 setup.</p>
        </div>

        <div style='text-align: center; margin: 30px 0; padding: 0 10px;'>
            <a href='{$setup_link}' style='background-color: #2563eb; color: #ffffff; padding: 14px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; display: inline-block; max-width: 100%; box-sizing: border-box;'>
                Set Password & Start Setup
            </a>
        </div>
        
        <p style='color: #94a3b8; font-size: 12px; text-align: center;'>Your login ID is: <code style='background: #f1f5f9; padding: 2px 6px; border-radius: 4px;'>{$email}</code></p>
    ";

    try {
        sendCustomEmail($email, $subject, $message, $hotel_name);
    } catch (Exception $mailError) {
        error_log("Onboarding Email Failed: " . $mailError->getMessage());
    }

    $response['success'] = true;
    $response['message'] = 'Hotel registered successfully and onboarding email sent to ' . $email;
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
