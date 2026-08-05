<div id="hotels-wrapper" class="opacity-0 max-w-7xl mx-auto">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Registered Hotels</h1>
            <p class="text-sm text-slate-500 mt-1">Manage hotel partnerships, setup progress, and status.</p>
        </div>
        <a href="<?= BASE_URL ?>admin/hotel/add.php" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-5 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
            <i class="fas fa-hotel"></i> Add Hotel
        </a>
    </header>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="hotelsTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-sm text-slate-500 uppercase tracking-wider">
                        <th class="py-4 font-semibold">Registered ID</th>
                        <th class="py-4 font-semibold">Hotel & Contact</th>
                        <th class="py-4 font-semibold">Destination</th>
                        <th class="py-4 font-semibold">Owner</th>
                        <th class="py-4 font-semibold">Setup Progress</th>
                        <th class="py-4 font-semibold text-center">Status</th>
                        <th class="py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700"></tbody>
            </table>
        </div>
    </section>
</div>

<!-- Pro Styling for DataTables -->
<style>
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.35rem 0.75rem;
        outline: none;
        color: #475569;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    table.dataTable tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s;
    }

    table.dataTable tbody tr:hover {
        background-color: #f8fafc;
    }
</style>

<script>
    // Exposed globally for inline onclick execution
    function sendReminder(hotelId) {
        Swal.fire({
            title: 'Send Onboarding Reminder?',
            text: "This will email the setup link to the hotel owner.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, Send Email!'
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof showLoader === 'function') showLoader();
                $.post('<?= BASE_URL ?>admin/hotel/send_reminder.php', {
                    id: hotelId,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                }, function(res) {
                    if (typeof hideLoader === 'function') hideLoader();
                    if (res.success) {
                        Swal.fire('Sent!', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    $(document).ready(function() {
        if (typeof gsap !== 'undefined') gsap.to("#hotels-wrapper", {
            opacity: 1,
            y: 0,
            duration: 0.5
        });

        $('#hotelsTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= BASE_URL ?>admin/hotel/get_data.php',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'hotel_registered_id',
                    className: 'py-4 font-mono text-sm font-semibold text-indigo-600'
                },
                {
                    data: 'hotel_name',
                    className: 'py-4'
                },
                {
                    data: 'destination',
                    className: 'py-4 font-medium text-slate-600'
                },
                {
                    data: 'owner_name',
                    className: 'py-4 font-medium text-slate-700'
                },
                {
                    data: 'progress',
                    className: 'py-4'
                },
                {
                    data: 'status',
                    className: 'py-4 text-center',
                    render: data => data == 1 ?
                        `<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-200">Active</span>` :
                        `<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-rose-200">Inactive</span>`
                },
                {
                    data: 'actions',
                    className: 'py-4 text-right',
                    orderable: false
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        $(document).on('click', '.toggle-status', function() {
            var id = $(this).data('id');
            var currentStatus = $(this).data('status');
            var newStatus = currentStatus ? 0 : 1;

            if (typeof showLoader === 'function') showLoader();
            $.post('<?= BASE_URL ?>admin/hotel/toggle_status.php', {
                id: id,
                status: newStatus,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
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
                    $('#hotelsTable').DataTable().ajax.reload(null, false);
                } else Swal.fire('Error', res.message, 'error');
            }, 'json');
        });
    });
</script>