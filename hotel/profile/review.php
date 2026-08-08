<?php
// hotel/profile/review.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireHotelOwner();

$hotel_id = $_SESSION['hotel_id'];
$db = Database::getInstance()->getConnection();

// Fetch Data
$stmt = $db->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM hotel_addresses WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$address = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$rooms = $stmt->fetchAll();

$page_title = 'Profile Setup - Review';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/hotel/profile/review_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hotel/views/layouts/hotel_layout.php';
