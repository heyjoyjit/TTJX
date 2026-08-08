<?php
// admin/invoices/index_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// Fetch all invoices with basic details
$stmt = $pdo->query("
    SELECT i.id, i.invoice_number, i.invoice_type, i.customer_name, i.destination_name, 
           i.total, i.invoice_date, i.status, h.hotel_name
    FROM invoices i
    LEFT JOIN hotels h ON i.hotel_id = h.id
    ORDER BY i.created_at DESC
");
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Invoice Management</h1>
            <p class="mt-2 text-sm text-gray-500">Manage all Client Tax Invoices and Hotel Settlement Bills.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="add.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                + Create New Invoice
            </a>
        </div>
    </div>


    <div class="mb-4 flex space-x-2">
        <button onclick="filterInvoices('all')" class="px-4 py-2 text-sm font-bold bg-gray-800 text-white rounded-lg">All Invoices</button>
        <button onclick="filterInvoices('customer')" class="px-4 py-2 text-sm font-bold bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200">Client Invoices</button>
        <button onclick="filterInvoices('hotel')" class="px-4 py-2 text-sm font-bold bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200">Hotel Settlements</button>
    </div>
    <script>
        function filterInvoices(type) {
            const rows = document.querySelectorAll('.invoice-row');
            rows.forEach(row => {
                if (type === 'all' || row.dataset.type === type) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice / Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bill To</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Destination</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-indigo-600"><?php echo htmlspecialchars($inv['invoice_number']); ?></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php if ($inv['invoice_type'] === 'hotel'): ?>
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-semibold">Hotel Bill</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full font-semibold">Client Tax Inv</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        <?php echo $inv['invoice_type'] === 'hotel' ? htmlspecialchars($inv['hotel_name']) : htmlspecialchars($inv['customer_name']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($inv['destination_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    ₹<?php echo number_format($inv['total'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $color = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($inv['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="view.php?id=<?php echo $inv['id']; ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md">View</a>
                                    <a href="download_pdf.php?id=<?php echo $inv['id']; ?>" class="text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1 rounded-md" target="_blank">PDF</a>
                                    <a href="send_email.php?id=<?php echo $inv['id']; ?>" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 px-3 py-1 rounded-md">Email</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No invoices generated yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>