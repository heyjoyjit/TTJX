<?php
// admin/destinations/update_top_order.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    $orders = $_POST['orders'] ?? [];
    if (!is_array($orders)) throw new Exception('Invalid data');

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    foreach ($orders as $item) {
        $id = intval($item['id']);
        $order = intval($item['order']);
        $stmt = $db->prepare("UPDATE destinations SET top_sort_order = ? WHERE id = ? AND is_top_destination = 1");
        $stmt->execute([$order, $id]);
    }
    $db->commit();
    $response['success'] = true;
    $response['message'] = 'Top destinations reordered successfully';
} catch (Exception $e) {
    $db->rollBack();
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
