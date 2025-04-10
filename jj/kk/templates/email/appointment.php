<?php
/**
 * Email template for appointments
 * Used when sending appointment confirmations and reminders to customers
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
    <title>Appointment Confirmation - Garage Management System</title>
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
            background-color: #27ae60;
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
        .appointment-details {
            background-color: #f2f2f2;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #27ae60;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn-cancel {
            background-color: #e74c3c;
        }
        .btn-reschedule {
            background-color: #f39c12;
        }
        .important {
            color: #e74c3c;
            font-weight: bold;
        }
        .calendar-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $is_reminder ? 'Appointment Reminder' : 'Appointment Confirmation'; ?></h1>
        </div>
        
        <div class="content">
            <p>Dear <?php echo $customer['name']; ?>,</p>
            
            <?php if ($is_reminder): ?>
            <p>This is a friendly reminder about your upcoming appointment at our garage.</p>
            <?php else: ?>
            <p>Thank you for scheduling an appointment with us. Your appointment has been confirmed.</p>
            <?php endif; ?>
            
            <div class="appointment-details">
                <h2>Appointment Details</h2>
                <p>
                    <strong>Service:</strong> <?php echo $appointment['service']; ?><br>
                    <strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($appointment['date'])); ?><br>
                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['time'])); ?><br>
                    <strong>Duration:</strong> Approximately <?php echo $appointment['duration']; ?> minutes<br>
                    <strong>Technician:</strong> <?php echo $appointment['technician']; ?>
                </p>
                
                <h2>Vehicle Information</h2>
                <p>
                    <strong>Make:</strong> <?php echo $vehicle['make']; ?><br>
                    <strong>Model:</strong> <?php echo $vehicle['model']; ?><br>
                    <strong>Year:</strong> <?php echo $vehicle['year']; ?><br>
                    <strong>License Plate:</strong> <?php echo $vehicle['license_plate']; ?>
                </p>
            </div>
            
            <h2>Location</h2>
            <p>
                <?php echo COMPANY_NAME; ?><br>
                <?php echo COMPANY_ADDRESS; ?><br>
                <?php echo COMPANY_PHONE; ?>
            </p>
            
            <?php if ($appointment['notes']): ?>
            <h2>Additional Notes</h2>
            <p><?php echo $appointment['notes']; ?></p>
            <?php endif; ?>
            
            <h2>Preparation</h2>
            <p>To ensure a smooth service experience, please:</p>
            <ul>
                <li>Arrive 10 minutes before your scheduled appointment time</li>
                <li>Bring your vehicle registration and identification</li>
                <li>Remove any valuable personal items from your vehicle</li>
                <li>Make a note of your current mileage</li>
            </ul>
            
            <p>You can add this appointment to your calendar by clicking the button below:</p>
            <a href="<?php echo $calendar_link; ?>" class="btn">Add to Calendar</a>
            
            <p>Need to make changes? You can manage your appointment online or contact us directly:</p>
            <div style="margin-top: 15px;">
                <a href="<?php echo $reschedule_link; ?>" class="btn btn-reschedule">Reschedule</a>
                <a href="<?php echo $cancel_link; ?>" class="btn btn-cancel">Cancel</a>
            </div>
            
            <p class="important">Please note: If you need to cancel or reschedule, please do so at least 24 hours in advance to avoid any cancellation fees.</p>
            
            <p>If you have any questions or need further assistance, please don't hesitate to contact us at <?php echo COMPANY_PHONE; ?> or reply to this email.</p>
            
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
