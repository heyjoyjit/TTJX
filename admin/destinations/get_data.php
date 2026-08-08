<?php
// admin/destinations/get_data.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();

header('Content-Type: application/json');

// Prevent PHP from outputting errors directly into the JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, place_name, banner_title, main_media, is_top_destination, status FROM destinations ORDER BY id DESC");
    $data = $stmt->fetchAll();
    
    // Clear the output buffer to remove any stray HTML/Whitespace/Warnings
    if (ob_get_length()) ob_clean();
    
    echo json_encode(['data' => $data]);
} catch (Exception $e) {
    // If the database query fails, return a JSON error so DataTables doesn't crash with invalid format
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => $e->getMessage(), 'data' => []]);
}
exit;