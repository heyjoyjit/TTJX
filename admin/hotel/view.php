<?php
// admin/hotel/view.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . 'admin/hotel/index.php');

$page_title = 'View Hotel';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/hotel/view_content.php';
// FIXED: Removed the incorrect '/views/' path[cite: 48]
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';