<?php
// admin/hotel/view_content.php
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    echo '<div class="text-red-500 p-8 text-center font-semibold text-xl">Hotel not found.</div>';
    return;
}
?>
<div id="view-hotel-wrapper" class="opacity-0 max-w-5xl mx-auto pb-12">

    <!-- Action Bar -->
    <div class="flex justify-between items-center mb-6">
        <a href="<?= BASE_URL ?>admin/hotel/index.php" class="text-slate-400 hover:text-blue-600 transition-colors bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 font-medium flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Hotels
        </a>
        <a href="<?= BASE_URL ?>admin/hotel/edit.php?id=<?= $id ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium shadow-sm shadow-blue-500/30 transition-colors flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit Hotel
        </a>
    </div>

    <!-- Main Profile Card -->
    <div class="bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">

        <!-- Decorative Header Bar -->
        <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-8 mb-8 mt-2">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?= escape($hotel['hotel_name']) ?></h1>
                        <p class="text-slate-500 font-mono text-sm mt-1">ID: <?= escape($hotel['hotel_registered_id']) ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <?php if ($hotel['status']): ?>
                    <span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wider border border-emerald-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active Partner
                    </span>
                <?php else: ?>
                    <span class="bg-rose-50 text-rose-600 px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wider border border-rose-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Inactive
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Hotel Contact Info -->
            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">Hotel Contact Information</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-envelope text-blue-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Primary Email</p>
                            <p class="text-slate-800 font-medium"><?= escape($hotel['email']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone-alt text-blue-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Reception Phone</p>
                            <p class="text-slate-800 font-medium"><?= escape($hotel['phone']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-blue-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Location / Address</p>
                            <p class="text-slate-800 font-medium"><?= nl2br(escape($hotel['location'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Info -->
            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">Owner Details</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-user-tie text-emerald-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Owner Full Name</p>
                            <p class="text-slate-800 font-medium"><?= escape($hotel['owner_name']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-mobile-alt text-emerald-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Owner Direct Phone</p>
                            <p class="text-slate-800 font-medium"><?= escape($hotel['owner_phone']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-at text-emerald-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Owner Email</p>
                            <p class="text-slate-800 font-medium"><?= !empty($hotel['owner_email']) ? escape($hotel['owner_email']) : '<span class="text-slate-400 italic">Not Provided</span>' ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#view-hotel-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }
    });
</script>