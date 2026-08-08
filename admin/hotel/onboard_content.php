<?php
// admin/hotel/onboard_content.php
$db = Database::getInstance()->getConnection();

// Fetch Hotel Details
$stmt = $db->prepare("SELECT h.*, d.place_name FROM hotels h LEFT JOIN destinations d ON h.destination_id = d.id WHERE h.id = ?");
$stmt->execute([$id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    echo '<div class="text-red-500 p-8 text-center font-semibold text-xl">Hotel record not found.</div>';
    return;
}

// Fetch Phase 1 Address
$addrStmt = $db->prepare("SELECT * FROM hotel_addresses WHERE hotel_id = ?");
$addrStmt->execute([$id]);
$address = $addrStmt->fetch();

// Fetch Phase 2 Rooms
$roomStmt = $db->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY id ASC");
$roomStmt->execute([$id]);
$rooms = $roomStmt->fetchAll();
?>

<!-- GLOBAL FULLSCREEN INTERACTIVE LOADER OVERLAY -->
<div id="globalLoader" class="fixed inset-0 z-[99999] bg-slate-900/80 backdrop-blur-md hidden flex-col items-center justify-center transition-all duration-300">
    <div class="relative flex items-center justify-center mb-4">
        <div class="w-20 h-20 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
        <i class="fas fa-cloud-upload-alt absolute text-2xl text-indigo-400 animate-pulse"></i>
    </div>
    <h3 class="text-xl font-bold text-white tracking-wide" id="loaderTitle">Processing Media & Data...</h3>
    <p class="text-slate-400 text-sm mt-1" id="loaderSub">Please wait while the system synchronizes changes.</p>
</div>

<!-- MEDIA LIGHTBOX MODAL -->
<div id="lightboxModal" class="fixed inset-0 z-[99998] bg-black/90 backdrop-blur-lg hidden flex-col items-center justify-center p-4">
    <button type="button" onclick="closeLightbox()" class="absolute top-6 right-6 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xl transition-all">
        <i class="fas fa-times"></i>
    </button>
    <div id="lightboxContent" class="max-w-5xl max-h-[85vh] overflow-hidden rounded-2xl flex items-center justify-center"></div>
</div>

<div id="onboard-hotel-wrapper" class="opacity-0 max-w-6xl mx-auto pb-16 pt-4">

    <!-- HEADER BAR -->
    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/80 backdrop-blur-md p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="<?= BASE_URL ?>admin/hotel/index.php" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Onboarding Suite</h1>
            </div>
            <p class="text-sm text-slate-500 ml-13">Property: <span class="font-bold text-indigo-600"><?= escape($hotel['hotel_name']) ?></span> (ID: <code class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-mono text-xs"><?= escape($hotel['hotel_registered_id']) ?></code>)</p>
        </div>
        <div>
            <?php if ($hotel['is_onboarded']): ?>
                <span class="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 px-5 py-2.5 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> 100% Onboarding Live
                </span>
            <?php else: ?>
                <span class="bg-amber-500/10 text-amber-600 border border-amber-500/20 px-5 py-2.5 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span> Setup Pending
                </span>
            <?php endif; ?>
        </div>
    </header>

    <!-- PHASE 1: ADDRESS PROOF & LOCATION -->
    <form id="onboardAddressForm" method="post" action="<?= BASE_URL ?>admin/hotel/onboard_save.php" class="space-y-8 mb-10">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $hotel['id'] ?>">

        <section class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 text-white flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 backdrop-blur-md flex items-center justify-center text-xl text-indigo-300">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Phase 1: Location & Verified Address</h2>
                    <p class="text-indigo-200/70 text-xs">Set up the exact physical street location for guest navigation.</p>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Street Address Line 1 *</label>
                    <input type="text" name="address_line1" required value="<?= escape($address['address_line1'] ?? '') ?>" placeholder="e.g. 123 Ocean View Boulevard" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Address Line 2 <span class="text-slate-400 font-normal lowercase">(optional)</span></label>
                    <input type="text" name="address_line2" value="<?= escape($address['address_line2'] ?? '') ?>" placeholder="e.g. Suite 400, Building B" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">City / Town / Village *</label>
                    <input type="text" name="city" required value="<?= escape($address['city'] ?? '') ?>" placeholder="e.g. Calangute" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">State / Province *</label>
                    <input type="text" name="state" required value="<?= escape($address['state'] ?? '') ?>" placeholder="e.g. Goa" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Country *</label>
                    <input type="text" name="country" required value="<?= escape($address['country'] ?? '') ?>" placeholder="e.g. India" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Zip / Postal Code *</label>
                    <input type="text" name="zip" required value="<?= escape($address['zip'] ?? '') ?>" placeholder="e.g. 403516" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-slate-800 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-500/25 transition-all flex items-center gap-2 active:scale-95">
                    <i class="fas fa-save"></i> Save Address Proof
                </button>
            </div>
        </section>
    </form>

    <!-- PHASE 2: ROOM & AMENITY MEDIA MANAGEMENT -->
    <section class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-violet-950 to-slate-900 p-6 text-white flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-500/20 border border-violet-400/30 backdrop-blur-md flex items-center justify-center text-xl text-violet-300">
                    <i class="fas fa-bed"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Phase 2: Rooms, Amenities & Media</h2>
                    <p class="text-violet-200/70 text-xs">Configure room specifications, custom amenities, and multi-category media galleries.</p>
                </div>
            </div>
            <button type="button" id="adminAddRoomBtn" class="bg-white text-slate-900 hover:bg-violet-50 font-bold py-2.5 px-5 rounded-xl shadow-lg text-xs transition-all flex items-center gap-2 active:scale-95">
                <i class="fas fa-plus text-indigo-600"></i> Add Room Category
            </button>
        </div>

        <div class="p-8">
            <?php if (empty($rooms)): ?>
                <div class="text-center py-16 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Room Profiles Configured</h3>
                    <p class="text-slate-500 text-xs max-w-sm mx-auto mt-1 mb-6">Add at least one room category with photos/videos to bring this hotel to 100% onboarding status.</p>
                    <button type="button" onclick="$('#adminAddRoomBtn').click()" class="bg-indigo-600 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-md hover:bg-indigo-700 transition-all">+ Add First Room</button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($rooms as $r): ?>
                        <div class="border border-slate-200/80 rounded-2xl p-6 bg-slate-50/50 relative hover:border-indigo-300 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg tracking-tight"><?= escape($r['room_title']) ?></h3>
                                    <p class="text-xs text-slate-500"><i class="fas fa-bed text-indigo-400 mr-1"></i> <?= $r['beds'] ?> Bed(s) | Capacity: <?= $r['adults'] ?> Adults, <?= $r['children'] ?> Children</p>
                                </div>
                                <div class="flex gap-1.5 opacity-90 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="admin-edit-room-btn w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center shadow-sm transition-all" data-id="<?= $r['id'] ?>" title="Edit Room & Media">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button type="button" class="admin-delete-room-btn w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center shadow-sm transition-all" data-id="<?= $r['id'] ?>" title="Delete Room">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white border border-slate-100 rounded-xl p-3 text-xs text-slate-600 space-y-1 mb-2">
                                <span class="font-bold text-slate-400 block uppercase text-[10px] tracking-wider mb-1">Standard Amenities Extra Rates</span>
                                <div class="flex flex-wrap gap-3 font-mono text-[11px]">
                                    <span>TV: <strong class="text-slate-800">₹<?= number_format($r['tv_price']) ?></strong></span>
                                    <span>AC: <strong class="text-slate-800">₹<?= number_format($r['ac_price']) ?></strong></span>
                                    <span>WiFi: <strong class="text-slate-800">₹<?= number_format($r['wifi_price']) ?></strong></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

</div>

<!-- ADMIN ROOM & UNLIMITED MEDIA MODAL -->
<div id="adminRoomModal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-100">

        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold" id="adminModalTitle">Configure Room Profile</h2>
                    <p class="text-xs text-slate-400">Set capacity, amenity surcharges, and media galleries.</p>
                </div>
            </div>
            <button type="button" onclick="closeAdminModal()" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="overflow-y-auto p-8 flex-1 space-y-8 custom-scrollbar">
            <form id="adminRoomForm" enctype="multipart/form-data" method="post" action="<?= BASE_URL ?>admin/hotel/admin_rooms_save.php">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
                <input type="hidden" name="room_id" id="adminRoomId" value="0">

                <!-- Room Specifications -->
                <div>
                    <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Basic Room Specifications
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Room Title *</label>
                            <input type="text" name="room_title" id="adminRoomTitle" required placeholder="e.g. Presidential Ocean View Suite" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Total Beds *</label>
                            <input type="number" name="beds" id="adminBeds" required min="1" value="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Max Adults *</label>
                            <input type="number" name="adults" id="adminAdults" required min="1" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Max Children</label>
                            <input type="number" name="children" id="adminChildren" min="0" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <br/>

                <!-- Standard Amenities Pricing -->
                <div>
                    <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-tags"></i> Extra Amenity Surcharges <span class="text-[10px] text-slate-400 font-normal">(₹ / night)</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="fas fa-tv text-slate-400 mr-1"></i> TV Rate</label>
                            <input type="number" step="0.01" name="tv_price" id="adminTvPrice" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="fas fa-snowflake text-slate-400 mr-1"></i> AC Rate</label>
                            <input type="number" step="0.01" name="ac_price" id="adminAcPrice" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="fas fa-wifi text-slate-400 mr-1"></i> WiFi Rate</label>
                            <input type="number" step="0.01" name="wifi_price" id="adminWifiPrice" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <br/>

                <!-- Custom Amenities Section -->
                <div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2 mb-3">
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-concierge-bell"></i> Custom Amenities & Services
                        </h3>
                        <button type="button" id="adminAddAmenityBtn" class="text-xs bg-indigo-50 text-indigo-600 font-bold px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors">
                            + Add Amenity
                        </button>
                    </div>
                    <div id="adminAmenitiesContainer" class="space-y-2"></div>
                </div>

                <br/>

                <!-- UNLIMITED MULTI-CATEGORY MEDIA GALLERIES -->
                <div>
                    <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i class="fas fa-photo-video"></i> Unlimited Media Uploads (Photos & Videos)
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">Select multiple images or video clips per section. You can preview and dismiss items before saving.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <?php
                        $cats = [
                            'beds' => 'Bed Setup Photos/Videos',
                            'tv' => 'TV Area & Entertainment',
                            'ac' => 'Climate Control / AC',
                            'balcony' => 'Balcony & Views',
                            'fridge' => 'Minibar & Fridge',
                            'inner_view' => 'Interior Views',
                            'outer_view' => 'Exterior / Corridor',
                            'amenities' => 'Amenity Photos/Videos',
                            'others' => 'Other Documents/Media'
                        ];
                        foreach ($cats as $key => $label):
                        ?>
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between">
                                <label class="block font-bold text-slate-700 mb-2"><?= $label ?></label>
                                <input type="file" name="gallery_<?= $key ?>[]" accept="image/*,video/*,image/gif" multiple class="gallery-picker-input w-full text-[11px] file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer" data-cat="<?= $key ?>">

                                <!-- Staged Live Upload Previews -->
                                <div class="staged-previews-grid flex flex-wrap gap-2 mt-3" id="staged-<?= $key ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- EXISTING UPLOADED GALLERY PREVIEW GRID -->
                    <div id="adminExistingGallerySection" class="mt-6 hidden">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Saved Gallery Items</h4>
                        <div id="adminExistingGallery" class="flex flex-wrap gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
            <button type="button" onclick="closeAdminModal()" class="px-5 py-2.5 text-slate-600 font-bold hover:bg-slate-200 rounded-xl transition-colors text-sm">Cancel</button>
            <button type="button" onclick="$('#adminRoomForm').submit()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-8 rounded-xl shadow-lg shadow-indigo-500/25 transition-all text-sm flex items-center gap-2 active:scale-95">
                <i class="fas fa-check-circle"></i> Save Room & Media
            </button>
        </div>
    </div>
</div>

<script>
    // GLOBAL LOADER UTILITIES
    function showGlobalLoader(title = "Processing Media & Data...", sub = "Please wait while the system synchronizes changes.") {
        $('#loaderTitle').text(title);
        $('#loaderSub').text(sub);
        $('#globalLoader').removeClass('hidden').addClass('flex');
    }

    function hideGlobalLoader() {
        $('#globalLoader').addClass('hidden').removeClass('flex');
    }

    // LIGHTBOX PREVIEW UTILITIES
    function openLightbox(mediaUrl, isVideo = false) {
        let content = isVideo ?
            `<video src="${mediaUrl}" controls autoplay class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl"></video>` :
            `<img src="${mediaUrl}" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl" />`;

        $('#lightboxContent').html(content);
        $('#lightboxModal').removeClass('hidden').addClass('flex');
    }

    function closeLightbox() {
        $('#lightboxModal').addClass('hidden').removeClass('flex');
        $('#lightboxContent').empty();
    }

    function closeAdminModal() {
        $('#adminRoomModal').addClass('hidden');
        $('#adminRoomForm')[0].reset();
        $('#adminRoomId').val(0);
        $('#adminAmenitiesContainer, #adminExistingGallery, .staged-previews-grid').empty();
        $('#adminExistingGallerySection').addClass('hidden');
    }

    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#onboard-hotel-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }

        // LIVE STAGED FILE PREVIEW ENGINE (FEATURE 2 & 5)
        $(document).on('change', '.gallery-picker-input', function() {
            let cat = $(this).data('cat');
            let files = this.files;
            let targetGrid = $('#staged-' + cat);
            targetGrid.empty();

            if (files && files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    let isVideo = file.type.startsWith('video/');
                    let url = URL.createObjectURL(file);

                    let card = `
                        <div class="relative w-16 h-16 rounded-xl overflow-hidden border border-indigo-200 shadow-sm group bg-slate-900" id="staged-file-${cat}-${index}">
                            ${isVideo ? `<video src="${url}" class="w-full h-full object-cover opacity-70"></video>` : `<img src="${url}" class="w-full h-full object-cover">`}
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
                                <button type="button" class="text-white text-xs bg-indigo-600 rounded-full w-5 h-5 flex items-center justify-center shadow" onclick="openLightbox('${url}', ${isVideo})"><i class="fas fa-eye"></i></button>
                            </div>
                            <span class="absolute top-0.5 right-0.5 bg-rose-500 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center cursor-pointer shadow" onclick="$(this).closest('div').remove()" title="Dismiss"><i class="fas fa-times"></i></span>
                        </div>
                    `;
                    targetGrid.append(card);
                });
            }
        });

        // OPEN ROOM MODAL
        $('#adminAddRoomBtn').click(function() {
            $('#adminModalTitle').text('Add Room Profile');
            $('#adminRoomId').val(0);
            $('#adminRoomForm')[0].reset();
            $('#adminAmenitiesContainer, #adminExistingGallery, .staged-previews-grid').empty();
            $('#adminExistingGallerySection').addClass('hidden');
            $('#adminRoomModal').removeClass('hidden');
        });

        // ADD DYNAMIC CUSTOM AMENITIES (FEATURE 4)
        $('#adminAddAmenityBtn').click(function() {
            $('#adminAmenitiesContainer').append(`
                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <input type="text" name="amenity_title[]" placeholder="Amenity Title (e.g. Jacuzzi Access)" required class="flex-1 bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="number" name="amenity_price[]" placeholder="Rate ₹" required step="0.01" class="w-28 bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" class="text-rose-500 p-1.5 hover:bg-rose-50 rounded-lg transition-colors" onclick="$(this).closest('div').remove()"><i class="fas fa-trash-alt text-xs"></i></button>
                </div>
            `);
        });

        // FETCH AND EDIT ROOM
        $(document).on('click', '.admin-edit-room-btn', function() {
            let id = $(this).data('id');
            $('#adminModalTitle').text('Edit Room & Media Galleries');
            $('#adminRoomId').val(id);
            showGlobalLoader("Fetching Room Details...", "Loading galleries and pricing configuration.");

            $.post('<?= BASE_URL ?>admin/hotel/admin_rooms_get.php', {
                id: id,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                hideGlobalLoader();
                if (res.success) {
                    let d = res.data;
                    $('#adminRoomTitle').val(d.room_title);
                    $('#adminBeds').val(d.beds);
                    $('#adminAdults').val(d.adults);
                    $('#adminChildren').val(d.children);
                    $('#adminTvPrice').val(d.tv_price);
                    $('#adminAcPrice').val(d.ac_price);
                    $('#adminWifiPrice').val(d.wifi_price);

                    $('#adminAmenitiesContainer').empty();
                    if (d.amenities && d.amenities.length > 0) {
                        d.amenities.forEach(am => {
                            $('#adminAmenitiesContainer').append(`
                                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                    <input type="text" name="amenity_title[]" value="${am.title}" required class="flex-1 bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs outline-none">
                                    <input type="number" name="amenity_price[]" value="${am.price}" required step="0.01" class="w-28 bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs outline-none">
                                    <button type="button" class="text-rose-500 p-1.5 hover:bg-rose-50 rounded-lg" onclick="$(this).closest('div').remove()"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            `);
                        });
                    }

                    // RENDER EXISTING UPLOADED MEDIA CARDS WITH REMOVE BUTTON (FEATURE 2 & 5)
                    $('#adminExistingGallery').empty();
                    if (d.gallery && d.gallery.length > 0) {
                        $('#adminExistingGallerySection').removeClass('hidden');
                        d.gallery.forEach(g => {
                            let isVid = g.media_type === 'video';
                            let mediaUrl = '<?= BASE_URL ?>uploads/hotel/gallery/' + g.media;

                            let content = isVid ?
                                `<video src="${mediaUrl}" class="w-full h-full object-cover" muted></video>` :
                                `<img src="${mediaUrl}" class="w-full h-full object-cover">`;

                            $('#adminExistingGallery').append(`
                                <div class="relative w-20 h-20 rounded-2xl overflow-hidden border border-slate-200 shadow-sm group bg-slate-900" id="saved-gal-${g.id}">
                                    ${content}
                                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5">
                                        <button type="button" class="w-6 h-6 rounded-full bg-white text-slate-800 flex items-center justify-center text-[10px] shadow" onclick="openLightbox('${mediaUrl}', ${isVid})"><i class="fas fa-eye"></i></button>
                                        <button type="button" class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-[10px] shadow delete-gallery-item" data-id="${g.id}"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <span class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] text-center uppercase tracking-wider py-0.5 truncate px-1">${g.category}</span>
                                </div>
                            `);
                        });
                    } else {
                        $('#adminExistingGallerySection').addClass('hidden');
                    }

                    $('#adminRoomModal').removeClass('hidden');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                hideGlobalLoader();
                Swal.fire('Error', 'Failed to connect to server.', 'error');
            });
        });

        // DELETE SAVED GALLERY ITEM DIRECTLY FROM PREVIEW (FEATURE 5)
        $(document).on('click', '.delete-gallery-item', function() {
            let mediaId = $(this).data('id');
            Swal.fire({
                title: 'Delete Media File?',
                text: 'This item will be permanently removed from disk.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    showGlobalLoader("Deleting Media File...", "Removing media from storage server.");
                    $.post('<?= BASE_URL ?>admin/hotel/admin_rooms_delete_gallery.php', {
                        id: mediaId,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    }, function(res) {
                        hideGlobalLoader();
                        if (res.success) {
                            $('#saved-gal-' + mediaId).remove();
                            Swal.fire({
                                title: 'Deleted!',
                                text: res.message,
                                icon: 'success',
                                toast: true,
                                position: 'top-end',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json').fail(function() {
                        hideGlobalLoader();
                        Swal.fire('Error', 'Server communication error.', 'error');
                    });
                }
            });
        });

        // DELETE ENTIRE ROOM
        $(document).on('click', '.admin-delete-room-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Room Profile?',
                text: 'This will remove the room specification and all associated gallery photos/videos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete Room'
            }).then((r) => {
                if (r.isConfirmed) {
                    showGlobalLoader("Deleting Room Profile...", "Cleaning up database records and galleries.");
                    $.post('<?= BASE_URL ?>admin/hotel/admin_rooms_delete.php', {
                        id: id,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    }, function(res) {
                        hideGlobalLoader();
                        if (res.success) location.reload();
                        else Swal.fire('Error', res.message, 'error');
                    }, 'json');
                }
            });
        });

        // SUBMIT ROOM FORM (FEATURE 6: SCREEN BLOCKING LOADER)
        $('#adminRoomForm').submit(function(e) {
            e.preventDefault();
            showGlobalLoader("Uploading & Processing Media...", "Compressing images and processing media uploads.");

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    hideGlobalLoader();
                    if (res.success) {
                        Swal.fire({
                                title: 'Room Saved!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#4f46e5'
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    hideGlobalLoader();
                    Swal.fire('Error', 'Server connection error during upload.', 'error');
                }
            });
        });

        // SUBMIT ADDRESS FORM (FEATURE 6: SCREEN BLOCKING LOADER)
        $('#onboardAddressForm').submit(function(e) {
            e.preventDefault();
            showGlobalLoader("Saving Address Data...", "Verifying location information.");

            $.post($(this).attr('action'), $(this).serialize(), function(res) {
                hideGlobalLoader();
                if (res.success) {
                    Swal.fire({
                            title: 'Address Saved!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#4f46e5'
                        })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                hideGlobalLoader();
                Swal.fire('Error', 'Server communication error.', 'error');
            });
        });
    });
</script>