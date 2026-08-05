<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isAdminLoggedIn()) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

$error = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Protection Check
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Attempt Login
        if ($username && $password && adminLogin($username, $password)) {

            // Handle "Remember Me"
            if (isset($_POST['remember_me'])) {
                $params = session_get_cookie_params();
                // Extend session cookie to 1 year
                setcookie(
                    session_name(),
                    session_id(),
                    time() + 31536000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// Generate new token for the form
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure administrative login portal for the TravelTara.">
    <meta name="robots" content="noindex, nofollow">

    <title>Admin Login | TravelTara</title>

    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center min-h-screen text-slate-100 antialiased">

    <div class="w-full max-w-md p-8 m-4 bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl">

        <!-- Logo Placeholder -->
        <div class="flex justify-center mb-6">
            <div class="bg-blue-600/20 p-4 rounded-full border border-blue-500/30">
                <!-- Replace this SVG with your actual <img> logo tag -->
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="TravelTara Logo" class="h-20 w-auto">
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Welcome Back</h1>
            <p class="text-sm text-slate-400">Sign in to your admin dashboard</p>
        </div>

        <!-- Error Alert -->
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6 flex items-center gap-3" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium"><?= escape($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="" class="space-y-6">

            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

            <!-- Username Field -->
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2" for="username">Username</label>
                <input type="text" name="username" id="username"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="admin_user"
                    autocomplete="username"
                    required>
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2" for="password">Password</label>
                <input type="password" name="password" id="password"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 px-4 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center text-sm text-slate-300 cursor-pointer group">
                    <input type="checkbox" name="remember_me" class="mr-2 rounded border-slate-700 bg-slate-900/50 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900 cursor-pointer">
                    <span class="group-hover:text-white transition-colors">Remember me</span>
                </label>
                <a href="forgot_password.php" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg shadow-blue-500/30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                Sign In
            </button>

        </form>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; <?= date('Y') ?> TravelTara. All rights reserved.
        </div>

    </div>
</body>

</html>