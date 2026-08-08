<?php
// admin/hotel/edit_content.php
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    echo '<div class="text-red-500 p-8 text-center font-semibold text-xl">Hotel not found.</div>';
    return;
}

$destStmt = $db->query("SELECT id, place_name FROM destinations WHERE status = 1 ORDER BY place_name ASC");
$destinations = $destStmt->fetchAll();
?>
<div id="edit-hotel-wrapper" class="opacity-0 max-w-5xl mx-auto pb-12">
    <header class="mb-8 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/hotel/index.php" class="text-slate-400 hover:text-blue-500 transition-colors"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Full Setup: <?= escape($hotel['hotel_name']) ?></h1>
            </div>
            <p class="text-sm text-slate-500 ml-7">Manage complete registration and Phase 1 details.</p>
        </div>
    </header>

    <form id="editHotelForm" method="post" action="<?= BASE_URL ?>admin/hotel/update.php" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $hotel['id'] ?>">

        <!-- Registration Info: Hotel Details -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-3"><i class="fas fa-building text-blue-500 mr-2"></i>Hotel Registration Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Name *</label>
                    <input type="text" name="hotel_name" required value="<?= escape($hotel['hotel_name']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Destination *</label>
                    <select name="destination_id" required class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                        <option value="">-- Select Destination --</option>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $d['id'] == $hotel['destination_id'] ? 'selected' : '' ?>><?= escape($d['place_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Email *</label>
                    <input type="email" name="email" required value="<?= escape($hotel['email']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Hotel Phone *</label>
                    <input type="text" name="phone" required value="<?= escape($hotel['phone']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Location / Area *</label>
                    <input type="text" name="location" required value="<?= escape($hotel['location']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
            </div>
        </section>

        <!-- Registration Info: Owner Details -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-3"><i class="fas fa-user-tie text-emerald-500 mr-2"></i>Owner Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Name *</label>
                    <input type="text" name="owner_name" required value="<?= escape($hotel['owner_name']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Phone *</label>
                    <input type="text" name="owner_phone" required value="<?= escape($hotel['owner_phone']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Owner Email</label>
                    <input type="email" name="owner_email" value="<?= escape($hotel['owner_email'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3">
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3 p-6 bg-slate-50 rounded-2xl border border-slate-200">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-8 rounded-lg shadow-md transition-all flex items-center gap-2">
                <i class="fas fa-save"></i> Save Details & Notify Hotel
            </button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') gsap.to("#edit-hotel-wrapper", {
            opacity: 1,
            y: 0,
            duration: 0.6
        });
        $('#editHotelForm').submit(function(e) {
            e.preventDefault();
            if (typeof showLoader === 'function') showLoader();
            $.post($(this).attr('action'), $(this).serialize(), function(res) {
                if (typeof hideLoader === 'function') hideLoader();
                if (res.success) {
                    Swal.fire({
                        title: 'Updated!',
                        text: res.message,
                        icon: 'success'
                    }).then(() => window.location.href = '<?= BASE_URL ?>admin/hotel/index.php');
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });
    });
</script>