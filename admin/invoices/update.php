<?php
// admin/invoices/update.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_id'])) {
    $invoice_id = intval($_POST['invoice_id']);

    try {
        $pdo->beginTransaction();

        // 1. Recalculate everything securely (same logic as save.php)
        $calculated_subtotal = 0;
        $secure_items = [];
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $qty = intval($item['quantity']);
                $price = floatval($item['unit_price']);
                $line_total = $qty * $price;
                $calculated_subtotal += $line_total;
                $secure_items[] = ['desc' => $item['description'], 'hsn' => $item['hsn_sac'], 'qty' => $qty, 'price' => $price, 'total' => $line_total];
            }
        }

        $taxData = calculateGST($calculated_subtotal, $_POST['tax_type'], floatval($_POST['cgst_rate'] ?? 0), floatval($_POST['sgst_rate'] ?? 0), floatval($_POST['igst_rate'] ?? 0));

        // 2. Update the main invoice record
        // Generate NEW UPI QR Code for the updated amount
        $compAddr = getPrimaryCompanyAddress($pdo);
        $qrCodeUrl = generateUPIQrCodeUrl($compAddr['upi_id'], $compAddr['company_name'], $taxData['grand_total'], $_POST['booking_ref_no']);

        // Generate NEW Razorpay Link for the updated amount
        $newRazorpayLinkId = null;
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/razorpay_helper.php';
            
            $description = "Updated Payment for Invoice " . $_POST['booking_ref_no'];
            $razorpayResponse = createRazorpayPaymentLink($taxData['grand_total'], $_POST['customer_email'], $_POST['customer_phone'], $_POST['customer_name'], $description, $_POST['booking_ref_no']);

            if ($razorpayResponse && isset($razorpayResponse['id'])) {
                $newRazorpayLinkId = $razorpayResponse['id'];

                // Update payment_links table (or insert a new one)
                $plStmt = $pdo->prepare("INSERT INTO payment_links (hotel_id, customer_email, customer_name, amount, description, razorpay_link_id, razorpay_link_url, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY))");
                $plStmt->execute([$_POST['hotel_id'] ?: null, $_POST['customer_email'], $_POST['customer_name'], $taxData['grand_total'], $description, $razorpayResponse['id'], $razorpayResponse['short_url']]);
            }
        }

        // 2. Update the main invoice record (Now includes upi_qr_data, invoice_date, and optionally razorpay_link_id)
        $updateSql = "
            UPDATE invoices SET 
                invoice_type=?, hotel_id=?, company_address_id=?, customer_name=?, customer_email=?, 
                customer_phone=?, client_gstin=?, billing_address=?, place_of_supply=?, destination_name=?, 
                pax_adults=?, pax_children=?, pax_infants=?, pax_cwb=?, pax_cnb=?, pax_extra_bed=?, 
                pax_custom_details=?, travel_tier=?, travel_start_date=?, travel_end_date=?, 
                invoice_date=?, due_date=?, subtotal=?, tax_type=?, cgst_rate=?, sgst_rate=?, igst_rate=?, 
                cgst_amount=?, sgst_amount=?, igst_amount=?, total_taxable_value=?, total=?, 
                upi_qr_data=?, updated_at=NOW()";

        $params = [
            $_POST['invoice_type'],
            !empty($_POST['hotel_id']) ? $_POST['hotel_id'] : null,
            $_POST['company_address_id'],
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['client_gstin'],
            $_POST['billing_address'],
            $_POST['place_of_supply'],
            $_POST['destination_name'],
            intval($_POST['pax_adults']),
            intval($_POST['pax_children']),
            intval($_POST['pax_infants']),
            intval($_POST['pax_cwb']),
            intval($_POST['pax_cnb']),
            intval($_POST['pax_extra_bed']),
            $_POST['pax_custom_details'],
            $_POST['travel_tier'],
            $_POST['travel_start_date'] ?: null,
            $_POST['travel_end_date'] ?: null,
            $_POST['invoice_date'],
            $_POST['due_date'],
            $taxData['taxable'],
            $_POST['tax_type'],
            $taxData['cgst_rate'],
            $taxData['sgst_rate'],
            $taxData['igst_rate'],
            $taxData['cgst_amount'],
            $taxData['sgst_amount'],
            $taxData['igst_amount'],
            $taxData['taxable'],
            $taxData['grand_total'],
            $qrCodeUrl
        ];

        // Only update razorpay link if successfully generated
        if ($newRazorpayLinkId) {
            $updateSql .= ", razorpay_link_id=? ";
            $params[] = $newRazorpayLinkId;
        }

        $updateSql .= " WHERE id=?";
        $params[] = $invoice_id;

        $stmt = $pdo->prepare($updateSql);
        $stmt->execute($params);

        // 3. Wipe old items and insert new ones
        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoice_id]);

        $itemStmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, hsn_sac, quantity, unit_price, taxable_value, cgst_amount, sgst_amount, igst_amount, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($secure_items as $item) {
            $i_cgst = round(($item['total'] * $taxData['cgst_rate']) / 100, 2);
            $i_sgst = round(($item['total'] * $taxData['sgst_rate']) / 100, 2);
            $i_igst = round(($item['total'] * $taxData['igst_rate']) / 100, 2);
            $itemStmt->execute([$invoice_id, $item['desc'], $item['hsn'], $item['qty'], $item['price'], $item['total'], $i_cgst, $i_sgst, $i_igst, ($item['total'] + $i_cgst + $i_sgst + $i_igst)]);
        }

        $pdo->commit();
        header("Location: view.php?id=" . $invoice_id . "&success=Invoice Updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error updating invoice: " . $e->getMessage());
    }
}
