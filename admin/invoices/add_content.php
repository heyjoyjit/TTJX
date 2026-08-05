<?php
// admin/invoices/add_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$primaryAddr = getPrimaryCompanyAddress($pdo);
$hotels = $pdo->query("SELECT id, hotel_name, city FROM hotels WHERE status = 1 ORDER BY hotel_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$destinations = $pdo->query("SELECT id, place_name FROM destinations WHERE status = 1 ORDER BY place_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Create Advanced Tax Invoice
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Generate compliant B2B/B2C client or hotel settlement invoices with dynamic PAX & GST logic.
            </p>
        </div>
    </div>

    <form action="save.php" method="POST" id="invoiceForm" class="space-y-8 bg-white shadow-xl rounded-2xl p-6 sm:p-10 border border-gray-100">

        <!-- SECTION 1: Invoice Header & Receiver Type -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Target</label>
                <select name="invoice_type" id="invoice_type" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 border" onchange="toggleReceiverType(this.value)">
                    <option value="customer" selected>Customer / Traveler Invoice</option>
                    <option value="hotel">Hotel / Vendor Settlement</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Booking Reference No.</label>
                <input type="text" name="booking_ref_no" value="TRV-<?php echo strtoupper(substr(md5(uniqid()), 0, 6)); ?>" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 border" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Billing Company</label>
                <input type="text" readonly value="<?php echo htmlspecialchars($primaryAddr['company_name']); ?> (<?php echo $primaryAddr['gstin']; ?>)" class="w-full rounded-xl bg-gray-50 border-gray-300 text-gray-500 text-sm p-3 border" />
                <input type="hidden" name="company_address_id" value="<?php echo $primaryAddr['id']; ?>">
            </div>
        </div>

        <!-- SECTION 2: Dynamic Customer / Hotel Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div id="hotel_select_box" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Hotel</label>
                <select name="hotel_id" class="w-full rounded-xl border-gray-300 shadow-sm text-sm p-3 border">
                    <option value="">-- Choose Hotel --</option>
                    <?php foreach ($hotels as $h): ?>
                        <option value="<?php echo $h['id']; ?>"><?php echo htmlspecialchars($h['hotel_name'] . ' (' . $h['city'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Name / Business Name</label>
                <input type="text" name="customer_name" required placeholder="e.g. John Doe / Acme Travels" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Email</label>
                <input type="email" name="customer_email" required placeholder="client@example.com" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Phone</label>
                <input type="text" name="customer_phone" required placeholder="+919876543210" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client GSTIN (Optional B2B)</label>
                <input type="text" name="client_gstin" placeholder="19AAAAA0000A1Z5" class="w-full rounded-xl border-gray-300 text-sm p-3 border uppercase" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Billing Address</label>
                <input type="text" name="billing_address" required placeholder="Full billing address" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>
        </div>

        <!-- SECTION 3: Dynamic PAX & Travel Details -->
        <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100 space-y-6">
            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Travel & PAX Configuration
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Destination</label>
                    <input type="text" name="destination_name" placeholder="e.g. Sikkim 4D/3N" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" required />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tier / Category</label>
                    <select name="travel_tier" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                        <option value="Budget">Budget Tier</option>
                        <option value="Standard" selected>Standard Tier</option>
                        <option value="Luxury">Bespoke Luxury</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Start Date</label>
                    <input type="date" name="travel_start_date" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">End Date</label>
                    <input type="date" name="travel_end_date" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" />
                </div>
            </div>

            <!-- PAX Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl border border-indigo-100">
                <div>
                    <label class="block text-xs font-bold text-gray-700">Adults (12+ Yrs)</label>
                    <input type="number" name="pax_adults" min="1" value="1" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Children (2-11 Yrs)</label>
                    <input type="number" name="pax_children" min="0" value="0" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700">Infants (Under 2 Yrs)</label>
                    <input type="number" name="pax_infants" min="0" value="0" class="w-full rounded-lg border-gray-300 text-sm p-2 border mt-1" />
                </div>
            </div>
        </div>

        <!-- SECTION 4: Dynamic Tax & GST Configuration -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Taxation Type</label>
                <select name="tax_type" id="tax_type" class="w-full rounded-xl border-gray-300 text-sm p-3 border" onchange="recalculateTotals()">
                    <option value="intra_state" selected>Intra-State GST (CGST 2.5% + SGST 2.5%)</option>
                    <option value="inter_state">Inter-State GST (IGST 5%)</option>
                    <option value="international_lut">International (Export Under LUT - 0%)</option>
                    <option value="manual">Manual GST Override</option>
                </select>

                <!-- Manual Tax Inputs (Hidden by default) -->
                <div id="manual_tax_inputs" class="hidden grid-cols-3 gap-4 mt-4 bg-gray-50 p-4 rounded-lg border">
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom CGST (%)</label>
                        <input type="number" step="0.01" id="manual_cgst" name="cgst_rate" value="0" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom SGST (%)</label>
                        <input type="number" step="0.01" id="manual_sgst" name="sgst_rate" value="0" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Custom IGST (%)</label>
                        <input type="number" step="0.01" id="manual_igst" name="igst_rate" value="0" class="w-full rounded-md border-gray-300 text-sm p-2" oninput="recalculateTotals()">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Place of Supply (POS)</label>
                <input type="text" name="place_of_supply" value="West Bengal (19)" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <!-- Add this right before the Due Date div -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Date</label>
                <input type="date" name="invoice_date" value="<?php echo isset($inv['invoice_date']) ? $inv['invoice_date'] : date('Y-m-d'); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Due Date</label>
                <input type="date" name="due_date" value="<?php echo date('Y-m-d'); ?>" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
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
                        <tr class="item-row">
                            <td class="p-2 border"><input type="text" name="items[0][description]" value="Sikkim 4D/3N - Budget Tier" class="w-full border-0 focus:ring-0 text-sm" required /></td>
                            <td class="p-2 border"><input type="text" name="items[0][hsn_sac]" value="9985" class="w-full border-0 focus:ring-0 text-sm text-center" /></td>
                            <td class="p-2 border"><input type="number" name="items[0][quantity]" value="1" min="1" class="qty-input w-full border-0 focus:ring-0 text-sm text-center" oninput="recalculateTotals()" /></td>
                            <td class="p-2 border"><input type="number" step="0.01" name="items[0][unit_price]" value="0.95" class="price-input w-full border-0 focus:ring-0 text-sm" oninput="recalculateTotals()" /></td>
                            <td class="p-2 border"><input type="number" step="0.01" name="items[0][total]" value="0.95" readonly class="row-total w-full border-0 bg-gray-50 text-sm font-semibold" /></td>
                            <td class="p-2 border text-center"><button type="button" class="text-red-500 hover:text-red-700" onclick="removeRow(this)">✕</button></td>
                        </tr>
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
                <input type="text" name="transaction_id" placeholder="e.g. pay_SgGgBjoJxB1GMb" class="w-full rounded-xl border-gray-300 text-sm p-3 border" />
            </div>

            <div class="w-full md:w-1/3 bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total Taxable Value:</span>
                    <span class="font-bold" id="lblTaxable">₹0.95</span>
                    <input type="hidden" name="subtotal" id="inpTaxable" value="0.95" />
                </div>
                <div class="flex justify-between text-sm" id="cgstRow">
                    <span class="text-gray-600">CGST Amount:</span>
                    <span class="font-bold" id="lblCGST">₹0.02</span>
                    <input type="hidden" name="cgst_amount" id="inpCGST" value="0.02" />
                </div>
                <div class="flex justify-between text-sm" id="sgstRow">
                    <span class="text-gray-600">SGST Amount:</span>
                    <span class="font-bold" id="lblSGST">₹0.02</span>
                    <input type="hidden" name="sgst_amount" id="inpSGST" value="0.02" />
                </div>
                <div class="flex justify-between text-sm hidden" id="igstRow">
                    <span class="text-gray-600">IGST Amount:</span>
                    <span class="font-bold" id="lblIGST">₹0.00</span>
                    <input type="hidden" name="igst_amount" id="inpIGST" value="0.00" />
                </div>
                <div class="flex justify-between text-base font-extrabold border-t pt-2 text-indigo-900">
                    <span>Grand Total:</span>
                    <span id="lblGrandTotal">₹1.00</span>
                    <input type="hidden" name="total" id="inpGrandTotal" value="1.00" />
                </div>
            </div>
        </div>

        <!-- Submit Action -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition-all">
                Save & Generate Tax Invoice
            </button>
        </div>
    </form>
</div>

<script>
    function toggleReceiverType(val) {
        const hotelBox = document.getElementById('hotel_select_box');
        if (val === 'hotel') {
            hotelBox.classList.remove('hidden');
        } else {
            hotelBox.classList.add('hidden');
        }
    }

    // NEW: Toggle Manual Tax Inputs
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

    let globalRowCounter = 1; // Start at 1 since row 0 exists

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

        // FIXED: Logic now accurately handles Manual and LUT
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
            // Read from manual inputs
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
            // LUT / International (0%)
            document.getElementById('cgstRow').classList.add('hidden');
            document.getElementById('sgstRow').classList.add('hidden');
            document.getElementById('igstRow').classList.add('hidden');
        }

        const grandTotal = subtotal + cgst + sgst + igst;

        document.getElementById('lblTaxable').innerText = '₹' + subtotal.toFixed(2);
        document.getElementById('inpTaxable').value = subtotal.toFixed(2);

        document.getElementById('lblCGST').innerText = '₹' + cgst.toFixed(2);
        document.getElementById('inpCGST').value = cgst.toFixed(2);

        document.getElementById('lblSGST').innerText = '₹' + sgst.toFixed(2);
        document.getElementById('inpSGST').value = sgst.toFixed(2);

        document.getElementById('lblIGST').innerText = '₹' + igst.toFixed(2);
        document.getElementById('inpIGST').value = igst.toFixed(2);

        document.getElementById('lblGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
        document.getElementById('inpGrandTotal').value = grandTotal.toFixed(2);
    }

    document.querySelector('select[name="hotel_id"]').addEventListener('change', async function() {
        if (!this.value) return;

        // Fetch hotel details (you will need a quick admin/hotel/get_hotel_json.php file to return the hotel row)
        try {
            let res = await fetch(`../hotel/get_data.php?id=${this.value}`); // Assuming get_data.php returns JSON
            let data = await res.json();

            if (data) {
                document.querySelector('input[name="customer_name"]').value = data.hotel_name || '';
                document.querySelector('input[name="customer_email"]').value = data.email || '';
                document.querySelector('input[name="customer_phone"]').value = data.phone || '';
                document.querySelector('input[name="billing_address"]').value = `${data.location || ''}, ${data.city || ''}`;
            }
        } catch (err) {
            console.error("Failed to auto-fill hotel details.");
        }
    });
</script>