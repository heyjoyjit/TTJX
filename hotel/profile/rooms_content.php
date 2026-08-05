<!-- hotel/profile/rooms_content.php -->
<div id="rooms-onboarding-wrapper" class="opacity-0 max-w-6xl mx-auto pb-16 mt-2 transition-opacity duration-500">

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
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base shadow-lg shadow-blue-500/30 ring-4 ring-white">2</div>
                <span class="text-[11px] sm:text-xs font-bold text-blue-600 mt-2">Rooms</span>
            </div>
            <div class="flex-1 h-1 bg-slate-200 -mt-5 mx-2"></div>
            <div class="flex flex-col items-center relative z-10">
                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-200 text-slate-400 rounded-full flex items-center justify-center font-bold text-sm sm:text-base ring-4 ring-white">3</div>
                <span class="text-[11px] sm:text-xs font-bold text-slate-400 mt-2">Review</span>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 sm:px-8 py-5 sm:py-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500/20 border border-blue-400/30 rounded-2xl flex items-center justify-center text-lg sm:text-xl text-blue-300 shrink-0">
                    <i class="fas fa-bed"></i>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight">Phase 2: Room Categories & Media</h2>
                    <p class="text-blue-200/70 text-xs sm:text-sm">Manage room types, capacities, amenity surcharges, and galleries.</p>
                </div>
            </div>
            <button type="button" id="addRoomBtn" class="bg-white text-slate-900 hover:bg-blue-50 font-bold py-2.5 px-5 rounded-xl shadow-lg text-xs transition-all flex items-center justify-center gap-2 active:scale-95 shrink-0">
                <i class="fas fa-plus text-blue-600"></i> Add Room Category
            </button>
        </div>

        <div class="p-5 sm:p-8">
            <?php if (empty($rooms)): ?>
                <div class="text-center py-12 sm:py-16 border-2 border-dashed border-slate-200 rounded-2xl sm:rounded-3xl bg-slate-50">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-4"><i class="fas fa-door-open"></i></div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800">No Rooms Configured</h3>
                    <p class="text-slate-500 text-xs sm:text-sm mb-6">Add at least one room category to configure your property profile.</p>
                    <button type="button" onclick="$('#addRoomBtn').click()" class="bg-blue-600 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-md hover:bg-blue-700 transition-all">+ Add First Room</button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <?php foreach ($rooms as $room): ?>
                        <div class="border border-slate-200/80 rounded-2xl p-5 sm:p-6 bg-slate-50/50 relative hover:border-blue-300 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-base sm:text-lg tracking-tight"><?= escape($room['room_title']) ?></h3>
                                    <p class="text-xs text-slate-500 mt-0.5"><i class="fas fa-bed text-blue-500 mr-1"></i> <?= $room['beds'] ?> Bed(s) | Max: <?= $room['adults'] ?> Adults, <?= $room['children'] ?> Kids</p>
                                </div>
                                <div class="flex gap-1.5 shrink-0">
                                    <button class="edit-room-btn w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all shadow-sm" data-id="<?= $room['id'] ?>" title="Edit Room"><i class="fas fa-edit text-xs"></i></button>
                                    <button class="delete-room-btn w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-all shadow-sm" data-id="<?= $room['id'] ?>" title="Delete Room"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </div>
                            <div class="bg-white border border-slate-100 rounded-xl p-3 text-xs text-slate-600 font-mono flex flex-wrap gap-3">
                                <span>TV: <strong><?= $room['tv_price'] > 0 ? '₹' . number_format($room['tv_price']) : 'Without' ?></strong></span>
                                <span>AC: <strong><?= $room['ac_price'] > 0 ? '₹' . number_format($room['ac_price']) : 'Without' ?></strong></span>
                                <span>WiFi: <strong><?= $room['wifi_price'] > 0 ? '₹' . number_format($room['wifi_price']) : 'Without' ?></strong></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-slate-50 border-t border-slate-100 p-4 sm:p-6 flex flex-col-reverse sm:flex-row justify-between items-center gap-3">
            <a href="<?= BASE_URL ?>hotel/profile/address.php" class="w-full sm:w-auto text-center text-slate-500 hover:text-slate-800 font-semibold text-xs sm:text-sm transition-colors flex items-center justify-center gap-2"><i class="fas fa-arrow-left"></i> Back to Address</a>
            <a href="<?= BASE_URL ?>hotel/profile/review.php" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/25 text-xs sm:text-sm transition-all flex items-center justify-center gap-2 active:scale-95">Continue to Step 3 <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- FIXED BUG 3: HIGH-DEFINITION RESPONSIVE ROOM MODAL -->
