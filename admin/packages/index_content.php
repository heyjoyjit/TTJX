<div id="packages-wrapper" class="opacity-0">

    <!-- Semantic Header Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Packages</h1>
            <p class="text-sm text-slate-500 mt-1">Manage all your travel packages, categories, and pricing.</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= BASE_URL ?>admin/packages/add.php" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 px-5 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Package
            </a>
        </div>
    </header>

    <!-- Semantic Section for the Data Table -->
    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="packagesTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-sm text-slate-500 uppercase tracking-wider">
                        <th class="py-4 font-semibold">ID</th>
                        <th class="py-4 font-semibold">Destination</th>
                        <th class="py-4 font-semibold">Pricing Category</th> <!-- Renamed and Size removed -->
                        <th class="py-4 font-semibold">Package Title</th>
                        <th class="py-4 font-semibold">Base Price</th>
                        <th class="py-4 font-semibold text-center">Status</th>
                        <th class="py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700"></tbody>
            </table>
        </div>
    </section>
</div>

<!-- Custom Pro Styling for DataTables to match Tailwind -->
<style>
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.35rem 0.75rem;
        outline: none;
        color: #475569;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin-left: 0.25rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569 !important;
        cursor: pointer;
        transition: all 0.2s;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8fafc;
        color: #0f172a !important;
        border-color: #cbd5e1;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #eff6ff;
        color: #2563eb !important;
        border-color: #bfdbfe;
        font-weight: 600;
    }

    table.dataTable tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s;
    }

    table.dataTable tbody tr:hover {
        background-color: #f8fafc;
    }

    table.dataTable.no-footer {
        border-bottom: none;
    }

    table.dataTable thead th {
        border-bottom: 2px solid #e2e8f0;
    }
</style>

<script>
    $(document).ready(function() {

        // 1. GSAP Entrance Animations
        if (typeof gsap !== 'undefined') {
            gsap.to("#packages-wrapper", {
                opacity: 1,
                duration: 0.5
            });
            gsap.from("header", {
                y: -20,
                opacity: 0,
                duration: 0.6,
                ease: "power2.out",
                delay: 0.1
            });
            gsap.from("section", {
                y: 30,
                opacity: 0,
                duration: 0.7,
                ease: "power3.out",
                delay: 0.3
            });
        } else {
            document.getElementById('packages-wrapper').style.opacity = 1;
        }

        // 2. Initialize DataTable
        $('#packagesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= BASE_URL ?>admin/packages/get_data.php',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id',
                    className: 'py-4 text-slate-500 font-medium'
                },
                {
                    data: 'destination_name',
                    className: 'py-4 font-semibold text-slate-800',
                    render: function(data) {
                        return `<span class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-blue-400"></i> ${data}</span>`;
                    }
                },
                // Removed the Size Column block completely
                {
                    data: 'pricing_category_name',
                    className: 'py-4 text-slate-600',
                    render: function(data) {
                        return `<span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-md text-xs font-medium border border-indigo-100">${data}</span>`;
                    }
                },
                {
                    data: 'title',
                    className: 'py-4 text-slate-800 font-medium'
                },
                {
                    data: 'price',
                    className: 'py-4',
                    render: function(data) {
                        const formattedPrice = parseFloat(data).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        return `<span class="font-semibold text-emerald-600">₹${formattedPrice}</span>`;
                    }
                },
                {
                    data: 'status',
                    className: 'py-4 text-center',
                    render: function(data) {
                        return data == 1 ?
                            `<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-200">Visible</span>` :
                            `<span class="bg-slate-50 text-slate-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-slate-200">Hidden</span>`;
                    }
                },
                {
                    data: null,
                    className: 'py-4 text-right',
                    orderable: false,
                    render: function(row) {
                        let toggleColor = row.status == 1 ? 'text-amber-500 hover:bg-amber-50 hover:text-amber-600' : 'text-emerald-500 hover:bg-emerald-50 hover:text-emerald-600';
                        let toggleIcon = row.status == 1 ? 'fa-eye-slash' : 'fa-eye';
                        let toggleTitle = row.status == 1 ? 'Hide Package' : 'Show Package';

                        return `
                            <div class="flex items-center justify-end space-x-2">
                                <button class="toggle-status w-8 h-8 rounded-full flex items-center justify-center transition-colors ${toggleColor}" data-id="${row.id}" data-status="${row.status}" title="${toggleTitle}">
                                    <i class="fas ${toggleIcon}"></i>
                                </button>
                                <a href="<?= BASE_URL ?>admin/packages/view.php?id=${row.id}" class="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="View">
                                    <i class="fas fa-search"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/packages/edit.php?id=${row.id}" class="w-8 h-8 rounded-full flex items-center justify-center text-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="delete-package w-8 h-8 rounded-full flex items-center justify-center text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors" data-id="${row.id}" title="Delete Package">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [
                [0, 'desc']
            ],
            language: {
                search: "",
                searchPlaceholder: "Search packages...",
                lengthMenu: "Show _MENU_ entries"
            }
        });

        // 3. Toggle Status AJAX
        $(document).on('click', '.toggle-status', function() {
            var id = $(this).data('id');
            var currentStatus = $(this).data('status');
            var newStatus = currentStatus == 1 ? 0 : 1;

            if (typeof showLoader === 'function') showLoader();
            $.post('<?= BASE_URL ?>admin/packages/toggle_status.php', {
                id: id,
                status: newStatus,
                csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
            }, function(res) {
                if (typeof hideLoader === 'function') hideLoader();
                if (res.success) {
                    Swal.fire({
                        title: 'Success',
                        text: res.message,
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    $('#packagesTable').DataTable().ajax.reload(null, false);
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });

        // 4. Delete package via AJAX
        $(document).on('click', '.delete-package', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Delete this package?',
                text: "This will remove all associated data (sizes, categories, highlights, guidelines, days, gallery).",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();

                    $.ajax({
                        url: '<?= BASE_URL ?>admin/packages/delete.php',
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
                                    text: 'Package successfully removed.',
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                $('#packagesTable').DataTable().ajax.reload(null, false);
                            } else Swal.fire('Error', res.message || 'Action failed', 'error');
                        },
                        error: function() {
                            if (typeof hideLoader === 'function') hideLoader();
                            Swal.fire('Error', 'Server connection failed', 'error');
                        }
                    });
                }
            });
        });
    });
</script>