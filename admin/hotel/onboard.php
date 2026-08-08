<?php
// admin/hotel/onboard.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) redirect(BASE_URL . 'admin/hotel/index.php');

$page_title = 'Complete Hotel Onboarding';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/hotel/onboard_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';
