<?php
// admin/hotel/get_data.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireAdmin();

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT h.*, d.place_name 
    FROM hotels h 
    LEFT JOIN destinations d ON h.destination_id = d.id 
    ORDER BY h.id DESC
");
$hotels = $stmt->fetchAll();

$data = [];
foreach ($hotels as $h) {
    $hotel_id = $h['id'];

    // --- REQUIREMENT 1: SETUP PERCENTAGE ---
    $completed_steps = 1; // Base Registration (33%)

    // Check Phase 1: Address
    $addrStmt = $db->prepare("SELECT id FROM hotel_addresses WHERE hotel_id = ?");
    $addrStmt->execute([$hotel_id]);
    if ($addrStmt->fetch()) {
        $completed_steps++; // 66%
    }

    // Check Phase 2: Rooms
    $roomStmt = $db->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = ? LIMIT 1");
    $roomStmt->execute([$hotel_id]);
    if ($roomStmt->fetch()) {
        $completed_steps++; // 100%
    }

    $percentage = round(($completed_steps / 3) * 100);
    if ($percentage > 99) $percentage = 100;

    if ($percentage == 100 && $h['is_onboarded'] == 0) {
        $db->query("UPDATE hotels SET is_onboarded = 1 WHERE id = " . intval($hotel_id));
        $h['is_onboarded'] = 1;
    }

    $progressColor = $percentage == 100 ? 'bg-emerald-500' : ($percentage > 50 ? 'bg-blue-500' : 'bg-amber-500');
    $progressBar = "
        <div class='w-full min-w-[120px]'>
            <div class='flex justify-between text-xs font-bold mb-1'>
                <span class='text-slate-600'>" . ($percentage == 100 ? 'Live' : 'Pending') . "</span>
                <span class='text-slate-500'>{$percentage}%</span>
            </div>
            <div class='w-full bg-slate-200 rounded-full h-2'>
                <div class='{$progressColor} h-2 rounded-full transition-all' style='width: {$percentage}%'></div>
            </div>
        </div>
    ";

    // --- ACTION BUTTONS ---
    $actions = "<div class='flex items-center justify-end space-x-2'>";

    // View Profile Details
    $actions .= "<a href='" . BASE_URL . "admin/hotel/view.php?id={$h['id']}' class='w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-500 hover:bg-slate-200 transition-colors' title='View Profile'><i class='fas fa-eye text-xs'></i></a>";

    // Standard Edit Info
    $actions .= "<a href='" . BASE_URL . "admin/hotel/edit.php?id={$h['id']}' class='w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors' title='Edit Registration Info'><i class='fas fa-edit text-xs'></i></a>";

    if ($percentage < 100) {
        // REQUIREMENT 2: Send Reminder Email
        $actions .= "<button onclick='sendReminder({$h['id']})' class='w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-colors' title='Send Reminder Email'><i class='fas fa-paper-plane text-xs'></i></button>";

        // REQUIREMENT 3: Dedicated Admin Onboarding Portal
        $actions .= "<a href='" . BASE_URL . "admin/hotel/onboard.php?id={$h['id']}' class='w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors' title='Complete Onboarding Setup'><i class='fas fa-clipboard-check text-xs'></i></a>";
    } else {
        // Dedicated Onboarding Page (for managing address/rooms post-onboarding)
        $actions .= "<a href='" . BASE_URL . "admin/hotel/onboard.php?id={$h['id']}' class='w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors' title='Manage Onboarding Setup'><i class='fas fa-tasks text-xs'></i></a>";
    }

    // Toggle Active Status
    $toggleColor = $h['status'] ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50';
    $toggleIcon = $h['status'] ? 'fa-toggle-on' : 'fa-toggle-off';
    $actions .= "<button class='toggle-status w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 transition-colors {$toggleColor}' data-id='{$h['id']}' data-status='{$h['status']}' title='Toggle Active Status'><i class='fas {$toggleIcon} text-lg'></i></button>";

    $actions .= "</div>";

    $data[] = [
        'hotel_registered_id' => escape($h['hotel_registered_id']),
        'hotel_name' => "<div class='font-bold text-slate-800'>" . escape($h['hotel_name']) . "</div><div class='text-xs text-slate-500'>" . escape($h['email']) . " | " . escape($h['phone']) . "</div>",
        'destination' => escape($h['place_name'] ?? 'Unassigned'),
        'owner_name' => "<div class='font-medium text-slate-700'>" . escape($h['owner_name']) . "</div><div class='text-xs text-slate-400'>" . escape($h['owner_phone']) . "</div>",
        'progress' => $progressBar,
        'status' => $h['status'],
        'actions' => $actions
    ];
}

echo json_encode(['data' => $data]);
