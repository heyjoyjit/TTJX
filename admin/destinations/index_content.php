<div id="destinations-wrapper" class="opacity-0">

    <!-- Semantic Header Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Destinations</h1>
            <p class="text-sm text-slate-500 mt-1">Manage all your travel destinations, banners, and media.</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= BASE_URL ?>admin/destinations/top_sort.php" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg shadow-sm transition-all flex items-center">
                <i class="fas fa-sort mr-2 text-slate-400"></i> Manage Top
            </a>
            <a href="<?= BASE_URL ?>admin/destinations/add.php" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 px-4 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center">
                <i class="fas fa-plus mr-2"></i> Add Destination
            </a>
        </div>
    </header>

    <!-- Semantic Section for the Data Table -->
    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="destinationsTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-sm text-slate-500 uppercase tracking-wider">
                        <th class="py-4 font-semibold">ID</th>
                        <th class="py-4 font-semibold">Place Name</th>
                        <th class="py-4 font-semibold">Banner Title</th>
                        <th class="py-4 font-semibold">Main Media</th>
                        <th class="py-4 font-semibold">Top</th>
                        <th class="py-4 font-semibold">Status</th>
                        <th class="py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    <!-- DataTable will populate via AJAX -->
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Custom Pro Styling for DataTables to match Tailwind -->
<style>
    /* DataTable Wrapper Overrides */
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
            gsap.to("#destinations-wrapper", {
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
            document.getElementById('destinations-wrapper').style.opacity = 1;
        }

        // 2. Initialize DataTable
        $('#destinationsTable').DataTable({
            processing: true,
            serverSide: false, // Server side is false, handled by get_data.php
            ajax: {
                url: '<?= BASE_URL ?>admin/destinations/get_data.php',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id',
                    className: 'py-4 text-slate-500 font-medium'
                },
                {
                    data: 'place_name',
                    className: 'py-4 font-semibold text-slate-800'
                },
                {
                    data: 'banner_title',
                    className: 'py-4 text-slate-600'
                },
                {
                    data: 'main_media',
                    className: 'py-4',
                    render: function(data) {
                        return '<div class="w-16 h-12 rounded-lg overflow-hidden border border-slate-200 shadow-sm"><img src="<?= BASE_URL ?>uploads/destinations/main/' + data + '" class="w-full h-full object-cover"></div>';
                    }
                },
                {
                    data: 'is_top_destination',
                    className: 'py-4',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"><i class="fas fa-star mr-1 text-[10px]"></i> Yes</span>' :
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">No</span>';
                    }
                },
                {
                    data: 'status',
                    className: 'py-4',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full"></span> Visible</span>' :
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><span class="w-2 h-2 mr-1.5 bg-red-500 rounded-full"></span> Hidden</span>';
                    }
                },
                {
                    data: null,
                    className: 'py-4 text-right',
                    orderable: false,
                    render: function(row) {
                        const statusColor = row.status == 1 ? 'text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:text-slate-500 hover:bg-slate-50';
                        const statusIcon = row.status == 1 ? 'fa-toggle-on' : 'fa-toggle-off';

                        return `
                            <div class="flex items-center justify-end space-x-2">
                                <a href="<?= BASE_URL ?>admin/destinations/view.php?id=${row.id}" class="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/destinations/edit.php?id=${row.id}" class="w-8 h-8 rounded-full flex items-center justify-center text-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="toggle-status w-8 h-8 rounded-full flex items-center justify-center ${statusColor} transition-colors" data-id="${row.id}" data-status="${row.status}" title="Toggle Visibility">
                                    <i class="fas ${statusIcon} text-lg"></i>
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
                searchPlaceholder: "Search destinations...",
                lengthMenu: "Show _MENU_ entries"
            }
        });

        // 3. Toggle status via AJAX
        $(document).on('click', '.toggle-status', function() {
            var id = $(this).data('id');
            var current = $(this).data('status');
            var newStatus = current == 1 ? 0 : 1;

            // Check if showLoader function exists globally
            if (typeof showLoader === 'function') showLoader();

            $.ajax({
                url: '<?= BASE_URL ?>admin/destinations/toggle_status.php',
                type: 'POST',
                data: {
                    id: id,
                    status: newStatus,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(res) {
                    if (typeof hideLoader === 'function') hideLoader();

                    if (res.success) {
                        // Toast notification for premium feel
                        Swal.fire({
                            title: 'Success!',
                            text: 'Visibility status updated.',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('#destinationsTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Action failed', 'error');
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