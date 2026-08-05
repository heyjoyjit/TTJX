<?php
// admin/payment_links/get_data.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    // FIXED: Mapped to actual schema (customer_name, hotel_id)
    $stmt = $db->query("SELECT id, hotel_id, customer_name, customer_email, amount, status, razorpay_link_url, created_at FROM payment_links ORDER BY id DESC");
    $data = $stmt->fetchAll();
    
    if (ob_get_length()) ob_clean();
    echo json_encode(['data' => $data]);
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => $e->getMessage(), 'data' => []]);
}
exit;