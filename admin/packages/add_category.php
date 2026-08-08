<?php
// admin/packages/add_category.php
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

    $name = trim($_POST['name'] ?? '');
    $destination_id = intval($_POST['destination_id'] ?? 0);

    if (empty($name) || !$destination_id) {
        throw new Exception('Pricing Category Name and Destination are required.');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO package_categories (destination_id, pricing_category_name) VALUES (?, ?)");
    $stmt->execute([$destination_id, $name]);

    $response['id'] = $db->lastInsertId();
    $response['success'] = true;
    $response['message'] = 'Pricing Category added successfully';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
