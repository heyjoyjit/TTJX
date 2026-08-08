<?php
$db = Database::getInstance()->getConnection();
$hotels = $db->query("SELECT id, hotel_name, email, phone FROM hotels WHERE status = 1 ORDER BY hotel_name")->fetchAll();
$themes = ['default', 'modern', 'classic'];
?>
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Create Payment Link</h1>
    <form id="paymentLinkForm" method="post" action="<?= BASE_URL ?>admin/payment_links/save.php">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Link Type *</label>
                <select name="link_type" id="linkType" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="customer">Customer</option>
                    <option value="hotel">Hotel</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Template Theme</label>
                <select name="template_theme" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <?php foreach ($themes as $theme): ?>
                        <option value="<?= $theme ?>"><?= ucfirst($theme) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Amount (₹) *</label>
                <input type="number" name="amount" step="0.01" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Expiry Date (optional)</label>
                <input type="datetime-local" name="expires_at" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
        </div>

        <div id="hotelSelect" style="display:none;" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Select Hotel</label>
            <select name="hotel_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Select Hotel</option>
                <?php foreach ($hotels as $h): ?>
                    <option value="<?= $h['id'] ?>" data-email="<?= $h['email'] ?>" data-phone="<?= $h['phone'] ?>"><?= escape($h['hotel_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mt-4">
            <h3 class="font-semibold">Client Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Client Name *</label>
                    <input type="text" name="client_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" id="clientName">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Client Email *</label>
                    <input type="email" name="client_email" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" id="clientEmail">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Client Phone</label>
                    <input type="text" name="client_phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" id="clientPhone">
                </div>
            </div>
            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700">Description (optional)</label>
                <textarea name="description" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Payment Link</button>
            <a href="<?= BASE_URL ?>admin/payment_links/index.php" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
        </div>
    </form>
</div>

<script>
    $('#linkType').change(function() {
        if ($(this).val() === 'hotel') {
            $('#hotelSelect').show();
        } else {
            $('#hotelSelect').hide();
            $('#clientName').val('');
            $('#clientEmail').val('');
            $('#clientPhone').val('');
        }
    });

    $('select[name="hotel_id"]').change(function() {
        var selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#clientName').val(selected.text());
            $('#clientEmail').val(selected.data('email'));
            $('#clientPhone').val(selected.data('phone'));
        }
    });

    $('#paymentLinkForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        showLoader();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                hideLoader();
                if (res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => {
                        window.location.href = '<?= BASE_URL ?>admin/payment_links/index.php';
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                hideLoader();
                Swal.fire('Error', 'Something went wrong', 'error');
            }
        });
    });
</script>