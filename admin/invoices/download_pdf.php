<?php
// admin/invoices/download_pdf.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$invoice_id = intval($_GET['id'] ?? 0);

// 1. Fetch Invoice AND Primary Company Details
$stmt = $pdo->prepare("
    SELECT i.*, 
           c.company_name, c.tagline, c.address_line_1 as comp_addr1, 
           c.city as comp_city, c.state as comp_state, c.pincode as comp_pin, 
           c.phone as comp_phone, c.email as comp_email, c.gstin as comp_gstin, 
           c.pan as comp_pan, c.bank_name, c.account_name, c.account_number, 
           c.ifsc_code, c.upi_id
    FROM invoices i
    LEFT JOIN company_addresses c ON i.company_address_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    die("Invoice Not Found.");
}

// 2. Fetch Invoice Line Items
$itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$itemsStmt->execute([$invoice_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Buffer and Generate PDF using the Central Template
ob_start();

// Include the centralized template to guarantee the 100% exact design match
include 'pdf_template.php';

$html = ob_get_clean();

try {
    // Instantiate mPDF exactly ONCE
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 12,
        'margin_bottom' => 15,
        'margin_left' => 12,
        'margin_right' => 12
    ]);

    $mpdf->SetTitle('Invoice_' . $inv['invoice_number']);

    // NATIVE mPDF WATERMARK SUPPORT (Fires only once)
    if (strtolower($inv['status']) === 'paid') {
        $mpdf->SetWatermarkText('PAID');
        $mpdf->watermark_font = 'Helvetica';
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->showWatermarkText = true;
    }

    // Write HTML exactly ONCE
    $mpdf->WriteHTML($html);
    $mpdf->Output('Invoice_' . $inv['invoice_number'] . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
} catch (\Mpdf\MpdfException $e) {
    echo "PDF Generation Error: " . $e->getMessage();
}
