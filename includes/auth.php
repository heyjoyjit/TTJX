<?php
// includes/auth.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

function adminLogin($username, $password)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

function adminLogout()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        redirect(BASE_URL . 'admin/login.php');
    }
}

// Hotel Owner Authentication
function hotelOwnerLogin($username, $password)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, hotel_id, username, password, registration_completed FROM hotel_owners WHERE username = ?");
    $stmt->execute([$username]);
    $owner = $stmt->fetch();
    if ($owner && password_verify($password, $owner['password'])) {
        $_SESSION['hotel_owner_id'] = $owner['id'];
        $_SESSION['hotel_id'] = $owner['hotel_id'];
        $_SESSION['hotel_username'] = $owner['username'];
        $_SESSION['registration_completed'] = $owner['registration_completed'];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function isHotelOwnerLoggedIn() {
    return !empty($_SESSION['hotel_owner_id']) && !empty($_SESSION['hotel_id']);
}

function requireHotelOwner() {
    if (!isHotelOwnerLoggedIn()) {
        redirect(BASE_URL . 'hotel/login.php');
    }

    // Redirect un-onboarded owners attempting to access live portal pages back to onboarding
    if (empty($_SESSION['registration_completed'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $allowed = ['/hotel/profile/', 'logout.php', 'address_save.php', 'rooms_save.php', 'submit.php', 'rooms_get.php', 'rooms_delete.php', 'rooms_delete_gallery.php'];
        
        $isAllowed = false;
        foreach ($allowed as $path) {
            if (strpos($uri, $path) !== false) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            redirect(BASE_URL . 'hotel/profile/address.php');
        }
    }
}

function hotelOwnerLogout() {
    unset($_SESSION['hotel_owner_id'], $_SESSION['hotel_id'], $_SESSION['hotel_username'], $_SESSION['registration_completed']);
}