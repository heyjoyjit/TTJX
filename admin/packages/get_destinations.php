<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT d.id, d.place_name, 
           (SELECT COUNT(*) FROM package_sizes WHERE destination_id = d.id) AS sizes_count
    FROM destinations d
    ORDER BY d.id DESC
");
$data = $stmt->fetchAll();
echo json_encode(['data' => $data]);
