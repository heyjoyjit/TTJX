<?php
// admin/views/layouts/admin_layout.php
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> | Traveltara Admin</title>

    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <!-- Add this to your layout files' <head> section -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 & GSAP -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= generateCsrfToken() ?>">

    <style>
        /* Custom Scrollbar for a premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 overflow-hidden antialiased">

    <!-- Tailwind Native Loader Overlay -->
    <div id="loaderOverlay" style="display:none;" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[99999] flex justify-center items-center">
        <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- Layout Wrapper -->
    <div class="flex h-screen w-full relative">

        <!-- Responsive Sidebar -->
        <aside id="admin-sidebar" class="w-64 bg-slate-900 flex flex-col h-full shadow-2xl fixed inset-y-0 left-0 z-40 transform -translate-x-full transition-transform duration-300 md:relative md:translate-x-0">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center justify-between px-6 bg-slate-950/50 border-b border-slate-800 shrink-0">
                <div class="flex items-center">
                    <img src="<?= BASE_URL ?>assets/images/icon.png" alt="Traveltara Logo" class="h-8 w-auto mr-3">
                    <span class="text-xl font-bold text-white tracking-wide">Traveltara</span>
                </div>
                <!-- Mobile Close Button -->
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Menu</p>

                <a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-tachometer-alt w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Dashboard</span>
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Company Settings</p>
                <a href="<?= BASE_URL ?>admin/company_settings/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-building w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Company Settings</span>
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Travel Operations</p>

                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-map-marker-alt w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Destinations</span>
                </a>

                <a href="<?= BASE_URL ?>admin/packages/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-boxes w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Packages</span>
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Partners & Finance</p>

                <a href="<?= BASE_URL ?>admin/hotels/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-hotel w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Hotels</span>
                </a>

                <a href="<?= BASE_URL ?>admin/invoices/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-file-invoice w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Invoices</span>
                </a>

                <a href="<?= BASE_URL ?>admin/payment_links/index.php" class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all group text-white">
                    <i class="fas fa-link w-6 text-center text-slate-400 group-hover:text-blue-400 transition-colors"></i>
                    <span class="ml-2 font-medium">Payment Links</span>
                </a>
            </nav>

            <!-- Bottom Profile / Logout -->
            <div class="p-4 border-t border-slate-800 bg-slate-900 shrink-0">
                <a href="<?= BASE_URL ?>admin/logout.php" class="flex items-center justify-center w-full bg-slate-800 hover:bg-red-500/10 text-slate-400 hover:text-red-500 py-2 rounded-lg transition-all border border-slate-700 hover:border-red-500/30" style="display: flex !important; visibility: visible !important;">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span class="font-medium text-sm">Secure Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col relative min-w-0 bg-slate-50 h-screen overflow-hidden">

            <!-- Top Navbar -->
            <header id="admin-navbar" class="h-16 bg-white border-b border-slate-200 flex items-center justify-between pl-4 pr-6 md:pl-6 md:pr-8 z-20 sticky top-0 shrink-0" style="opacity: 1 !important; filter: none !important;">

                <!-- Left Side: Title & Menu -->
                <div class="flex items-center min-w-0">
                    <!-- Mobile Menu Toggle -->
                    <button onclick="toggleSidebar()" class="md:hidden text-slate-500 hover:text-blue-600 focus:outline-none mr-3 transition-colors p-2 rounded-lg hover:bg-slate-100 shrink-0">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-lg font-semibold text-slate-700 truncate"><?= $page_title ?? 'Admin Dashboard' ?></h2>
                </div>

                <!-- Right Side: Notifications & Profile -->
                <div class="flex items-center space-x-3 md:space-x-4 shrink-0 ml-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-400 hover:text-blue-500 transition-colors rounded-full hover:bg-slate-100">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Admin Profile -->
                    <div class="flex items-center gap-3 pl-3 md:pl-4 border-l border-slate-200">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-slate-700"><?= escape($_SESSION['admin_username']) ?></p>
                            <p class="text-xs text-slate-500">Administrator</p>
                        </div>
                        <!-- Added shrink-0 to prevent squishing -->
                        <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-inner shrink-0">
                            <?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <main id="admin-main-content" class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 custom-scrollbar">
                <?php include $content_view; ?>
            </main>

        </div>
    </div>

    <!-- Global JS & Entry Animations -->
    <script src="<?= BASE_URL ?>assets/js/admin.js"></script>
    <script>
        // --- 1. Mobile Sidebar Toggle Logic ---
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            // Strictly toggle Tailwind classes (No GSAP inline interference)
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        document.addEventListener("DOMContentLoaded", () => {

            // --- 2. Safe highlighting logic ---
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(link => {
                if (link.getAttribute('href').includes(currentPath) && currentPath !== '/') {
                    // Use Tailwind classes instead of inline styles so GSAP doesn't wipe them out
                    link.classList.add('bg-blue-600/20', 'border-r-4', 'border-blue-500', 'text-white');

                    const icon = link.querySelector('i');
                    if (icon) {
                        icon.classList.remove('text-slate-400');
                        icon.classList.add('text-blue-400');
                    }
                }
            });

            // --- 3. GSAP Animations (FIXED) ---
            if (typeof gsap !== 'undefined') {
                const tl = gsap.timeline();
                const isDesktop = window.innerWidth >= 768;

                // Only animate the sidebar on Desktop
                if (isDesktop) {
                    tl.from("#admin-sidebar", {
                        x: -50,
                        opacity: 0,
                        duration: 0.5,
                        ease: "power2.out",
                        clearProps: "all"
                    }).from(".nav-item", {
                        x: -15,
                        opacity: 0,
                        duration: 0.3,
                        stagger: 0.05,
                        ease: "power1.out",
                        clearProps: "all"
                    }, "-=0.2");
                }

                // Animate the main content, but LEAVE THE NAVBAR ALONE so it never gets stuck
                tl.from("#admin-main-content", {
                    y: 20,
                    opacity: 0,
                    duration: 0.4,
                    ease: "power2.out",
                    clearProps: "all"
                }, isDesktop ? "-=0.2" : "+=0");
            }
        });
    </script>
</body>

</html>