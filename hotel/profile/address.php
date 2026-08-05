<?php
// hotel/profile/address.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireHotelOwner();

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM hotel_addresses WHERE hotel_id = ?");
$stmt->execute([$_SESSION['hotel_id']]);
$address = $stmt->fetch();

$page_title = 'Profile Setup - Address';
$content_view = __DIR__ . '/address_content.php';

// Safe Layout Include
$layoutPath = $_SERVER['DOCUMENT_ROOT'] . '/hotel/views/layouts/hotel_layout.php';

require_once $layoutPath;