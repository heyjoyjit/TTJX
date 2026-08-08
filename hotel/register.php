<?php
// hotel/register.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

if (isHotelOwnerLoggedIn()) {
    redirect(BASE_URL . 'hotel/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Registration | <?= defined('SITE_NAME') ? SITE_NAME : 'TravelTara' ?></title>
    <icon rel="icon" href="<?= BASE_URL ?>assets/images/favicon.ico" type="image/x-icon">
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
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 py-10">

    <div id="loaderOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] hidden flex justify-center items-center">
        <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center">
            <i class="fas fa-circle-notch fa-spin text-4xl text-blue-600 mb-3"></i>
            <p class="text-slate-700 font-semibold">Creating your account...</p>
        </div>
    </div>

    <div id="auth-container" class="opacity-0 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-90"></div>
            <div class="relative z-10">
                <h1 class="text-3xl font-black text-white tracking-tight mb-2">Partner With Us</h1>
                <p class="text-blue-100 font-medium">Join our network and manage your hotel bookings seamlessly.</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8 md:p-10">
            <form id="registerForm" method="post" action="<?= BASE_URL ?>hotel/register_process.php">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Hotel Details -->
                    <div class="space-y-5">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 mb-4">1. Hotel Details</h3>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hotel Name *</label>
                            <input type="text" name="hotel_name" required placeholder="e.g. Grand Plaza Resort" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hotel Email (Login ID) *</label>
                            <input type="email" name="email" required placeholder="contact@hotel.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hotel Phone *</label>
                            <input type="text" name="phone" required placeholder="+91 9876543210" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">City / Location *</label>
                            <input type="text" name="location" required placeholder="e.g. Goa, India" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <!-- Owner Details -->
                    <div class="space-y-5">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 mb-4">2. Owner / Manager Details</h3>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="owner_name" required placeholder="John Doe" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Direct Phone *</label>
                            <input type="text" name="owner_phone" required placeholder="+91 9123456780" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Personal Email <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="email" name="owner_email" placeholder="john.doe@personal.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Create Password *</label>
                            <input type="password" name="password" required minlength="6" placeholder="Min. 6 characters" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-slate-100 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-slate-500 font-medium text-center md:text-left">
                        Already registered? <a href="<?= BASE_URL ?>hotel/login.php" class="text-blue-600 hover:text-blue-800 transition-colors border-b border-transparent hover:border-blue-600 pb-0.5">Back to Login</a>
                    </p>
                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-[0.98]">
                        Complete Registration <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            gsap.to("#auth-container", {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: "power3.out",
                startAt: {
                    y: 30
                }
            });

            $('#registerForm').submit(function(e) {
                e.preventDefault();
                $('#loaderOverlay').removeClass('hidden').addClass('flex');

                $.post($(this).attr('action'), $(this).serialize(), function(res) {
                    $('#loaderOverlay').removeClass('flex').addClass('hidden');
                    if (res.success) {
                        Swal.fire({
                                title: 'Welcome!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            })
                            .then(() => window.location.href = '<?= BASE_URL ?>hotel/profile/address.php');
                    } else {
                        Swal.fire('Registration Failed', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    $('#loaderOverlay').removeClass('flex').addClass('hidden');
                    Swal.fire('Error', 'Server connection failed', 'error');
                });
            });
        });
    </script>
</body>

</html>