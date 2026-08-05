<?php
// admin/hotel/add.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Add Hotel';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/hotel/add_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php'; // FIXED PATH