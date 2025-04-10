<?php
/**
 * PDF template for invoices
 * Used when generating PDF invoices for printing or download
 */

// Prevent direct access
if (!defined('GARAGE_CMS')) {
    die('Direct access not permitted');
}

// This template is designed to be used with TCPDF library
?>

<!-- CSS styles for PDF -->
<style>
    body {
        font-family: dejavusans;
        color: #333;
    }
    .invoice-header {
        margin-bottom: 20px;
    }
    .invoice-title {
        font-size: 22pt;
        font-weight: bold;
        color: #3498db;
        margin-bottom: 5px;
    }
    .invoice-subtitle {
        font-size: 10pt;
        color: #777;
    }
    .company-details {
        text-align: right;
        font-size: 9pt;
    }
    .company-name {
        font-size: 14pt;
        font-weight: bold;
        color: #333;
    }
    .invoice-meta {
        margin: 20px 0;
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f9f9f9;
    }
    .invoice-meta-title {
        font-weight: bold;
        font-size: 10pt;
    }
    .customer-details {
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 12pt;
        font-weight: bold;
        color: #3498db;
        border-bottom: 1px solid #3498db;
        margin-top: 15px;
        margin-bottom: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
        text-align: left;
        padding: 8px;
        border-bottom: 2px solid #ddd;
    }
    table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    .item-row:nth-child(even) {
        background-color: #f9f9f9;
    }
    .total-section {
        margin-top: 20px;
        text-align: right;
    }
    .total-row {
        margin: 5px 0;
    }
    .total-title {
        font-weight: bold;
        display: inline-block;
        width: 150px;
        text-align: right;
    }
    .total-value {
        display: inline-block;
        width: 100px;
        text-align: right;
    }
    .grand-total {
        font-size: 12pt;
        font-weight: bold;
        color: #3498db;
        margin-top: 10px;
    }
    .notes {
        margin-top: 30px;
        font-size: 9pt;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }
    .payment-info {
        margin-top: 20px;
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f9f9f9;
    }
    .footer {
        position: absolute;
        bottom: 10px;
        width: 100%;
        text-align: center;
        font-size: 8pt;
        color: #777;
    }
    .status-paid {
        color: #27ae60;
        font-weight: bold;
        font-size: 14pt;
        text-align: right;
        transform: rotate(-15deg);
        position: absolute;
        top: 50px;
        right: 50px;
        border: 2px solid #27ae60;
        padding: 5px 15px;
    }
    .status-unpaid {
        color: #e74c3c;
        font-weight: bold;
        font-size: 14pt;
        text-align: right;
        transform: rotate(-15deg);
        position: absolute;
        top: 50px;
        right: 50px;
        border: 2px solid #e74c3c;
        padding: 5px 15px;
    }
    .barcode {
        text-align: center;
        margin-top: 20px;
    }
</style>

