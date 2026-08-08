<?php
// admin/hotel/update.php
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

    // Registration Fields
    $destination_id = intval($_POST['destination_id'] ?? 0);
    $hotel_name = trim($_POST['hotel_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');
    $owner_phone = trim($_POST['owner_phone'] ?? '');
    $owner_email = trim($_POST['owner_email'] ?? '');

    $db = Database::getInstance()->getConnection();

    // Update Database
    $stmt = $db->prepare("
        UPDATE hotels 
        SET destination_id=?, hotel_name=?, email=?, phone=?, location=?, owner_name=?, owner_phone=?, owner_email=?
        WHERE id=?
    ");

    $stmt->execute([
        $destination_id,
        $hotel_name,
        $email,
        $phone,
        $location,
        $owner_name,
        $owner_phone,
        $owner_email,
        $id
    ]);

    // Send Notification Email
    $subject = "Your Traveltara Hotel Profile was Updated";
    $message = "
        <h1 style='color: #1e293b; font-size: 24px; text-align: center;'>Profile Update Notice ✏️</h1>
        <p style='color: #475569; text-align: center; font-size: 16px;'>Hello Team <strong>{$hotel_name}</strong>,</p>
        <p style='color: #475569; text-align: center; font-size: 16px; line-height: 1.6;'>
            This is an automated notification to let you know that your hotel's profile information has been successfully updated by our administrative team.
        </p>
        
        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px 20px; margin: 25px 0; border-radius: 8px;'>
            <h3 style='color: #334155; margin-top: 0; margin-bottom: 10px; font-size: 15px;'>Updates may include:</h3>
            <ul style='color: #64748b; margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.6;'>
                <li>Destination Assignment</li>
                <li>General Registration Details</li>
                <li>Owner Contact Details</li>
            </ul>
        </div>
    ";

    try {
        sendCustomEmail($email, $subject, $message, $hotel_name);
    } catch (Exception $mailError) {
        error_log("Failed to send update email to {$email}: " . $mailError->getMessage());
    }

    $response['success'] = true;
    $response['message'] = 'Hotel details updated and notification email sent!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
