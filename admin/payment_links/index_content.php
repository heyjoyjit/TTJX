<div id="payment-links-wrapper" class="opacity-0 max-w-7xl mx-auto">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Payment Links</h1>
            <p class="text-sm text-slate-500 mt-1">Generate and manage Razorpay payment links for clients.</p>
        </div>
        <a href="<?= BASE_URL ?>admin/payment_links/add.php" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-5 rounded-lg shadow-md shadow-blue-500/30 transition-all flex items-center gap-2">
            <i class="fas fa-link"></i> Create Link
        </a>
    </header>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="linksTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-sm text-slate-500 uppercase tracking-wider">
                        <th class="py-4 font-semibold">Client Name</th>
                        <th class="py-4 font-semibold">Type</th>
                        <th class="py-4 font-semibold">Amount</th>
                        <th class="py-4 font-semibold">Status</th>
                        <th class="py-4 font-semibold">Created On</th>
                        <th class="py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700"></tbody>
            </table>
        </div>
    </section>
</div>

<!-- Custom Pro Styling for DataTables -->
<style>
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.35rem 0.75rem;
        outline: none;
        color: #475569;
        transition: all 0.2s;
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

    table.dataTable.no-footer {
        border-bottom: none;
    }
</style>

<script>
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') gsap.to("#payment-links-wrapper", {
            opacity: 1,
            y: 0,
            duration: 0.5
        });

        $('#linksTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= BASE_URL ?>admin/payment_links/get_data.php',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'customer_name',
                    className: 'py-4 font-medium text-slate-800'
                },
                {
                    data: 'hotel_id',
                    className: 'py-4 text-xs text-slate-500',
                    render: data => data ? `<span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded-md"><i class="fas fa-hotel"></i> Hotel</span>` : `<span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md"><i class="fas fa-user"></i> Direct</span>`
                },
                {
                    data: 'amount',
                    className: 'py-4',
                    render: data => `<span class="font-semibold text-emerald-600">₹${parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>`
                },
                {
                    data: 'status',
                    className: 'py-4',
                    render: data => {
                        let color = data === 'paid' ? 'emerald' : (data === 'expired' ? 'red' : 'amber');
                        return `<span class="bg-${color}-100 text-${color}-700 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider">${data}</span>`;
                    }
                },
                {
                    data: 'created_at',
                    className: 'py-4 text-sm text-slate-500',
                    render: data => data ? new Date(data).toLocaleDateString('en-GB') : 'N/A'
                },
                {
                    data: null,
                    className: 'py-4 text-right',
                    orderable: false,
                    render: row => {
                        let actions = `<div class="flex items-center justify-end space-x-2">
                            <a href="${row.razorpay_link_url}" target="_blank" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-500 hover:bg-blue-500 hover:text-white transition-colors" title="Open Link"><i class="fas fa-external-link-alt"></i></a>`;

                        if (row.status !== 'paid' && row.status !== 'expired') {
                            actions += `<button class="send-email-btn w-8 h-8 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-500 hover:bg-indigo-500 hover:text-white transition-colors" data-id="${row.id}" title="Send Email"><i class="fas fa-paper-plane"></i></button>`;
                        }
                        return actions + `</div>`;
                    }
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        $(document).on('click', '.send-email-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Send via Email?',
                text: "The payment link will be sent to the client.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Send'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof showLoader === 'function') showLoader();
                    $.post('<?= BASE_URL ?>admin/payment_links/send_email.php', {
                        id: id,
                        csrf_token: $('meta[name="csrf-token"]').attr('content') || getCsrfToken()
                    }, function(res) {
                        if (typeof hideLoader === 'function') hideLoader();
                        if (res.success) {
                            Swal.fire({
                                title: 'Sent!',
                                text: res.message,
                                icon: 'success',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else Swal.fire('Error', res.message, 'error');
                    }, 'json');
                }
            });
        });
    });
</script>