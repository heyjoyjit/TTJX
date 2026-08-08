<?php
// hotel/login.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isHotelOwnerLoggedIn()) {
    redirect(BASE_URL . 'hotel/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password && hotelOwnerLogin($username, $password)) {
        redirect(BASE_URL . 'hotel/dashboard.php');
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Partner Login | <?= defined('SITE_NAME') ? SITE_NAME : 'TravelTara' ?></title>
    <icon rel="shortcut icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div id="auth-container" class="opacity-0 w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

        <!-- Left Side: Branding/Image -->
        <div class="md:w-5/12 bg-gradient-to-br from-blue-600 to-indigo-700 p-12 text-white flex flex-col justify-between relative overflow-hidden hidden md:flex">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')] bg-cover bg-center mix-blend-overlay opacity-20"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-2xl mb-6 shadow-inner">
                    <i class="fas fa-hotel"></i>
                </div>
                <h2 class="text-4xl font-bold tracking-tight mb-4">Partner Portal</h2>
                <p class="text-blue-100 text-lg">Manage your bookings, track revenue, and grow your hospitality business with us.</p>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-blue-200">&copy; <?= date('Y') ?> <?= defined('SITE_NAME') ? SITE_NAME : 'Travel System' ?></p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:w-7/12 p-8 md:p-14 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-10 text-center md:text-left">
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Welcome Back</h1>
                    <p class="text-slate-500">Sign in to access your hotel dashboard.</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
                        <i class="fas fa-exclamation-circle text-rose-500"></i> <?= escape($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Registered Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="email" name="username" required placeholder="manager@hotel.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-slate-700">Password</label>
                            <a href="<?= BASE_URL ?>hotel/forgot_password.php" class="text-xs font-semibold text-blue-600 hover:text-blue-500 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-11 pr-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-[0.98]">
                        Sign In <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-500 font-medium">
                    Don't have an account? <a href="<?= BASE_URL ?>hotel/register.php" class="text-blue-600 hover:text-blue-800 transition-colors border-b border-transparent hover:border-blue-600 pb-0.5">Register your hotel</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.to("#auth-container", {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: "power3.out",
                startAt: {
                    y: 30
                }
            });
        });
    </script>
</body>

</html>