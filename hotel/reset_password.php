<?php
// hotel/reset_password.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$validToken = false;
$ownerId = 0;

if (empty($token)) {
    $error = "No onboarding token provided.";
} else {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, hotel_id, username FROM hotel_owners WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $owner = $stmt->fetch();

    if ($owner) {
        $validToken = true;
        $ownerId = $owner['id'];
    } else {
        $error = "This onboarding link is invalid or has expired.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Password Validation Pattern
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if (empty($password) || $password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!preg_match($pattern, $password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear setup token
        $stmt = $db->prepare("UPDATE hotel_owners SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed, $ownerId]);

        // Automatically log the hotel owner into the session
        $ownerStmt = $db->prepare("SELECT id, hotel_id, username, registration_completed FROM hotel_owners WHERE id = ?");
        $ownerStmt->execute([$ownerId]);
        $ownerData = $ownerStmt->fetch();

        $_SESSION['hotel_owner_id'] = $ownerData['id'];
        $_SESSION['hotel_id'] = $ownerData['hotel_id'];
        $_SESSION['hotel_username'] = $ownerData['username'];
        $_SESSION['registration_completed'] = 0; // Forces Phase 1 Onboarding

        // Redirect straight to Phase 1 Setup
        header("Location: " . BASE_URL . "hotel/profile/address.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password | TravelTara</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10 text-center">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
            <i class="fas fa-key"></i>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Create Account Password</h1>

        <?php if ($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-medium text-left">
                <i class="fas fa-exclamation-circle mr-1"></i> <?= escape($error) ?>
            </div>
            <?php if (!$validToken): ?>
                <a href="<?= BASE_URL ?>hotel/login.php" class="w-full inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3.5 px-4 rounded-xl transition-all">Back to Login</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <p class="text-slate-500 text-sm mb-6 text-left">Password must contain at least 8 characters, an uppercase letter, a number, and a special character.</p>

            <form method="post" class="space-y-5 text-left">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Password *</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password *</label>
                    <div class="relative">
                        <i class="fas fa-check-double absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="password" name="confirm_password" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all">
                    Save Password & Start Setup <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>