<?php
// admin/destinations/view.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    redirect(BASE_URL . 'admin/destinations/index.php');
}

$page_title = 'View Destination';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/destinations/view_content.php';
// FIXED: Corrected path to layout
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';