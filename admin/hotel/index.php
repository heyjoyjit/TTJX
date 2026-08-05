<?php
// admin/hotel/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Manage Hotels';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/hotel/index_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php'; // FIXED PATH