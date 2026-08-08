<?php
// admin/packages/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Manage Packages';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/packages/index_content.php';

// FIXED: Corrected path to layout by removing the incorrect '/views/' folder reference[cite: 32]
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';
