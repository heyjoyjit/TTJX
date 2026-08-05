<?php
// includes/mailer.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendCustomEmail($toEmail, $subject, $htmlBody, $toName = null, $attachments = [])
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smarak.haldar.official@gmail.com';
        $mail->Password   = 'cthv vfwy jblr fsba';

        // FIX: Switch from TLS (587) to SSL (465) for hosting compatibility
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Additional SSL options to prevent certificate handshake blocks on shared servers
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('smarak.haldar.official@gmail.com', 'TravelTara');
        if ($toName) {
            $mail->addAddress($toEmail, $toName);
        } else {
            $mail->addAddress($toEmail);
        }

        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $filePath) {
                if (file_exists($filePath)) $mail->addAttachment($filePath);
            }
        }

        // HTML Body Content
        $currentYear = date('Y');
        $headerImage = "https://images.unsplash.com/photo-1542314831-c6a4d142104d?auto=format&fit=crop&w=600&h=200&q=80";

        $themedBody = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='background-color: #f8fafc; font-family: sans-serif; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; padding: 40px 20px;'>
                <tr>
                    <td align='center'>
                        <table width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);'>
                            <tr>
                                <td style='background-color: #0f172a; text-align: center; height: 200px;'>
                                    <img src='{$headerImage}' alt='TravelTara' style='width: 100%; height: 200px; object-fit: cover; display: block;' />
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 30px 40px 10px; text-align: center;'>
                                    <h2 style='margin: 0; color: #2563eb; font-size: 28px;'>✈️ TravelTara</h2>
                                    <p style='color: #64748b; font-size: 12px; margin-top: 6px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;'>Partner Onboarding Network</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 20px 40px 40px;'>
                                    {$htmlBody}
                                </td>
                            </tr>
                            <tr>
                                <td style='background-color: #f1f5f9; padding: 30px 40px; text-align: center; border-top: 1px solid #e2e8f0;'>
                                    <p style='margin: 0; color: #64748b; font-size: 13px;'>&copy; {$currentYear} TravelTara. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $themedBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Re-throw so callers can see the actual error instead of hiding it
        throw new Exception("Mailer Error: " . $mail->ErrorInfo);
    }
}

function configureMailer(PHPMailer $mail) {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com'; // Replace with your actual SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'smarak.haldar.official@gmail.com';
    $mail->Password   = 'cthv vfwy jblr fsba'; // Use environment variables in production
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Or ENCRYPTION_SMTPS for port 465
    $mail->Port       = 587; 
    
    $mail->setFrom('smarak.haldar.official@gmail.com', 'Traveltara');
    $mail->addReplyTo('smarak.haldar.official@gmail.com', 'Traveltara Support');
}
