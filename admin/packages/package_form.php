<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;

$db = Database::getInstance()->getConnection();
$package = null;
$galleries = [];
$highlights = [];
$guidelines = [];
$days = [];

if ($package_id) {
    $stmt = $db->prepare("SELECT * FROM package_details WHERE id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    if ($package) {
        $category_id = $package['category_id'];
        // Fetch related
        $stmt = $db->prepare("SELECT * FROM package_galleries WHERE package_detail_id = ? ORDER BY sort_order");
        $stmt->execute([$package_id]);
        $galleries = $stmt->fetchAll();
        $stmt = $db->prepare("SELECT * FROM package_highlights WHERE package_detail_id = ?");
        $stmt->execute([$package_id]);
        $highlights = $stmt->fetchAll();
        $stmt = $db->prepare("SELECT * FROM package_guidelines WHERE package_detail_id = ?");
        $stmt->execute([$package_id]);
        $guidelines = $stmt->fetchAll();
        $stmt = $db->prepare("SELECT * FROM package_days WHERE package_detail_id = ? ORDER BY day_number");
        $stmt->execute([$package_id]);
        $days = $stmt->fetchAll();
    }
}

if (!$category_id && !$package_id) {
    echo '<div class="text-red-500">Invalid request</div>';
    exit;
}
?>

