<?php
// hotel/profile/rooms.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireHotelOwner();

$hotel_id = $_SESSION['hotel_id'];
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY id");
$stmt->execute([$hotel_id]);
$rooms = $stmt->fetchAll();

$page_title = 'Profile Setup - Rooms';
$content_view = $_SERVER['DOCUMENT_ROOT'] . '/hotel/profile/rooms_content.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hotel/views/layouts/hotel_layout.php'; // Using proper layout