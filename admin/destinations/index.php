<?php
// admin/destinations/index.php

// 1. FIX: Include configuration and auth files to make requireAdmin() available
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

$page_title = 'Destinations Management';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/admin/destinations/index_content.php';

// 2. Load the layout (which calls requireAdmin())
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';