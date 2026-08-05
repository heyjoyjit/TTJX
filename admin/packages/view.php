<?php
// admin/packages/view.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . 'admin/packages/index.php');

$page_title = 'View Package Details';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/packages/view_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';