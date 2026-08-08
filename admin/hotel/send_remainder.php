<?php
// admin/hotel/send_reminder.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

requireAdmin();
header('Content-Type: application/json');

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid Security Token.');

    $id = intval($_POST['id']);
    $db = Database::getInstance()->getConnection();

    // Fetch hotel and owner details
    $stmt = $db->prepare("
        SELECT h.hotel_name, h.email, ho.id as owner_id, ho.reset_token 
        FROM hotels h 
        LEFT JOIN hotel_owners ho ON h.id = ho.hotel_id 
        WHERE h.id = ?
    ");
    $stmt->execute([$id]);
    $hotel = $stmt->fetch();

    if (!$hotel) throw new Exception("Hotel record not found.");
    if (empty($hotel['email'])) throw new Exception("No email address found for this hotel.");

    // Generate fresh setup token if missing or expired
    $token = $hotel['reset_token'];
    if (empty($token)) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+72 hours'));
        $db->prepare("UPDATE hotel_owners SET reset_token = ?, reset_expires = ? WHERE hotel_id = ?")->execute([$token, $expires, $id]);
    }

    $setup_link = BASE_URL . "hotel/reset_password.php?token=" . $token;
    $login_link = BASE_URL . "hotel/login.php";

    $subject = "Action Required: Complete Your Hotel Profile on TravelTara";
    $message = "
        <h1 style='color: #1e293b; font-size: 24px; text-align: center;'>Profile Setup Pending ⏳</h1>
        <p style='color: #475569; text-align: center;'>Hello Team <strong>{$hotel['hotel_name']}</strong>,</p>
        <p style='color: #475569; text-align: center;'>Your property onboarding on TravelTara is currently incomplete. Please finish setting up your address and room details to go live.</p>
        
        <div style='text-align: center; margin: 35px 0;'>
            <a href='{$setup_link}' style='background-color: #f59e0b; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;'>⭐ Complete Profile Setup</a>
        </div>
        
        <p style='color: #64748b; font-size: 13px; text-align: center;'>Already set up your password? <a href='{$login_link}' style='color: #2563eb;'>Sign in to your portal here</a>.</p>
    ";

    sendCustomEmail($hotel['email'], $subject, $message, $hotel['hotel_name']);
    echo json_encode(['success' => true, 'message' => 'Onboarding reminder sent to ' . $hotel['email']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
