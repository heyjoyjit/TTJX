<?php
// admin/invoices/verify_payment.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF token');

    $payment_id = $_POST['payment_id'];
    $order_id = $_POST['order_id'];
    $signature = $_POST['signature'];
    $invoice_id = intval($_POST['invoice_id']);

    if (!razorpay_verify_payment_signature($order_id, $payment_id, $signature)) {
        throw new Exception('Invalid signature');
    }

    $db = Database::getInstance()->getConnection();
    // FIXED: Mapped to the `status` column instead of the ghost `payment_status` column
    $stmt = $db->prepare("UPDATE invoices SET status = 'paid', razorpay_payment_id = ? WHERE id = ? AND razorpay_order_id = ?");
    $stmt->execute([$payment_id, $invoice_id, $order_id]);

    $response['success'] = true;
    $response['message'] = 'Payment verified and invoice marked as paid.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
