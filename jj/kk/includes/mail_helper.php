<?php
/**
 * Mail Helper
 * Provides helper functions for sending emails using PHPMailer
 */

// Include PHPMailer library
require_once '../vendor/phpmailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $alt_body Plain text alternative body
 * @param array $attachments Optional attachments (array of paths)
 * @param array $cc Optional CC recipients (array of emails)
 * @param array $bcc Optional BCC recipients (array of emails)
 * @return bool Success status
 */
function sendEmail($to, $subject, $body, $alt_body = '', $attachments = [], $cc = [], $bcc = []) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Get email settings from database or config
        $settings = getEmailSettings();
        
        // Server settings
        if ($settings['smtp_enabled']) {
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->Port = $settings['smtp_port'];
            $mail->SMTPAuth = $settings['smtp_auth'];
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            $mail->SMTPSecure = $settings['smtp_secure'];
        } else {
            $mail->isMail();
        }
        
        // Sender
        $mail->From = $settings['from_email'];
        $mail->FromName = $settings['from_name'];
        
        // Recipients
        $mail->addAddress($to);
        
        // CC
        if (!empty($cc)) {
            foreach ($cc as $cc_address) {
                $mail->addCC($cc_address);
            }
        }
        
        // BCC
        if (!empty($bcc)) {
            foreach ($bcc as $bcc_address) {
                $mail->addBCC($bcc_address);
            }
        }
        
        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $alt_body ? $alt_body : strip_tags($body);
        
        // Send email
        $mail->send();
        
        // Log email
        logEmail($to, $subject, true);
        
        return true;
    } catch (Exception $e) {
        // Log error
        logEmail($to, $subject, false, $mail->ErrorInfo);
        
        return false;
    }
}

/**
 * Get email settings
 * 
 * @return array Email settings
 */
function getEmailSettings() {
    // In a real implementation, these would be retrieved from the database or config file
    return [
        'smtp_enabled' => true,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_auth' => true,
        'smtp_username' => 'user@example.com',
        'smtp_password' => 'password',
        'smtp_secure' => 'tls',
        'from_email' => 'garage@example.com',
        'from_name' => 'Garage Management System'
    ];
}

/**
 * Log email
 * 
 * @param string $recipient Recipient email
 * @param string $subject Email subject
 * @param bool $success Success status
 * @param string $error_message Optional error message
 * @return void
 */
function logEmail($recipient, $subject, $success, $error_message = '') {
    // In a real implementation, this would log to a database or file
    $log_entry = [
        'date' => date('Y-m-d H:i:s'),
        'recipient' => $recipient,
        'subject' => $subject,
        'success' => $success,
        'error_message' => $error_message
    ];
    
    // For now, just log to a file
    $log_file = '../logs/email.log';
    $log_dir = dirname($log_file);
    
    // Create log directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Append to log file
    file_put_contents(
        $log_file,
        date('Y-m-d H:i:s') . ' | ' . 
        ($success ? 'SUCCESS' : 'FAILED') . ' | ' . 
        $recipient . ' | ' . 
        $subject . 
        ($error_message ? ' | ' . $error_message : '') . 
        PHP_EOL,
        FILE_APPEND
    );
}

/**
 * Send appointment confirmation email
 * 
 * @param int $appointment_id Appointment ID
 * @return bool Success status
 */
