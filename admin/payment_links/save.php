<?php
// admin/payment_links/save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED: Missing auth include

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $hotel_id = !empty($_POST['hotel_id']) ? intval($_POST['hotel_id']) : null;
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? ''); // Used for API, not saved in DB
    $amount = floatval($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $theme = $_POST['theme'] ?? 'default';
    $expires_at = !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;

    if (empty($customer_name) || empty($customer_email) || $amount <= 0) {
        throw new Exception('Client name, email, and a valid amount are required.');
    }

    // Create Razorpay payment link
    $customer = [
        'name' => $customer_name,
        'email' => $customer_email,
        'contact' => $customer_phone
    ];

    // Note: Ensure your razorpay_helper.php correctly maps $expires_at to a unix timestamp if required by Razorpay
    $link = razorpay_create_payment_link($amount, 'INR', $description, $customer, $expires_at);

    if (!isset($link['id']) || !isset($link['short_url'])) {
        throw new Exception('Failed to generate link from Razorpay.');
    }

    $db = Database::getInstance()->getConnection();
    // FIXED: Insert matches exact DB schema (no client_phone or link_type)
    $stmt = $db->prepare("INSERT INTO payment_links (hotel_id, customer_name, customer_email, amount, description, razorpay_link_id, razorpay_link_url, theme, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");

    $stmt->execute([
        $hotel_id,
        $customer_name,
        $customer_email,
        $amount,
        $description,
        $link['id'],
        $link['short_url'],
        $theme,
        $expires_at
    ]);

    $response['success'] = true;
    $response['message'] = 'Payment link created successfully!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
