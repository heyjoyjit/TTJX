<!-- hotel/profile/address_content.php -->
<div id="onboarding-wrapper" class="opacity-0 max-w-4xl mx-auto pb-12 mt-2 transition-opacity duration-500">

    <!-- Responsive Stepper -->
    <div class="mb-8 sm:mb-12">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight text-center mb-6 sm:mb-8">Set Up Your Hotel Profile</h1>

        <div class="flex items-center justify-center max-w-md sm:max-w-xl mx-auto px-4">
            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base shadow-lg shadow-blue-500/30 ring-4 ring-white">1</div>
                <span class="text-[11px] sm:text-xs font-bold text-blue-600 mt-2">Location</span>
            </div>

            <div class="flex-1 h-1 bg-slate-200 -mt-5 mx-2"></div>

            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-200 text-slate-400 rounded-full flex items-center justify-center font-bold text-sm sm:text-base ring-4 ring-white">2</div>
                <span class="text-[11px] sm:text-xs font-bold text-slate-400 mt-2">Rooms</span>
            </div>

            <div class="flex-1 h-1 bg-slate-200 -mt-5 mx-2"></div>

            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-200 text-slate-400 rounded-full flex items-center justify-center font-bold text-sm sm:text-base ring-4 ring-white">3</div>
                <span class="text-[11px] sm:text-xs font-bold text-slate-400 mt-2">Review</span>
            </div>
        </div>
    </div>

    <!-- Form Box -->
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="bg-slate-900 px-6 sm:px-8 py-5 sm:py-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-90"></div>
            <div class="relative z-10 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-lg sm:text-xl shadow-inner shrink-0">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight">Step 1: Property Address</h2>
                    <p class="text-blue-100 text-xs sm:text-sm">Where exactly is your property located?</p>
                </div>
            </div>
        </div>

        <form id="addressForm" method="post" action="<?= BASE_URL ?>hotel/profile/address_save.php" class="p-5 sm:p-8 lg:p-10 space-y-5 sm:space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Street Address <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fas fa-map-pin absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" name="address_line1" required placeholder="e.g. 123 Ocean View Boulevard" value="<?= escape($address['address_line1'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 pl-11 pr-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Apartment, suite, etc. <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <div class="relative">
                        <i class="fas fa-building absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" name="address_line2" placeholder="e.g. Building A, Floor 2" value="<?= escape($address['address_line2'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 pl-11 pr-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">City / Town / Village <span class="text-rose-500">*</span></label>
                    <input type="text" name="city" required placeholder="e.g. Calangute" value="<?= escape($address['city'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">State / Province <span class="text-rose-500">*</span></label>
                    <input type="text" name="state" required placeholder="e.g. Goa" value="<?= escape($address['state'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Country <span class="text-rose-500">*</span></label>
                    <input type="text" name="country" required placeholder="e.g. India" value="<?= escape($address['country'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Zip / Postal Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="zip" required placeholder="e.g. 403516" value="<?= escape($address['zip'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-4 text-xs sm:text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 sm:pt-8 border-t border-slate-100 flex flex-col-reverse sm:flex-row justify-between items-center gap-3 sm:gap-4">
                <!-- FIXED BUG 1: Points directly to Phase 2 (rooms.php) instead of causing a redirect loop -->
                <a href="<?= BASE_URL ?>hotel/profile/rooms.php" class="w-full sm:w-auto text-center py-2.5 px-4 text-slate-500 hover:text-slate-800 font-semibold text-xs sm:text-sm transition-colors">
                    Skip for now <i class="fas fa-chevron-right ml-1"></i>
                </a>
                <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/25 text-xs sm:text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                    Save & Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        const wrapper = document.getElementById('onboarding-wrapper');
        if (typeof gsap !== 'undefined') {
            gsap.to("#onboarding-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6
            });
        } else if (wrapper) {
            wrapper.style.opacity = '1';
        }

        $('#addressForm').submit(function(e) {
            e.preventDefault();
            if (typeof showLoader === 'function') showLoader();

            $.post($(this).attr('action'), $(this).serialize(), function(res) {
                if (typeof hideLoader === 'function') hideLoader();

                if (res.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '<?= BASE_URL ?>hotel/profile/rooms.php';
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                if (typeof hideLoader === 'function') hideLoader();
                Swal.fire('Error', 'Server connection failed. Please try again.', 'error');
            });
        });
    });
</script>