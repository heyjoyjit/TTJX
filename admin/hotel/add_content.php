<?php
// admin/hotel/add_content.php
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, place_name FROM destinations WHERE status = 1 ORDER BY place_name ASC");
$destinations = $stmt->fetchAll();
?>
<div id="add-hotel-wrapper" class="opacity-0 max-w-4xl mx-auto pb-12">
    <header class="mb-8 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/hotel/index.php" class="text-slate-400 hover:text-blue-500 transition-colors"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Register New Hotel</h1>
            </div>
            <p class="text-sm text-slate-500 ml-7">Register a hotel to automatically send them their onboarding link.</p>
        </div>
    </header>

    <form id="addHotelForm" method="post" action="<?= BASE_URL ?>admin/hotel/save.php" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3"><i class="fas fa-building text-blue-500"></i> Hotel Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Name <span class="text-red-500">*</span></label>
                    <input type="text" name="hotel_name" required placeholder="e.g. Grand Plaza Resort" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Destination <span class="text-red-500">*</span></label>
                    <select name="destination_id" required class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Select Destination --</option>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= escape($d['place_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Email (For Onboarding Link) <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="contact@hotel.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required placeholder="+91 9876543210" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Location / Area <span class="text-red-500">*</span></label>
                    <input type="text" name="location" required placeholder="e.g. South Goa Beach" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3"><i class="fas fa-user-tie text-emerald-500"></i> Owner Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" required placeholder="John Doe" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_phone" required placeholder="+91 9123456780" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Email <span class="text-xs text-slate-400 font-normal">(Optional)</span></label>
                    <input type="email" name="owner_email" placeholder="owner@personal.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3 p-6 bg-slate-50 rounded-2xl border border-slate-200">
            <a href="<?= BASE_URL ?>admin/hotel/index.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-all">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-8 rounded-lg shadow-md transition-all flex items-center gap-2 text-lg">
                <i class="fas fa-paper-plane"></i> Save & Send Onboarding Link
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') gsap.to("#add-hotel-wrapper", {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: "power3.out"
        });

        $('#addHotelForm').submit(function(e) {
            e.preventDefault();
            if (typeof showLoader === 'function') showLoader();
            $.post($(this).attr('action'), $(this).serialize(), function(res) {
                if (typeof hideLoader === 'function') hideLoader();
                if (res.success) {
                    Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success'
                        })
                        .then(() => window.location.href = '<?= BASE_URL ?>admin/hotel/index.php');
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });
    });
</script>