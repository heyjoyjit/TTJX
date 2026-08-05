<?php
// admin/destinations/edit_content.php
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();
if (!$dest) {
    echo '<div class="text-red-500 p-8 text-center font-semibold">Destination not found.</div>';
    return;
}

$stmtGall = $db->prepare("SELECT * FROM destination_galleries WHERE destination_id = ? ORDER BY sort_order");
$stmtGall->execute([$id]);
$galleries = $stmtGall->fetchAll();

$stmtAtt = $db->prepare("SELECT * FROM destination_attachments WHERE destination_id = ?");
$stmtAtt->execute([$id]);
$attachments = $stmtAtt->fetchAll();
?>

<div id="edit-destination-wrapper" class="opacity-0 max-w-5xl mx-auto">

    <header class="mb-8 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="text-slate-400 hover:text-blue-500 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Edit Destination</h1>
            </div>
            <p class="text-sm text-slate-500 ml-7">Modifying details for <span class="font-semibold text-blue-600"><?= escape($dest['place_name']) ?></span>.</p>
        </div>
    </header>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form id="editDestinationForm" enctype="multipart/form-data" method="post" action="<?= BASE_URL ?>admin/destinations/update.php">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $dest['id'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Place Name -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Place Name <span class="text-red-500">*</span></label>
                    <input type="text" name="place_name" required value="<?= escape($dest['place_name']) ?>"
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Banner Title -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Title <span class="text-red-500">*</span></label>
                    <input type="text" name="banner_title" required value="<?= escape($dest['banner_title']) ?>"
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Banner Subtitle -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Subtitle</label>
                    <input type="text" name="banner_subtitle" value="<?= escape($dest['banner_subtitle']) ?>"
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>

                <!-- Main Media Replacement -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Main Media (Replace)</label>
                    <div id="main-dropzone" class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 hover:border-blue-400 transition-colors group mb-4">
                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 group-hover:text-blue-500 mb-2"></i>
                        <p class="text-sm text-slate-500">Drag & drop or click to replace</p>
                        <input type="file" id="main_media" accept="image/*,video/*,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>

                    <!-- New File Preview Container -->
                    <div id="main-preview-container" class="mt-4 hidden"></div>

                    <!-- Current Media Display -->
                    <div id="main-current-display">
                        <?php if ($dest['main_media']): ?>
                            <p class="text-xs text-slate-400 mb-1 font-medium">Currently saved:</p>
                            <div class="relative w-32 h-20 rounded-lg overflow-hidden border border-slate-200 shadow-sm">
                                <img src="<?= BASE_URL ?>uploads/destinations/main/<?= $dest['main_media'] ?>" class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sub Media Replacement -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sub Media (Replace)</label>
                    <div id="sub-dropzone" class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 hover:border-blue-400 transition-colors group mb-4">
                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 group-hover:text-blue-500 mb-2"></i>
                        <p class="text-sm text-slate-500">Drag & drop or click to replace</p>
                        <input type="file" id="sub_media" accept="image/*,video/*,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>

                    <!-- New File Preview Container -->
                    <div id="sub-preview-container" class="mt-4 hidden"></div>

                    <!-- Current Media Display -->
                    <div id="sub-current-display">
                        <?php if ($dest['sub_media']): ?>
                            <p class="text-xs text-slate-400 mb-1 font-medium">Currently saved:</p>
                            <div class="relative w-32 h-20 rounded-lg overflow-hidden border border-slate-200 shadow-sm">
                                <img src="<?= BASE_URL ?>uploads/destinations/sub/<?= $dest['sub_media'] ?>" class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gallery Management -->
                <div class="md:col-span-2 bg-slate-50 rounded-xl p-6 border border-slate-200">

                    <!-- Add New Media to Gallery -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Add to Gallery (Images/Videos)</label>
                        <input type="file" id="gallery" accept="image/*,video/*,image/gif" multiple
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">

                        <!-- Grid Container for Multi-File Previews -->
                        <div id="gallery-preview-container" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mt-4"></div>
                    </div>

                    <!-- Current Saved Gallery -->
                    <label class="block text-sm font-semibold text-slate-700 mb-4 border-t border-slate-200 pt-4">Currently Saved Gallery Items</label>
                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($galleries as $g): ?>
                            <div class="relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-300 shadow-sm bg-white" id="gallery-<?= $g['id'] ?>">
                                <?php if ($g['media_type'] === 'image'): ?>
                                    <img src="<?= BASE_URL ?>uploads/destinations/gallery/<?= $g['media'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <video class="w-full h-full object-cover" muted>
                                        <source src="<?= BASE_URL ?>uploads/destinations/gallery/<?= $g['media'] ?>">
                                    </video>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" class="bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-all delete-gallery" data-id="<?= $g['id'] ?>" title="Delete Media">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($galleries)) echo '<span class="text-sm text-slate-400 italic">No gallery items uploaded yet.</span>'; ?>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Attachments (PDF, DOCX)</label>
                    <input type="file" name="attachments[]" accept=".pdf,.docx" multiple
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all mb-4">

                    <ul class="space-y-2">
                        <?php foreach ($attachments as $a): ?>
                            <li id="attachment-<?= $a['id'] ?>" class="flex items-center justify-between bg-white border border-slate-200 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">
                                <a href="<?= BASE_URL ?>uploads/destinations/attachments/<?= $a['file_name'] ?>" target="_blank" class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-file-alt text-indigo-400 mr-3 text-lg"></i> <?= escape($a['original_name']) ?>
                                </a>
                                <button type="button" class="text-red-400 hover:text-red-600 p-2 transition-colors delete-attachment" data-id="<?= $a['id'] ?>" title="Delete File">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>

                <!-- Top Destination Toggle -->
                <div class="md:col-span-2 bg-slate-50 p-6 rounded-xl border border-slate-200">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="is_top_destination" value="1" class="sr-only peer" id="topCheck" <?= $dest['is_top_destination'] ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="ml-3 text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Mark as Top Destination</span>
                    </label>

                    <div id="topSortContainer" style="<?= $dest['is_top_destination'] ? '' : 'display:none;' ?>" class="mt-4 pl-14 overflow-hidden">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sort Order (1-10)</label>
                        <input type="number" name="top_sort_order" min="1" max="10" value="<?= $dest['top_sort_order'] ?? '' ?>"
                            class="block w-32 bg-white border border-slate-200 rounded-lg py-2 px-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-all">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Update Destination
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Global variables for live file preview states
        let selectedMainFile = null;
        let selectedSubFile = null;
        let selectedGalleryFiles = [];

        if (typeof gsap !== 'undefined') {
            gsap.to("#edit-destination-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }

        $('#topCheck').change(function() {
            if ($(this).is(':checked')) $('#topSortContainer').slideDown(200);
            else $('#topSortContainer').slideUp(200);
        });

        // ----------------------------------------------------
        // Preview Element Generator
        // ----------------------------------------------------
        function createPreviewElement(file, onRemoveCallback) {
            const wrapper = document.createElement('div');
            wrapper.className = 'relative w-full aspect-video md:aspect-square bg-slate-900 rounded-xl overflow-hidden shadow-inner group border border-slate-200';

            let element;
            if (file.type.startsWith('video/')) {
                element = document.createElement('video');
                element.className = 'w-full h-full object-cover';
                element.muted = true;
                element.playsInline = true;
                element.autoplay = true;
            } else {
                element = document.createElement('img');
                element.className = 'w-full h-full object-cover';
            }

            element.src = URL.createObjectURL(file);
            wrapper.appendChild(element);

            // Floating Unselect Button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center shadow-md transition-transform transform scale-90 group-hover:scale-100 z-10';
            deleteBtn.innerHTML = '<i class="fas fa-times text-xs"></i>';
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                URL.revokeObjectURL(element.src);
                onRemoveCallback();
            });

            wrapper.appendChild(deleteBtn);
            return wrapper;
        }

        // ----------------------------------------------------
        // 1. Handling Main Media
        // ----------------------------------------------------
        $('#main_media').change(function(e) {
            const file = e.target.files[0];
            if (!file) return;

            selectedMainFile = file;
            $('#main-dropzone, #main-current-display').addClass('hidden');

            const preview = createPreviewElement(file, () => {
                selectedMainFile = null;
                $('#main_media').val('');
                $('#main-preview-container').empty().addClass('hidden');
                $('#main-dropzone, #main-current-display').removeClass('hidden');
            });
            $('#main-preview-container').empty().append(preview).removeClass('hidden');
        });

        // ----------------------------------------------------
        // 2. Handling Sub Media
        // ----------------------------------------------------
        $('#sub_media').change(function(e) {
            const file = e.target.files[0];
            if (!file) return;

            selectedSubFile = file;
            $('#sub-dropzone, #sub-current-display').addClass('hidden');

            const preview = createPreviewElement(file, () => {
                selectedSubFile = null;
                $('#sub_media').val('');
                $('#sub-preview-container').empty().addClass('hidden');
                $('#sub-dropzone, #sub-current-display').removeClass('hidden');
            });
            $('#sub-preview-container').empty().append(preview).removeClass('hidden');
        });

        // ----------------------------------------------------
        // 3. Handling New Gallery Media
        // ----------------------------------------------------
        $('#gallery').change(function(e) {
            const files = Array.from(e.target.files);

            files.forEach(file => {
                // Prevent showing duplicates based on unique attributes
                if (selectedGalleryFiles.some(f => f.name === file.name && f.size === file.size)) return;

                selectedGalleryFiles.push(file);

                const preview = createPreviewElement(file, () => {
                    selectedGalleryFiles = selectedGalleryFiles.filter(f => !(f.name === file.name && f.size === file.size));
                    $(preview).remove();
                });

                $('#gallery-preview-container').append(preview);
            });

            // Clear input so same file can be chosen again if needed
            $('#gallery').val('');
        });

        // ----------------------------------------------------
        // Server Deletions (AJAX)
        // ----------------------------------------------------
        $('.delete-gallery').click(function() {
            var id = $(this).data('id');
            var parent = $('#gallery-' + id);
            Swal.fire({
                title: 'Delete this media?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/destinations/delete_gallery.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (res.success) {
                                gsap.to(parent, {
                                    scale: 0,
                                    opacity: 0,
                                    duration: 0.3,
                                    onComplete: () => parent.remove()
                                });
                            } else Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });

        $('.delete-attachment').click(function() {
            var id = $(this).data('id');
            var parent = $('#attachment-' + id);
            Swal.fire({
                title: 'Remove attachment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/destinations/delete_attachment.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (res.success) {
                                gsap.to(parent, {
                                    x: 50,
                                    opacity: 0,
                                    duration: 0.3,
                                    onComplete: () => parent.remove()
                                });
                            } else Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });

        // ----------------------------------------------------
        // Form Submission
        // ----------------------------------------------------
        $('#editDestinationForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Append single tracked uploads implicitly if replaced
            if (selectedMainFile) formData.append('main_media', selectedMainFile);
            if (selectedSubFile) formData.append('sub_media', selectedSubFile);

            // Append live array of gallery additions
            selectedGalleryFiles.forEach(file => {
                formData.append('gallery[]', file);
            });

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
                    if (res.success) {
                        Swal.fire({
                                title: 'Success!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            })
                            .then(() => window.location.href = '<?= BASE_URL ?>admin/destinations/index.php');
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