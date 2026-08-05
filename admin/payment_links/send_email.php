<?php
// admin/payment_links/send_email.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php'; // FIXED: Missing auth include

// Load PHPMailer (Adjust this path if you installed PHPMailer manually instead of via Composer)
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

requireAdmin();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    // 1. CSRF & Validation
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token.');
    }

    $id = intval($_POST['id']);
    if (!$id) throw new Exception('Invalid payment link ID.');

    $db = Database::getInstance()->getConnection();

    // Fetch Link Data[cite: 9]
    $stmt = $db->prepare("SELECT customer_name, customer_email, razorpay_link_url, amount, description, theme FROM payment_links WHERE id = ?");
    $stmt->execute([$id]);
    $link = $stmt->fetch();

    if (!$link) {
        throw new Exception('Payment link not found.');
    }

    // 2. Email Variables Setup
    $customerName = htmlspecialchars($link['customer_name']);
    $amountFormatted = number_format($link['amount'], 2);
    $payUrl = $link['razorpay_link_url'];
    $description = !empty($link['description']) ? htmlspecialchars($link['description']) : 'Payment for Travel Services';

    // Fallback constants if not defined in your config.php
    $companyName = defined('SITE_NAME') ? SITE_NAME : 'Travel System Inc.';
    $logoUrl = defined('SITE_LOGO_URL') ? SITE_LOGO_URL : BASE_URL . 'assets/images/logo.png';
    $theme = strtolower($link['theme'] ?? 'default');

    // 3. Pro HTML Templates
    $htmlBody = "";

    if ($theme === 'modern') {
        // ==========================================
        // TEMPLATE 1: MODERN & BOLD (Gradient & Cards)
        // ==========================================
        $htmlBody = "
        <div style=\"font-family: 'Inter', 'Helvetica Neue', Helvetica, sans-serif; background-color: #f3f4f6; padding: 40px 20px; margin: 0; line-height: 1.6;\">
            <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);\">
                
                <!-- Header with Gradient -->
                <div style=\"background: linear-gradient(135deg, #4f46e5, #3b82f6); padding: 40px 20px; text-align: center;\">
                    <img src=\"{$logoUrl}\" alt=\"{$companyName}\" style=\"height: 50px; max-width: 200px; filter: brightness(0) invert(1); object-fit: contain;\">
                    <h1 style=\"color: #ffffff; margin: 25px 0 0 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;\">Secure Payment Request</h1>
                </div>
                
                <!-- Body -->
                <div style=\"padding: 40px 30px; text-align: center;\">
                    <p style=\"color: #6b7280; font-size: 16px; margin-top: 0;\">Hello <strong>{$customerName}</strong>,</p>
                    <p style=\"color: #374151; font-size: 16px;\">Please complete your payment of</p>
                    
                    <div style=\"font-size: 42px; font-weight: 900; color: #10b981; margin: 20px 0; letter-spacing: -1px;\">
                        ₹{$amountFormatted}
                    </div>
                    
                    <div style=\"background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 8px; padding: 15px; margin-bottom: 35px;\">
                        <p style=\"color: #6b7280; font-size: 14px; margin: 0;\">{$description}</p>
                    </div>
                    
                    <a href=\"{$payUrl}\" style=\"display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; font-weight: bold; padding: 16px 36px; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); text-transform: uppercase; letter-spacing: 0.5px;\">
                        Proceed to Payment
                    </a>
                    
                    <p style=\"color: #9ca3af; font-size: 13px; margin-top: 30px;\">If the button doesn't work, copy and paste this URL into your browser:<br><a href=\"{$payUrl}\" style=\"color: #3b82f6; word-break: break-all;\">{$payUrl}</a></p>
                </div>
                
                <!-- Footer -->
                <div style=\"background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #f3f4f6;\">
                    <p style=\"color: #9ca3af; font-size: 12px; margin: 0;\">&copy; " . date('Y') . " {$companyName}. All rights reserved.</p>
                </div>
            </div>
        </div>";
    } else {
        // ==========================================
        // TEMPLATE 2: CLASSIC / DEFAULT (Clean Corporate)
        // ==========================================
        $htmlBody = "
        <div style=\"font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; background-color: #ffffff; padding: 30px 20px; margin: 0; line-height: 1.6;\">
            <div style=\"max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-top: 5px solid #2563eb; border-radius: 8px;\">
                
                <!-- Header -->
                <div style=\"padding: 25px 30px; border-bottom: 1px solid #e2e8f0; text-align: center; background: #f8fafc;\">
                    <img src=\"{$logoUrl}\" alt=\"{$companyName}\" style=\"height: 45px; max-width: 200px; object-fit: contain;\">
                </div>
                
                <!-- Body -->
                <div style=\"padding: 35px 30px; color: #334155;\">
                    <h2 style=\"margin-top: 0; color: #1e293b; font-size: 22px;\">Payment Invoice</h2>
                    <p>Dear <strong>{$customerName}</strong>,</p>
                    <p>This is a notification that a payment has been requested by {$companyName}.</p>
                    
                    <table style=\"width: 100%; border-collapse: collapse; margin: 25px 0;\">
                        <tr>
                            <td style=\"padding: 12px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold; width: 30%;\">Description</td>
                            <td style=\"padding: 12px; border-bottom: 1px solid #e2e8f0; color: #334155;\">{$description}</td>
                        </tr>
                        <tr>
                            <td style=\"padding: 12px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: bold;\">Amount Due</td>
                            <td style=\"padding: 12px; border-bottom: 1px solid #e2e8f0; color: #059669; font-weight: bold; font-size: 18px;\">₹{$amountFormatted}</td>
                        </tr>
                    </table>
                    
                    <div style=\"text-align: center; margin: 35px 0;\">
                        <a href=\"{$payUrl}\" style=\"background: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;\">Pay Securely Now</a>
                    </div>
                    
                    <p style=\"font-size: 14px; color: #64748b; margin-bottom: 0;\">Thank you for your business!</p>
                </div>
                
                <!-- Footer -->
                <div style=\"padding: 20px; text-align: center; background: #f8fafc; border-top: 1px solid #e2e8f0;\">
                    <p style=\"color: #94a3b8; font-size: 12px; margin: 0;\">{$companyName}<br>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </div>";
    }

    // Plain text fallback
    $altBody = "Dear {$customerName},\n\nPlease complete your payment of ₹{$amountFormatted} for: {$description}.\n\nPay here: {$payUrl}\n\nThank you,\n{$companyName}";

    // 4. Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings (Make sure these constants are defined in your config.php)
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.example.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['username'] : '';
        $mail->Password   = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['password'] : '';
        $mail->SMTPSecure = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['encryption'] : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['port'] : 587;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $fromEmail = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['from_email'] : 'no-reply@travelsystem.com';
        $fromName  = defined('EMAIL_CONFIG') ? unserialize(EMAIL_CONFIG)['from_name'] : $companyName;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($link['customer_email'], $customerName);
        $mail->addReplyTo($fromEmail, $fromName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Payment Request from {$companyName}";
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();

        // 5. Update DB Status
        $db->prepare("UPDATE payment_links SET sent_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);

        $response['success'] = true;
        $response['message'] = 'Pro-designed email sent successfully via PHPMailer.';
    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
