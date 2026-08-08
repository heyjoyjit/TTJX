<?php
// admin/packages/view_content.php

try {
    $db = Database::getInstance()->getConnection();

    // 1. Fetch Core Package Details First (No JOINs to prevent silent crashes)
    $stmt = $db->prepare("SELECT * FROM package_details WHERE id = ?");
    $stmt->execute([$id]);
    $pkg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pkg) {
        echo '<div class="text-rose-500 p-8 text-center font-semibold text-xl">Package not found in database.</div>';
        return;
    }

    // 2. Safely Fetch Destination (With Smart Fallback)
    $destName = 'Not Assigned';
    $destIdToFetch = null;

    // Try the new structure first (Destination mapped directly to Package)
    if (!empty($pkg['destination_id'])) {
        $destIdToFetch = $pkg['destination_id'];
    } 
    // Fallback: Try the old structure (Destination mapped to Category)
    elseif (!empty($pkg['package_category_id'])) {
        $catFallbackStmt = $db->prepare("SELECT destination_id FROM package_categories WHERE id = ?");
        $catFallbackStmt->execute([$pkg['package_category_id']]);
        $legacyDestId = $catFallbackStmt->fetchColumn();
        if ($legacyDestId) {
            $destIdToFetch = $legacyDestId;
        }
    }

    // If we found an ID using either method, fetch the name
    if ($destIdToFetch) {
        $dStmt = $db->prepare("SELECT place_name FROM destinations WHERE id = ?");
        $dStmt->execute([$destIdToFetch]);
        $fetchedDest = $dStmt->fetchColumn();
        if ($fetchedDest) {
            $destName = $fetchedDest;
        }
    }

    // 3. Safely Fetch Category (Handles both possible column names)
    $catName = 'Not Assigned';
    if (!empty($pkg['package_category_id'])) {
        $cStmt = $db->prepare("SELECT * FROM package_categories WHERE id = ?");
        $cStmt->execute([$pkg['package_category_id']]);
        $cat = $cStmt->fetch(PDO::FETCH_ASSOC);
        if ($cat) {
            $catName = $cat['pricing_category_name'] ?? ($cat['category_name'] ?? 'Not Assigned');
        }
    }

    // Bind them back to the array for the HTML to use
    $pkg['destination_name'] = $destName;
    $pkg['category_name'] = $catName;

    // 4. Fetch Related Data
    $hlStmt = $db->prepare("SELECT * FROM package_highlights WHERE package_detail_id = ? ORDER BY sort_order");
    $hlStmt->execute([$id]);
    $highlights = $hlStmt->fetchAll(PDO::FETCH_ASSOC);

    $glStmt = $db->prepare("SELECT * FROM package_guidelines WHERE package_detail_id = ? ORDER BY sort_order");
    $glStmt->execute([$id]);
    $guidelines = $glStmt->fetchAll(PDO::FETCH_ASSOC);

    $dayStmt = $db->prepare("SELECT * FROM package_days WHERE package_detail_id = ? ORDER BY day_number");
    $dayStmt->execute([$id]);
    $days = $dayStmt->fetchAll(PDO::FETCH_ASSOC);

    $gallStmt = $db->prepare("SELECT * FROM package_galleries WHERE package_detail_id = ? ORDER BY sort_order");
    $gallStmt->execute([$id]);
    $galleries = $gallStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Offer Calculations
    $basePrice = floatval($pkg['price']);
    $finalPrice = $basePrice;
    $offerTag = '';

    if (isset($pkg['offer_type']) && $pkg['offer_type'] !== 'none') {
        if ($pkg['offer_type'] === 'price') {
            $finalPrice = $basePrice - floatval($pkg['offer_value']);
            $offerTag = "₹" . number_format($pkg['offer_value'], 2) . " OFF";
        } elseif ($pkg['offer_type'] === 'percent') {
            $finalPrice = $basePrice - ($basePrice * (floatval($pkg['offer_value']) / 100));
            $offerTag = floatval($pkg['offer_value']) . "% OFF";
        }
    }
} catch (PDOException $e) {
    // If anything fails, print the exact SQL error so it doesn't hide silently
    echo '<div class="bg-rose-50 border border-rose-200 text-rose-700 p-6 rounded-xl max-w-3xl mx-auto mt-8 font-mono text-sm">';
    echo '<strong>Database Error:</strong><br>' . htmlspecialchars($e->getMessage());
    echo '</div>';
    return;
}
?>

