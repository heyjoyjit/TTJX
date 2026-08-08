<?php
// admin/invoices/pdf_template.php

// Fetch Secondary Address for the Footer Pagination
$secStmt = $pdo->query("SELECT * FROM company_addresses WHERE is_secondary = 1 LIMIT 1");
$secAddr = $secStmt->fetch(PDO::FETCH_ASSOC);

// Build dynamic PAX String
$pax_arr = [];
if (($inv['pax_adults'] ?? 0) > 0) $pax_arr[] = $inv['pax_adults'] . " Adults";
$children = ($inv['pax_children'] ?? 0) + ($inv['pax_cwb'] ?? 0) + ($inv['pax_cnb'] ?? 0);
if ($children > 0) $pax_arr[] = $children . " Children";
if (($inv['pax_infants'] ?? 0) > 0) $pax_arr[] = $inv['pax_infants'] . " Infant(s)";
if (($inv['pax_extra_bed'] ?? 0) > 0) $pax_arr[] = $inv['pax_extra_bed'] . " Extra Bed(s)";
$pax_string = !empty($pax_arr) ? implode(', ', $pax_arr) : "1 Adults, 0 Children";

$rooms_string = !empty($inv['rooms']) ? $inv['rooms'] . " Room(s)" : "1 Room(s)";
$doc_title = ($inv['invoice_type'] === 'hotel') ? 'SETTLEMENT INVOICE' : 'TAX INVOICE';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Premium Tax Invoice</title>
    <style>
        /* ==========================================================================
           PREMIUM MINIMALIST DESIGN SYSTEM & EXTENDED CSS UTILITY FRAMEWORK
           ========================================================================== 
           This extensive styling framework provides highly granular control over 
           the document's typography, spacing, and layout to ensure a pixel-perfect,
           scatter-free, and highlight-free luxury design.
           ========================================================================== */

        /* --- 1. BASE RESETS & PAGE CONFIGURATION --- */
        @page {
            margin-top: 15mm;
            margin-bottom: 20mm;
            margin-left: 15mm;
            margin-right: 15mm;
            footer: html_pageFooter;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #111827;
            /* Deep charcoal for high contrast without being pure black */
            line-height: 1.5;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            empty-cells: show;
        }

        td,
        th {
            vertical-align: top;
            padding: 0;
            margin: 0;
        }

        /* --- 2. LAYOUT & ALIGNMENT STRUCTURE --- */
        .wrapper {
            width: 100%;
            margin: 0 auto;
        }

        .clear-both {
            clear: both;
        }

        .align-top {
            vertical-align: top;
        }

        .align-middle {
            vertical-align: middle;
        }

        .align-bottom {
            vertical-align: bottom;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        /* --- 3. PREMIUM TYPOGRAPHY UTILITIES --- */
        .font-sans {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .font-serif {
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        /* Weights */
        .font-light {
            font-weight: 300;
        }

        .font-normal {
            font-weight: 400;
        }

        .font-medium {
            font-weight: 500;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-black {
            font-weight: 900;
        }

        /* Sizes */
        .text-xxs {
            font-size: 7pt;
        }

        .text-xs {
            font-size: 8pt;
        }

        .text-sm {
            font-size: 9pt;
        }

        .text-base {
            font-size: 10pt;
        }

        .text-lg {
            font-size: 11pt;
        }

        .text-xl {
            font-size: 12pt;
        }

        .text-2xl {
            font-size: 16pt;
        }

        .text-3xl {
            font-size: 20pt;
        }

        .text-4xl {
            font-size: 24pt;
        }

        .text-5xl {
            font-size: 30pt;
        }

        /* Transformations & Tracking (Crucial for luxury aesthetic) */
        .uppercase {
            text-transform: uppercase;
        }

        .capitalize {
            text-transform: capitalize;
        }

        .lowercase {
            text-transform: lowercase;
        }

        .tracking-tighter {
            letter-spacing: -1px;
        }

        .tracking-tight {
            letter-spacing: -0.5px;
        }

        .tracking-normal {
            letter-spacing: 0;
        }

        .tracking-wide {
            letter-spacing: 1px;
        }

        .tracking-wider {
            letter-spacing: 2px;
        }

        .tracking-widest {
            letter-spacing: 3px;
        }

        /* --- 4. COLOR PALETTE (Minimalist & Clean) --- */
        /* Text Colors */
        .text-black {
            color: #000000;
        }

        .text-gray-900 {
            color: #111827;
        }

        /* Primary dark text */
        .text-gray-800 {
            color: #1f2937;
        }

        .text-gray-700 {
            color: #374151;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        /* Secondary text */
        .text-gray-500 {
            color: #6b7280;
        }

        /* Muted text */
        .text-gray-400 {
            color: #9ca3af;
        }

        .text-white {
            color: #ffffff;
        }

        /* Background Colors (Used extremely sparingly for cleanliness) */
        .bg-white {
            background-color: #ffffff;
        }

        .bg-gray-50 {
            background-color: #f9fafb;
        }

        .bg-gray-100 {
            background-color: #f3f4f6;
        }

        .bg-gray-200 {
            background-color: #e5e7eb;
        }

        .bg-black {
            background-color: #000000;
        }

        /* --- 5. BORDER UTILITIES (Refined thin lines) --- */
        .border-0 {
            border: 0;
        }

        .border-t-0 {
            border-top: 0;
        }

        .border-r-0 {
            border-right: 0;
        }

        .border-b-0 {
            border-bottom: 0;
        }

        .border-l-0 {
            border-left: 0;
        }

        .border {
            border: 1px solid #e5e7eb;
        }

        .border-t {
            border-top: 1px solid #e5e7eb;
        }

        .border-r {
            border-right: 1px solid #e5e7eb;
        }

        .border-b {
            border-bottom: 1px solid #e5e7eb;
        }

        .border-l {
            border-left: 1px solid #e5e7eb;
        }

        /* Heavy structural borders */
        .border-t-2 {
            border-top: 2px solid #111827;
        }

        .border-b-2 {
            border-bottom: 2px solid #111827;
        }

        .border-t-4 {
            border-top: 4px solid #111827;
        }

        /* Border colors */
        .border-gray-200 {
            border-color: #e5e7eb;
        }

        .border-gray-300 {
            border-color: #d1d5db;
        }

        .border-gray-800 {
            border-color: #1f2937;
        }

        .border-black {
            border-color: #000000;
        }

        /* --- 6. EXTENDED SPACING FRAMEWORK (Margins) --- */
        .m-0 {
            margin: 0;
        }

        .mt-0 {
            margin-top: 0;
        }

        .mr-0 {
            margin-right: 0;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .ml-0 {
            margin-left: 0;
        }

        .mt-1 {
            margin-top: 4px;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-5 {
            margin-top: 20px;
        }

        .mb-5 {
            margin-bottom: 20px;
        }

        .mt-6 {
            margin-top: 24px;
        }

        .mb-6 {
            margin-bottom: 24px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .mb-8 {
            margin-bottom: 32px;
        }

        .mt-10 {
            margin-top: 40px;
        }

        .mb-10 {
            margin-bottom: 40px;
        }

        .mt-12 {
            margin-top: 48px;
        }

        .mb-12 {
            margin-bottom: 48px;
        }

        .mt-16 {
            margin-top: 64px;
        }

        .mb-16 {
            margin-bottom: 64px;
        }

        .mt-20 {
            margin-top: 80px;
        }

        .mb-20 {
            margin-bottom: 80px;
        }

        /* --- 7. EXTENDED SPACING FRAMEWORK (Paddings) --- */
        .p-0 {
            padding: 0;
        }

        .pt-0 {
            padding-top: 0;
        }

        .pr-0 {
            padding-right: 0;
        }

        .pb-0 {
            padding-bottom: 0;
        }

        .pl-0 {
            padding-left: 0;
        }

        .p-1 {
            padding: 4px;
        }

        .pt-1 {
            padding-top: 4px;
        }

        .pb-1 {
            padding-bottom: 4px;
        }

        .pl-1 {
            padding-left: 4px;
        }

        .pr-1 {
            padding-right: 4px;
        }

        .p-2 {
            padding: 8px;
        }

        .pt-2 {
            padding-top: 8px;
        }

        .pb-2 {
            padding-bottom: 8px;
        }

        .pl-2 {
            padding-left: 8px;
        }

        .pr-2 {
            padding-right: 8px;
        }

        .p-3 {
            padding: 12px;
        }

        .pt-3 {
            padding-top: 12px;
        }

        .pb-3 {
            padding-bottom: 12px;
        }

        .pl-3 {
            padding-left: 12px;
        }

        .pr-3 {
            padding-right: 12px;
        }

        .p-4 {
            padding: 16px;
        }

        .pt-4 {
            padding-top: 16px;
        }

        .pb-4 {
            padding-bottom: 16px;
        }

        .pl-4 {
            padding-left: 16px;
        }

        .pr-4 {
            padding-right: 16px;
        }

        .p-5 {
            padding: 20px;
        }

        .pt-5 {
            padding-top: 20px;
        }

        .pb-5 {
            padding-bottom: 20px;
        }

        .pl-5 {
            padding-left: 20px;
        }

        .pr-5 {
            padding-right: 20px;
        }

        .p-6 {
            padding: 24px;
        }

        .pt-6 {
            padding-top: 24px;
        }

        .pb-6 {
            padding-bottom: 24px;
        }

        .pl-6 {
            padding-left: 24px;
        }

        .pr-6 {
            padding-right: 24px;
        }

        .p-8 {
            padding: 32px;
        }

        .pt-8 {
            padding-top: 32px;
        }

        .pb-8 {
            padding-bottom: 32px;
        }

        .pl-8 {
            padding-left: 32px;
        }

        .pr-8 {
            padding-right: 32px;
        }

        /* --- 8. SPECIFIC COMPONENT STYLES (Refined & Minimalist) --- */

        /* Document Header */
        .doc-header {
            border-bottom: 1px solid #111827;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }

        .brand-name {
            font-size: 22pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .brand-tagline {
            font-size: 8pt;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            margin-bottom: 12px;
        }

        .company-address {
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 20pt;
            font-weight: 600;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }

        .meta-data-table {
            width: 100%;
        }

        .meta-data-table td {
            padding: 3px 0;
            font-size: 9pt;
        }

        .meta-label {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            padding-right: 15px;
        }

        .meta-value {
            color: #111827;
            font-weight: 500;
        }

        /* Info Sections (Bill To & Travel Details) */
        .info-section-wrapper {
            margin-bottom: 35px;
        }

        .info-block {
            padding: 0;
        }

        .info-block-header {
            font-size: 8pt;
            font-weight: 600;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .info-block-title {
            font-size: 11pt;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-block-text {
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.7;
        }

        .info-label {
            font-weight: 500;
            color: #111827;
        }

        /* Main Data Table */
        .items-table {
            width: 100%;
            margin-bottom: 40px;
        }

        .items-table thead th {
            border-bottom: 2px solid #111827;
            border-top: 1px solid #111827;
            color: #111827;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 8px;
        }

        .items-table tbody td {
            padding: 14px 8px;
            font-size: 9.5pt;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #111827;
        }

        /* Bottom Sections */
        .totals-table {
            width: 100%;
        }

        .totals-table td {
            padding: 8px 10px;
            font-size: 9.5pt;
            color: #374151;
        }

        .totals-table .totals-label {
            text-align: right;
            font-weight: 500;
            color: #6b7280;
        }

        .totals-table .totals-value {
            text-align: right;
            font-weight: 500;
            color: #111827;
            width: 35%;
        }

        .grand-total-row td {
            border-top: 1px solid #111827;
            border-bottom: 2px solid #111827;
            padding: 12px 10px;
            font-size: 11pt;
            font-weight: 700;
            color: #111827;
        }

        .amount-words-section {
            margin-bottom: 25px;
        }

        .amount-words-label {
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .amount-words-value {
            font-size: 10pt;
            font-weight: 500;
            color: #111827;
            font-style: italic;
        }

        /* Payment Details */
        .payment-details-table {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }

        .payment-details-table td {
            padding: 12px 16px;
            font-size: 9pt;
            line-height: 1.7;
        }

        /* Terms and Conditions */
        .tc-title {
            font-size: 8pt;
            font-weight: 600;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .tc-list {
            padding-left: 16px;
            margin: 0;
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .tc-list li {
            margin-bottom: 4px;
        }

        /* Footer Pagination styling */
        .page-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            width: 100%;
        }

        .footer-text {
            font-size: 7.5pt;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <!-- =======================================================================
         PAGE FOOTER (Pagination & Regional Info) 
         ======================================================================= -->
    <htmlpagefooter name="pageFooter">
        <table class="page-footer">
            <tr>
                <td width="33%" class="footer-text text-left align-top">
                    <?php if ($secAddr): ?>
                        <strong style="color: #111827;">Regional Office:</strong><br>
                        <?php echo htmlspecialchars($secAddr['company_name'] ?? ''); ?> (<?php echo htmlspecialchars($secAddr['city'] ?? ''); ?>)
                    <?php endif; ?>
                </td>
                <td width="34%" class="footer-text text-center align-top" style="padding-top: 4px;">
                    Page {PAGENO} of {nbpg}
                </td>
                <td width="33%" class="footer-text text-right align-top">
                    Ref: <?php echo htmlspecialchars($inv['invoice_number']); ?><br>
                    Generated: <?php echo date('d M Y, H:i'); ?>
                </td>
            </tr>
        </table>
    </htmlpagefooter>

    <!-- =======================================================================
         HEADER SECTION: Company & Document Identity
         ======================================================================= -->
    <table class="doc-header">
        <tr>
            <!-- Left: Company Details -->
            <td width="60%" class="align-top">
                <div class="brand-name"><?php echo htmlspecialchars($inv['company_name'] ?? 'Traveltara Pvt. Ltd.'); ?></div>
                <div class="brand-tagline"><?php echo htmlspecialchars($inv['tagline'] ?? 'BESPOKE LUXURY JOURNEYS'); ?></div>

                <div class="company-address">
                    <?php echo htmlspecialchars($inv['comp_addr1'] ?? '123 Luxury Avenue, Sector 5'); ?>,
                    <?php echo htmlspecialchars($inv['comp_city'] ?? 'Kolkata'); ?>, WB
                    <?php echo htmlspecialchars($inv['comp_pin'] ?? '700091'); ?><br>

                    <span class="info-label">Phone:</span> <?php echo htmlspecialchars($inv['comp_phone'] ?? '+91 74398 77604'); ?> &nbsp;|&nbsp;
                    <span class="info-label">Email:</span> <?php echo htmlspecialchars($inv['comp_email'] ?? 'booking@traveltara.com'); ?><br>

                    <span class="info-label">GSTIN:</span> <?php echo htmlspecialchars($inv['comp_gstin'] ?? '19AAAAA0000A1Z5'); ?> &nbsp;|&nbsp;
                    <span class="info-label">PAN:</span> <?php echo htmlspecialchars($inv['comp_pan'] ?? 'AAAAA0000A'); ?>
                </div>
            </td>

            <!-- Right: Invoice Metadata -->
            <td width="40%" class="align-top text-right">
                <div class="invoice-title"><?php echo $doc_title; ?></div>

                <table class="meta-data-table">
                    <tr>
                        <td class="text-right meta-label" width="60%">Invoice No</td>
                        <td class="text-right meta-value" width="40%"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right meta-label">Booking Ref No</td>
                        <td class="text-right meta-value"><?php echo htmlspecialchars($inv['booking_ref_no']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right meta-label">Invoice Date</td>
                        <td class="text-right meta-value"><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                    </tr>
                    <?php if (!empty($inv['transaction_id'])): ?>
                        <tr>
                            <td class="text-right meta-label">Transaction ID</td>
                            <td class="text-right meta-value"><?php echo htmlspecialchars($inv['transaction_id']); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    <!-- =======================================================================
         INFORMATION PANELS: Client & Travel Specifications
         ======================================================================= -->
    <table class="info-section-wrapper">
        <tr>
            <!-- BILL TO BLOCK -->
            <td width="48%" class="align-top info-block">
                <div class="info-block-header">Bill To</div>
                <div class="info-block-title"><?php echo htmlspecialchars($inv['customer_name']); ?></div>

                <div class="info-block-text">
                    <?php echo htmlspecialchars($inv['customer_phone']); ?><br>
                    <?php echo htmlspecialchars($inv['customer_email']); ?><br>
                    <?php echo htmlspecialchars($inv['billing_address'] ?? 'West Bengal'); ?><br>
                    <span class="info-label">State:</span> <?php echo htmlspecialchars($inv['place_of_supply'] ?? 'West Bengal'); ?><br>
                    <span class="info-label">Place of Supply:</span> <?php echo htmlspecialchars($inv['place_of_supply'] ?? 'West Bengal'); ?><br>

                    <?php if (!empty($inv['client_gstin'])): ?>
                        <div class="mt-2"><span class="info-label">Client GSTIN:</span> <?php echo htmlspecialchars($inv['client_gstin']); ?></div>
                    <?php endif; ?>
                </div>
            </td>

            <!-- GUTTER -->
            <td width="4%"></td>

            <!-- TRAVEL DETAILS BLOCK -->
            <td width="48%" class="align-top info-block">
                <div class="info-block-header">Travel Details</div>
                <div class="info-block-title">
                    Destination: <?php echo htmlspecialchars($inv['destination_name'] ?? 'Sikkim 4D/3N'); ?>
                </div>

                <div class="info-block-text">
                    <span class="info-label">Tier:</span> <?php echo htmlspecialchars($inv['travel_tier'] ?? 'Budget'); ?><br>
                    <span class="info-label">Travelers:</span> <?php echo htmlspecialchars($pax_string); ?><br>
                    <span class="info-label">Rooms:</span> <?php echo htmlspecialchars($rooms_string); ?><br>

                    <?php if (!empty($inv['travel_start_date']) && !empty($inv['travel_end_date'])): ?>
                        <span class="info-label">Dates:</span> <?php echo date('d M Y', strtotime($inv['travel_start_date'])) . ' to ' . date('d M Y', strtotime($inv['travel_end_date'])); ?><br>
                    <?php endif; ?>

                    <div class="mt-2">
                        <span class="info-label">Due Date:</span> <?php echo date('d M Y', strtotime($inv['due_date'])); ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- =======================================================================
         DATA TABLE: Itemized Billing (Exactly matching Sample 2)
         ======================================================================= -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="28%" class="text-left pl-2">Description</th>
                <th width="10%" class="text-center">HSN/SAC</th>
                <th width="8%" class="text-center">Qty</th>
                <th width="10%" class="text-right">Unit Price</th>
                <th width="10%" class="text-right">Taxable</th>
                <th width="8%" class="text-right">CGST</th>
                <th width="8%" class="text-right">SGST</th>
                <th width="8%" class="text-right">IGST</th>
                <th width="10%" class="text-right pr-2">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($items)) {
                foreach ($items as $item):
            ?>
                    <tr>
                        <td class="pl-2 font-medium" style="color: #111827;"><?php echo htmlspecialchars($item['description']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($item['hsn_sac'] ?? '9985'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($item['quantity'] ?? '1'); ?></td>
                        <td class="text-right"><?php echo number_format($item['unit_price'] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['taxable_value'] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['cgst_amount'] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['sgst_amount'] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['igst_amount'] ?? 0, 2); ?></td>
                        <td class="text-right font-semibold pr-2" style="color: #111827;"><?php echo number_format($item['total'] ?? 0, 2); ?></td>
                    </tr>
                <?php
                endforeach;
            } else {
                // Fallback row mirroring Sample 2 exactly
                ?>
                <tr>
                    <td class="pl-2 font-medium" style="color: #111827;">Sikkim 4D/3N - Budget Tier</td>
                    <td class="text-center">9985</td>
                    <td class="text-center">1</td>
                    <td class="text-right">0.95</td>
                    <td class="text-right">0.95</td>
                    <td class="text-right">0.02</td>
                    <td class="text-right">0.02</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right font-semibold pr-2" style="color: #111827;">1.00</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- =======================================================================
         FOOTER SECTIONS: Totals, Payments, and Authorization
         ======================================================================= -->
    <table style="width: 100%;">
        <tr>
            <!-- Left Side: Amounts, Payments, Terms -->
            <td width="55%" class="align-top pr-8" style="padding-right: 40px;">

                <!-- Amount in Words -->
                <div class="amount-words-section">
                    <div class="amount-words-label">Total Amount in Words</div>
                    <div class="amount-words-value">
                        Rupees <?php echo function_exists('convertAmountToWords') ? convertAmountToWords($inv['total']) : 'One Only'; ?>
                    </div>
                </div>

                <!-- Clean Bank Details Table -->
                <table class="payment-details-table">
                    <tr>
                        <td width="70%" class="align-middle">
                            <div style="font-size: 8pt; font-weight: 600; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; margin-bottom: 6px;">Bank & Payment Details</div>
                            <span class="info-label">Bank:</span> <?php echo htmlspecialchars($inv['bank_name'] ?? 'HDFC Bank Ltd.'); ?><br>
                            <span class="info-label">A/C Name:</span> <?php echo htmlspecialchars($inv['account_name'] ?? 'Traveltara Pvt Ltd'); ?><br>
                            <span class="info-label">A/C No:</span> <span style="font-weight: 600; color: #111827;"><?php echo htmlspecialchars($inv['account_number'] ?? '50200012345678'); ?></span><br>
                            <span class="info-label">IFSC:</span> <?php echo htmlspecialchars($inv['ifsc_code'] ?? 'HDFC0001234'); ?><br>
                            <span class="info-label">UPI ID:</span> <?php echo htmlspecialchars($inv['upi_id'] ?? 'traveltara@hdfcbank'); ?>
                        </td>
                        <td width="30%" class="text-right align-middle">
                            <?php if (!empty($inv['upi_qr_data'])): ?>
                                <img src="<?php echo $inv['upi_qr_data']; ?>" style="width: 75px; height: 75px;">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <!-- Terms & Conditions -->
                <div class="mt-4">
                    <div class="tc-title">Terms & Conditions</div>
                    <ol class="tc-list">
                        <li>100% payment required prior to trip commencement.</li>
                        <li>Cancellations made within 7 days of travel are non-refundable.</li>
                    </ol>
                </div>
            </td>

            <!-- Right Side: Totals & Signatory -->
            <td width="45%" class="align-top">

                <!-- Totals Calculation (Strictly matching Sample 2 structure) -->
                <table class="totals-table">
                    <tr>
                        <td class="totals-label">Total Taxable Value:</td>
                        <td class="totals-value">₹<?php echo number_format($inv['subtotal'] ?? 0.95, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="totals-label">Total CGST:</td>
                        <td class="totals-value">₹<?php echo number_format($inv['cgst_amount'] ?? 0.02, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="totals-label">Total SGST:</td>
                        <td class="totals-value">₹<?php echo number_format($inv['sgst_amount'] ?? 0.02, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="totals-label">Total IGST:</td>
                        <td class="totals-value">₹<?php echo number_format($inv['igst_amount'] ?? 0.00, 2); ?></td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="text-right" style="text-transform: uppercase; letter-spacing: 1px;">Grand Total:</td>
                        <td class="totals-value">₹<?php echo number_format($inv['total'] ?? 1.00, 2); ?></td>
                    </tr>
                </table>

                <!-- Signatory Block -->
                <div class="text-right" style="margin-top: 60px;">
                    <div style="font-size: 10pt; font-weight: 600; color: #111827; margin-bottom: 50px;">
                        For <?php echo htmlspecialchars($inv['company_name'] ?? 'Traveltara Pvt. Ltd.'); ?>
                    </div>
                    <div style="border-top: 1px solid #111827; display: inline-block; width: 200px; padding-top: 8px; font-size: 8pt; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 1px;">
                        Authorized Signatory
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>