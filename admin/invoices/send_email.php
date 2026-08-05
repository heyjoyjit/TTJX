<?php
// admin/invoices/send_email.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pdo = Database::getInstance()->getConnection();

$invoice_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    die("Invoice not found.");
}

try {
    $mail = new PHPMailer(true);

    // Server settings configured from includes/mailer.php
    configureMailer($mail);

    // Fetch hotel email safely if it's a hotel invoice
    if ($inv['invoice_type'] === 'hotel' && !empty($inv['hotel_id'])) {
        $hotelStmt = $pdo->prepare("SELECT hotel_name, email FROM hotels WHERE id = ?");
        $hotelStmt->execute([$inv['hotel_id']]);
        $hotelData = $hotelStmt->fetch(PDO::FETCH_ASSOC);

        $targetEmail = $hotelData['email'];
        $targetName = $hotelData['hotel_name'];
    } else {
        $targetEmail = $inv['customer_email'];
        $targetName = $inv['customer_name'];
    }

    // Recipients
    $mail->addAddress($targetEmail, $targetName);
    $mail->addBCC('booking@traveltara.com', 'Traveltara Admin');

    // Content
    $mail->isHTML(true);

    // Determine target audience
    if ($inv['invoice_type'] === 'hotel') {
        $mail->Subject = "Settlement Invoice #" . $inv['invoice_number'] . " | traveltara";
    } else {
        $mail->Subject = "Tax Invoice #" . $inv['invoice_number'] . " - " . $inv['destination_name'] . " | traveltara";
    }

    // 1. Generate the PDF in memory using mPDF
    ob_start();

    include 'pdf_template.php'; // (Move the HTML part of download_pdf.php into a separate file for reuse)

    $html = ob_get_clean();

    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
    $mpdf->WriteHTML($html);
    $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the document as a string

    // 2. Attach the generated PDF string to the email
    $pdfFilename = 'Invoice_' . $inv['invoice_number'] . '.pdf';
    $mail->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');

    $mail->send();
    header("Location: view.php?id=" . $invoice_id . "&email_sent=1");
    exit;
} catch (Exception $e) {
    die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
}