<div id="view-package-wrapper" class="opacity-0 max-w-7xl mx-auto pb-12">

    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <a href="<?= BASE_URL ?>admin/packages/index.php" class="text-slate-400 hover:text-blue-600 transition-colors bg-white px-4 py-2.5 rounded-lg shadow-sm border border-slate-200 font-medium flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
        <div class="flex gap-3">
            <a href="<?= BASE_URL ?>admin/packages/edit.php?id=<?= $id ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm shadow-indigo-500/30 transition-colors flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Package
            </a>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-sm border border-slate-100 relative overflow-hidden mb-6">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-10 -mt-10 z-0"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <?php if (isset($pkg['status']) && $pkg['status'] == 1): ?>
                        <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-200 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active</span>
                    <?php else: ?>
                        <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-slate-200">Hidden</span>
                    <?php endif; ?>
                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-blue-100"><i class="fas fa-map-marker-alt mr-1"></i> <?= escape($pkg['destination_name']) ?></span>
                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-indigo-100"><i class="fas fa-tag mr-1"></i> <?= escape($pkg['category_name']) ?></span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight mb-2"><?= escape($pkg['title']) ?></h1>
                <?php if (!empty($pkg['subtitle'])): ?>
                    <p class="text-slate-500 text-lg font-medium"><?= escape($pkg['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Pricing Badge -->
            <div class="bg-slate-900 p-6 rounded-2xl text-white shadow-xl shadow-slate-900/20 w-full md:w-72 shrink-0">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Package Price</p>
                <div class="flex items-end gap-2 mb-2">
                    <h2 class="text-3xl font-black text-emerald-400">₹<?= number_format($finalPrice, 2) ?></h2>
                </div>

                <?php if (isset($pkg['offer_type']) && $pkg['offer_type'] !== 'none'): ?>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-slate-400 line-through">₹<?= number_format($basePrice, 2) ?></span>
                        <span class="bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded text-xs border border-rose-500/30"><?= $offerTag ?></span>
                    </div>
                    <?php if (!empty($pkg['upto_limit'])): ?>
                        <p class="text-xs text-blue-300 mt-3 font-medium bg-blue-900/30 py-1.5 px-3 rounded-lg border border-blue-700/50">
                            <i class="fas fa-info-circle mr-1"></i> Valid up to <?= escape($pkg['upto_limit']) ?>
                        </p>
                    <?php endif; ?>
                <?php elseif (!empty($pkg['previous_price'])): ?>
                    <div class="text-sm text-slate-400 line-through">₹<?= number_format($pkg['previous_price'], 2) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column (Itinerary & Guidelines) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Days / Itinerary -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4"><i class="fas fa-calendar-alt text-rose-500"></i> Itinerary Details</h2>

                <?php if (empty($days)): ?>
                    <p class="text-slate-400 italic text-sm">No itinerary days added.</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($days as $day): ?>
                            <div class="relative pl-8 before:content-[''] before:absolute before:left-3.5 before:top-8 before:bottom-[-24px] before:w-0.5 before:bg-slate-200 last:before:hidden">
                                <div class="absolute left-0 top-1 w-7 h-7 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-sm border border-rose-200">
                                    <?= $day['day_number'] ?>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 mb-2"><?= escape($day['title']) ?></h3>
                                <div class="text-slate-600 text-sm leading-relaxed prose prose-sm max-w-none">
                                    <?= $day['description'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Guidelines -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4"><i class="fas fa-clipboard-list text-cyan-500"></i> Guidelines & Policies</h2>

                <?php if (empty($guidelines)): ?>
                    <p class="text-slate-400 italic text-sm">No guidelines added.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($guidelines as $gl): ?>
                            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center text-lg">
                                        <i class="fas <?= escape($gl['icon']) ?>"></i>
                                    </div>
                                    <h3 class="font-bold text-slate-800"><?= escape($gl['title']) ?></h3>
                                </div>
                                <div class="text-slate-600 text-sm leading-relaxed prose prose-sm max-w-none">
                                    <?= $gl['description'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column (Highlights, Gallery, Docs) -->
        <div class="space-y-6">

            <!-- Highlights -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4"><i class="fas fa-star text-amber-500"></i> Key Highlights</h2>
                <?php if (empty($highlights)): ?>
                    <p class="text-slate-400 italic text-sm">No highlights added.</p>
                <?php else: ?>
                    <ul class="space-y-4">
                        <?php foreach ($highlights as $hl): ?>
                            <li class="flex items-start gap-3">
                                <div class="mt-0.5 w-6 h-6 shrink-0 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-xs">
                                    <i class="fas <?= escape($hl['icon']) ?>"></i>
                                </div>
                                <p class="text-sm text-slate-700 font-medium leading-tight"><?= escape($hl['text']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Documents -->
            <?php if (!empty($pkg['details_pdf'])): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4"><i class="fas fa-file-pdf text-red-500"></i> Documents</h2>
                    <a href="<?= BASE_URL ?>uploads/packages/pdfs/<?= $pkg['details_pdf'] ?>" target="_blank" class="flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-red-300 hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-red-100 text-red-500 rounded-xl flex items-center justify-center text-2xl group-hover:bg-red-500 group-hover:text-white transition-colors">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Package Brochure</p>
                            <p class="text-xs text-slate-500">Click to view or download PDF</p>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Media Gallery -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4"><i class="fas fa-images text-purple-500"></i> Media Gallery</h2>
                <?php if (empty($galleries)): ?>
                    <p class="text-slate-400 italic text-sm">No media files available.</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($galleries as $g): ?>
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-900 group">
                                <?php if ($g['media_type'] === 'image'): ?>
                                    <img src="<?= BASE_URL ?>uploads/packages/gallery/<?= $g['media'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <?php else: ?>
                                    <video class="w-full h-full object-cover opacity-80" muted loop autoplay>
                                        <source src="<?= BASE_URL ?>uploads/packages/gallery/<?= $g['media'] ?>">
                                    </video>
                                    <div class="absolute inset-0 flex items-center justify-center"><i class="fas fa-play-circle text-white/70 text-3xl"></i></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#view-package-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }
    });
</script>