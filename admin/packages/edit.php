<?php
// admin/packages/edit.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . 'admin/packages/index.php');

$page_title = 'Edit Package';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/packages/edit_content.php';
// FIXED: Removed the incorrect '/views/' path[cite: 40]
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';
