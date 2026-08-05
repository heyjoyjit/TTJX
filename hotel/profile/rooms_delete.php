<?php
// hotel/profile/rooms_delete.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireHotelOwner();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'redirect_to_onboarding' => false];

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('Invalid CSRF Token');

    $id = intval($_POST['id'] ?? 0);
    $hotel_id = $_SESSION['hotel_id'];
    $db = Database::getInstance()->getConnection();

    // 1. Delete associated physical files
    $stmt = $db->prepare("SELECT media FROM room_galleries WHERE room_id = ?");
    $stmt->execute([$id]);
    $galleries = $stmt->fetchAll();

    foreach ($galleries as $g) {
        if (!empty($g['media'])) {
            $filePath = BASE_PATH . 'uploads/hotels/gallery/' . $g['media'];
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    // 2. Delete room record
    $delStmt = $db->prepare("DELETE FROM hotel_rooms WHERE id = ? AND hotel_id = ?");
    $delStmt->execute([$id, $hotel_id]);

    // 3. SYSTEM REQUIREMENT 1: Check remaining room count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = ?");
    $countStmt->execute([$hotel_id]);
    $remainingRooms = $countStmt->fetchColumn();

    if ($remainingRooms == 0) {
        // Revert onboarding status when 0 rooms remain
        $db->prepare("UPDATE hotel_owners SET registration_completed = 0 WHERE hotel_id = ?")->execute([$hotel_id]);
        $db->prepare("UPDATE hotels SET is_onboarded = 0 WHERE id = ?")->execute([$hotel_id]);
        $_SESSION['registration_completed'] = 0;

        $response['redirect_to_onboarding'] = true;
        $response['redirect_url'] = BASE_URL . 'hotel/profile/rooms.php';
        $response['message'] = 'Room deleted. All rooms removed—your account has returned to the onboarding setup stage.';
    } else {
        $response['message'] = 'Room deleted successfully.';
    }

    $response['success'] = true;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
