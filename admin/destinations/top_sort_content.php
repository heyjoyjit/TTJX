<?php
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, place_name, top_sort_order FROM destinations WHERE is_top_destination = 1 ORDER BY top_sort_order ASC");
$topDestinations = $stmt->fetchAll();
?>

<!-- Include jQuery UI for Drag and Drop -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- Add jQuery UI Touch Punch to enable mobile touch support -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

<div id="sort-wrapper" class="opacity-0 max-w-4xl mx-auto">

    <header class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="text-slate-400 hover:text-blue-500 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Sort Top Destinations</h1>
            </div>
            <p class="text-sm text-slate-500 ml-7">Drag and drop the cards below to reorganize your featured destinations.</p>
        </div>
        <button id="saveTopOrder" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md shadow-emerald-500/30 transition-all flex items-center gap-2">
            <i class="fas fa-save"></i> Save Ordering
        </button>
    </header>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <?php if (empty($topDestinations)): ?>
            <div class="text-center py-10">
                <i class="fas fa-map-signs text-4xl text-slate-300 mb-4"></i>
                <p class="text-slate-500 font-medium">No top destinations found.</p>
                <p class="text-sm text-slate-400 mt-1">Edit a destination and mark it as "Top" to see it here.</p>
            </div>
        <?php else: ?>
            <ul id="topSortList" class="list-none p-0 space-y-3 relative">
                <?php foreach ($topDestinations as $index => $item): ?>
                    <li data-id="<?= $item['id'] ?>" class="sortable-item group bg-white border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all p-4 rounded-xl cursor-move flex justify-between items-center relative overflow-hidden">

                        <!-- Left active border indicator (shows on hover/drag) -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="flex items-center gap-4 pl-2">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm sort-number">
                                <?= $index + 1 ?>
                            </div>
                            <span class="text-lg font-semibold text-slate-700"><?= escape($item['place_name']) ?></span>
                        </div>

                        <div class="text-slate-300 group-hover:text-blue-400 transition-colors">
                            <i class="fas fa-grip-lines text-xl"></i>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<style>
    /* Styling for the drop placeholder */
    .ui-state-highlight {
        height: 72px;
        /* Match list item height */
        background-color: #eff6ff !important;
        /* blue-50 */
        border: 2px dashed #93c5fd !important;
        /* blue-300 */
        border-radius: 0.75rem;
        /* xl */
        margin-bottom: 0.75rem;
    }

    .ui-sortable-helper {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        opacity: 0.9;
        border-color: #3b82f6 !important;
    }
</style>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#sort-wrapper", {
                opacity: 1,
                duration: 0.5
            });
            gsap.from(".sortable-item", {
                x: -20,
                opacity: 0,
                duration: 0.4,
                stagger: 0.05,
                ease: "power2.out"
            });
        }

        // Initialize jQuery UI Sortable
        $("#topSortList").sortable({
            placeholder: "ui-state-highlight",
            axis: "y",
            cursor: "grabbing",
            update: function(event, ui) {
                // Instantly update the visual numbers when dropped
                $('#topSortList .sort-number').each(function(index) {
                    $(this).text(index + 1);
                });
            }
        });
        $("#topSortList").disableSelection();

        $('#saveTopOrder').click(function() {
            var ids = [];
            $('#topSortList li').each(function(index) {
                ids.push({
                    id: $(this).data('id'),
                    order: index + 1
                });
            });

            if (ids.length === 0) return;

            if (typeof showLoader === 'function') showLoader();
            $.ajax({
                url: '<?= BASE_URL ?>admin/destinations/update_top_order.php',
                type: 'POST',
                data: {
                    orders: ids,
                    csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                },
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) {
                        Swal.fire({
                            title: 'Order Saved!',
                            text: 'Your destinations have been reordered.',
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        });
                    } else Swal.fire('Error', res.message, 'error');
                },
                error: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                    Swal.fire('Error', 'Server connection failed', 'error');
                }
            });
        });
    });
</script>