<?php
// admin/packages/add.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Add Package';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/packages/add_content.php';
// FIXED: Removed the incorrect '/views/' path[cite: 34]
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';