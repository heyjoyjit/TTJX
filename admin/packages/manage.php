<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : 0;
if (!$destination_id) {
    redirect(BASE_URL . 'admin/packages/index.php');
}

// Fetch destination name
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT place_name FROM destinations WHERE id = ?");
$stmt->execute([$destination_id]);
$dest = $stmt->fetch();
if (!$dest) {
    redirect(BASE_URL . 'admin/packages/index.php');
}

$page_title = 'Manage Packages - ' . $dest['place_name'];
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/packages/manage_content.php';
// FIXED: Removed the incorrect '/views/' path
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';