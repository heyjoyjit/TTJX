<?php
// hotel/logout.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

hotelOwnerLogout();
redirect(BASE_URL . 'hotel/login.php');
