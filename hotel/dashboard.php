<?php
// hotel/dashboard.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireHotelOwner();

// If registration not completed, redirect to profile setup
if (empty($_SESSION['registration_completed'])) {
    redirect(BASE_URL . 'hotel/profile/review.php');
}

$page_title = 'Overview Dashboard';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/hotel/dashboard_content.php';
// FIXED: Adjusted the layout path to standard convention relative to hotel directory
require_once $_SERVER['DOCUMENT_ROOT'] . '/hotel/views/layouts/hotel_layout.php';
