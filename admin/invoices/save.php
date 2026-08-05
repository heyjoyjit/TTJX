<?php
// admin/invoices/save.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$pdo = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $invoice_type = $_POST['invoice_type'] ?? 'customer';
        $hotel_id = !empty($_POST['hotel_id']) ? intval($_POST['hotel_id']) : null;
        $company_address_id = intval($_POST['company_address_id']);
        $booking_ref_no = $_POST['booking_ref_no'];

        $customer_name = $_POST['customer_name'];
        $customer_email = $_POST['customer_email'];
        $customer_phone = $_POST['customer_phone'];
        $client_gstin = $_POST['client_gstin'] ?? null;
        $billing_address = $_POST['billing_address'];
        $place_of_supply = $_POST['place_of_supply'];

        $destination_name = $_POST['destination_name'];
        $travel_tier = $_POST['travel_tier'];
        $travel_start_date = !empty($_POST['travel_start_date']) ? $_POST['travel_start_date'] : null;
        $travel_end_date = !empty($_POST['travel_end_date']) ? $_POST['travel_end_date'] : null;

        // Capture ALL PAX Details
        $pax_adults = intval($_POST['pax_adults'] ?? 1);
        $pax_children = intval($_POST['pax_children'] ?? 0);
        $pax_infants = intval($_POST['pax_infants'] ?? 0);
        $pax_cwb = intval($_POST['pax_cwb'] ?? 0);
        $pax_cnb = intval($_POST['pax_cnb'] ?? 0);
        $pax_extra_bed = intval($_POST['pax_extra_bed'] ?? 0);
        $pax_custom_details = $_POST['pax_custom_details'] ?? null;

        $due_date = $_POST['due_date'];
        $transaction_id = $_POST['transaction_id'] ?? null;

        // 1. SECURE CALCULATION: Calculate subtotal securely from POST items
        $calculated_subtotal = 0;
        $secure_items = [];
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $qty = intval($item['quantity']);
                $price = floatval($item['unit_price']);
                $line_total = $qty * $price;
                $calculated_subtotal += $line_total;

                $secure_items[] = [
                    'desc' => $item['description'],
                    'hsn' => $item['hsn_sac'],
                    'qty' => $qty,
                    'price' => $price,
                    'total' => $line_total
                ];
            }
        }

        // 2. SECURE CALCULATION: Run the subtotal through our backend GST Engine
        $tax_type = $_POST['tax_type'];
        $manual_cgst = floatval($_POST['cgst_rate'] ?? 0);
        $manual_sgst = floatval($_POST['sgst_rate'] ?? 0);
        $manual_igst = floatval($_POST['igst_rate'] ?? 0);

        $taxData = calculateGST($calculated_subtotal, $tax_type, $manual_cgst, $manual_sgst, $manual_igst);

        // Format unique sequential invoice number
        $invoice_number = "INV-" . date('Ym') . "-" . sprintf("%04d", rand(1, 9999));

        // Fetch company bank/UPI details
        $compAddr = getPrimaryCompanyAddress($pdo);
        $qrCodeUrl = generateUPIQrCodeUrl($compAddr['upi_id'], $compAddr['company_name'], $taxData['grand_total'], $booking_ref_no);

        // Insert Invoice Record
        $stmt = $pdo->prepare("
            INSERT INTO invoices (
                invoice_number, booking_ref_no, invoice_type, hotel_id, company_address_id,
                customer_name, customer_email, customer_phone, client_gstin, billing_address,
                place_of_supply, destination_name, pax_adults, pax_children, pax_infants,
                pax_cwb, pax_cnb, pax_extra_bed, pax_custom_details, travel_tier, 
                travel_start_date, travel_end_date, invoice_date, due_date,
                subtotal, tax_type, cgst_rate, sgst_rate, igst_rate, 
                cgst_amount, sgst_amount, igst_amount, total_taxable_value,
                total, status, transaction_id, upi_qr_data
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, 
                ?, ?, CURDATE(), ?,
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?,
                ?, 'sent', ?, ?
            )
        ");

        $stmt->execute([
            $invoice_number,
            $booking_ref_no,
            $invoice_type,
            $hotel_id,
            $company_address_id,
            $customer_name,
            $customer_email,
            $customer_phone,
            $client_gstin,
            $billing_address,
            $place_of_supply,
            $destination_name,
            $pax_adults,
            $pax_children,
            $pax_infants,
            $pax_cwb,
            $pax_cnb,
            $pax_extra_bed,
            $pax_custom_details,
            $travel_tier,
            $travel_start_date,
            $travel_end_date,
            $due_date,
            $taxData['taxable'],
            $tax_type,
            $taxData['cgst_rate'],
            $taxData['sgst_rate'],
            $taxData['igst_rate'],
            $taxData['cgst_amount'],
            $taxData['sgst_amount'],
            $taxData['igst_amount'],
            $taxData['taxable'],
            $taxData['grand_total'],
            $transaction_id,
            $qrCodeUrl
        ]);

        $invoice_id = $pdo->lastInsertId();

        // Insert Secure Line Items with Item-Level Tax Calculation
        $itemStmt = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, description, hsn_sac, quantity, unit_price, taxable_value, cgst_amount, sgst_amount, igst_amount, total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($secure_items as $item) {
            $i_cgst = round(($item['total'] * $taxData['cgst_rate']) / 100, 2);
            $i_sgst = round(($item['total'] * $taxData['sgst_rate']) / 100, 2);
            $i_igst = round(($item['total'] * $taxData['igst_rate']) / 100, 2);
            $i_total_with_tax = $item['total'] + $i_cgst + $i_sgst + $i_igst;

            $itemStmt->execute([
                $invoice_id,
                $item['desc'],
                $item['hsn'],
                $item['qty'],
                $item['price'],
                $item['total'],
                $i_cgst,
                $i_sgst,
                $i_igst,
                $i_total_with_tax
            ]);
        }

        // Generate Razorpay Payment Link automatically
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php')) {
            $description = "Payment for Invoice " . $invoice_number . " - " . $destination_name;

            $razorpayResponse = createRazorpayPaymentLink($taxData['grand_total'], $customer_email, $customer_phone, $customer_name, $description, $booking_ref_no);

            if ($razorpayResponse && isset($razorpayResponse['id'])) {
                $plStmt = $pdo->prepare("
                    INSERT INTO payment_links (hotel_id, customer_email, customer_name, amount, description, razorpay_link_id, razorpay_link_url, status, expires_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY))
                ");
                $plStmt->execute([
                    $hotel_id,
                    $customer_email,
                    $customer_name,
                    $taxData['grand_total'],
                    $description,
                    $razorpayResponse['id'],
                    $razorpayResponse['short_url']
                ]);

                // Fixed: Saving to razorpay_link_id instead of razorpay_order_id
                $pdo->prepare("UPDATE invoices SET razorpay_link_id = ? WHERE id = ?")->execute([$razorpayResponse['id'], $invoice_id]);
            }
        }

        $pdo->commit();
        header("Location: view.php?id=" . $invoice_id . "&success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving invoice: " . $e->getMessage());
    }
}