<form id="packageForm" action="<?= BASE_URL ?>admin/packages/save_package.php" enctype="multipart/form-data" method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="category_id" value="<?= $category_id ?>">
    <?php if ($package): ?>
        <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Title *</label>
            <input type="text" name="title" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" value="<?= $package ? escape($package['title']) : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Subtitle</label>
            <input type="text" name="subtitle" class="mt-1 block w-full border border-gray-300 rounded-md p-2" value="<?= $package ? escape($package['subtitle']) : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Price *</label>
            <input type="number" step="0.01" name="price" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" value="<?= $package ? $package['price'] : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Discount</label>
            <input type="number" step="0.01" name="discount" class="mt-1 block w-full border border-gray-300 rounded-md p-2" value="<?= $package ? $package['discount'] : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Previous Price</label>
            <input type="number" step="0.01" name="previous_price" class="mt-1 block w-full border border-gray-300 rounded-md p-2" value="<?= $package ? $package['previous_price'] : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Details PDF (optional)</label>
            <input type="file" name="details_pdf" accept=".pdf" class="mt-1 block w-full">
            <?php if ($package && $package['details_pdf']): ?>
                <div class="mt-1"><a href="<?= BASE_URL ?>uploads/packages/<?= $package['details_pdf'] ?>" target="_blank" class="text-blue-500 underline">Current PDF</a></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gallery -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Gallery (min 2 images/videos)</label>
        <input type="file" name="gallery[]" accept="image/*,video/*,image/gif" multiple class="mt-1 block w-full">
        <?php if ($galleries): ?>
            <div class="flex flex-wrap gap-2 mt-2">
                <?php foreach ($galleries as $g): ?>
                    <div class="relative inline-block" id="pkg-gallery-<?= $g['id'] ?>">
                        <?php if ($g['media_type'] === 'image'): ?>
                            <img src="<?= BASE_URL ?>uploads/packages/gallery/<?= $g['media'] ?>" width="80" class="rounded shadow">
                        <?php else: ?>
                            <video width="80">
                                <source src="<?= BASE_URL ?>uploads/packages/gallery/<?= $g['media'] ?>">
                            </video>
                        <?php endif; ?>
                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs delete-pkg-gallery" data-id="<?= $g['id'] ?>">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Highlights -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Highlights (max 10)</label>
        <div id="highlightsContainer">
            <?php if ($highlights): foreach ($highlights as $h): ?>
                    <div class="highlight-item flex gap-2 mt-1">
                        <input type="text" name="highlights[icon][]" placeholder="Icon class (e.g., fas fa-star)" class="border border-gray-300 rounded p-1 w-1/4" value="<?= escape($h['icon']) ?>">
                        <input type="text" name="highlights[text][]" placeholder="Text (max 150 chars)" maxlength="150" class="border border-gray-300 rounded p-1 flex-1" value="<?= escape($h['text']) ?>">
                        <button type="button" class="remove-highlight text-red-500"><i class="fas fa-minus-circle"></i></button>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="highlight-item flex gap-2 mt-1">
                    <input type="text" name="highlights[icon][]" placeholder="Icon class" class="border border-gray-300 rounded p-1 w-1/4">
                    <input type="text" name="highlights[text][]" placeholder="Text (max 150 chars)" maxlength="150" class="border border-gray-300 rounded p-1 flex-1">
                    <button type="button" class="remove-highlight text-red-500"><i class="fas fa-minus-circle"></i></button>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" id="addHighlight" class="mt-1 text-sm text-blue-500 hover:text-blue-700"><i class="fas fa-plus"></i> Add Highlight</button>
    </div>

    <!-- Guidelines -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Guidelines</label>
        <div id="guidelinesContainer">
            <?php if ($guidelines): foreach ($guidelines as $g): ?>
                    <div class="guideline-item border p-2 mt-1 rounded">
                        <div class="flex gap-2">
                            <input type="text" name="guidelines[icon][]" placeholder="Icon class" class="border border-gray-300 rounded p-1 w-1/4" value="<?= escape($g['icon']) ?>">
                            <input type="text" name="guidelines[title][]" placeholder="Title" class="border border-gray-300 rounded p-1 flex-1" value="<?= escape($g['title']) ?>">
                            <button type="button" class="remove-guideline text-red-500"><i class="fas fa-minus-circle"></i></button>
                        </div>
                        <textarea name="guidelines[description][]" placeholder="Description" rows="2" class="mt-1 block w-full border border-gray-300 rounded p-1"><?= escape($g['description']) ?></textarea>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="guideline-item border p-2 mt-1 rounded">
                    <div class="flex gap-2">
                        <input type="text" name="guidelines[icon][]" placeholder="Icon class" class="border border-gray-300 rounded p-1 w-1/4">
                        <input type="text" name="guidelines[title][]" placeholder="Title" class="border border-gray-300 rounded p-1 flex-1">
                        <button type="button" class="remove-guideline text-red-500"><i class="fas fa-minus-circle"></i></button>
                    </div>
                    <textarea name="guidelines[description][]" placeholder="Description" rows="2" class="mt-1 block w-full border border-gray-300 rounded p-1"></textarea>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" id="addGuideline" class="mt-1 text-sm text-blue-500 hover:text-blue-700"><i class="fas fa-plus"></i> Add Guideline</button>
    </div>

    <!-- Days -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Day-wise Plans</label>
        <div id="daysContainer">
            <?php if ($days): foreach ($days as $d): ?>
                    <div class="day-item border p-2 mt-1 rounded">
                        <div class="flex gap-2">
                            <input type="number" name="days[day_number][]" placeholder="Day #" class="border border-gray-300 rounded p-1 w-20" value="<?= $d['day_number'] ?>">
                            <input type="text" name="days[title][]" placeholder="Title" class="border border-gray-300 rounded p-1 flex-1" value="<?= escape($d['title']) ?>">
                            <button type="button" class="remove-day text-red-500"><i class="fas fa-minus-circle"></i></button>
                        </div>
                        <textarea name="days[description][]" placeholder="Description" rows="2" class="mt-1 block w-full border border-gray-300 rounded p-1"><?= escape($d['description']) ?></textarea>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="day-item border p-2 mt-1 rounded">
                    <div class="flex gap-2">
                        <input type="number" name="days[day_number][]" placeholder="Day #" class="border border-gray-300 rounded p-1 w-20">
                        <input type="text" name="days[title][]" placeholder="Title" class="border border-gray-300 rounded p-1 flex-1">
                        <button type="button" class="remove-day text-red-500"><i class="fas fa-minus-circle"></i></button>
                    </div>
                    <textarea name="days[description][]" placeholder="Description" rows="2" class="mt-1 block w-full border border-gray-300 rounded p-1"></textarea>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" id="addDay" class="mt-1 text-sm text-blue-500 hover:text-blue-700"><i class="fas fa-plus"></i> Add Day</button>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Package</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Add Highlight
        $('#addHighlight').click(function() {
            var clone = $('.highlight-item:first').clone();
            clone.find('input').val('');
            $('#highlightsContainer').append(clone);
            // Limit to 10
            if ($('.highlight-item').length >= 10) {
                $(this).hide();
            }
        });
        $(document).on('click', '.remove-highlight', function() {
            if ($('.highlight-item').length > 1) {
                $(this).closest('.highlight-item').remove();
                $('#addHighlight').show();
            }
        });

        // Add Guideline
        $('#addGuideline').click(function() {
            var clone = $('.guideline-item:first').clone();
            clone.find('input, textarea').val('');
            $('#guidelinesContainer').append(clone);
        });
        $(document).on('click', '.remove-guideline', function() {
            if ($('.guideline-item').length > 1) {
                $(this).closest('.guideline-item').remove();
            }
        });

        // Add Day
        $('#addDay').click(function() {
            var clone = $('.day-item:first').clone();
            clone.find('input, textarea').val('');
            $('#daysContainer').append(clone);
        });
        $(document).on('click', '.remove-day', function() {
            if ($('.day-item').length > 1) {
                $(this).closest('.day-item').remove();
            }
        });

        // Delete gallery item (package gallery)
        $(document).on('click', '.delete-pkg-gallery', function() {
            var id = $(this).data('id');
            var parent = $('#pkg-gallery-' + id);
            Swal.fire({
                title: 'Delete this gallery item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/packages/delete_package_gallery.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            hideLoader();
                            if (res.success) {
                                parent.remove();
                                Swal.fire('Deleted', '', 'success');
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function() {
                            hideLoader();
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    });
                }
            });
        });
    });
</script>