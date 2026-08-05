<?php
// admin/invoices/pdf_template.php
// Assumes $inv and $items arrays are already populated by the calling script

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

// Fetch Secondary Address
$secStmt = $pdo->query("SELECT * FROM company_addresses WHERE is_secondary = 1 LIMIT 1");
$secAddr = $secStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
            position: relative;
        }

        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e1b4b;
            margin: 0;
            text-transform: uppercase;
        }

        .tagline {
            font-size: 10px;
            color: #6b7280;
            margin: 0;
            letter-spacing: 2px;
        }

        .invoice-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #b45309;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 50%;
            float: right;
            margin-top: 20px;
        }

        .totals-row {
            border-bottom: 1px solid #e5e7eb;
            padding: 5px 0;
        }

        .bank-details {
            width: 50%;
            float: left;
            margin-top: 20px;
            font-size: 10px;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            margin-top: 10px;
        }

        .watermark {
            position: absolute;
            top: 30%;
            left: 25%;
            font-size: 120px;
            color: rgba(34, 197, 94, 0.15);
            font-weight: bold;
            transform: rotate(-45deg);
            border: 8px solid rgba(34, 197, 94, 0.15);
            padding: 20px;
            text-transform: uppercase;
            z-index: -1;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <?php if (strtolower($inv['status']) === 'paid'): ?>
        <div class="watermark">PAID</div>
    <?php endif; ?>

    <table width="100%" class="header">
        <tr>
            <td width="60%">
                <h1 class="company-name"><?php echo htmlspecialchars($inv['company_name']); ?></h1>
                <p class="tagline"><?php echo htmlspecialchars($inv['tagline']); ?></p>
                <p style="margin-top: 10px;">
                    <?php echo $inv['comp_addr1']; ?>, <?php echo $inv['comp_city']; ?> - <?php echo $inv['comp_pin']; ?><br>
                    GSTIN: <?php echo $inv['comp_gstin']; ?> | PAN: <?php echo $inv['comp_pan']; ?>
                </p>
            </td>
            <td width="40%" class="text-right">
                <div class="invoice-title"><?php echo ($inv['invoice_type'] === 'hotel') ? 'SETTLEMENT INVOICE' : 'TAX INVOICE'; ?></div>
                <p><strong>Invoice No:</strong> <?php echo $inv['invoice_number']; ?><br>
                    <strong>Booking Ref:</strong> <?php echo $inv['booking_ref_no']; ?><br>
                    <strong>Date:</strong> <?php echo date('d M Y', strtotime($inv['invoice_date'])); ?>
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <strong style="color:#9ca3af; font-size:10px;">BILL TO</strong><br>
                <strong><?php echo htmlspecialchars($inv['customer_name']); ?></strong><br>
                <?php echo htmlspecialchars($inv['billing_address']); ?><br>
                Phone: <?php echo $inv['customer_phone']; ?><br>
                <?php if ($inv['client_gstin']) echo "GSTIN: " . $inv['client_gstin']; ?>
            </td>
            <td width="50%" class="text-right">
                <strong>Travel:</strong> <?php echo htmlspecialchars($inv['destination_name']); ?><br>
                <strong>Dates:</strong> <?php echo $inv['travel_start_date'] ? date('d M Y', strtotime($inv['travel_start_date'])) . ' to ' . date('d M Y', strtotime($inv['travel_end_date'])) : 'TBD'; ?><br>
                <?php
                // Build a clean PAX string
                $pax_arr = [];
                if ($inv['pax_adults'] > 0) $pax_arr[] = $inv['pax_adults'] . " Adults";
                if ($inv['pax_children'] > 0) $pax_arr[] = $inv['pax_children'] . " Children";
                if ($inv['pax_cwb'] > 0) $pax_arr[] = $inv['pax_cwb'] . " CWB";
                if ($inv['pax_cnb'] > 0) $pax_arr[] = $inv['pax_cnb'] . " CNB";
                if ($inv['pax_infants'] > 0) $pax_arr[] = $inv['pax_infants'] . " Infants";
                if ($inv['pax_extra_bed'] > 0) $pax_arr[] = $inv['pax_extra_bed'] . " Extra Bed";
                $pax_string = implode(', ', $pax_arr);
                ?>
                <strong>PAX:</strong> <?php echo $pax_string; ?><br><br>
                <strong>Place of Supply:</strong> <?php echo $inv['place_of_supply']; ?>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>HSN/SAC</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit (₹)</th>
                <th class="text-right">Taxable (₹)</th>

                <?php if ($inv['tax_type'] === 'inter_state'): ?>
                    <th class="text-right">IGST</th>
                <?php elseif ($inv['tax_type'] === 'international_lut'): ?>
                    <!-- No tax columns for LUT -->
                <?php else: ?>
                    <th class="text-right">CGST</th>
                    <th class="text-right">SGST</th>
                <?php endif; ?>

                <th class="text-right">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td class="text-center"><?php echo $item['hsn_sac']; ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($item['taxable_value'], 2); ?></td>

                    <!-- FIXED: Printing Item-Level Tax instead of Global Tax -->
                    <td class="text-right"><?php echo number_format($item['cgst_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($item['sgst_amount'], 2); ?></td>

                    <td class="text-right"><strong><?php echo number_format($item['total'], 2); ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width: 100%; margin-top: 20px;">
        <div class="bank-details">
            <strong>BANK & PAYMENT DETAILS</strong><br>
            Bank: <?php echo $inv['bank_name']; ?> | A/C Name: <?php echo $inv['account_name']; ?><br>
            A/C No: <?php echo $inv['account_number']; ?> | IFSC: <?php echo $inv['ifsc_code']; ?><br>
            UPI ID: <?php echo $inv['upi_id']; ?><br>
            <?php if ($inv['upi_qr_data']): ?>
                <img src="<?php echo $inv['upi_qr_data']; ?>" class="qr-code">
            <?php endif; ?>

            <div style="margin-top: 15px; font-size: 10px; color: #6b7280;">
                <strong>TERMS & CONDITIONS</strong><br>
                1. 100% payment required prior to trip commencement.<br>
                2. Cancellations made within 7 days of travel are non-refundable.
            </div>
        </div>

        <div class="totals text-right">
            <div class="totals-row">Total Taxable Value: ₹<?php echo number_format($inv['subtotal'], 2); ?></div>
            <div class="totals-row">CGST (<?php echo $inv['cgst_rate']; ?>%): ₹<?php echo number_format($inv['cgst_amount'], 2); ?></div>
            <div class="totals-row">SGST (<?php echo $inv['sgst_rate']; ?>%): ₹<?php echo number_format($inv['sgst_amount'], 2); ?></div>
            <?php if ($inv['igst_amount'] > 0): ?>
                <div class="totals-row">IGST (<?php echo $inv['igst_rate']; ?>%): ₹<?php echo number_format($inv['igst_amount'], 2); ?></div>
            <?php endif; ?>
            <div class="totals-row" style="font-size: 16px; font-weight: bold; border-bottom: 2px solid #1e1b4b;">
                Grand Total: ₹<?php echo number_format($inv['total'], 2); ?>
            </div>
            <div style="font-size: 10px; margin-top: 5px; color: #6b7280;">
                Amount in Words: <?php echo convertAmountToWords($inv['total']); ?>
            </div>
        </div>
    </div>

    <?php if ($secAddr): ?>
        <div class="footer">
            <strong>Secondary Branch:</strong> <?php echo htmlspecialchars($secAddr['company_name']); ?> | <?php echo htmlspecialchars($secAddr['address_line_1']); ?>, <?php echo htmlspecialchars($secAddr['city']); ?> - <?php echo htmlspecialchars($secAddr['pincode']); ?>
        </div>
    <?php endif; ?>

</body>

</html>