<?php
// razorpay_webhook.php (Place in root directory)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

// 1. Define your Webhook Secret (You will set this exact string in the Razorpay Dashboard)
$webhookSecret = 'TRAVELTARA_SECURE_WEBHOOK_2026';

// 2. Read the raw POST payload from Razorpay
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// 3. Reject if empty
if (empty($payload) || empty($signature)) {
    http_response_code(400);
    die("Invalid request");
}

// 4. Verify the cryptographic signature
$expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    die("Signature mismatch");
}

// 5. Decode the payload
$data = json_decode($payload, true);

// 6. Handle the specific event
if (isset($data['event']) && $data['event'] === 'payment_link.paid') {

    // Extract vital information from the payload
    $paymentLinkId = $data['payload']['payment_link']['entity']['id'];
    $paymentId = $data['payload']['payment']['entity']['id'];
    $amountPaid = $data['payload']['payment']['entity']['amount'] / 100; // Razorpay sends amount in paise

    try {
        $pdo->beginTransaction();

        // Update the payment_links table
        $stmtLink = $pdo->prepare("UPDATE payment_links SET status = 'paid' WHERE razorpay_link_id = ?");
        $stmtLink->execute([$paymentLinkId]);

        // Update the invoices table using the stored razorpay_order_id (which holds the link ID)
        $stmtInvoice = $pdo->prepare("
            UPDATE invoices 
            SET status = 'paid', razorpay_payment_id = ?, updated_at = NOW() 
            WHERE razorpay_link_id = ?
        ");
        $stmtInvoice->execute([$paymentId, $paymentLinkId]);

        $pdo->commit();

        // Return 200 OK to tell Razorpay we successfully processed the event
        http_response_code(200);
        echo "Webhook processed successfully.";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        error_log("Razorpay Webhook DB Error: " . $e->getMessage());
        die("Database error");
    }
}

// Ignore other events but acknowledge receipt
http_response_code(200);
echo "Event ignored";
