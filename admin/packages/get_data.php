<?php
// admin/packages/get_data.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

requireAdmin();

// Prevent PHP warnings from breaking DataTables JSON
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
        $db = Database::getInstance()->getConnection();

        // Removed package_sizes JOIN. Category now links directly to Destination.
        $sql = "SELECT pd.id, d.place_name AS destination_name, pc.pricing_category_name, pd.title, pd.price, pd.status
            FROM package_details pd
            JOIN package_categories pc ON pd.package_category_id = pc.id
            JOIN destinations d ON pc.destination_id = d.id
            ORDER BY pd.id DESC";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (ob_get_length()) ob_clean();
        echo json_encode(['data' => $data]);
} catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => $e->getMessage(), 'data' => []]);
}
exit;
