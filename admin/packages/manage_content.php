<!-- CSS Fix: Force SweetAlert Modals above Tailwind z-[9999] modals -->
<style>
    div.swal2-container {
        z-index: 999999 !important;
    }
</style>

<div id="manage-packages-wrapper" class="opacity-0 max-w-6xl mx-auto pb-12">

    <header class="mb-8 flex justify-between items-end">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= BASE_URL ?>admin/packages/index.php" class="w-10 h-10 rounded-full flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 shadow-sm transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Packages for <?= escape($dest['place_name']) ?></h1>
            </div>
            <p class="text-sm text-slate-500 ml-12">Manage sizes, categories, and specific package details for this destination.</p>
        </div>
    </header>

    <!-- Add Size Form (Card) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 flex items-center gap-6">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
            <i class="fas fa-expand-arrows-alt text-xl"></i>
        </div>
        <div class="flex-1">
            <h2 class="text-lg font-bold text-slate-800 mb-1">Add Package Size</h2>
            <p class="text-xs text-slate-500">e.g. "2 Adults, 1 Child" or "Group of 10"</p>
        </div>
        <form id="addSizeForm" class="flex items-center gap-3 w-full max-w-md">
            <input type="hidden" name="destination_id" value="<?= $destination_id ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="text" name="name" placeholder="Size Name" required class="flex-1 bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-4 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-5 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Add
            </button>
        </form>
    </div>

    <!-- Sizes Container (AJAX Loaded) -->
    <div id="sizesContainer" class="space-y-6">
        <!-- Will be loaded via AJAX -->
        <div class="p-12 text-center text-slate-400">
            <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
            <p>Loading structure...</p>
        </div>
    </div>
</div>

<!-- Modal for Adding/Editing Package Detail -->
<div id="packageModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[9999] transition-opacity opacity-0 duration-300">
    <div class="relative top-10 mx-auto p-8 border border-slate-200 w-11/12 md:w-3/4 lg:w-2/3 shadow-2xl rounded-2xl bg-white transform scale-95 transition-transform duration-300">
        <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
            <h3 id="packageModalTitle" class="text-2xl font-bold text-slate-800">Package Details</h3>
            <button onclick="closePackageModal()" class="text-slate-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="packageModalBody">
            <!-- Dynamic form will be loaded here -->
        </div>
    </div>
</div>

<script>
    function openPackageModal(categoryId, packageId) {
        var url = '<?= BASE_URL ?>admin/packages/package_form.php';
        var data = {
            category_id: categoryId
        };

        if (packageId) {
            data.package_id = packageId;
            $('#packageModalTitle').text('Edit Package Detail');
        } else {
            $('#packageModalTitle').text('Add Package Detail');
        }

        if (typeof showLoader === 'function') showLoader();

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            dataType: 'html',
            success: function(html) {
                if (typeof hideLoader === 'function') hideLoader();

                $('#packageModalBody').html(html);

                // Animate Modal In
                const modal = document.getElementById('packageModal');
                modal.classList.remove('hidden');
                void modal.offsetWidth; // force reflow
                modal.classList.remove('opacity-0');
                modal.querySelector('.relative').classList.remove('scale-95');

                // Re-bind form submit for the dynamic form
                $('#packageForm').submit(function(e) {
                    e.preventDefault();
                    if (typeof tinymce !== 'undefined') tinymce.triggerSave();

                    var formData = new FormData(this);
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
                                    .then(() => {
                                        closePackageModal();
                                        loadSizes();
                                    });
                            } else Swal.fire('Error', res.message, 'error');
                        },
                        error: function() {
                            if (typeof hideLoader === 'function') hideLoader();
                            Swal.fire('Error', 'Server connection failed', 'error');
                        }
                    });
                });
            },
            error: function() {
                if (typeof hideLoader === 'function') hideLoader();
                Swal.fire('Error', 'Failed to load form', 'error');
            }
        });
    }

    function closePackageModal() {
        const modal = document.getElementById('packageModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.relative').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            $('#packageModalBody').html('');
        }, 300);
    }

    function loadSizes() {
        var destId = <?= $destination_id ?>;
        $.ajax({
            url: '<?= BASE_URL ?>admin/packages/get_sizes.php',
            type: 'GET',
            data: {
                destination_id: destId
            },
            dataType: 'html',
            success: function(html) {
                $('#sizesContainer').html(html);
                bindSizeEvents();
            },
            error: function() {
                $('#sizesContainer').html('<div class="text-red-500 text-center p-6"><i class="fas fa-exclamation-triangle mb-2 text-2xl"></i><br>Failed to load structure.</div>');
            }
        });
    }

    function bindSizeEvents() {
        // Delete size
        $('.delete-size').click(function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Delete this size?',
                text: "This will remove all associated categories and packages.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/packages/delete_size.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (res.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                loadSizes();
                            } else Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });

        // Add Category form
        $('.add-category-form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var sizeId = form.data('size-id');
            var categoryName = form.find('input[name="name"]').val(); // MATCHED PARAMETER TO BACKEND
            if (!categoryName) return;

            if (typeof showLoader === 'function') showLoader();
            $.ajax({
                url: '<?= BASE_URL ?>admin/packages/add_category.php',
                type: 'POST',
                data: {
                    size_id: sizeId,
                    name: categoryName, // MATCHED PARAMETER TO BACKEND
                    csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                },
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Category added.',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        loadSizes();
                    } else Swal.fire('Error', res.message, 'error');
                }
            });
        });

        // Delete Category
        $('.delete-category').click(function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Delete this category?',
                text: "This will remove all associated packages.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/packages/delete_category.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (res.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                loadSizes();
                            } else Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });

        // Trigger Modals
        $('.add-package').click(function() {
            var categoryId = $(this).data('category-id');
            openPackageModal(categoryId, null);
        });
        $('.edit-package').click(function() {
            var packageId = $(this).data('package-id');
            var categoryId = $(this).data('category-id');
            openPackageModal(categoryId, packageId);
        });

        // Delete Package Detail
        $('.delete-package').click(function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Delete this package?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/packages/delete_package.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (res.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                loadSizes();
                            } else Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        });
    }

    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("#manage-packages-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }

        loadSizes();

        // Add Size Main Form
        $('#addSizeForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            if (typeof showLoader === 'function') showLoader();
            $.ajax({
                url: '<?= BASE_URL ?>admin/packages/add_size.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Size added.',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('#addSizeForm')[0].reset();
                        loadSizes();
                    } else Swal.fire('Error', res.message, 'error');
                },
                error: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });
</script>