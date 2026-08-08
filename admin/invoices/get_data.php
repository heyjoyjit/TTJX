<?php
// admin/invoices/get_data.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    // FIXED: Removed ghost 'payment_status' column and mapped to correct schema
    $stmt = $db->query("SELECT id, invoice_number, invoice_type, customer_name, invoice_date, due_date, total, status FROM invoices ORDER BY id DESC");
    $data = $stmt->fetchAll();
    
    if (ob_get_length()) ob_clean();
    echo json_encode(['data' => $data]);
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => $e->getMessage(), 'data' => []]);
}
exit;