<?php
// admin/payment_links/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$page_title = 'Payment Links';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/payment_links/index_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php'; // FIXED PATH