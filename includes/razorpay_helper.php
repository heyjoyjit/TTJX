<?php
// includes/razorpay_helper.php

function razorpay_create_order($amount, $currency = 'INR', $receipt = null)
{
    $key_id = RAZORPAY_KEY_ID;
    $key_secret = RAZORPAY_KEY_SECRET;
    $url = 'https://api.razorpay.com/v1/orders';
    $data = [
        'amount' => intval($amount * 100), // amount in paise
        'currency' => $currency,
        'receipt' => $receipt ?: 'order_' . uniqid(),
        'payment_capture' => 1 // auto capture
    ];
    $payload = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($result, true);
    } else {
        throw new Exception('Razorpay order creation failed: ' . $result);
    }
}

function razorpay_create_payment_link($amount, $currency = 'INR', $description = '', $customer = [], $expire_by = null)
{
    $key_id = RAZORPAY_KEY_ID;
    $key_secret = RAZORPAY_KEY_SECRET;
    $url = 'https://api.razorpay.com/v1/payment_links';
    $data = [
        'amount' => intval($amount * 100),
        'currency' => $currency,
        'description' => $description,
        'customer' => $customer,
        'notify' => ['sms' => true, 'email' => true],
        'reminder_enable' => true,
    ];
    if ($expire_by) {
        $data['expire_by'] = strtotime($expire_by) * 1000; // milliseconds
    }
    $payload = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($result, true);
    } else {
        throw new Exception('Razorpay payment link creation failed: ' . $result);
    }
}

function razorpay_verify_payment_signature($order_id, $payment_id, $signature)
{
    $key_secret = RAZORPAY_KEY_SECRET;
    $payload = $order_id . '|' . $payment_id;
    $expected_signature = hash_hmac('sha256', $payload, $key_secret);
    return hash_equals($expected_signature, $signature);
}

<?php
// includes/razorpay_helper.php
// Add this function if it does not already exist

function createRazorpayPaymentLink($amount, $customer_email, $customer_phone, $customer_name, $description, $reference_id) {
    // Replace with your actual Razorpay API Keys (Store these in config.php ideally)
    $key_id = "YOUR_RAZORPAY_KEY_ID";
    $key_secret = "YOUR_RAZORPAY_KEY_SECRET";

    $url = "https://api.razorpay.com/v1/payment_links";

    $data = [
        "amount" => round($amount * 100), // Razorpay accepts amounts in paise
        "currency" => "INR",
        "accept_partial" => false,
        "description" => $description,
        "reference_id" => $reference_id,
        "customer" => [
            "name" => $customer_name,
            "email" => $customer_email,
            "contact" => "+91" . preg_replace('/[^0-9]/', '', $customer_phone)
        ],
        "notify" => [
            "sms" => true,
            "email" => true
        ],
        "reminder_enable" => true
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("Razorpay Error: " . $err);
        return false;
    }
    
    return json_decode($response, true);
}