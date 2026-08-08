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
    die("Invoice not found.");
}

// 2. Fetch Invoice Line Items (Crucial for the PDF template loop)
$itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$itemsStmt->execute([$invoice_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

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
    // Define your company logo URL here (Must be a live, absolute URL)
    $logo_url = "https://traveltara.com/assets/images/logo.png"; // Replace with your actual logo URL
    $current_year = date('Y');

    // Determine target audience and set professional templates
    if ($inv['invoice_type'] === 'hotel') {
        $mail->Subject = "Settlement Invoice #" . $inv['invoice_number'] . " | Traveltara";
        $mail->Body = "
        <div style='background-color: #f3f4f6; padding: 40px 20px; font-family: Helvetica, Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;'>
                
                <!-- Header -->
                <div style='text-align: center; padding: 30px 20px; border-bottom: 1px solid #f3f4f6;'>
                    <img src='" . $logo_url . "' alt='Traveltara' style='max-height: 50px; width: auto;'>
                </div>
                
                <!-- Body -->
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #1e1b4b; margin-top: 0; font-size: 22px;'>Vendor Settlement Document</h2>
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>Dear " . htmlspecialchars($targetName) . " Team,</p>
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>Please find attached the official settlement and commission invoice for the upcoming booking (Ref: <strong>" . htmlspecialchars($inv['booking_ref_no']) . "</strong>).</p>
                    
                    <div style='background-color: #f8fafc; border-left: 4px solid #4f46e5; padding: 15px 20px; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                        <p style='margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Invoice Summary</p>
                        <p style='margin: 0 0 5px 0; color: #334155; font-size: 14px;'><strong>Invoice #:</strong> " . $inv['invoice_number'] . "</p>
                        <p style='margin: 0; color: #0f172a; font-size: 18px;'><strong>Net Payable:</strong> ₹" . number_format($inv['total'], 2) . "</p>
                    </div>
                    
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>If you have any discrepancies regarding this settlement, please contact our accounts team.</p>
                    <br>
                    <p style='color: #4b5563; font-size: 15px; margin: 0;'>Best Regards,</p>
                    <p style='color: #1e1b4b; font-size: 15px; font-weight: bold; margin: 5px 0 0 0;'>Traveltara Accounts</p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;'>
                    <p style='color: #94a3b8; font-size: 12px; margin: 0;'>&copy; " . $current_year . " Traveltara. All rights reserved.</p>
                </div>
            </div>
        </div>";
    } else {
        $mail->Subject = "Tax Invoice #" . $inv['invoice_number'] . " - " . $inv['destination_name'] . " | Traveltara";
        $mail->Body = "
        <div style='background-color: #f3f4f6; padding: 40px 20px; font-family: Helvetica, Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;'>
                
                <!-- Header -->
                <div style='text-align: center; padding: 30px 20px; border-bottom: 1px solid #f3f4f6;'>
                    <img src='" . $logo_url . "' alt='Traveltara' style='max-height: 50px; width: auto;'>
                </div>
                
                <!-- Body -->
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #1e1b4b; margin-top: 0; font-size: 22px;'>Your Booking is Confirmed!</h2>
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>Dear " . htmlspecialchars($inv['customer_name']) . ",</p>
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>Thank you for choosing Traveltara for your journey to <strong>" . htmlspecialchars($inv['destination_name']) . "</strong>. We are thrilled to host you.</p>
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>Please find your official Tax Invoice securely attached to this email for your records.</p>
                    
                    <div style='background-color: #f8fafc; border-left: 4px solid #10b981; padding: 15px 20px; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                        <p style='margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-weight: bold; text-transform: uppercase;'>Invoice Summary</p>
                        <p style='margin: 0 0 5px 0; color: #334155; font-size: 14px;'><strong>Invoice #:</strong> " . $inv['invoice_number'] . "</p>
                        <p style='margin: 0; color: #0f172a; font-size: 18px;'><strong>Total Amount:</strong> ₹" . number_format($inv['total'], 2) . "</p>
                    </div>
                    
                    <p style='color: #4b5563; font-size: 15px; line-height: 1.6;'>If you have any questions regarding this invoice or your upcoming itinerary, simply reply to this email.</p>
                    <br>
                    <p style='color: #4b5563; font-size: 15px; margin: 0;'>Warm Regards,</p>
                    <p style='color: #1e1b4b; font-size: 15px; font-weight: bold; margin: 5px 0 0 0;'>The Traveltara Team</p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;'>
                    <p style='color: #94a3b8; font-size: 12px; margin: 0;'>&copy; " . $current_year . " Traveltara. All rights reserved.</p>
                </div>
            </div>
        </div>";
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
