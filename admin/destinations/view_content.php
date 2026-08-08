<?php
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();
if (!$dest) {
    echo '<div class="text-red-500 p-8 text-center font-semibold">Destination not found.</div>';
    return;
}

// Galleries
$stmtGall = $db->prepare("SELECT * FROM destination_galleries WHERE destination_id = ? ORDER BY sort_order");
$stmtGall->execute([$id]);
$galleries = $stmtGall->fetchAll();

// Attachments
$stmtAtt = $db->prepare("SELECT * FROM destination_attachments WHERE destination_id = ?");
$stmtAtt->execute([$id]);
$attachments = $stmtAtt->fetchAll();
?>

<div id="view-destination-wrapper" class="opacity-0 max-w-6xl mx-auto pb-10">

    <!-- Header section -->
    <header class="mb-6 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 shadow-sm transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Destination Details</h1>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= BASE_URL ?>admin/destinations/edit.php?id=<?= $id ?>" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2 px-5 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Core Info & Media -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Hero Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group">
                <div class="relative h-64 md:h-80 w-full bg-slate-900">
                    <?php if ($dest['main_media']): ?>
                        <img src="<?= BASE_URL ?>uploads/destinations/main/<?= $dest['main_media'] ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-600"><i class="fas fa-image text-4xl"></i></div>
                    <?php endif; ?>

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

                    <!-- Top Badges -->
                    <div class="absolute top-4 left-4 flex gap-2">
                        <?php if ($dest['status']): ?>
                            <span class="px-3 py-1 bg-emerald-500/90 text-white text-xs font-bold uppercase tracking-wider rounded-full backdrop-blur-sm"><i class="fas fa-eye mr-1"></i> Visible</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-red-500/90 text-white text-xs font-bold uppercase tracking-wider rounded-full backdrop-blur-sm"><i class="fas fa-eye-slash mr-1"></i> Hidden</span>
                        <?php endif; ?>

                        <?php if ($dest['is_top_destination']): ?>
                            <span class="px-3 py-1 bg-amber-500/90 text-white text-xs font-bold uppercase tracking-wider rounded-full backdrop-blur-sm"><i class="fas fa-star mr-1"></i> Top #<?= $dest['top_sort_order'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Text Content -->
                    <div class="absolute bottom-0 left-0 p-6 w-full text-white">
                        <h2 class="text-4xl font-black mb-2 drop-shadow-md"><?= escape($dest['place_name']) ?></h2>
                        <p class="text-xl font-medium text-blue-300 drop-shadow-sm"><?= escape($dest['banner_title']) ?></p>
                        <?php if ($dest['banner_subtitle']): ?>
                            <p class="text-sm text-slate-300 mt-2 line-clamp-2 max-w-2xl"><?= escape($dest['banner_subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-images text-blue-500"></i> Media Gallery
                </h3>

                <?php if (empty($galleries)): ?>
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <i class="fas fa-image text-slate-300 text-3xl mb-2"></i>
                        <p class="text-slate-500 text-sm">No gallery media uploaded.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($galleries as $g): ?>
                            <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group cursor-pointer shadow-sm hover:shadow-md transition-all">
                                <?php if ($g['media_type'] === 'image'): ?>
                                    <img src="<?= BASE_URL ?>uploads/destinations/gallery/<?= $g['media'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <?php else: ?>
                                    <video class="w-full h-full object-cover">
                                        <source src="<?= BASE_URL ?>uploads/destinations/gallery/<?= $g['media'] ?>">
                                    </video>
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/10 transition-colors">
                                        <i class="fas fa-play-circle text-white text-3xl drop-shadow-md"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Meta & Attachments -->
        <div class="space-y-6">

            <!-- Secondary Media -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Secondary Media</h3>
                <?php if ($dest['sub_media']): ?>
                    <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm aspect-video">
                        <img src="<?= BASE_URL ?>uploads/destinations/sub/<?= $dest['sub_media'] ?>" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-sm italic">No sub media provided.</p>
                <?php endif; ?>
            </div>

            <!-- Documents & Attachments -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center justify-between">
                    <span>Documents</span>
                    <span class="bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs"><?= count($attachments) ?> Files</span>
                </h3>

                <?php if (empty($attachments)): ?>
                    <p class="text-slate-500 text-sm italic">No documents attached.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($attachments as $a): ?>
                            <li>
                                <a href="<?= BASE_URL ?>uploads/destinations/attachments/<?= $a['file_name'] ?>" target="_blank" class="group flex items-start p-3 bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-200 rounded-xl transition-colors">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-500 flex items-center justify-center shrink-0 mr-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <?php if (strpos($a['file_type'], 'pdf') !== false): ?>
                                            <i class="fas fa-file-pdf text-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-word text-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-semibold text-slate-700 group-hover:text-blue-700 truncate"><?= escape($a['original_name']) ?></p>
                                        <p class="text-xs text-slate-400 mt-0.5"><?= number_format($a['file_size'] / 1024, 1) ?> KB</p>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Quick Stats/Info Box -->
            <div class="bg-slate-900 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <!-- Decoration -->
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>

                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 relative z-10">System Data</h3>
                <div class="space-y-3 relative z-10 text-sm text-slate-300">
                    <div class="flex justify-between border-b border-slate-700 pb-2">
                        <span>Database ID</span>
                        <span class="font-mono text-white">#<?= $dest['id'] ?></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-700 pb-2">
                        <span>Total Media Assets</span>
                        <span class="text-white font-semibold"><?= count($galleries) + ($dest['main_media'] ? 1 : 0) + ($dest['sub_media'] ? 1 : 0) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Status</span>
                        <span class="text-white font-semibold"><?= $dest['status'] ? 'Active' : 'Inactive' ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#view-destination-wrapper", {
                opacity: 1,
                duration: 0.6,
                ease: "power2.out"
            });
        }
    });
</script>