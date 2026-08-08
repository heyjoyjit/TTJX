<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$page_title = 'Dashboard';
$content_view = __DIR__ . '/dashboard_content.php';
require_once __DIR__ . '/layouts/admin_layout.php';
