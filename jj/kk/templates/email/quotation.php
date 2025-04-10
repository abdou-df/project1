<?php
/**
 * Email template for quotations
 * Used when sending price quotations to customers
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
    <title>Quotation #<?php echo $quotation['quotation_number']; ?> - Garage Management System</title>
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
            background-color: #9b59b6;
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
            background-color: #9b59b6;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .important {
            color: #e74c3c;
            font-weight: bold;
        }
        .note {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #9b59b6;
            margin: 20px 0;
        }
        .validity {
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quotation #<?php echo $quotation['quotation_number']; ?></h1>
        </div>
        
        <div class="content">
            <p>Dear <?php echo $customer['name']; ?>,</p>
            
            <p>Thank you for your inquiry. We are pleased to provide you with the following quotation for the requested services for your vehicle.</p>
            
            <h2>Quotation Details</h2>
            <p>
                <strong>Quotation Number:</strong> <?php echo $quotation['quotation_number']; ?><br>
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($quotation['date'])); ?><br>
                <strong>Valid Until:</strong> <?php echo date('F j, Y', strtotime($quotation['valid_until'])); ?>
            </p>
            
            <h2>Vehicle Information</h2>
            <p>
                <strong>Make:</strong> <?php echo $vehicle['make']; ?><br>
                <strong>Model:</strong> <?php echo $vehicle['model']; ?><br>
                <strong>Year:</strong> <?php echo $vehicle['year']; ?><br>
                <strong>License Plate:</strong> <?php echo $vehicle['license_plate']; ?><br>
                <strong>VIN:</strong> <?php echo $vehicle['vin']; ?>
            </p>
            
            <h2>Proposed Services & Parts</h2>
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
                    <?php foreach ($quotation_items as $item): ?>
                    <tr>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>$<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" align="right">Subtotal:</td>
                        <td>$<?php echo number_format($quotation['subtotal'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" align="right">Tax (<?php echo $quotation['tax_rate']; ?>%):</td>
                        <td>$<?php echo number_format($quotation['tax_amount'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" align="right">Total:</td>
                        <td>$<?php echo number_format($quotation['total'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="note">
                <h3>Additional Information</h3>
                <p><?php echo $quotation['notes']; ?></p>
            </div>
            
            <p class="validity">This quotation is valid until <?php echo date('F j, Y', strtotime($quotation['valid_until'])); ?>. Prices may change after this date.</p>
            
            <p>To accept this quotation and schedule the service, please click the button below:</p>
            
            <a href="<?php echo $accept_link; ?>" class="btn">Accept Quotation</a>
            
            <p>If you would like to discuss any aspect of this quotation or have any questions, please don't hesitate to contact us at <?php echo COMPANY_PHONE; ?> or reply to this email.</p>
            
            <p>
                Best regards,<br>
                <?php echo $quotation['prepared_by']; ?><br>
                <?php echo COMPANY_NAME; ?>
            </p>
            
            <p class="important">Terms and Conditions:</p>
            <ul>
                <li>This quotation is subject to our standard terms and conditions.</li>
                <li>Additional work found necessary during the service will be quoted separately.</li>
                <li>Estimated completion time: <?php echo $quotation['estimated_time']; ?> hours.</li>
                <li>Payment terms: <?php echo $quotation['payment_terms']; ?>.</li>
            </ul>
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