function sendAppointmentConfirmationEmail($appointment_id) {
    // Get appointment data
    $appointment = new Appointment();
    if (!$appointment->getById($appointment_id)) {
        return false;
    }
    
    // Get customer data
    $customer = new Customer();
    if (!$customer->getById($appointment->getCustomerId())) {
        return false;
    }
    
    // Get vehicle data
    $vehicle = new Vehicle();
    if (!$vehicle->getById($appointment->getVehicleId())) {
        return false;
    }
    
    // Get service data
    $service = new Service();
    if (!$service->getById($appointment->getServiceId())) {
        return false;
    }
    
    // Email subject
    $subject = 'Appointment Confirmation - ' . date('d/m/Y', strtotime($appointment->getAppointmentDate()));
    
    // Email body
    $body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { width: 600px; margin: 0 auto; }
            .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; }
            h1 { color: #444; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Appointment Confirmation</h1>
            </div>
            <div class="content">
                <p>Dear ' . $customer->getName() . ',</p>
                <p>Your appointment has been confirmed with the following details:</p>
                
                <h2>Appointment Details</h2>
                <table>
                    <tr>
                        <th>Date</th>
                        <td>' . date('d/m/Y', strtotime($appointment->getAppointmentDate())) . '</td>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td>' . date('H:i', strtotime($appointment->getAppointmentTime())) . '</td>
                    </tr>
                    <tr>
                        <th>Service</th>
                        <td>' . $service->getName() . '</td>
                    </tr>
                    <tr>
                        <th>Estimated Duration</th>
                        <td>' . $service->getDuration() . ' minutes</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>' . $appointment->getStatus() . '</td>
                    </tr>
                </table>
                
                <h2>Vehicle Details</h2>
                <table>
                    <tr>
                        <th>Make/Model</th>
                        <td>' . $vehicle->getMake() . ' ' . $vehicle->getModel() . '</td>
                    </tr>
                    <tr>
                        <th>Year</th>
                        <td>' . $vehicle->getYear() . '</td>
                    </tr>
                    <tr>
                        <th>License Plate</th>
                        <td>' . $vehicle->getLicensePlate() . '</td>
                    </tr>
                </table>
                
                <p>If you need to reschedule or cancel your appointment, please contact us at least 24 hours in advance.</p>
                
                <p>Thank you for choosing our garage!</p>
                
                <p>Best regards,<br>
                Garage Management System</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' Garage Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return sendEmail($customer->getEmail(), $subject, $body);
}

/**
 * Send invoice email
 * 
 * @param int $invoice_id Invoice ID
 * @return bool Success status
 */
function sendInvoiceEmail($invoice_id) {
    // Get invoice data
    $invoice = new Invoice();
    if (!$invoice->getById($invoice_id)) {
        return false;
    }
    
    // Get customer data
    $customer = new Customer();
    if (!$customer->getById($invoice->getCustomerId())) {
        return false;
    }
    
    // Generate PDF
    $pdf_content = generateInvoicePDF($invoice_id);
    if (!$pdf_content) {
        return false;
    }
    
    // Save PDF to temporary file
    $temp_file = '../temp/invoice_' . $invoice_id . '.pdf';
    $temp_dir = dirname($temp_file);
    
    // Create temp directory if it doesn't exist
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0755, true);
    }
    
    // Write PDF to file
    file_put_contents($temp_file, $pdf_content);
    
    // Email subject
    $subject = 'Invoice #' . $invoice->getInvoiceNumber();
    
    // Email body
    $body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { width: 600px; margin: 0 auto; }
            .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; }
            h1 { color: #444; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Invoice #' . $invoice->getInvoiceNumber() . '</h1>
            </div>
            <div class="content">
                <p>Dear ' . $customer->getName() . ',</p>
                <p>Please find attached your invoice #' . $invoice->getInvoiceNumber() . ' for services rendered on ' . date('d/m/Y', strtotime($invoice->getInvoiceDate())) . '.</p>
                
                <p><strong>Invoice Details:</strong></p>
                <ul>
                    <li>Invoice Number: ' . $invoice->getInvoiceNumber() . '</li>
                    <li>Date: ' . date('d/m/Y', strtotime($invoice->getInvoiceDate())) . '</li>
                    <li>Due Date: ' . date('d/m/Y', strtotime($invoice->getDueDate())) . '</li>
                    <li>Total Amount: $' . number_format($invoice->getTotalAmount(), 2) . '</li>
                </ul>
                
                <p>The invoice is attached as a PDF file. You can also view your invoice online by clicking the button below:</p>
                
                <p style="text-align: center;">
                    <a href="https://example.com/invoices/view/' . $invoice_id . '" class="button">View Invoice Online</a>
                </p>
                
                <p>Payment is due by ' . date('d/m/Y', strtotime($invoice->getDueDate())) . '. Please make your payment promptly to avoid late fees.</p>
                
                <p>Thank you for your business!</p>
                
                <p>Best regards,<br>
                Garage Management System</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' Garage Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email with attachment
    $result = sendEmail($customer->getEmail(), $subject, $body, '', [$temp_file]);
    
    // Delete temporary file
    unlink($temp_file);
    
    return $result;
}

/**
 * Send low stock notification email
 * 
 * @param array $low_stock_items Array of low stock items
 * @return bool Success status
 */
