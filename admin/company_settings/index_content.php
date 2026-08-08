<?php
// admin/company_settings/index_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// Fetch all company addresses
$stmt = $pdo->query("SELECT * FROM company_addresses ORDER BY is_primary DESC, created_at DESC");
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Company Settings</h1>
            <p class="mt-2 text-sm text-gray-500">Manage your primary billing address, secondary branches, and bank/UPI details.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Address List -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($addresses as $addr): ?>
                <div class="bg-white shadow-md rounded-2xl p-6 border-l-4 <?php echo $addr['is_primary'] ? 'border-indigo-600' : 'border-gray-200'; ?>">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <?php echo htmlspecialchars($addr['company_name']); ?>
                                <?php if ($addr['is_primary']): ?>
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-bold rounded-md">PRIMARY</span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($addr['address_line_1']); ?>, <?php echo htmlspecialchars($addr['city']); ?> - <?php echo htmlspecialchars($addr['pincode']); ?></p>
                            <div class="mt-3 grid grid-cols-2 gap-4 text-xs text-gray-600">
                                <div><span class="font-bold">GSTIN:</span> <?php echo $addr['gstin'] ?: 'N/A'; ?></div>
                                <div><span class="font-bold">PAN:</span> <?php echo $addr['pan'] ?: 'N/A'; ?></div>
                                <div><span class="font-bold">Bank:</span> <?php echo $addr['bank_name'] ?: 'N/A'; ?> (<?php echo $addr['account_number']; ?>)</div>
                                <div><span class="font-bold">UPI:</span> <?php echo $addr['upi_id'] ?: 'N/A'; ?></div>
                            </div>
                        </div>
                        <?php if (!$addr['is_primary']): ?>
                            <form action="set_primary.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $addr['id']; ?>">
                                <button type="submit" class="text-xs bg-gray-100 hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 font-bold py-1.5 px-3 rounded-lg transition-colors">Set Primary</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right Column: Add New Address Form -->
        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 h-fit">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Add New Branch / Address</h3>
            <form action="save_address.php" method="POST" class="space-y-4">

                <!-- Company & Tagline -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Company Name</label>
                        <input type="text" name="company_name" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" value="Traveltara Pvt. Ltd.">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tagline</label>
                        <input type="text" name="tagline" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" value="BESPOKE LUXURY JOURNEYS">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                    </div>
                </div>

                <!-- Address Line -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Address Line 1</label>
                    <input type="text" name="address_line_1" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                </div>

                <!-- City, State, Pincode -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">State</label>
                        <input type="text" name="state" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pincode</label>
                        <input type="text" name="pincode" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border">
                    </div>
                </div>

                <!-- GST, PAN, State Code -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">GSTIN</label>
                        <input type="text" name="gstin" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">PAN</label>
                        <input type="text" name="pan" class="w-full rounded-lg border-gray-300 text-sm p-2.5 border uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">State Code</label>
                        <input type="text" name="state_code" required class="w-full rounded-lg border-gray-300 text-sm p-2.5 border" value="19">
                    </div>
                </div>

                <!-- Banking Details -->
                <div class="bg-gray-100 p-3 rounded-lg border border-gray-200 space-y-3 mt-2">
                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Bank & UPI Details</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" class="w-full rounded-lg border-gray-300 text-sm p-2 border">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="account_name" class="w-full rounded-lg border-gray-300 text-sm p-2 border">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" class="w-full rounded-lg border-gray-300 text-sm p-2 border">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="w-full rounded-lg border-gray-300 text-sm p-2 border uppercase">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">UPI ID</label>
                            <input type="text" name="upi_id" class="w-full rounded-lg border-gray-300 text-sm p-2 border">
                        </div>
                    </div>
                </div>

                <div class="flex items-center mt-3">
                    <input type="checkbox" name="is_primary" id="is_primary" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_primary" class="ml-2 block text-sm text-gray-900 font-semibold">Set as Primary Billing Address</label>
                </div>

                <button type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl transition-colors">Save Address</button>
            </form>
        </div>

    </div>
</div>