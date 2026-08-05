<?php
// hotel/invoices/download_pdf.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

// Assuming hotel auth logic sets $_SESSION['hotel_id']
$hotel_id = $_SESSION['hotel_id'] ?? 0;
if (!$hotel_id) die("Unauthorized.");

$invoice_id = intval($_GET['id'] ?? 0);

// SECURITY: Must include WHERE hotel_id = ? to prevent downloading other hotels' invoices!
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

if (!$inv) die("Invoice Not Found or Access Denied.");

$itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemsStmt->execute([$invoice_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/invoices/pdf_template.php'; // Safely reuse the template
$html = ob_get_clean();

try {
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 15, 'margin_bottom' => 15]);
    $mpdf->SetTitle('Settlement_Invoice_' . $inv['invoice_number']);
    $mpdf->WriteHTML($html);
    $mpdf->Output('Settlement_Invoice_' . $inv['invoice_number'] . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
} catch (\Mpdf\MpdfException $e) {
    echo $e->getMessage();
}
