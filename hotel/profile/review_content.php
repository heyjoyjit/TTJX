<!-- hotel/profile/review_content.php -->
<div id="review-wrapper" class="opacity-0 max-w-5xl mx-auto pb-12 mt-2 transition-opacity duration-500">

    <!-- Stepper -->
    <div class="mb-8 sm:mb-12 text-center">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight mb-6 sm:mb-8">Set Up Your Hotel Profile</h1>
        <div class="flex items-center justify-center max-w-md sm:max-w-xl mx-auto px-4">
            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-emerald-500 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/30 ring-4 ring-white"><i class="fas fa-check"></i></div>
                <span class="text-[11px] sm:text-xs font-bold text-emerald-600 mt-2">Location</span>
            </div>
            <div class="flex-1 h-1 bg-emerald-500 -mt-5 mx-2"></div>
            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-emerald-500 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/30 ring-4 ring-white"><i class="fas fa-check"></i></div>
                <span class="text-[11px] sm:text-xs font-bold text-emerald-600 mt-2">Rooms</span>
            </div>
            <div class="flex-1 h-1 bg-blue-600 -mt-5 mx-2"></div>
            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base shadow-lg shadow-blue-500/30 ring-4 ring-white">3</div>
                <span class="text-[11px] sm:text-xs font-bold text-blue-600 mt-2">Review</span>
            </div>
        </div>
    </div>

    <!-- Review Container -->
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="bg-slate-900 px-6 sm:px-8 py-5 sm:py-6 text-white relative flex items-center gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-lg sm:text-xl shadow-inner shrink-0">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold tracking-tight">Step 3: Final Review & Submit</h2>
                <p class="text-blue-100 text-xs sm:text-sm">Verify your details before completing profile onboarding.</p>
            </div>
        </div>

        <div class="p-5 sm:p-8 space-y-6 sm:space-y-8">
            <!-- Property Info -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 mb-3 flex items-center gap-1.5"><i class="fas fa-building text-blue-500"></i> Property Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 bg-slate-50 p-4 sm:p-6 rounded-2xl border border-slate-100 text-xs sm:text-sm">
                    <div><span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase block">Hotel Name</span>
                        <p class="font-bold text-slate-800"><?= escape($hotel['hotel_name']) ?></p>
                    </div>
                    <div><span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase block">Registered ID</span>
                        <p class="font-mono text-blue-600 font-bold"><?= escape($hotel['hotel_registered_id']) ?></p>
                    </div>
                    <div><span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase block">Email Address</span>
                        <p class="font-medium text-slate-800"><?= escape($hotel['email']) ?></p>
                    </div>
                    <div><span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase block">Contact Phone</span>
                        <p class="font-medium text-slate-800"><?= escape($hotel['phone']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Address Info -->
            <div>
                <div class="flex justify-between items-center border-b border-slate-100 pb-2 mb-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-emerald-500"></i> Verified Address</h3>
                    <a href="<?= BASE_URL ?>hotel/profile/address.php" class="text-xs font-bold text-blue-600 hover:underline">Edit Address</a>
                </div>

                <?php if ($address): ?>
                    <div class="bg-emerald-50 p-4 sm:p-6 rounded-2xl border border-emerald-100 flex items-start gap-3 sm:gap-4 text-xs sm:text-sm">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center shrink-0"><i class="fas fa-check"></i></div>
                        <div>
                            <p class="font-bold text-slate-800 mb-1"><?= escape($address['address_line1']) ?><?= $address['address_line2'] ? ', ' . escape($address['address_line2']) : '' ?></p>
                            <p class="text-slate-600"><?= escape($address['city']) ?>, <?= escape($address['state']) ?> - <?= escape($address['zip']) ?>, <?= escape($address['country']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-rose-50 p-5 rounded-2xl border border-rose-100 text-center text-xs sm:text-sm">
                        <p class="text-rose-700 font-bold mb-2">Address details missing</p>
                        <a href="<?= BASE_URL ?>hotel/profile/address.php" class="inline-block bg-white text-rose-600 font-bold px-4 py-2 rounded-xl border border-rose-200 shadow-sm">Complete Address</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rooms Info -->
            <div>
                <div class="flex justify-between items-center border-b border-slate-100 pb-2 mb-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-bed text-indigo-500"></i> Rooms Configured (<?= count($rooms) ?>)</h3>
                    <a href="<?= BASE_URL ?>hotel/profile/rooms.php" class="text-xs font-bold text-blue-600 hover:underline">Manage Rooms</a>
                </div>

                <?php if ($rooms): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <?php foreach ($rooms as $room): ?>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 sm:p-4 text-xs sm:text-sm">
                                <h4 class="font-bold text-slate-800 mb-1"><?= escape($room['room_title']) ?></h4>
                                <p class="text-slate-500"><i class="fas fa-bed text-slate-400 mr-1"></i> <?= $room['beds'] ?> Bed(s) | <?= $room['adults'] ?> Adults, <?= $room['children'] ?> Kids</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-rose-50 p-5 rounded-2xl border border-rose-100 text-center text-xs sm:text-sm">
                        <p class="text-rose-700 font-bold mb-2">No rooms configured</p>
                        <a href="<?= BASE_URL ?>hotel/profile/rooms.php" class="inline-block bg-white text-rose-600 font-bold px-4 py-2 rounded-xl border border-rose-200 shadow-sm">Add Rooms</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="bg-slate-50 border-t border-slate-100 p-4 sm:p-6 flex flex-col-reverse sm:flex-row justify-between items-center gap-3">
            <a href="<?= BASE_URL ?>hotel/profile/rooms.php" class="w-full sm:w-auto text-center text-slate-500 hover:text-slate-800 font-semibold text-xs sm:text-sm transition-colors flex items-center justify-center gap-2"><i class="fas fa-arrow-left"></i> Back to Rooms</a>
            <button id="submitBtn" <?= (!$address || empty($rooms)) ? 'disabled class="w-full sm:w-auto opacity-50 cursor-not-allowed bg-slate-400 text-white font-bold py-3 px-8 rounded-xl text-xs sm:text-sm"' : 'class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-emerald-500/25 text-xs sm:text-sm transition-all active:scale-95 flex items-center justify-center gap-2"' ?>>
                <i class="fas fa-paper-plane"></i> Finalize Onboarding
            </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const wrapper = document.getElementById('review-wrapper');
        if (typeof gsap !== 'undefined') {
            gsap.to("#review-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6
            });
        } else if (wrapper) {
            wrapper.style.opacity = '1';
        }

        $('#submitBtn').click(function() {
            if ($(this).is(':disabled')) return;

            Swal.fire({
                title: 'Finalize Onboarding Profile?',
                text: 'Your partner dashboard will be unlocked.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Yes, Submit Profile'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.post('<?= BASE_URL ?>hotel/profile/submit.php', {
                        csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                    }, function(res) {
                        if (typeof hideLoader === 'function') hideLoader();
                        if (res.success) {
                            Swal.fire({
                                title: 'Welcome!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => window.location.href = '<?= BASE_URL ?>hotel/dashboard.php');
                        } else Swal.fire('Error', res.message, 'error');
                    }, 'json');
                }
            });
        });
    });
</script>