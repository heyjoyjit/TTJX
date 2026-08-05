<?php
// admin/packages/add_content.php
$db = Database::getInstance()->getConnection();

// Fetch destinations
$destStmt = $db->query("SELECT id, place_name FROM destinations WHERE status = 1 ORDER BY place_name");
$destinations = $destStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories mapped to destinations
$catStmt = $db->query("SELECT id, destination_id, pricing_category_name as name FROM package_categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="add-package-wrapper" class="opacity-0 max-w-6xl mx-auto pb-12">

    <header class="mb-8 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/packages/index.php" class="w-10 h-10 rounded-full flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 shadow-sm transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Add New Package</h1>
            </div>
            <p class="text-sm text-slate-500 ml-12">Create a comprehensive travel package with itineraries, pricing, and media.</p>
        </div>
    </header>

    <form id="addPackageForm" enctype="multipart/form-data" method="post" action="<?= BASE_URL ?>admin/packages/save.php">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="space-y-6">

            <!-- SECTION 1: General Details -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fas fa-info-circle text-blue-500"></i> General Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Package Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required placeholder="e.g. 5 Days Exotic Goa Getaway" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Subtitle <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="text" name="subtitle" placeholder="e.g. Includes flights, premium meals, and sightseeing" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Destination <span class="text-red-500">*</span></label>
                        <select name="destination_id" id="destinationSelect" required class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="">Select Destination</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= escape($d['place_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pricing Category <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select name="category_id" id="categorySelect" required disabled class="flex-1 bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">Select Destination First</option>
                            </select>
                            <button type="button" id="addCategoryBtn" class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 hover:text-indigo-700 px-4 rounded-lg font-bold transition-colors" title="Add Pricing Category"><i class="fas fa-plus"></i></button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Defines the base variant (e.g., "Couple", "Group of 4")</p>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: Pricing & Offers -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fas fa-tags text-emerald-500"></i> Pricing & Offer System
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Base Price (₹) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400 font-medium">₹</span>
                            <input type="number" name="price" step="0.01" required placeholder="0.00" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 pl-8 pr-4 text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Strike-through Price (₹)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400 font-medium">₹</span>
                            <input type="number" name="prev_price" step="0.01" placeholder="e.g. 5000" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 pl-8 pr-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Active Offer Status</label>
                        <select name="offer_type" id="offerType" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                            <option value="none">No Active Offer</option>
                            <option value="price">Flat Price Discount (₹)</option>
                            <option value="percent">Percentage Discount (%)</option>
                        </select>
                    </div>

                    <!-- Dynamic Offer Configuration Box -->
                    <div id="offerDetailsContainer" class="hidden md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6 bg-emerald-50 p-6 rounded-xl border border-emerald-100">
                        <div>
                            <label id="offerValueLabel" class="block text-sm font-bold text-emerald-800 mb-2">Flat Discount (₹)</label>
                            <div class="relative">
                                <span id="offerIcon" class="absolute left-4 top-3 text-emerald-600 font-bold">₹</span>
                                <input type="number" name="offer_value" id="offerValue" step="0.01" placeholder="0.00" class="w-full bg-white border border-emerald-200 rounded-lg py-3 pl-8 pr-4 text-emerald-900 font-bold focus:ring-2 focus:ring-emerald-500 outline-none placeholder-emerald-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-emerald-800 mb-2">Valid Up To (Limit)</label>
                            <div class="flex gap-2">
                                <select name="upto_limit_preset" id="uptoLimitPreset" class="w-1/2 bg-white border border-emerald-200 rounded-lg py-3 px-3 text-emerald-900 font-bold focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="3">Up to 3</option>
                                    <option value="6">Up to 6</option>
                                    <option value="custom">Custom Limit...</option>
                                </select>
                                <input type="text" name="upto_limit_custom" id="uptoLimitCustom" placeholder="e.g. 10 People" class="hidden w-1/2 bg-white border border-emerald-200 rounded-lg py-3 px-3 text-emerald-900 font-bold focus:ring-2 focus:ring-emerald-500 outline-none placeholder-emerald-300">
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-3 border border-slate-200 rounded-xl p-4 bg-slate-50 flex items-center justify-between mt-2">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Brochure / Details PDF</label>
                            <input type="file" name="details_pdf" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Gallery Media -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-images text-purple-500"></i> Package Gallery <span class="text-red-500">*</span></h2>
                    <span class="text-xs font-semibold bg-purple-100 text-purple-600 px-3 py-1 rounded-full">Min 2 files required</span>
                </div>
                <div class="mb-4">
                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 hover:border-purple-400 transition-colors group cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 group-hover:text-purple-500 mb-3 transition-colors"></i>
                        <p class="text-sm font-medium text-slate-600">Click or drag images/videos here to add to gallery</p>
                        <p class="text-xs text-slate-400 mt-1">Supports JPG, PNG, GIF, MP4</p>
                        <input type="file" id="galleryInput" accept="image/*,video/*,image/gif" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>
                <div id="gallery-preview-container" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4"></div>
            </section>

            <!-- SECTION 4: Highlights -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-star text-amber-500"></i> Highlights</h2>
                    <button type="button" id="addHighlightBtn" class="bg-amber-100 text-amber-700 hover:bg-amber-200 font-semibold py-1.5 px-4 rounded-lg text-sm transition-colors flex items-center gap-2"><i class="fas fa-plus"></i> Add Highlight</button>
                </div>
                <div id="highlightsContainer" class="space-y-3"></div>
            </section>

            <!-- SECTION 5: Guidelines -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-clipboard-list text-cyan-500"></i> Guidelines</h2>
                    <button type="button" id="addGuidelineBtn" class="bg-cyan-100 text-cyan-700 hover:bg-cyan-200 font-semibold py-1.5 px-4 rounded-lg text-sm transition-colors flex items-center gap-2"><i class="fas fa-plus"></i> Add Guideline</button>
                </div>
                <div id="guidelinesContainer" class="space-y-6"></div>
            </section>

            <!-- SECTION 6: Itinerary / Days -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-calendar-alt text-rose-500"></i> Day Plans (Itinerary)</h2>
                    <button type="button" id="addDayBtn" class="bg-rose-100 text-rose-700 hover:bg-rose-200 font-semibold py-1.5 px-4 rounded-lg text-sm transition-colors flex items-center gap-2"><i class="fas fa-plus"></i> Add Day</button>
                </div>
                <div id="daysContainer" class="space-y-6"></div>
            </section>

            <!-- Submit -->
            <div class="flex justify-end gap-3 mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                <a href="<?= BASE_URL ?>admin/packages/index.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-all">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-8 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2 text-lg">
                    <i class="fas fa-save"></i> Publish Package
                </button>
            </div>

        </div>
    </form>
</div>

<!-- Modals -->
<div id="modal-container-vault" class="hidden">
    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="hidden fixed top-0 left-0 w-screen h-screen bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[9999] transition-opacity opacity-0 duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-sm transform scale-95 transition-transform duration-300">
            <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Add Pricing Category</h2>
            <input type="text" id="newCategoryName" placeholder="e.g. Honeymoon Suite" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-slate-800 mb-6 focus:ring-2 focus:ring-blue-500 outline-none">
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded-lg font-medium transition-colors" onclick="closeModal('addCategoryModal')">Cancel</button>
                <button type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium shadow-md transition-colors" id="saveCategoryBtn">Save Category</button>
            </div>
        </div>
    </div>

    <!-- Visual Icon Picker Modal -->
    <div id="iconPickerModal" class="hidden fixed top-0 left-0 w-screen h-screen bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[9999] transition-opacity opacity-0 duration-300 p-4">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-3xl transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h2 class="text-xl font-bold text-slate-800">Choose an Icon</h2>
                <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeModal('iconPickerModal')"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div class="mb-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                    <input type="text" id="iconSearch" placeholder="Search icons..." class="w-full border border-slate-200 rounded-lg py-3 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 outline-none text-slate-700 bg-slate-50">
                </div>
            </div>
            <div class="overflow-y-auto pr-2 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 pb-2" id="iconGridContainer"></div>
        </div>
    </div>
</div>

<style>
    div.swal2-container {
        z-index: 999999 !important;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // Make categories available to JS for dynamic filtering
    const pricingCategories = <?= json_encode($categories) ?>;
    let selectedGalleryFiles = [];

    const getTinyConfig = (selector) => ({
        selector: selector,
        plugins: 'lists link table wordcount',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat',
        height: 200,
        menubar: false,
        skin: 'oxide',
        content_style: "body { font-family: 'Inter', sans-serif; font-size: 14px; color: #334155; }",
        branding: false,
        promotion: false
    });

    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    const iconList = [
        'fa-plane', 'fa-plane-departure', 'fa-plane-arrival', 'fa-hotel', 'fa-bed', 'fa-swimming-pool', 'fa-water', 'fa-umbrella-beach', 'fa-mountain', 'fa-hiking', 'fa-campground', 'fa-tree', 'fa-leaf', 'fa-map', 'fa-map-marker-alt', 'fa-map-pin', 'fa-map-signs', 'fa-route', 'fa-compass', 'fa-globe', 'fa-globe-americas', 'fa-globe-asia', 'fa-globe-europe', 'fa-passport', 'fa-suitcase', 'fa-suitcase-rolling', 'fa-luggage-cart', 'fa-camera', 'fa-camera-retro', 'fa-video', 'fa-images', 'fa-binoculars', 'fa-car', 'fa-car-side', 'fa-bus', 'fa-bus-alt', 'fa-train', 'fa-tram', 'fa-subway', 'fa-ship', 'fa-boat', 'fa-bicycle', 'fa-motorcycle', 'fa-walking', 'fa-running', 'fa-ticket-alt', 'fa-utensils', 'fa-hamburger', 'fa-pizza-slice', 'fa-hotdog', 'fa-coffee', 'fa-mug-hot', 'fa-glass-cheers', 'fa-glass-martini-alt', 'fa-beer', 'fa-wine-glass-alt', 'fa-shopping-bag', 'fa-shopping-cart', 'fa-store', 'fa-gift', 'fa-monument', 'fa-landmark', 'fa-university', 'fa-building', 'fa-church', 'fa-mosque', 'fa-synagogue', 'fa-vihara', 'fa-torii-gate', 'fa-archway', 'fa-place-of-worship', 'fa-spa', 'fa-sun', 'fa-moon', 'fa-star', 'fa-cloud', 'fa-cloud-sun', 'fa-cloud-moon', 'fa-snowflake', 'fa-wind', 'fa-fire', 'fa-thermometer-half', 'fa-info-circle', 'fa-check-circle', 'fa-exclamation-circle', 'fa-question-circle', 'fa-times-circle', 'fa-plus-circle', 'fa-minus-circle', 'fa-heart', 'fa-thumbs-up', 'fa-star-half-alt', 'fa-clock', 'fa-calendar', 'fa-calendar-alt', 'fa-calendar-check', 'fa-history', 'fa-money-bill', 'fa-money-bill-wave', 'fa-coins', 'fa-credit-card', 'fa-wallet', 'fa-receipt', 'fa-file-invoice-dollar', 'fa-tag', 'fa-tags', 'fa-percent', 'fa-award', 'fa-medal', 'fa-trophy', 'fa-crown', 'fa-shield-alt', 'fa-lock', 'fa-unlock', 'fa-key', 'fa-cog', 'fa-cogs', 'fa-wrench', 'fa-hammer', 'fa-tools', 'fa-wifi', 'fa-signal', 'fa-mobile-alt', 'fa-phone', 'fa-phone-alt', 'fa-envelope', 'fa-paper-plane', 'fa-user', 'fa-users', 'fa-users-cog', 'fa-user-tie', 'fa-child', 'fa-baby', 'fa-restroom', 'fa-wheelchair', 'fa-blind', 'fa-deaf'
    ];

    let currentInputTarget = null;
    let currentDisplayTarget = null;

    function initializeIconGrid() {
        const container = $('#iconGridContainer');
        container.empty();
        iconList.forEach(iconClass => {
            container.append(`<button type="button" data-icon="${iconClass}" title="${iconClass}" class="icon-select-btn flex flex-col items-center justify-center p-4 border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors text-slate-600 shadow-sm"><i class="fas ${iconClass} text-2xl mb-2"></i><span class="text-[10px] text-slate-400 truncate w-full text-center">${iconClass.replace('fa-', '')}</span></button>`);
        });
    }

    $(document).ready(function() {
        $('body').append($('#addCategoryModal'));
        $('body').append($('#iconPickerModal'));
        $('#modal-container-vault').remove();

        if (typeof gsap !== 'undefined') gsap.to("#add-package-wrapper", {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: "power3.out"
        });
        initializeIconGrid();

        // Dynamic Categories based on Destination selection
        $('#destinationSelect').change(function() {
            const destId = $(this).val();
            const catSelect = $('#categorySelect');
            catSelect.empty();

            if (!destId) {
                catSelect.append('<option value="">Select Destination First</option>');
                catSelect.prop('disabled', true);
                return;
            }

            const filteredCats = pricingCategories.filter(c => c.destination_id == destId);
            if (filteredCats.length === 0) {
                catSelect.append('<option value="">No Pricing Categories Found</option>');
            } else {
                catSelect.append('<option value="">Select Pricing Category</option>');
                filteredCats.forEach(c => catSelect.append(`<option value="${c.id}">${c.name}</option>`));
            }
            catSelect.prop('disabled', false);
        });

        // Offer Logic
        $('#offerType').change(function() {
            const val = $(this).val();
            const container = $('#offerDetailsContainer');
            const label = $('#offerValueLabel');
            const icon = $('#offerIcon');

            if (val === 'none') {
                container.slideUp(300);
                $('#offerValue').val('');
            } else {
                container.hide().removeClass('hidden').slideDown(300);
                if (val === 'price') {
                    label.text('Flat Discount (₹)');
                    icon.text('₹');
                } else if (val === 'percent') {
                    label.text('Percentage Discount (%)');
                    icon.text('%');
                }
            }
        });

        $('#uptoLimitPreset').change(function() {
            const customInput = $('#uptoLimitCustom');
            if ($(this).val() === 'custom') {
                customInput.hide().removeClass('hidden').fadeIn(200);
                customInput.focus();
            } else {
                customInput.fadeOut(200, function() {
                    $(this).addClass('hidden').val('');
                });
            }
        });

        // Modals & Pickers
        $('#iconSearch').on('input', function() {
            const term = $(this).val().toLowerCase();
            $('.icon-select-btn').each(function() {
                $(this).data('icon').toLowerCase().includes(term) ? $(this).show() : $(this).hide();
            });
        });

        $(document).on('click', '.open-icon-picker', function(e) {
            e.preventDefault();
            currentInputTarget = $(this).data('input-target');
            currentDisplayTarget = $(this).data('display-target');
            $('#iconSearch').val('').trigger('input');
            openModal('iconPickerModal');
        });

        $(document).on('click', '.icon-select-btn', function(e) {
            e.preventDefault();
            const selectedIcon = $(this).data('icon');
            $(currentInputTarget).val(selectedIcon);
            $(currentDisplayTarget).html(`<i class="fas ${selectedIcon} text-lg"></i>`);
            closeModal('iconPickerModal');
        });

        $('#addCategoryBtn').click(function() {
            if (!$('#destinationSelect').val()) return Swal.fire('Required', 'Please select a Destination first.', 'warning');
            openModal('addCategoryModal');
        });

        // Add Category AJAX
        $('#saveCategoryBtn').click(function() {
            const name = $('#newCategoryName').val().trim();
            const destId = $('#destinationSelect').val();

            if (!name) return Swal.fire('Error', 'Name is required.', 'error');
            if (typeof showLoader === 'function') showLoader();

            $.post('<?= BASE_URL ?>admin/packages/add_category.php', {
                name: name,
                destination_id: destId,
                csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
            }, function(res) {
                if (typeof hideLoader === 'function') hideLoader();
                if (res.success) {
                    pricingCategories.push({
                        id: res.id,
                        destination_id: destId,
                        name: name
                    });
                    $('#categorySelect').append(new Option(name, res.id)).val(res.id).prop('disabled', false);
                    closeModal('addCategoryModal');
                    $('#newCategoryName').val('');
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });

        // Highlights
        let highlightCount = 0;
        $('#addHighlightBtn').click(function() {
            if (highlightCount >= 10) return Swal.fire('Limit reached', 'Maximum 10 highlights allowed.', 'warning');
            highlightCount++;
            const uniqueId = Date.now();
            $('#highlightsContainer').append(`
                <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl transition-all">
                    <input type="hidden" name="highlight_icon[]" id="hi_input_${uniqueId}" value="fa-star" required>
                    <button type="button" data-input-target="#hi_input_${uniqueId}" data-display-target="#hi_disp_${uniqueId}" class="open-icon-picker w-12 h-11 shrink-0 flex items-center justify-center bg-white border border-slate-300 rounded-lg hover:border-amber-400 hover:text-amber-500 shadow-sm"><span id="hi_disp_${uniqueId}" class="text-slate-600"><i class="fas fa-star text-lg"></i></span></button>
                    <div class="flex-1"><input type="text" name="highlight_text[]" placeholder="Description (max 150 chars)" maxlength="150" required class="w-full bg-white border border-slate-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"></div>
                    <button type="button" class="remove-highlight w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-lg border border-red-100"><i class="fas fa-times"></i></button>
                </div>
            `);
        });
        $(document).on('click', '.remove-highlight', function() {
            $(this).parent().remove();
            highlightCount--;
        });

        // Guidelines
        $('#addGuidelineBtn').click(function() {
            const uniqueId = 'guideline_' + Date.now();
            $('#guidelinesContainer').append(`
                <div class="bg-slate-50 border border-slate-200 p-5 rounded-xl relative group">
                    <button type="button" class="remove-card absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-lg border border-red-100"><i class="fas fa-trash-alt"></i></button>
                    <div class="flex items-end gap-4 mb-4 pr-12">
                        <div><label class="block text-xs font-semibold text-slate-500 mb-1">Icon</label><input type="hidden" name="guideline_icon[]" id="guide_input_${uniqueId}" value="fa-check-circle" required><button type="button" data-input-target="#guide_input_${uniqueId}" data-display-target="#guide_disp_${uniqueId}" class="open-icon-picker w-12 h-10 flex items-center justify-center bg-white border border-slate-300 rounded-lg hover:border-cyan-400 hover:text-cyan-500 shadow-sm"><span id="guide_disp_${uniqueId}" class="text-slate-600"><i class="fas fa-check-circle text-lg"></i></span></button></div>
                        <div class="flex-1"><label class="block text-xs font-semibold text-slate-500 mb-1">Title</label><input type="text" name="guideline_title[]" required class="w-full border border-slate-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-cyan-500"></div>
                    </div>
                    <textarea name="guideline_desc[]" id="${uniqueId}" class="w-full"></textarea>
                </div>
            `);
            tinymce.init(getTinyConfig(`#${uniqueId}`));
        });

        // Days
        $('#addDayBtn').click(function() {
            const uniqueId = 'day_' + Date.now();
            $('#daysContainer').append(`
                <div class="bg-slate-50 border border-slate-200 p-5 rounded-xl relative group border-l-4 border-l-rose-400">
                    <button type="button" class="remove-card absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-lg border border-red-100"><i class="fas fa-trash-alt"></i></button>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 pr-12">
                        <div><label class="block text-xs font-semibold text-slate-500 mb-1">Day Number</label><input type="number" name="day_number[]" required min="1" class="w-full border border-slate-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-rose-500"></div>
                        <div class="md:col-span-2"><label class="block text-xs font-semibold text-slate-500 mb-1">Day Title</label><input type="text" name="day_title[]" required class="w-full border border-slate-200 rounded-lg p-2 text-sm outline-none focus:ring-2 focus:ring-rose-500"></div>
                    </div>
                    <textarea name="day_desc[]" id="${uniqueId}" class="w-full"></textarea>
                </div>
            `);
            tinymce.init(getTinyConfig(`#${uniqueId}`));
        });

        $(document).on('click', '.remove-card', function() {
            const container = $(this).closest('.bg-slate-50');
            const textarea = container.find('textarea').attr('id');
            if (textarea) tinymce.get(textarea).remove();
            container.remove();
        });

        // Gallery
        $('#galleryInput').change(function(e) {
            Array.from(e.target.files).forEach(file => {
                if (selectedGalleryFiles.some(f => f.name === file.name && f.size === file.size)) return;
                selectedGalleryFiles.push(file);

                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-full aspect-square bg-slate-900 rounded-xl overflow-hidden shadow-sm group border border-slate-200';
                let element = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
                element.className = 'w-full h-full object-cover';
                element.src = URL.createObjectURL(file);
                if (file.type.startsWith('video/')) {
                    element.muted = true;
                    element.autoplay = true;
                    element.loop = true;
                }
                wrapper.appendChild(element);

                const btn = document.createElement('button');
                btn.className = 'absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity z-10';
                btn.innerHTML = '<i class="fas fa-times text-xs"></i>';
                btn.onclick = (ev) => {
                    ev.preventDefault();
                    URL.revokeObjectURL(element.src);
                    selectedGalleryFiles = selectedGalleryFiles.filter(f => !(f.name === file.name && f.size === file.size));
                    wrapper.remove();
                };
                wrapper.appendChild(btn);
                $('#gallery-preview-container').append(wrapper);
            });
            $('#galleryInput').val('');
        });

        // Form Submit
        $('#addPackageForm').submit(function(e) {
            e.preventDefault();
            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            if (selectedGalleryFiles.length < 2) return Swal.fire('Validation Error', 'Please upload at least 2 files for the gallery.', 'warning');

            var formData = new FormData(this);
            selectedGalleryFiles.forEach(file => formData.append('gallery[]', file));

            if (typeof showLoader === 'function') showLoader();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) Swal.fire({
                        title: 'Success!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => window.location.href = '<?= BASE_URL ?>admin/packages/index.php');
                    else Swal.fire('Error', res.message, 'error');
                }
            });
        });
    });
</script>