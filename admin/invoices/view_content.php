<?php
// admin/invoices/view_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$invoice_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.tagline, c.address_line_1 as comp_addr1, c.city as comp_city, 
           c.state as comp_state, c.pincode as comp_pin, c.phone as comp_phone, c.email as comp_email,
           c.gstin as comp_gstin, c.pan as comp_pan, c.bank_name, c.account_name, c.account_number, 
           c.ifsc_code, c.upi_id
    FROM invoices i
    LEFT JOIN company_addresses c ON i.company_address_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    die("<div class='p-8 text-red-500 font-bold'>Invoice Not Found.</div>");
}

$itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemsStmt->execute([$invoice_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-2xl p-8 my-8 text-gray-800 border border-gray-200" id="printSection">

    <!-- HEADER -->
    <div class="flex justify-between items-start border-b pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-indigo-950 tracking-wide"><?php echo strtolower($inv['company_name']); ?></h1>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest"><?php echo htmlspecialchars($inv['tagline']); ?></p>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-900 text-xs font-bold rounded-md uppercase">
                TAX INVOICE
            </span>
            <p class="text-xs text-gray-500 mt-2"><strong>Invoice No:</strong> <?php echo $inv['invoice_number']; ?></p>
            <p class="text-xs text-gray-500"><strong>Booking Ref:</strong> <?php echo $inv['booking_ref_no']; ?></p>
        </div>

        <!-- Add this below the TAX INVOICE span in view_content.php -->
        <?php if (strtolower($inv['status']) === 'paid'): ?>
            <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 text-sm font-extrabold rounded-md border border-green-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                PAID IN FULL
            </div>
        <?php endif; ?>
    </div>

    <!-- SELLER & CLIENT DETAILS -->
    <div class="grid grid-cols-2 gap-8 py-6 text-xs border-b">
        <div>
            <p class="font-bold text-gray-900 text-sm mb-1"><?php echo $inv['company_name']; ?></p>
            <p><?php echo $inv['comp_addr1']; ?>, <?php echo $inv['comp_city']; ?>, <?php echo $inv['comp_state']; ?> <?php echo $inv['comp_pin']; ?></p>
            <p>Phone: <?php echo $inv['comp_phone']; ?> | Email: <?php echo $inv['comp_email']; ?></p>
            <p class="mt-1 font-semibold text-gray-700">GSTIN: <?php echo $inv['comp_gstin']; ?> | PAN: <?php echo $inv['comp_pan']; ?></p>
        </div>
        <div>
            <p class="font-bold text-gray-400 uppercase tracking-wider text-[10px] mb-1">BILL TO</p>
            <p class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($inv['customer_name']); ?></p>
            <p><?php echo htmlspecialchars($inv['billing_address']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($inv['customer_phone']); ?> | Email: <?php echo htmlspecialchars($inv['customer_email']); ?></p>
            <?php if (!empty($inv['client_gstin'])): ?>
                <p class="font-bold text-indigo-900">Client GSTIN: <?php echo $inv['client_gstin']; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- META METRICS -->
    <div class="grid grid-cols-4 gap-4 py-4 text-xs border-b bg-gray-50/50 p-3 rounded-xl my-4">
        <div>
            <span class="text-gray-400 block">Invoice Date</span>
            <span class="font-bold"><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></span>
        </div>
        <div>
            <span class="text-gray-400 block">Transaction ID</span>
            <span class="font-bold"><?php echo $inv['transaction_id'] ?: 'N/A'; ?></span>
        </div>
        <div>
            <span class="text-gray-400 block">Due Date</span>
            <span class="font-bold"><?php echo date('d M Y', strtotime($inv['due_date'])); ?></span>
        </div>
        <div>
            <span class="text-gray-400 block">Place of Supply</span>
            <span class="font-bold"><?php echo $inv['place_of_supply']; ?></span>
        </div>
    </div>

    <!-- TRAVEL DETAILS -->
    <div class="mb-6 p-4 bg-indigo-50/40 rounded-xl border border-indigo-100 flex justify-between text-xs">
        <div>
            <span class="font-bold text-indigo-950 block text-sm"><?php echo htmlspecialchars($inv['destination_name']); ?></span>
            <span class="text-gray-500">Tier: <?php echo $inv['travel_tier']; ?></span>
        </div>
        <div class="text-right">
            <span class="font-bold text-gray-800 block">Travelers: <?php echo $inv['pax_adults']; ?> Adults, <?php echo $inv['pax_children']; ?> Children, <?php echo $inv['pax_infants']; ?> Infants</span>
            <?php if ($inv['travel_start_date']): ?>
                <span class="text-gray-500"><?php echo date('d M Y', strtotime($inv['travel_start_date'])); ?> to <?php echo date('d M Y', strtotime($inv['travel_end_date'])); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <table class="w-full text-left text-xs mb-6 border-collapse">
        <thead>
            <tr class="border-b border-gray-300 text-gray-500 uppercase font-bold">
                <th class="py-2">Description</th>
                <th class="py-2 text-center">HSN/SAC</th>
                <th class="py-2 text-center">Qty</th>
                <th class="py-2 text-right">Unit Price (₹)</th>
                <th class="py-2 text-right">Taxable</th>
                <th class="py-2 text-right">CGST</th>
                <th class="py-2 text-right">SGST</th>
                <th class="py-2 text-right">Total (₹)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($items as $item): ?>
                <tr>
                    <td class="py-3 font-semibold text-gray-900"><?php echo htmlspecialchars($item['description']); ?></td>
                    <td class="py-3 text-center"><?php echo $item['hsn_sac']; ?></td>
                    <td class="py-3 text-center"><?php echo $item['quantity']; ?></td>
                    <td class="py-3 text-right"><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="py-3 text-right"><?php echo number_format($item['taxable_value'], 2); ?></td>
                    <td class="py-3 text-right"><?php echo number_format($inv['cgst_amount'], 2); ?></td>
                    <td class="py-3 text-right"><?php echo number_format($inv['sgst_amount'], 2); ?></td>
                    <td class="py-3 text-right font-bold text-gray-900"><?php echo number_format($item['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TOTALS & BANK DETAILS -->
    <div class="grid grid-cols-2 gap-8 pt-4 border-t">
        <div class="text-xs space-y-3">
            <p class="font-bold text-gray-900 uppercase tracking-wider text-[10px]">BANK & PAYMENT DETAILS</p>
            <p>Bank: <strong><?php echo $inv['bank_name']; ?></strong> | A/C Name: <strong><?php echo $inv['account_name']; ?></strong></p>
            <p>A/C No: <strong><?php echo $inv['account_number']; ?></strong> | IFSC: <strong><?php echo $inv['ifsc_code']; ?></strong></p>
            <p>UPI ID: <strong><?php echo $inv['upi_id']; ?></strong></p>

            <?php if (!empty($inv['upi_qr_data'])): ?>
                <div class="pt-2 flex items-center gap-4">
                    <img src="<?php echo $inv['upi_qr_data']; ?>" alt="Scan & Pay UPI QR" class="w-24 h-24 border p-1 rounded-lg shadow-sm" />
                    <p class="text-[10px] text-gray-500 italic max-w-[150px]">Scan with GPay/PhonePe to complete instant invoice payment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-xs space-y-2 text-right">
            <div class="flex justify-between border-b pb-1">
                <span class="text-gray-500">Total Taxable Value:</span>
                <span class="font-bold">₹<?php echo number_format($inv['subtotal'], 2); ?></span>
            </div>
            <div class="flex justify-between border-b pb-1">
                <span class="text-gray-500">Total CGST:</span>
                <span class="font-bold">₹<?php echo number_format($inv['cgst_amount'], 2); ?></span>
            </div>
            <div class="flex justify-between border-b pb-1">
                <span class="text-gray-500">Total SGST:</span>
                <span class="font-bold">₹<?php echo number_format($inv['sgst_amount'], 2); ?></span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-indigo-950 pt-2 border-t-2 border-gray-900">
                <span>Grand Total:</span>
                <span>₹<?php echo number_format($inv['total'], 2); ?></span>
            </div>
            <p class="text-[10px] text-gray-500 font-semibold italic pt-2">
                Amount in Words: <?php echo convertAmountToWords($inv['total']); ?>
            </p>
        </div>
    </div>

    <!-- TERMS -->
    <div class="mt-8 pt-4 border-t text-[10px] text-gray-400 space-y-1">
        <p class="font-bold text-gray-600 uppercase">TERMS & CONDITIONS</p>
        <p>1. 100% payment required prior to trip commencement.</p>
        <p>2. Cancellations made within 7 days of travel are non-refundable.</p>
    </div>
</div>

<!-- ACTION BUTTONS -->
<div class="max-w-4xl mx-auto flex justify-end gap-4 mb-12">
    <button onclick="window.print()" class="px-6 py-2.5 bg-gray-800 text-white rounded-xl text-sm font-bold shadow-md hover:bg-black">
        🖨️ Print / Download PDF
    </button>
    <?php if ($inv['status'] !== 'paid'): ?>
        <a href="mark_status.php?id=<?php echo $inv['id']; ?>&status=paid" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-green-700" onclick="return confirm('Mark as Paid via Offline/Manual transfer?');">
            ✔️ Mark as Paid
        </a>
    <?php endif; ?>
    <a href="send_email.php?id=<?php echo $inv['id']; ?>" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700">
        ✉️ Email PDF Invoice to Client & Hotel
    </a>
</div>