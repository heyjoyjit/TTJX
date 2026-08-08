<?php
// hotel/invoices/index_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
// Assuming the logged-in hotel's ID is stored in the session
$hotel_id = $_SESSION['hotel_id'] ?? 0;

// Fetch ONLY invoices meant for this specific hotel
$stmt = $pdo->prepare("
    SELECT id, invoice_number, destination_name, total, invoice_date, status, due_date
    FROM invoices 
    WHERE hotel_id = ? AND invoice_type = 'hotel'
    ORDER BY created_at DESC
");
$stmt->execute([$hotel_id]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Settlement Invoices</h1>
        <p class="mt-2 text-sm text-gray-500">View and download your official settlement bills from Traveltara.</p>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice No.</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Package / Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Due</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Download</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                    <?php echo htmlspecialchars($inv['invoice_number']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($inv['destination_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></div>
                                    <div class="text-xs text-gray-500">Due: <?php echo date('d M Y', strtotime($inv['due_date'])); ?></div>
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
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- Reuses the admin PDF generator, just ensure auth checks allow hotel sessions to view their own IDs -->
                                    <a href="<?= BASE_URL ?>hotel/invoices/download_pdf.php?id=<?php echo $inv['id']; ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-4 py-2 rounded-lg font-bold" target="_blank">
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No settlement invoices available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>