<div id="roomModal" class="hidden fixed inset-0 bg-slate-950/75 backdrop-blur-md z-[99999] flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-100 my-auto">

        <!-- Modal Header -->
        <div class="p-4 sm:p-6 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white shrink-0">
            <h2 class="text-base sm:text-lg font-bold" id="modalTitle">Configure Room Specification</h2>
            <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all"><i class="fas fa-times text-sm"></i></button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="overflow-y-auto p-4 sm:p-8 flex-1 space-y-6 sm:space-y-8 custom-scrollbar">
            <form id="roomForm" enctype="multipart/form-data" method="post" action="<?= BASE_URL ?>hotel/profile/rooms_save.php">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="room_id" id="roomId" value="0">

                <!-- 1. Room Specs -->
                <div>
                    <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3 flex items-center gap-1.5"><i class="fas fa-info-circle"></i> Basic Specifications</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-5">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Room Title *</label>
                            <input type="text" name="room_title" id="roomTitle" required placeholder="e.g. Deluxe Sea View Suite" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Total Beds *</label>
                            <input type="number" name="beds" id="beds" required min="1" value="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Max Adults *</label>
                            <input type="number" name="adults" id="adults" required min="1" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Max Children</label>
                            <input type="number" name="children" id="children" min="0" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- 2. Amenity Pricing with Toggles -->
                <div>
                    <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3 flex items-center gap-1.5"><i class="fas fa-tags"></i> Inclusions & Pricing (₹ / Night)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div class="bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-xl">
                            <label class="flex justify-between items-center font-bold text-xs text-slate-700 mb-2 cursor-pointer">
                                <span><i class="fas fa-tv text-slate-400 mr-1"></i> TV Included?</span>
                                <input type="checkbox" id="tvToggle" class="w-4 h-4 accent-blue-600 cursor-pointer">
                            </label>
                            <input type="number" name="tv_price" id="tvPrice" step="0.01" value="0" placeholder="TV Price ₹" class="w-full bg-white border rounded-lg py-1.5 px-3 text-xs outline-none hidden">
                        </div>

                        <div class="bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-xl">
                            <label class="flex justify-between items-center font-bold text-xs text-slate-700 mb-2 cursor-pointer">
                                <span><i class="fas fa-snowflake text-slate-400 mr-1"></i> AC Included?</span>
                                <input type="checkbox" id="acToggle" class="w-4 h-4 accent-blue-600 cursor-pointer">
                            </label>
                            <input type="number" name="ac_price" id="acPrice" step="0.01" value="0" placeholder="AC Price ₹" class="w-full bg-white border rounded-lg py-1.5 px-3 text-xs outline-none hidden">
                        </div>

                        <div class="bg-slate-50 border border-slate-200 p-3 sm:p-4 rounded-xl">
                            <label class="flex justify-between items-center font-bold text-xs text-slate-700 mb-2 cursor-pointer">
                                <span><i class="fas fa-wifi text-slate-400 mr-1"></i> WiFi Included?</span>
                                <input type="checkbox" id="wifiToggle" class="w-4 h-4 accent-blue-600 cursor-pointer">
                            </label>
                            <input type="number" name="wifi_price" id="wifiPrice" step="0.01" value="0" placeholder="WiFi Price ₹" class="w-full bg-white border rounded-lg py-1.5 px-3 text-xs outline-none hidden">
                        </div>
                    </div>
                </div>

                <!-- 3. Dynamic Custom Amenities -->
                <div>
                    <div class="flex justify-between items-center border-b pb-2 mb-3">
                        <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider"><i class="fas fa-concierge-bell mr-1"></i> Custom Amenities</h3>
                        <button type="button" id="addAmenityBtn" class="text-[11px] bg-blue-50 text-blue-600 font-bold px-2.5 py-1 rounded-lg hover:bg-blue-100 transition-colors">+ Add Custom</button>
                    </div>
                    <div id="amenitiesContainer" class="space-y-2"></div>
                </div>

                <!-- 4. Categorized Gallery Cards & Dynamic Custom Uploads -->
                <div>
                    <div class="flex justify-between items-center border-b pb-2 mb-3">
                        <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider"><i class="fas fa-photo-video mr-1"></i> Media Galleries (Photos, Videos & GIFs)</h3>
                        <button type="button" id="addCustomGalleryCardBtn" class="text-[11px] bg-indigo-50 text-indigo-600 font-bold px-2.5 py-1 rounded-lg hover:bg-indigo-100 transition-colors">+ Add Gallery Section</button>
                    </div>

                    <!-- Standard Upload Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs mb-4">
                        <?php
                        $cats = ['beds' => 'Beds', 'tv' => 'TV Area', 'inner_view' => 'Inner View', 'outer_view' => 'Outer View', 'ac' => 'AC Unit', 'balcony' => 'Balcony', 'fridge' => 'Minibar / Fridge', 'others' => 'Others'];
                        foreach ($cats as $key => $label):
                        ?>
                            <div class="bg-slate-50 border p-2.5 rounded-xl flex flex-col justify-between">
                                <label class="block font-bold text-slate-700 text-[11px] text-center mb-1"><?= $label ?></label>
                                <input type="file" name="gallery_<?= $key ?>[]" accept="image/*,video/*,image/gif" multiple class="w-full text-[10px] file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dynamic Custom Gallery Cards Container -->
                    <div id="customGalleryCardsContainer" class="space-y-2.5"></div>

                    <!-- Existing Gallery Previews -->
                    <div id="existingGallery" class="flex flex-wrap gap-2.5 mt-4 p-3 bg-slate-50 rounded-xl border border-slate-200 hidden"></div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2.5 shrink-0">
            <button type="button" onclick="closeModal()" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-200 rounded-xl text-xs sm:text-sm transition-colors">Cancel</button>
            <button type="button" onclick="$('#roomForm').submit()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-md text-xs sm:text-sm transition-all active:scale-95">Save Room Details</button>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        $('#roomModal').addClass('hidden');
        $('#roomForm')[0].reset();
        $('#roomId').val(0);
        $('#amenitiesContainer, #customGalleryCardsContainer, #existingGallery').empty();
        $('#existingGallery').addClass('hidden');
        $('#tvPrice, #acPrice, #wifiPrice').addClass('hidden').val(0);
        $('#tvToggle, #acToggle, #wifiToggle').prop('checked', false);
    }

    $(document).ready(function() {
        const wrapper = document.getElementById('rooms-onboarding-wrapper');
        if (typeof gsap !== 'undefined') {
            gsap.to("#rooms-onboarding-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6
            });
        } else if (wrapper) {
            wrapper.style.opacity = '1';
        }

        // Checkbox Toggle Handlers
        $('#tvToggle').change(function() {
            $('#tvPrice').toggleClass('hidden', !this.checked);
            if (!this.checked) $('#tvPrice').val(0);
        });
        $('#acToggle').change(function() {
            $('#acPrice').toggleClass('hidden', !this.checked);
            if (!this.checked) $('#acPrice').val(0);
        });
        $('#wifiToggle').change(function() {
            $('#wifiPrice').toggleClass('hidden', !this.checked);
            if (!this.checked) $('#wifiPrice').val(0);
        });

        // Add Dynamic Custom Amenity Row
        $('#addAmenityBtn').click(function() {
            $('#amenitiesContainer').append(`
                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <input type="text" name="amenity_title[]" placeholder="Amenity Title (e.g. Jacuzzi)" required class="flex-1 bg-white border border-slate-200 rounded-lg py-1 px-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="amenity_price[]" placeholder="Price ₹" required step="0.01" class="w-24 sm:w-28 bg-white border border-slate-200 rounded-lg py-1 px-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" class="text-rose-500 p-1 hover:bg-rose-50 rounded-lg" onclick="$(this).closest('div').remove()"><i class="fas fa-trash-alt text-xs"></i></button>
                </div>
            `);
        });

        // Add Dynamic Custom Gallery Section
        $('#addCustomGalleryCardBtn').click(function() {
            let cardId = Date.now();
            $('#customGalleryCardsContainer').append(`
                <div class="custom-gallery-card bg-indigo-50/60 border border-indigo-200 p-3 sm:p-4 rounded-xl relative">
                    <button type="button" class="absolute top-2.5 right-2.5 text-rose-500 hover:bg-rose-50 p-1 rounded-lg" onclick="$(this).closest('.custom-gallery-card').remove()">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pr-8">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Custom Section Title *</label>
                            <input type="text" name="custom_category_title[]" required placeholder="e.g. Pool Side Views" class="w-full bg-white border rounded-lg py-1 px-2.5 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Upload Media Files *</label>
                            <input type="file" name="custom_category_files_${cardId}[]" accept="image/*,video/*,image/gif" multiple required class="w-full text-[10px] file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-indigo-600 file:text-white">
                            <input type="hidden" name="custom_category_keys[]" value="${cardId}">
                        </div>
                    </div>
                </div>
            `);
        });

        // Add Room Button Trigger
        $('#addRoomBtn').click(function() {
            closeModal();
            $('#modalTitle').text('Add Room Category');
            $('#roomModal').removeClass('hidden');
        });

        // Edit Room Button Trigger
        $(document).on('click', '.edit-room-btn', function() {
            let id = $(this).data('id');
            $('#modalTitle').text('Edit Room Category');
            $('#roomId').val(id);
            if (typeof showLoader === 'function') showLoader();

            $.post('<?= BASE_URL ?>hotel/profile/rooms_get.php', {
                id: id,
                csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
            }, function(res) {
                if (typeof hideLoader === 'function') hideLoader();
                if (res.success) {
                    let d = res.data;
                    $('#roomTitle').val(d.room_title);
                    $('#beds').val(d.beds);
                    $('#adults').val(d.adults);
                    $('#children').val(d.children);

                    if (parseFloat(d.tv_price) > 0) {
                        $('#tvToggle').prop('checked', true);
                        $('#tvPrice').removeClass('hidden').val(d.tv_price);
                    }
                    if (parseFloat(d.ac_price) > 0) {
                        $('#acToggle').prop('checked', true);
                        $('#acPrice').removeClass('hidden').val(d.ac_price);
                    }
                    if (parseFloat(d.wifi_price) > 0) {
                        $('#wifiToggle').prop('checked', true);
                        $('#wifiPrice').removeClass('hidden').val(d.wifi_price);
                    }

                    $('#amenitiesContainer').empty();
                    if (d.amenities && d.amenities.length > 0) {
                        d.amenities.forEach(am => {
                            $('#amenitiesContainer').append(`
                                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                    <input type="text" name="amenity_title[]" value="${am.title}" required class="flex-1 bg-white border border-slate-200 rounded-lg py-1 px-2.5 text-xs outline-none">
                                    <input type="number" name="amenity_price[]" value="${am.price}" required step="0.01" class="w-24 sm:w-28 bg-white border border-slate-200 rounded-lg py-1 px-2.5 text-xs outline-none">
                                    <button type="button" class="text-rose-500 p-1 hover:bg-rose-50 rounded-lg" onclick="$(this).closest('div').remove()"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            `);
                        });
                    }

                    $('#existingGallery').empty();
                    if (d.gallery && d.gallery.length > 0) {
                        $('#existingGallery').removeClass('hidden');
                        d.gallery.forEach(g => {
                            let content = g.media_type === 'image' ?
                                `<img src="<?= BASE_URL ?>uploads/hotels/gallery/${g.media}" class="w-full h-full object-cover">` :
                                `<video class="w-full h-full object-cover" muted><source src="<?= BASE_URL ?>uploads/hotels/gallery/${g.media}"></video>`;

                            $('#existingGallery').append(`
                                <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden border shadow-sm group bg-slate-900" id="gal-${g.id}">
                                    ${content}
                                    <button type="button" class="absolute inset-0 bg-black/60 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center delete-gallery" data-id="${g.id}"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            `);
                        });
                    }
                    $('#roomModal').removeClass('hidden');
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });

        // Delete Gallery Item AJAX
        $(document).on('click', '.delete-gallery', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete media?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete'
            }).then((res) => {
                if (res.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.post('<?= BASE_URL ?>hotel/profile/rooms_delete_gallery.php', {
                        id: id,
                        csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                    }, function(r) {
                        if (typeof hideLoader === 'function') hideLoader();
                        if (r.success) {
                            $('#gal-' + id).remove();
                            if ($('#existingGallery').children().length === 0) $('#existingGallery').addClass('hidden');
                        } else Swal.fire('Error', r.message, 'error');
                    }, 'json');
                }
            });
        });

        // SYSTEM REQUIREMENT 1: Delete Room & Revert Onboarding if all rooms deleted
        $(document).on('click', '.delete-room-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Room Profile?',
                text: 'If you delete all rooms, your account will be reverted to the onboarding setup stage.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete Room'
            }).then((r) => {
                if (r.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.post('<?= BASE_URL ?>hotel/profile/rooms_delete.php', {
                        id: id,
                        csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                    }, function(res) {
                        if (typeof hideLoader === 'function') hideLoader();
                        if (res.success) {
                            if (res.redirect_to_onboarding) {
                                Swal.fire({
                                    title: 'Onboarding Required',
                                    text: res.message,
                                    icon: 'info',
                                    confirmButtonColor: '#3b82f6'
                                }).then(() => {
                                    window.location.href = res.redirect_url;
                                });
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        });

        // Submit Room Form
        $('#roomForm').submit(function(e) {
            e.preventDefault();
            if (typeof showLoader === 'function') showLoader();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) {
                        Swal.fire({
                            title: 'Saved!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else Swal.fire('Error', res.message, 'error');
                },
                error: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                    Swal.fire('Error', 'Server connection error during upload.', 'error');
                }
            });
        });
    });
</script>