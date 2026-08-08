<div id="add-destination-wrapper" class="opacity-0 max-w-5xl mx-auto">

    <header class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <a href="<?= BASE_URL ?>admin/destinations/index.php" class="text-slate-400 hover:text-blue-500 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Add New Destination</h1>
        </div>
        <p class="text-sm text-slate-500 ml-7">Create a new travel destination and upload associated media.</p>
    </header>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form id="addDestinationForm" enctype="multipart/form-data" method="post" action="<?= BASE_URL ?>admin/destinations/save.php">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Place Name -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Place Name <span class="text-red-500">*</span></label>
                    <input type="text" name="place_name" required placeholder="e.g. Maldives"
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Banner Title -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Title <span class="text-red-500">*</span></label>
                    <input type="text" name="banner_title" required placeholder="e.g. Explore the Paradise"
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Banner Subtitle -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Subtitle</label>
                    <input type="text" name="banner_subtitle" placeholder="e.g. Discover white sand beaches and crystal clear waters..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>

                <!-- Main Media Upload -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Main Media <span class="text-red-500">*</span></label>
                    <div id="main-dropzone" class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 hover:border-blue-400 transition-colors group">
                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 group-hover:text-blue-500 mb-2"></i>
                        <p class="text-sm text-slate-500">Drag & drop or click to upload</p>
                        <input type="file" id="main_media" accept="image/*,video/*,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <!-- Live Container for Single File Preview -->
                    <div id="main-preview-container" class="mt-4 hidden"></div>
                </div>

                <!-- Sub Media Upload -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sub Media (Optional)</label>
                    <div id="sub-dropzone" class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 hover:border-blue-400 transition-colors group">
                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 group-hover:text-blue-500 mb-2"></i>
                        <p class="text-sm text-slate-500">Drag & drop or click to upload</p>
                        <input type="file" id="sub_media" accept="image/*,video/*,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <!-- Live Container for Single File Preview -->
                    <div id="sub-preview-container" class="mt-4 hidden"></div>
                </div>

                <!-- Gallery Upload (Multiple Files) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Gallery (Multiple Images/Videos)</label>
                    <input type="file" id="gallery" accept="image/*,video/*,image/gif" multiple
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all mb-4">
                    <!-- Grid Container for Multi-File Previews -->
                    <div id="gallery-preview-container" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4"></div>
                </div>

                <!-- Attachments -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Attachments (PDF, DOCX)</label>
                    <input type="file" name="attachments[]" accept=".pdf,.docx" multiple
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>

                <!-- Top Destination Toggle -->
                <div class="md:col-span-2 bg-slate-50 p-6 rounded-xl border border-slate-200">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="is_top_destination" value="1" class="sr-only peer" id="topCheck">
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="ml-3 text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Mark as Top Destination</span>
                    </label>

                    <div id="topSortContainer" style="display:none;" class="mt-4 pl-14 overflow-hidden">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sort Order (1-10)</label>
                        <input type="number" name="top_sort_order" min="1" max="10" placeholder="1"
                            class="block w-32 bg-white border border-slate-200 rounded-lg py-2 px-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="<?= BASE_URL ?>admin/destinations/index.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-all">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Save Destination
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Global variables tracking state array storage
        let selectedMainFile = null;
        let selectedSubFile = null;
        let selectedGalleryFiles = [];

        if (typeof gsap !== 'undefined') {
            gsap.to("#add-destination-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        }

        $('#topCheck').change(function() {
            if ($(this).is(':checked')) {
                $('#topSortContainer').slideDown(200);
            } else {
                $('#topSortContainer').slideUp(200);
            }
        });

        // ----------------------------------------------------
        // Helper Rendering Function for File Previews
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
            $('#main-dropzone').addClass('hidden');
            const preview = createPreviewElement(file, () => {
                selectedMainFile = null;
                $('#main_media').val('');
                $('#main-preview-container').empty().addClass('hidden');
                $('#main-dropzone').removeClass('hidden');
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
            $('#sub-dropzone').addClass('hidden');
            const preview = createPreviewElement(file, () => {
                selectedSubFile = null;
                $('#sub_media').val('');
                $('#sub-preview-container').empty().addClass('hidden');
                $('#sub-dropzone').removeClass('hidden');
            });
            $('#sub-preview-container').empty().append(preview).removeClass('hidden');
        });

        // ----------------------------------------------------
        // 3. Handling Gallery Media (Multiple Selection Stacked)
        // ----------------------------------------------------
        $('#gallery').change(function(e) {
            const files = Array.from(e.target.files);

            files.forEach(file => {
                // Prevent showing absolute duplicates matching unique parameter attributes
                if (selectedGalleryFiles.some(f => f.name === file.name && f.size === file.size)) return;

                selectedGalleryFiles.push(file);
                const currentIndex = selectedGalleryFiles.length - 1;

                const preview = createPreviewElement(file, () => {
                    // Filter out unselected entry tracking by match parameters
                    selectedGalleryFiles = selectedGalleryFiles.filter(f => !(f.name === file.name && f.size === file.size));
                    $(preview).remove();
                });

                $('#gallery-preview-container').append(preview);
            });

            // Clear original file array string to allow choosing the same sequence back-to-back
            $('#gallery').val('');
        });

        // ----------------------------------------------------
        // Form Submission Interception & Dynamic Data Assembly
        // ----------------------------------------------------
        $('#addDestinationForm').submit(function(e) {
            e.preventDefault();

            if (!selectedMainFile) {
                Swal.fire('Validation Error', 'Main media is required.', 'warning');
                return;
            }

            var formData = new FormData(this);

            // Append single tracked uploads explicitly
            formData.append('main_media', selectedMainFile);
            if (selectedSubFile) {
                formData.append('sub_media', selectedSubFile);
            }

            // Append live accumulated collection array elements to match multiple syntax structural keys
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
                        }).then(() => {
                            window.location.href = '<?= BASE_URL ?>admin/destinations/index.php';
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                    Swal.fire('Error', 'Server connection failed', 'error');
                }
            });
        });
    });
</script>