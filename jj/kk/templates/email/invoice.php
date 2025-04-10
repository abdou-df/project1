<?php
/**
 * Email template for invoices
 * Used when sending invoice notifications to customers
 */

// Prevent direct access
if (!defined('GARAGE_CMS')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice['invoice_number']; ?> - Garage Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3498db;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .important {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice #<?php echo $invoice['invoice_number']; ?></h1>
        </div>
        
        <div class="content">
            <p>Dear <?php echo $customer['name']; ?>,</p>
            
            <p>We hope this email finds you well. Please find attached your invoice for services rendered at our garage.</p>
            
            <h2>Invoice Details</h2>
            <p>
                <strong>Invoice Number:</strong> <?php echo $invoice['invoice_number']; ?><br>
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['date'])); ?><br>
                <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?><br>
                <strong>Status:</strong> <?php echo $invoice['status']; ?>
            </p>
            
            <h2>Vehicle Information</h2>
            <p>
                <strong>Make:</strong> <?php echo $vehicle['make']; ?><br>
                <strong>Model:</strong> <?php echo $vehicle['model']; ?><br>
                <strong>Year:</strong> <?php echo $vehicle['year']; ?><br>
                <strong>License Plate:</strong> <?php echo $vehicle['license_plate']; ?>
            </p>
            
            <h2>Services & Parts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice_items as $item): ?>
                    <tr>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>$<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" align="right">Subtotal:</td>
                        <td>$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" align="right">Tax (<?php echo $invoice['tax_rate']; ?>%):</td>
                        <td>$<?php echo number_format($invoice['tax_amount'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" align="right">Total:</td>
                        <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <?php if ($invoice['status'] == INVOICE_STATUS_UNPAID): ?>
            <p class="important">This invoice is due on <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?>. Please ensure timely payment to avoid any late fees.</p>
            
            <p>You can pay your invoice by clicking the button below:</p>
            
            <a href="<?php echo $payment_link; ?>" class="btn">Pay Now</a>
            <?php else: ?>
            <p>Thank you for your payment. This invoice has been fully paid.</p>
            <?php endif; ?>
            
            <p>If you have any questions regarding this invoice, please don't hesitate to contact us at <?php echo COMPANY_PHONE; ?> or reply to this email.</p>
            
            <p>Thank you for choosing our services!</p>
            
            <p>
                Best regards,<br>
                <?php echo COMPANY_NAME; ?> Team
            </p>
        </div>
        
        <div class="footer">
            <p>
                <?php echo COMPANY_NAME; ?><br>
                <?php echo COMPANY_ADDRESS; ?><br>
                <?php echo COMPANY_PHONE; ?><br>
                <?php echo COMPANY_EMAIL; ?>
            </p>
            <p>© <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
