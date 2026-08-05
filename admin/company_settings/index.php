<?php
// admin/company_settings/index.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// Define the page title
$page_title = "Company Settings | Traveltara Admin";

// Define the content file to be included by the layout
$content_view = 'index_content.php';

// Load the master admin layout
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/layouts/admin_layout.php';
