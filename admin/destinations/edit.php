<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    redirect(BASE_URL . 'admin/destinations/index.php');
}

$page_title = 'Edit Destination';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/destinations/edit_content.php';
// FIXED: Removed the incorrect '/views/' path
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';
