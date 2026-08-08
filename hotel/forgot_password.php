<?php
// hotel/forgot_password.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, username FROM hotel_owners WHERE username = ?");
        $stmt->execute([$email]);
        $owner = $stmt->fetch();

        if ($owner) {
            // Generate Password Reset Token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("UPDATE hotel_owners SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $owner['id']]);

            // Email Setup
            $resetLink = BASE_URL . 'hotel/reset_password.php?token=' . $token;

            $htmlBody = "
                <h1 style='color: #1e293b; font-size: 22px; text-align: center; margin-top: 0;'>Password Reset Request</h1>
                <p style='color: #475569; text-align: center; font-size: 15px; line-height: 1.6;'>We received a request to reset the password for your Partner Account. Click the button below to establish a new password. This link will expire in 1 hour.</p>
                
                <div style='text-align: center; margin: 30px 0; padding: 0 10px;'>
                    <a href='{$resetLink}' style='background-color: #2563eb; color: #ffffff; padding: 14px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; display: inline-block; max-width: 100%; box-sizing: border-box;'>
                        Reset My Password
                    </a>
                </div>
                
                <p style='color: #94a3b8; font-size: 13px; text-align: center; margin-bottom: 0;'>If you did not request a password reset, please ignore this email.</p>
            ";

            try {
                // Uses central helper from includes/mailer.php
                sendCustomEmail(
                    $email,
                    "Password Reset Request - " . (defined('SITE_NAME') ? SITE_NAME : 'TravelTara'),
                    $htmlBody,
                    $owner['username']
                );
            } catch (Exception $e) {
                $error = "Failed to send reset email. Please contact support.";
            }
        }

        // Only set success if no delivery error occurred
        if (empty($error)) {
            $success = "If an account exists with that email, a reset link has been sent.";
        }
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?= defined('SITE_NAME') ? SITE_NAME : 'TravelTara' ?></title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10 text-center">

        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
            <img src="<?= defined('SITE_LOGO_URL') ? SITE_LOGO_URL : BASE_URL . 'assets/images/logo.png' ?>" alt="<?= defined('SITE_NAME') ? SITE_NAME : 'TravelTara' ?>" class="h-10 w-auto">
        </div>

        <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-2">Forgot Password?</h1>
        <p class="text-slate-500 text-sm mb-8">Enter your registered email address and we'll send you a link to reset your password.</p>

        <!-- Message Banners (Render Error or Success, Never Both) -->
        <?php if ($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-2 text-left">
                <i class="fas fa-exclamation-circle text-rose-500 shrink-0"></i>
                <span><?= escape($error) ?></span>
            </div>
        <?php elseif ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-2 text-left">
                <i class="fas fa-check-circle text-emerald-500 shrink-0"></i>
                <span><?= escape($success) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6 text-left">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                    <input type="email" name="email" required placeholder="manager@hotel.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all">
                Send Reset Link
            </button>
        </form>

        <p class="mt-8 text-sm text-slate-500 font-medium">
            Remembered your password? <a href="<?= BASE_URL ?>hotel/login.php" class="text-indigo-600 hover:text-indigo-800 transition-colors">Back to Login</a>
        </p>
    </div>
</body>

</html>