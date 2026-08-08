<?php
// admin/invoices/edit_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$invoice_id = intval($_GET['id'] ?? 0);

// Fetch existing invoice
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    die("<div class='p-8 text-red-500 font-bold'>Invoice Not Found.</div>");
}

// Fetch existing line items
$itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$itemStmt->execute([$invoice_id]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

$primaryAddr = getPrimaryCompanyAddress($pdo);
$hotels = $pdo->query("SELECT id, hotel_name, city FROM hotels WHERE status = 1 ORDER BY hotel_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Edit Invoice: <?php echo htmlspecialchars($inv['invoice_number']); ?>
            </h2>
        </div>
    </div>

    <form action="update.php" method="POST" id="invoiceForm" class="space-y-8 bg-white shadow-xl rounded-2xl p-6 sm:p-10 border border-gray-100">
        <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">

        <!-- SECTION 1: Invoice Header & Receiver Type -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Target</label>
                <select name="invoice_type" id="invoice_type" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 border" onchange="toggleReceiverType(this.value)">
                    <option value="customer" <?php echo $inv['invoice_type'] === 'customer' ? 'selected' : ''; ?>>Customer / Traveler Invoice</option>
                    <option value="hotel" <?php echo $inv['invoice_type'] === 'hotel' ? 'selected' : ''; ?>>Hotel / Vendor Settlement</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Booking Reference No.</label>
                <input type="text" name="booking_ref_no" value="<?php echo htmlspecialchars($inv['booking_ref_no']); ?>" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 border" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Billing Company Address</label>
                <input type="text" readonly value="<?php echo htmlspecialchars($primaryAddr['company_name']); ?> (<?php echo $primaryAddr['gstin']; ?>)" class="w-full rounded-xl bg-gray-50 border-gray-300 text-gray-500 text-sm p-3 border" />
                <input type="hidden" name="company_address_id" value="<?php echo $inv['company_address_id']; ?>">
            </div>
        </div>

        <!-- SECTION 2: Dynamic Customer / Hotel Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div id="hotel_select_box" class="<?php echo $inv['invoice_type'] === 'hotel' ? '' : 'hidden'; ?>">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Hotel</label>
                <select name="hotel_id" class="w-full rounded-xl border-gray-300 shadow-sm text-sm p-3 border">
                    <option value="">-- Choose Hotel --</option>
                    <?php foreach ($hotels as $h): ?>
                        <option value="<?php echo $h['id']; ?>" <?php echo $inv['hotel_id'] == $h['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($h['hotel_name'] . ' (' . $h['city'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Name / Business Name</label>
                <input type="text" name="customer_name" required value="<?php echo htmlspecialchars($inv['customer_name']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Email</label>
                <input type="email" name="customer_email" required value="<?php echo htmlspecialchars($inv['customer_email']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Phone</label>
                <input type="text" name="customer_phone" required value="<?php echo htmlspecialchars($inv['customer_phone']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client GSTIN</label>
                <input type="text" name="client_gstin" value="<?php echo htmlspecialchars($inv['client_gstin']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border uppercase" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Billing Address</label>
                <input type="text" name="billing_address" required value="<?php echo htmlspecialchars($inv['billing_address']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>
        </div>

        <!-- SECTION 3: Dynamic PAX & Travel Details -->
        <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100 space-y-6">
            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">Travel & PAX Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Destination</label>
                    <input type="text" name="destination_name" value="<?php echo htmlspecialchars($inv['destination_name']); ?>" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" required />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tier / Category</label>
                    <select name="travel_tier" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                        <option value="Budget" <?php echo $inv['travel_tier'] === 'Budget' ? 'selected' : ''; ?>>Budget Tier</option>
                        <option value="Standard" <?php echo $inv['travel_tier'] === 'Standard' ? 'selected' : ''; ?>>Standard Tier</option>
                        <option value="Luxury" <?php echo $inv['travel_tier'] === 'Luxury' ? 'selected' : ''; ?>>Bespoke Luxury</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Start Date</label>
                    <input type="date" name="travel_start_date" value="<?php echo $inv['travel_start_date']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">End Date</label>
                    <input type="date" name="travel_end_date" value="<?php echo $inv['travel_end_date']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" />
                </div>
            </div>

            <!-- Advanced PAX Breakdown -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 bg-white p-4 rounded-xl border border-indigo-100">
                <div>
                    <label class="block text-xs font-bold text-gray-700">Adults</label>
                    <input type="number" name="pax_adults" min="1" value="<?php echo $inv['pax_adults']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Child (With Bed)</label>
                    <input type="number" name="pax_cwb" min="0" value="<?php echo $inv['pax_cwb']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Child (No Bed)</label>
                    <input type="number" name="pax_cnb" min="0" value="<?php echo $inv['pax_cnb']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Infants</label>
                    <input type="number" name="pax_infants" min="0" value="<?php echo $inv['pax_infants']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Extra Mattress</label>
                    <input type="number" name="pax_extra_bed" min="0" value="<?php echo $inv['pax_extra_bed']; ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
            </div>
            <div class="mt-3">
                <label class="block text-xs font-bold text-gray-700">Custom PAX Notes (Optional)</label>
                <input type="text" name="pax_custom_details" value="<?php echo htmlspecialchars($inv['pax_custom_details']); ?>" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
            </div>
        </div>

        <!-- SECTION 4: Dynamic Tax Configuration -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Taxation Type</label>
                <select name="tax_type" id="tax_type" class="w-full rounded-xl border-gray-300 text-sm p-3 border">
                    <option value="intra_state" <?php echo $inv['tax_type'] === 'intra_state' ? 'selected' : ''; ?>>Intra-State GST (CGST 2.5% + SGST 2.5%)</option>
                    <option value="inter_state" <?php echo $inv['tax_type'] === 'inter_state' ? 'selected' : ''; ?>>Inter-State GST (IGST 5%)</option>
                    <option value="international_lut" <?php echo $inv['tax_type'] === 'international_lut' ? 'selected' : ''; ?>>International (Export Under LUT - 0%)</option>
                    <option value="manual" <?php echo $inv['tax_type'] === 'manual' ? 'selected' : ''; ?>>Manual GST Override</option>
                </select>

                <div id="manual_tax_inputs" class="<?php echo $inv['tax_type'] === 'manual' ? 'grid' : 'hidden'; ?> grid-cols-3 gap-4 mt-4 bg-gray-50 p-4 rounded-lg border">
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom CGST (%)</label>
                        <input type="number" step="0.01" id="manual_cgst" name="cgst_rate" value="<?php echo $inv['cgst_rate']; ?>" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom SGST (%)</label>
                        <input type="number" step="0.01" id="manual_sgst" name="sgst_rate" value="<?php echo $inv['sgst_rate']; ?>" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom IGST (%)</label>
                        <input type="number" step="0.01" id="manual_igst" name="igst_rate" value="<?php echo $inv['igst_rate']; ?>" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Place of Supply (POS)</label>
                <input type="text" name="place_of_supply" value="<?php echo htmlspecialchars($inv['place_of_supply']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Due Date</label>
                <input type="date" name="due_date" value="<?php echo $inv['due_date']; ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>
        </div>

        <!-- SECTION 5: Line Items Table -->
        <div>
            <h3 class="text-lg font-bold text-gray-900 mb-4">Invoice Line Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border border-gray-200 rounded-xl" id="itemsTable">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-bold text-gray-700 uppercase">
                            <th class="p-3 border">Description</th>
                            <th class="p-3 border w-28">HSN/SAC</th>
                            <th class="p-3 border w-20">Qty</th>
                            <th class="p-3 border w-32">Unit Price (₹)</th>
                            <th class="p-3 border w-32">Total (₹)</th>
                            <th class="p-3 border w-16 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php foreach ($items as $index => $item): ?>
                            <tr class="item-row">
                                <td class="p-2 border"><input type="text" name="items[<?php echo $index; ?>][description]" value="<?php echo htmlspecialchars($item['description']); ?>" class="w-full border-0 focus:ring-0 text-sm" required /></td>
                                <td class="p-2 border"><input type="text" name="items[<?php echo $index; ?>][hsn_sac]" value="<?php echo htmlspecialchars($item['hsn_sac']); ?>" class="w-full border-0 focus:ring-0 text-sm text-center" /></td>
                                <td class="p-2 border"><input type="number" name="items[<?php echo $index; ?>][quantity]" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input w-full border-0 focus:ring-0 text-sm text-center" oninput="recalculateTotals()" /></td>
                                <td class="p-2 border"><input type="number" step="0.01" name="items[<?php echo $index; ?>][unit_price]" value="<?php echo $item['unit_price']; ?>" class="price-input w-full border-0 focus:ring-0 text-sm" oninput="recalculateTotals()" /></td>
                                <td class="p-2 border"><input type="number" step="0.01" name="items[<?php echo $index; ?>][total]" value="<?php echo $item['taxable_value']; ?>" readonly class="row-total w-full border-0 bg-gray-50 text-sm font-semibold" /></td>
                                <td class="p-2 border text-center"><button type="button" class="text-red-500 hover:text-red-700" onclick="removeRow(this)">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" onclick="addRow()" class="mt-4 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-100">
                + Add Item Line
            </button>
        </div>

        <!-- SECTION 6: Totals & Tax Summary -->
        <div class="flex flex-col md:flex-row justify-between items-start pt-6 border-t border-gray-200 gap-6">
            <div class="w-full md:w-1/2 space-y-2">
                <p class="text-sm font-bold text-gray-700">Payment Transaction ID</p>
                <input type="text" name="transaction_id" value="<?php echo htmlspecialchars($inv['transaction_id']); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div class="w-full md:w-1/3 bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total Taxable Value:</span>
                    <span class="font-bold" id="lblTaxable">₹<?php echo number_format($inv['subtotal'], 2); ?></span>
                </div>
                <div class="flex justify-between text-sm <?php echo ($inv['tax_type'] === 'inter_state' || $inv['tax_type'] === 'international_lut') && $inv['tax_type'] !== 'manual' ? 'hidden' : ''; ?>" id="cgstRow">
                    <span class="text-gray-600">CGST Amount:</span>
                    <span class="font-bold" id="lblCGST">₹<?php echo number_format($inv['cgst_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between text-sm <?php echo ($inv['tax_type'] === 'inter_state' || $inv['tax_type'] === 'international_lut') && $inv['tax_type'] !== 'manual' ? 'hidden' : ''; ?>" id="sgstRow">
                    <span class="text-gray-600">SGST Amount:</span>
                    <span class="font-bold" id="lblSGST">₹<?php echo number_format($inv['sgst_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between text-sm <?php echo ($inv['tax_type'] === 'intra_state' || $inv['tax_type'] === 'international_lut') && $inv['tax_type'] !== 'manual' ? 'hidden' : ''; ?>" id="igstRow">
                    <span class="text-gray-600">IGST Amount:</span>
                    <span class="font-bold" id="lblIGST">₹<?php echo number_format($inv['igst_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between text-base font-extrabold border-t pt-2 text-indigo-900">
                    <span>Grand Total:</span>
                    <span id="lblGrandTotal">₹<?php echo number_format($inv['total'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Submit Action -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition-all">
                Update Invoice
            </button>
        </div>
    </form>
</div>

<script>
    let globalRowCounter = <?php echo count($items); ?>;

    function toggleReceiverType(val) {
        const hotelBox = document.getElementById('hotel_select_box');
        if (val === 'hotel') {
            hotelBox.classList.remove('hidden');
        } else {
            hotelBox.classList.add('hidden');
        }
    }

    document.getElementById('tax_type').addEventListener('change', function() {
        const manualBox = document.getElementById('manual_tax_inputs');
        if (this.value === 'manual') {
            manualBox.classList.remove('hidden');
            manualBox.classList.add('grid');
        } else {
            manualBox.classList.add('hidden');
            manualBox.classList.remove('grid');
        }
        recalculateTotals();
    });

    document.querySelector('select[name="hotel_id"]').addEventListener('change', async function() {
        if (!this.value) return;
        try {
            let res = await fetch(`../hotel/get_hotel_json.php?id=${this.value}`);
            let data = await res.json();

            if (data && !data.error) {
                document.querySelector('input[name="customer_name"]').value = data.hotel_name || '';
                document.querySelector('input[name="customer_email"]').value = data.email || '';
                document.querySelector('input[name="customer_phone"]').value = data.phone || '';
                document.querySelector('input[name="billing_address"]').value = `${data.location || ''}, ${data.city || ''}`;
            }
        } catch (err) {
            console.error("Failed to auto-fill hotel details.");
        }
    });

    function addRow() {
        const html = `
    <tr class="item-row">
      <td class="p-2 border"><input type="text" name="items[${globalRowCounter}][description]" required class="w-full border-0 focus:ring-0 text-sm" /></td>
      <td class="p-2 border"><input type="text" name="items[${globalRowCounter}][hsn_sac]" value="9985" class="w-full border-0 focus:ring-0 text-sm text-center" /></td>
      <td class="p-2 border"><input type="number" name="items[${globalRowCounter}][quantity]" value="1" min="1" class="qty-input w-full border-0 focus:ring-0 text-sm text-center" oninput="recalculateTotals()" /></td>
      <td class="p-2 border"><input type="number" step="0.01" name="items[${globalRowCounter}][unit_price]" value="0.00" class="price-input w-full border-0 focus:ring-0 text-sm" oninput="recalculateTotals()" /></td>
      <td class="p-2 border"><input type="number" step="0.01" name="items[${globalRowCounter}][total]" value="0.00" readonly class="row-total w-full border-0 bg-gray-50 text-sm font-semibold" /></td>
      <td class="p-2 border text-center"><button type="button" class="text-red-500 hover:text-red-700" onclick="removeRow(this)">✕</button></td>
    </tr>`;
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
        globalRowCounter++;
    }

    function removeRow(btn) {
        if (document.querySelectorAll('.item-row').length > 1) {
            btn.closest('tr').remove();
            recalculateTotals();
        }
    }

    function recalculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const rowTot = qty * price;
            row.querySelector('.row-total').value = rowTot.toFixed(2);
            subtotal += rowTot;
        });

        const taxType = document.getElementById('tax_type').value;
        let cgst = 0,
            sgst = 0,
            igst = 0;

        if (taxType === 'intra_state') {
            cgst = subtotal * 0.025;
            sgst = subtotal * 0.025;
            document.getElementById('cgstRow').classList.remove('hidden');
            document.getElementById('sgstRow').classList.remove('hidden');
            document.getElementById('igstRow').classList.add('hidden');
        } else if (taxType === 'inter_state') {
            igst = subtotal * 0.05;
            document.getElementById('cgstRow').classList.add('hidden');
            document.getElementById('sgstRow').classList.add('hidden');
            document.getElementById('igstRow').classList.remove('hidden');
        } else if (taxType === 'manual') {
            let mCgst = parseFloat(document.getElementById('manual_cgst').value) || 0;
            let mSgst = parseFloat(document.getElementById('manual_sgst').value) || 0;
            let mIgst = parseFloat(document.getElementById('manual_igst').value) || 0;
            cgst = subtotal * (mCgst / 100);
            sgst = subtotal * (mSgst / 100);
            igst = subtotal * (mIgst / 100);
            document.getElementById('cgstRow').classList.remove('hidden');
            document.getElementById('sgstRow').classList.remove('hidden');
            document.getElementById('igstRow').classList.remove('hidden');
        } else {
            document.getElementById('cgstRow').classList.add('hidden');
            document.getElementById('sgstRow').classList.add('hidden');
            document.getElementById('igstRow').classList.add('hidden');
        }

        const grandTotal = subtotal + cgst + sgst + igst;

        document.getElementById('lblTaxable').innerText = '₹' + subtotal.toFixed(2);
        document.getElementById('lblCGST').innerText = '₹' + cgst.toFixed(2);
        document.getElementById('lblSGST').innerText = '₹' + sgst.toFixed(2);
        document.getElementById('lblIGST').innerText = '₹' + igst.toFixed(2);
        document.getElementById('lblGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
    }

    // Add this at the bottom of your <script> in edit_content.php
    window.addEventListener('DOMContentLoaded', (event) => {
        // Trigger the tax dropdown change to show/hide manual inputs if needed
        document.getElementById('tax_type').dispatchEvent(new Event('change'));
        // Force a fresh calculation based on pre-filled PHP values
        recalculateTotals();
    });
</script>