function sendLowStockNotificationEmail($low_stock_items) {
    if (empty($low_stock_items)) {
        return false;
    }
    
    // Get inventory manager email
    $inventory_manager_email = getInventoryManagerEmail();
    
    // Email subject
    $subject = 'Low Stock Notification - ' . count($low_stock_items) . ' items';
    
    // Build table rows
    $table_rows = '';
    foreach ($low_stock_items as $item) {
        $table_rows .= '
        <tr>
            <td>' . $item['part_number'] . '</td>
            <td>' . $item['name'] . '</td>
            <td>' . $item['category'] . '</td>
            <td>' . $item['quantity'] . '</td>
            <td>' . $item['reorder_level'] . '</td>
            <td>' . $item['supplier_name'] . '</td>
        </tr>';
    }
    
    // Email body
    $body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { width: 800px; margin: 0 auto; }
            .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; }
            h1 { color: #444; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
            .warning { color: #f44336; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 class="warning">Low Stock Notification</h1>
            </div>
            <div class="content">
                <p>The following items are currently below their reorder levels and need to be restocked:</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Part #</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Current Quantity</th>
                            <th>Reorder Level</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $table_rows . '
                    </tbody>
                </table>
                
                <p>Please take appropriate action to restock these items as soon as possible.</p>
                
                <p>This is an automated notification from the Garage Management System.</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' Garage Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return sendEmail($inventory_manager_email, $subject, $body);
}

/**
 * Get inventory manager email
 * 
 * @return string Inventory manager email
 */
function getInventoryManagerEmail() {
    // In a real implementation, this would get the email of the inventory manager from the database
    // For now, return a default email
    return 'inventory@example.com';
}

/**
 * Send appointment reminder email
 * 
 * @param int $appointment_id Appointment ID
 * @return bool Success status
 */
function sendAppointmentReminderEmail($appointment_id) {
    // Get appointment data
    $appointment = new Appointment();
    if (!$appointment->getById($appointment_id)) {
        return false;
    }
    
    // Get customer data
    $customer = new Customer();
    if (!$customer->getById($appointment->getCustomerId())) {
        return false;
    }
    
    // Get vehicle data
    $vehicle = new Vehicle();
    if (!$vehicle->getById($appointment->getVehicleId())) {
        return false;
    }
    
    // Get service data
    $service = new Service();
    if (!$service->getById($appointment->getServiceId())) {
        return false;
    }
    
    // Email subject
    $subject = 'Appointment Reminder - Tomorrow at ' . date('H:i', strtotime($appointment->getAppointmentTime()));
    
    // Email body
    $body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { width: 600px; margin: 0 auto; }
            .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; }
            h1 { color: #444; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
            .cancel-button { background-color: #f44336; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Appointment Reminder</h1>
            </div>
            <div class="content">
                <p>Dear ' . $customer->getName() . ',</p>
                <p>This is a friendly reminder that you have an appointment scheduled for tomorrow:</p>
                
                <h2>Appointment Details</h2>
                <table>
                    <tr>
                        <th>Date</th>
                        <td>' . date('d/m/Y', strtotime($appointment->getAppointmentDate())) . '</td>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td>' . date('H:i', strtotime($appointment->getAppointmentTime())) . '</td>
                    </tr>
                    <tr>
                        <th>Service</th>
                        <td>' . $service->getName() . '</td>
                    </tr>
                    <tr>
                        <th>Estimated Duration</th>
                        <td>' . $service->getDuration() . ' minutes</td>
                    </tr>
                </table>
                
                <h2>Vehicle Details</h2>
                <table>
                    <tr>
                        <th>Make/Model</th>
                        <td>' . $vehicle->getMake() . ' ' . $vehicle->getModel() . '</td>
                    </tr>
                    <tr>
                        <th>Year</th>
                        <td>' . $vehicle->getYear() . '</td>
                    </tr>
                    <tr>
                        <th>License Plate</th>
                        <td>' . $vehicle->getLicensePlate() . '</td>
                    </tr>
                </table>
                
                <p>Please arrive 10 minutes before your scheduled appointment time.</p>
                
                <p>If you need to reschedule or cancel your appointment, please click one of the buttons below:</p>
                
                <p style="text-align: center;">
                    <a href="https://example.com/appointments/reschedule/' . $appointment_id . '" class="button">Reschedule Appointment</a>
                    &nbsp;&nbsp;
                    <a href="https://example.com/appointments/cancel/' . $appointment_id . '" class="button cancel-button">Cancel Appointment</a>
                </p>
                
                <p>Thank you for choosing our garage!</p>
                
                <p>Best regards,<br>
                Garage Management System</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' Garage Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return sendEmail($customer->getEmail(), $subject, $body);
}
?>
