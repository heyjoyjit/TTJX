<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // Ensure hotel auth session exists
$page_title = "My Settlement Bills | Hotel Portal";
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/hotel/invoices/index_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hotel/views/layouts/hotel_layout.php';
