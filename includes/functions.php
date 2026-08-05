<?php
// includes/functions.php


// Include PHPMailer classes (Adjust the path if you are not using Composer)
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function redirect($url)
{
    header("Location: $url");
    exit;
}

function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Upload helper
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webp'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error code: ' . $file['error']);
    }

    // --- FIX: Automatically create target directory if it does not exist ---
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new Exception('Failed to create target upload directory: ' . $targetDir);
        }
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        throw new Exception('Invalid file type uploaded: ' . $mime);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid() . '.' . $ext;
    $targetPath = rtrim($targetDir, '/\\') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file to target directory.');
    }

    return $newName;
}

function generateHotelId($pdo)
{
    $year = date('y');
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(hotel_registered_id, 4, 3) AS UNSIGNED)) AS max_id FROM hotels");
    $row = $stmt->fetch();
    $next = str_pad(($row['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    return "TT/{$next}/HLT/{$year}";
}

function razorpayCreateOrder($amount, $currency = 'INR', $receipt = '')
{
    $apiKey = RAZORPAY_KEY_ID;
    $apiSecret = RAZORPAY_KEY_SECRET;
    $data = [
        'amount' => $amount * 100, // amount in paise
        'currency' => $currency,
        'receipt' => $receipt,
        'payment_capture' => 1
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':' . $apiSecret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    throw new Exception('Razorpay error: ' . $response);
}

function razorpayCreatePaymentLink($amount, $currency = 'INR', $description = '', $customer = [], $expiry = 86400)
{
    $apiKey = RAZORPAY_KEY_ID;
    $apiSecret = RAZORPAY_KEY_SECRET;
    $data = [
        'amount' => $amount * 100,
        'currency' => $currency,
        'description' => $description,
        'customer' => $customer,
        'expire_by' => time() + $expiry,
        'callback_url' => BASE_URL . 'payment_callback.php',
        'callback_method' => 'get'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payment_links');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':' . $apiSecret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        return json_decode($response, true);
    }
    throw new Exception('Razorpay error: ' . $response);
}

function sendMailerEmail($to, $subject, $message)
{
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.yourdomain.com'; // Replace with your SMTP host (e.g., smtp.gmail.com)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smarak.haldar.official@gmail.com'; // Replace with your SMTP username
        $mail->Password   = 'euxk xvwe idzf kkej'; // Replace with your SMTP password/app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS for port 465
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('no-reply@yourtravelsystem.com', 'Travel System Admin');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br(escape($message)); // Converts newlines to <br> for HTML
        $mail->AltBody = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging purposes
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
// includes/functions.php (Taxation, PAX & QR Helpers)

/**
 * Get Primary Company Address
 */
function getPrimaryCompanyAddress($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM company_addresses WHERE is_primary = 1 LIMIT 1");
    $stmt->execute();
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$address) {
        // Fallback to first available record
        $stmt = $pdo->query("SELECT * FROM company_addresses ORDER BY id ASC LIMIT 1");
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return $address;
}

/**
 * Dynamic Tax & GST Calculator Engine
 */
function calculateGST($taxableAmount, $taxType, $customCgst = 0, $customSgst = 0, $customIgst = 0)
{
    $result = ['taxable' => round((float)$taxableAmount, 2), 'cgst_rate' => 0, 'sgst_rate' => 0, 'igst_rate' => 0, 'cgst_amount' => 0, 'sgst_amount' => 0, 'igst_amount' => 0, 'total_tax' => 0, 'grand_total' => 0];

    if ($taxType === 'intra_state') {
        $result['cgst_rate'] = 2.5;
        $result['sgst_rate'] = 2.5;
        $result['cgst_amount'] = round(($result['taxable'] * 2.5) / 100, 2);
        $result['sgst_amount'] = round(($result['taxable'] * 2.5) / 100, 2);
        $result['total_tax'] = $result['cgst_amount'] + $result['sgst_amount'];
    } elseif ($taxType === 'inter_state') {
        $result['igst_rate'] = 5.0;
        $result['igst_amount'] = round(($result['taxable'] * 5.0) / 100, 2);
        $result['total_tax'] = $result['igst_amount'];
    } elseif ($taxType === 'manual') {
        $result['cgst_rate'] = $customCgst;
        $result['sgst_rate'] = $customSgst;
        $result['igst_rate'] = $customIgst;
        $result['cgst_amount'] = round(($result['taxable'] * $customCgst) / 100, 2);
        $result['sgst_amount'] = round(($result['taxable'] * $customSgst) / 100, 2);
        $result['igst_amount'] = round(($result['taxable'] * $customIgst) / 100, 2);
        $result['total_tax'] = $result['cgst_amount'] + $result['sgst_amount'] + $result['igst_amount'];
    }

    $result['grand_total'] = round($result['taxable'] + $result['total_tax'], 2);
    return $result;
}

/**
 * Convert Amount to Words (INR)
 */
function convertAmountToWords(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : '';
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10 * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? 'Rupees ' . $Rupees : '') . ($paise ? $paise : '') . ' Only';
}

/**
 * Generate Dynamic UPI Payment URL & QR Code Image URL
 */
function generateUPIQrCodeUrl($upiId, $companyName, $amount, $bookingRef)
{
    $upiString = "upi://pay?pa=" . urlencode($upiId) .
        "&pn=" . urlencode($companyName) .
        "&tr=" . urlencode($bookingRef) .
        "&am=" . number_format($amount, 2, '.', '') .
        "&cu=INR";

    // Clean public QR Generator API URL
    return "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($upiString);
}
