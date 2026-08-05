<?php
// admin/hotel/edit.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . 'admin/hotel/index.php');

$page_title = 'Edit Hotel';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/hotel/edit_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php'; // FIXED PATH