<!-- Invoice Content -->
<div class="invoice-container">
    <!-- Company Logo -->
    <table width="100%">
        <tr>
            <td width="50%">
                <div class="invoice-header">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-subtitle">Invoice #<?php echo $invoice['invoice_number']; ?></div>
                </div>
            </td>
            <td width="50%">
                <div class="company-details">
                    <div class="company-name"><?php echo COMPANY_NAME; ?></div>
                    <?php echo COMPANY_ADDRESS; ?><br>
                    Phone: <?php echo COMPANY_PHONE; ?><br>
                    Email: <?php echo COMPANY_EMAIL; ?><br>
                    Website: <?php echo COMPANY_WEBSITE; ?><br>
                    <?php if (COMPANY_TAX_ID): ?>
                    Tax ID: <?php echo COMPANY_TAX_ID; ?>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Status Stamp -->
    <?php if ($invoice['status'] == INVOICE_STATUS_PAID): ?>
    <div class="status-paid">PAID</div>
    <?php else: ?>
    <div class="status-unpaid">UNPAID</div>
    <?php endif; ?>
    
    <!-- Invoice Meta Information -->
    <table width="100%" class="invoice-meta">
        <tr>
            <td width="25%">
                <div class="invoice-meta-title">Invoice Date:</div>
                <?php echo date('F j, Y', strtotime($invoice['date'])); ?>
            </td>
            <td width="25%">
                <div class="invoice-meta-title">Due Date:</div>
                <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?>
            </td>
            <td width="25%">
                <div class="invoice-meta-title">Invoice #:</div>
                <?php echo $invoice['invoice_number']; ?>
            </td>
            <td width="25%">
                <div class="invoice-meta-title">Customer ID:</div>
                <?php echo $customer['id']; ?>
            </td>
        </tr>
    </table>
    
    <!-- Customer Information -->
    <table width="100%">
        <tr>
            <td width="50%" valign="top">
                <div class="section-title">Bill To:</div>
                <div class="customer-details">
                    <strong><?php echo $customer['name']; ?></strong><br>
                    <?php echo $customer['address']; ?><br>
                    <?php if ($customer['address2']): ?>
                    <?php echo $customer['address2']; ?><br>
                    <?php endif; ?>
                    <?php echo $customer['city']; ?>, <?php echo $customer['state']; ?> <?php echo $customer['zip']; ?><br>
                    <?php if ($customer['phone']): ?>
                    Phone: <?php echo $customer['phone']; ?><br>
                    <?php endif; ?>
                    Email: <?php echo $customer['email']; ?>
                </div>
            </td>
            <td width="50%" valign="top">
                <div class="section-title">Vehicle Information:</div>
                <div class="vehicle-details">
                    <strong><?php echo $vehicle['year']; ?> <?php echo $vehicle['make']; ?> <?php echo $vehicle['model']; ?></strong><br>
                    VIN: <?php echo $vehicle['vin']; ?><br>
                    License Plate: <?php echo $vehicle['license_plate']; ?><br>
                    Color: <?php echo $vehicle['color']; ?><br>
                    Mileage: <?php echo number_format($vehicle['mileage']); ?> miles
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Invoice Items -->
    <div class="section-title">Services & Parts:</div>
    <table width="100%">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="45%">Description</th>
                <th width="10%">Quantity</th>
                <th width="20%">Unit Price</th>
                <th width="20%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($invoice_items as $item): ?>
            <tr class="item-row">
                <td><?php echo $i++; ?></td>
                <td>
                    <strong><?php echo $item['description']; ?></strong>
                    <?php if ($item['notes']): ?>
                    <br><small><?php echo $item['notes']; ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td>$<?php echo number_format($item['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Totals -->
    <div class="total-section">
        <div class="total-row">
            <span class="total-title">Subtotal:</span>
            <span class="total-value">$<?php echo number_format($invoice['subtotal'], 2); ?></span>
        </div>
        
        <?php if ($invoice['discount'] > 0): ?>
        <div class="total-row">
            <span class="total-title">Discount (<?php echo $invoice['discount_type'] == 'percentage' ? $invoice['discount_rate'] . '%' : ''; ?>):</span>
            <span class="total-value">-$<?php echo number_format($invoice['discount'], 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="total-row">
            <span class="total-title">Tax (<?php echo $invoice['tax_rate']; ?>%):</span>
            <span class="total-value">$<?php echo number_format($invoice['tax_amount'], 2); ?></span>
        </div>
        
        <div class="total-row grand-total">
            <span class="total-title">TOTAL:</span>
            <span class="total-value">$<?php echo number_format($invoice['total'], 2); ?></span>
        </div>
        
        <?php if ($invoice['status'] == INVOICE_STATUS_PAID): ?>
        <div class="total-row">
            <span class="total-title">Amount Paid:</span>
            <span class="total-value">$<?php echo number_format($invoice['amount_paid'], 2); ?></span>
        </div>
        <div class="total-row">
            <span class="total-title">Balance Due:</span>
            <span class="total-value">$<?php echo number_format($invoice['balance'], 2); ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Payment Information -->
    <?php if ($invoice['status'] != INVOICE_STATUS_PAID): ?>
    <div class="payment-info">
        <div class="section-title">Payment Information:</div>
        <p>Please make payment by the due date: <strong><?php echo date('F j, Y', strtotime($invoice['due_date'])); ?></strong></p>
        
        <p><strong>Payment Methods:</strong></p>
        <ul>
            <li>Credit Card: Visa, MasterCard, American Express</li>
            <li>Check: Please make checks payable to "<?php echo COMPANY_NAME; ?>"</li>
            <li>Bank Transfer:
                <br>Bank: <?php echo COMPANY_BANK_NAME; ?>
                <br>Account: <?php echo COMPANY_BANK_ACCOUNT; ?>
                <br>Routing: <?php echo COMPANY_BANK_ROUTING; ?>
            </li>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Notes -->
    <?php if ($invoice['notes']): ?>
    <div class="notes">
        <div class="section-title">Notes:</div>
        <p><?php echo $invoice['notes']; ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Terms and Conditions -->
    <div class="notes">
        <div class="section-title">Terms & Conditions:</div>
        <p>1. Payment is due within <?php echo INVOICE_PAYMENT_TERMS; ?> days of invoice date.</p>
        <p>2. Late payments are subject to a <?php echo INVOICE_LATE_FEE_PERCENTAGE; ?>% fee.</p>
        <p>3. All services and parts come with a <?php echo SERVICE_WARRANTY_PERIOD; ?>-day warranty.</p>
        <p>4. Returns and exchanges must be made within <?php echo RETURN_PERIOD; ?> days of service.</p>
    </div>
    
    <!-- Barcode for scanning -->
    <div class="barcode">
        <!-- TCPDF barcode generation -->
        <!-- $pdf->write1DBarcode($invoice['invoice_number'], 'C128', '', '', '', 18, 0.4, $style, 'N'); -->
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business! | Invoice generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>© <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
    </div>
</div>
