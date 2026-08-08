<?php
// hotel/invoices/view_content.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

$hotel_id = $_SESSION['hotel_id'] ?? 0;
$invoice_id = intval($_GET['id'] ?? 0);

// Fetch only if it belongs to this hotel
$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.tagline, c.address_line_1 as comp_addr1, c.city as comp_city, 
           c.state as comp_state, c.pincode as comp_pin, c.phone as comp_phone, c.email as comp_email,
           c.gstin as comp_gstin, c.pan as comp_pan, c.bank_name, c.account_name, c.account_number, 
           c.ifsc_code, c.upi_id
    FROM invoices i
    LEFT JOIN company_addresses c ON i.company_address_id = c.id
    WHERE i.id = ? AND i.hotel_id = ? AND i.invoice_type = 'hotel'
");
$stmt->execute([$invoice_id, $hotel_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    die("<div class='p-8 text-red-500 font-bold bg-red-50 rounded-lg'>Access Denied or Invoice Not Found.</div>");
}

$itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemsStmt->execute([$invoice_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// We can securely reuse the admin's visual template!
include $_SERVER['DOCUMENT_ROOT'] . '/admin/invoices/pdf_template.php';
?>
<div class="max-w-4xl mx-auto flex justify-end gap-4 mb-12 mt-4">
    <a href="download_pdf.php?id=<?php echo $inv['id']; ?>" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700">
        ⬇️ Download PDF
    </a>
</div>