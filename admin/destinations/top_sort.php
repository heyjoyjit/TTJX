<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Manage Top Destinations';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/destinations/top_sort_content.php';
// FIXED: Removed the incorrect '/views/' path
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';