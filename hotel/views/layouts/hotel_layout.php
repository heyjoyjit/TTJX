<?php
// views/layouts/hotel_layout.php
requireHotelOwner();
$csrf_token = generateCsrfToken();

$partnerEmail = $_SESSION['hotel_username'] ?? 'Partner';
$userInitial  = !empty($partnerEmail) ? strtoupper(substr($partnerEmail, 0, 1)) : 'P';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?= $csrf_token ?>">
    <title><?= escape($page_title ?? 'Partner Portal') ?> | TravelTara</title>

    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        div.swal2-container {
            z-index: 999999 !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 overflow-hidden flex h-screen w-screen">

    <!-- Global Screen Blocker Loader -->
    <div id="loaderOverlay" class="fixed inset-0 bg-slate-900/70 backdrop-blur-md z-[99990] hidden justify-center items-center">
        <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center">
            <i class="fas fa-circle-notch fa-spin text-4xl text-blue-600 mb-3"></i>
            <p class="text-slate-700 font-bold text-sm tracking-wide">Processing...</p>
        </div>
    </div>

    <!-- Mobile Drawer Backdrop -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[40] hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Responsive Sidebar Drawer -->
    <aside id="sidebar" class="bg-slate-900 text-slate-300 w-72 h-full flex flex-col transition-transform duration-300 transform -translate-x-full lg:translate-x-0 fixed lg:relative z-[50] shadow-2xl lg:shadow-none shrink-0">
        <div class="h-16 sm:h-20 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/50">
            <div class="flex items-center gap-3 text-white font-black tracking-tight text-lg sm:text-xl">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30 text-sm">
                    <i class="fas fa-hotel text-white"></i>
                </div>
                Partner Portal
            </div>
            <button class="lg:hidden text-slate-400 hover:text-white p-1" onclick="toggleSidebar()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto custom-scrollbar py-6 px-4 space-y-1">
            <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>

            <a href="<?= BASE_URL ?>hotel/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors group <?= strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : '' ?>">
                <i class="fas fa-chart-pie w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>hotel/profile/address.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors group <?= strpos($_SERVER['REQUEST_URI'], 'profile') !== false ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : '' ?>">
                <i class="fas fa-address-card w-5 text-center <?= strpos($_SERVER['REQUEST_URI'], 'profile') !== false ? 'text-white' : 'text-slate-400 group-hover:text-blue-400' ?>"></i>
                <span class="font-medium text-sm">Hotel Profile & Setup</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-900">
            <a href="<?= BASE_URL ?>hotel/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors group">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="font-medium text-sm">Secure Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-slate-50">

        <!-- Header Bar -->
        <header class="h-16 sm:h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 z-10 shrink-0">
            <div class="flex items-center gap-3">
                <button class="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate max-w-[180px] sm:max-w-none"><?= escape($page_title ?? 'Overview') ?></h2>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-xs sm:text-sm font-bold text-slate-800 truncate max-w-[160px]"><?= escape($partnerEmail) ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-emerald-500">Partner Account</span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-white">
                    <?= $userInitial ?>
                </div>
            </div>
        </header>

        <!-- Dynamic Content View -->
        <main class="flex-1 overflow-y-auto p-3 sm:p-6 lg:p-8 custom-scrollbar">
            <?php
            if (!empty($content_view) && file_exists($content_view)) {
                include $content_view;
            } else {
                echo '<div class="p-8 text-center text-rose-500 font-bold">Error: View file not found.</div>';
            }
            ?>
        </main>
    </div>

    <script>
        function showLoader() {
            $('#loaderOverlay').removeClass('hidden').addClass('flex');
        }

        function hideLoader() {
            $('#loaderOverlay').removeClass('flex').addClass('hidden');
        }

        function getCsrfToken() {
            return $('meta[name="csrf-token"]').attr('content');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
</body>

</html>