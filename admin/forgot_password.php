<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$error = '';
$success = '';
$step = isset($_SESSION['reset_step']) ? $_SESSION['reset_step'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();

    if (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
        $email = trim($_POST['email'] ?? '');

        // Check if email exists in admins table
        $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(1, 999999));

            // Save to session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_step'] = 2; // Move to OTP step

            // Send OTP Email using PHPMailer
            $subject = "Admin Password Reset OTP";
            $message = "Hello,\n\nYour OTP for password reset is: " . $otp . "\n\nDo not share this code with anyone. If you didn't request this, please ignore this email.";

            if (sendMailerEmail($email, $subject, $message)) {
                $step = 2;
                $success = 'An OTP has been sent to your email address.';
            } else {
                $error = 'Failed to send OTP email. Please check the mail server configuration.';
                $_SESSION['reset_step'] = 1; // Reset step on failure
            }
        } else {
            $error = 'Invalid email address or not found in our records.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $enteredOtp = trim($_POST['otp'] ?? '');

        if (isset($_SESSION['reset_otp']) && $enteredOtp === $_SESSION['reset_otp']) {
            $email = $_SESSION['reset_email'];

            // Generate a secure reset token
            $resetToken = bin2hex(random_bytes(32));

            // Store token in DB (Requires a `reset_token` column in admins table)
            $stmt = $db->prepare("UPDATE admins SET reset_token = ? WHERE email = ?");
            $stmt->execute([$resetToken, $email]);

            // Send Reset Link Email using PHPMailer
            $resetLink = BASE_URL . "admin/reset_password.php?token=" . $resetToken;
            $subject = "Password Reset Link";
            $message = "Hello,\n\nClick the following link to reset your password:\n\n" . $resetLink . "\n\nIf you did not request a password reset, please ignore this email.";

            if (sendMailerEmail($email, $subject, $message)) {
                // Clear session OTP data
                unset($_SESSION['reset_otp']);
                unset($_SESSION['reset_step']);

                $step = 3;
                $success = 'OTP verified! A password reset link has been sent to your email.';
            } else {
                $error = 'Failed to send the reset link email. Please contact support.';
            }
        } else {
            $error = 'Invalid or expired OTP. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | TravelTara</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center min-h-screen text-slate-100 antialiased">
    <div class="w-full max-w-md p-8 m-4 bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl">

        <div class="flex justify-center mb-6">
            <div class="bg-blue-600/20 p-4 rounded-full border border-blue-500/30">
                <!-- Replace this SVG with your actual <img> logo tag -->
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="TravelTara Logo" class="h-20 w-auto">
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Password Reset</h1>
            <p class="text-sm text-slate-400">Follow the steps to regain access</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= escape($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= escape($success) ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <!-- Step 1: Enter Email -->
            <form method="post" action="" class="space-y-6">
                <input type="hidden" name="action" value="send_otp">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2" for="email">Admin Email Address</label>
                    <input type="email" name="email" id="email"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="admin@example.com" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition-all">
                    Send OTP
                </button>
            </form>

        <?php elseif ($step === 2): ?>
            <!-- Step 2: Enter OTP -->
            <form method="post" action="" class="space-y-6">
                <input type="hidden" name="action" value="verify_otp">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2" for="otp">Enter 6-Digit OTP</label>
                    <input type="text" name="otp" id="otp" maxlength="6"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 text-slate-100 text-center tracking-[0.5em] text-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="••••••" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition-all">
                    Verify OTP
                </button>
            </form>

        <?php elseif ($step === 3): ?>
            <!-- Step 3: Success state -->
            <div class="text-center">
                <p class="text-slate-300 mb-6">Please check your email inbox for the reset link.</p>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="login.php" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">&larr; Back to Login</a>
        </div>
    </div>
</body>

</html>