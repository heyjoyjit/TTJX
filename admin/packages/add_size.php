<?php
// admin/packages/add_size.php

// FIXED: Replaced $_SERVER['DOCUMENT_ROOT'] with __DIR__ relative paths to correctly point to the TTJX directory
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'id' => null];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }

    $name = trim($_POST['name'] ?? '');
    $destination_id = intval($_POST['destination_id'] ?? 0);

    if (empty($name)) throw new Exception('Size name is required');
    if (!$destination_id) throw new Exception('A valid destination must be selected');

    $db = Database::getInstance()->getConnection();

    // Check if the size already exists for this destination
    $stmt = $db->prepare("SELECT id FROM package_sizes WHERE size_name = ? AND destination_id = ?");
    $stmt->execute([$name, $destination_id]);
    if ($stmt->fetch()) {
        throw new Exception('This package size already exists for the selected destination');
    }

    // Insert new size with destination_id
    $stmt = $db->prepare("INSERT INTO package_sizes (size_name, destination_id) VALUES (?, ?)");
    $stmt->execute([$name, $destination_id]);

    $response['success'] = true;
    $response['id'] = $db->lastInsertId();
    $response['message'] = 'Size added successfully';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
