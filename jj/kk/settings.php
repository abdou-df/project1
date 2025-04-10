<?php
/**
 * Settings Page
 * Allows administrators to configure system settings
 */

// Start session and include required files
//ession_start();
//require_once '../config/config.php';
//require_once '../includes/functions.php';
//require_once '../includes/auth.php';

require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';




// Check if user is logged in and has admin privileges
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
/*
if (!isAdmin()) {
    header('Location: 403.php');
    exit;
}
*/
// Initialize variables
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which settings form was submitted
    if (isset($_POST['update_general_settings'])) {
        // Update general settings
        $company_name = sanitize($_POST['company_name']);
        $company_address = sanitize($_POST['company_address']);
        $company_phone = sanitize($_POST['company_phone']);
        $company_email = sanitize($_POST['company_email']);
        $company_website = sanitize($_POST['company_website']);
        $company_tax_id = sanitize($_POST['company_tax_id']);
        
        // Validate required fields
        if (empty($company_name) || empty($company_address) || empty($company_phone) || empty($company_email)) {
            $error_message = 'Please fill in all required fields.';
        } else {
            // Update settings in database
            // In a real application, you would update these in the database
            // For now, we'll just show a success message
            $success_message = 'General settings updated successfully.';
        }
    } elseif (isset($_POST['update_email_settings'])) {
        // Update email settings
        $smtp_host = sanitize($_POST['smtp_host']);
        $smtp_port = (int)$_POST['smtp_port'];
        $smtp_username = sanitize($_POST['smtp_username']);
        $smtp_password = $_POST['smtp_password']; // Note: In production, handle password securely
        $smtp_encryption = sanitize($_POST['smtp_encryption']);
        $email_from_name = sanitize($_POST['email_from_name']);
        $email_from_address = sanitize($_POST['email_from_address']);
        
        // Validate required fields
        if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($email_from_address)) {
            $error_message = 'Please fill in all required fields.';
        } else {
            // Update settings in database
            $success_message = 'Email settings updated successfully.';
        }
    } elseif (isset($_POST['update_invoice_settings'])) {
        // Update invoice settings
        $invoice_prefix = sanitize($_POST['invoice_prefix']);
        $invoice_next_number = (int)$_POST['invoice_next_number'];
        $invoice_tax_rate = (float)$_POST['invoice_tax_rate'];
        $invoice_payment_terms = (int)$_POST['invoice_payment_terms'];
        $invoice_late_fee_percentage = (float)$_POST['invoice_late_fee_percentage'];
        
        // Validate required fields
        if (empty($invoice_prefix) || $invoice_next_number <= 0 || $invoice_tax_rate < 0) {
            $error_message = 'Please fill in all required fields with valid values.';
        } else {
            // Update settings in database
            $success_message = 'Invoice settings updated successfully.';
        }
    } elseif (isset($_POST['update_backup_settings'])) {
        // Update backup settings
        $backup_frequency = sanitize($_POST['backup_frequency']);
        $backup_retention = (int)$_POST['backup_retention'];
        $backup_location = sanitize($_POST['backup_location']);
        
        // Update settings in database
        $success_message = 'Backup settings updated successfully.';
    }
}

// Load current settings
// In a real application, you would fetch these from the database
$settings = [
    'general' => [
        'company_name' => COMPANY_NAME,
        'company_address' => COMPANY_ADDRESS,
        'company_phone' => COMPANY_PHONE,
        'company_email' => COMPANY_EMAIL,
        'company_website' => COMPANY_WEBSITE,
        'company_tax_id' => COMPANY_TAX_ID,
    ],
    'email' => [
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'user@example.com',
        'smtp_password' => '********',
        'smtp_encryption' => 'tls',
        'email_from_name' => COMPANY_NAME,
        'email_from_address' => COMPANY_EMAIL,
    ],
    'invoice' => [
        'invoice_prefix' => 'INV-',
        'invoice_next_number' => 1001,
        'invoice_tax_rate' => 15.0,
        'invoice_payment_terms' => 30,
        'invoice_late_fee_percentage' => 5.0,
    ],
    'backup' => [
        'backup_frequency' => 'daily',
        'backup_retention' => 30,
        'backup_location' => 'local',
    ],
];

// Include header
$page_title = 'System Settings';
//include_once '../includes/header.php';
require_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">System Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check"></i> Success!</h5>
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="settings-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-tab" data-toggle="pill" href="#email" role="tab" aria-controls="email" aria-selected="false">Email</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="invoice-tab" data-toggle="pill" href="#invoice" role="tab" aria-controls="invoice" aria-selected="false">Invoice</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="backup-tab" data-toggle="pill" href="#backup" role="tab" aria-controls="backup" aria-selected="false">Backup</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settings-tabContent">
                        <!-- General Settings Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $settings['general']['company_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_address">Company Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="3" required><?php echo $settings['general']['company_address']; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="company_phone">Company Phone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?php echo $settings['general']['company_phone']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_email">Company Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="company_email" name="company_email" value="<?php echo $settings['general']['company_email']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_website">Company Website</label>
                                    <input type="url" class="form-control" id="company_website" name="company_website" value="<?php echo $settings['general']['company_website']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="company_tax_id">Tax ID / VAT Number</label>
                                    <input type="text" class="form-control" id="company_tax_id" name="company_tax_id" value="<?php echo $settings['general']['company_tax_id']; ?>">
                                </div>
                                <button type="submit" name="update_general_settings" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                        
                        <!-- Email Settings Tab -->
                        <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="smtp_host">SMTP Host <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo $settings['email']['smtp_host']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="smtp_port">SMTP Port <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo $settings['email']['smtp_port']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="smtp_username">SMTP Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo $settings['email']['smtp_username']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="smtp_password">SMTP Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo $settings['email']['smtp_password']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="smtp_encryption">Encryption</label>
                                    <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                        <option value="none" <?php echo $settings['email']['smtp_encryption'] == 'none' ? 'selected' : ''; ?>>None</option>
                                        <option value="ssl" <?php echo $settings['email']['smtp_encryption'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="tls" <?php echo $settings['email']['smtp_encryption'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="email_from_name">From Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="email_from_name" name="email_from_name" value="<?php echo $settings['email']['email_from_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email_from_address">From Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email_from_address" name="email_from_address" value="<?php echo $settings['email']['email_from_address']; ?>" required>
                                </div>
                                <button type="submit" name="update_email_settings" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-info" id="test_email">Test Email</button>
                            </form>
                        </div>
                        
                        <!-- Invoice Settings Tab -->
                        <div class="tab-pane fade" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="invoice_prefix">Invoice Number Prefix <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" value="<?php echo $settings['invoice']['invoice_prefix']; ?>" required>
                                    <small class="form-text text-muted">Example: INV-</small>
                                </div>
                                <div class="form-group">
                                    <label for="invoice_next_number">Next Invoice Number <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="invoice_next_number" name="invoice_next_number" value="<?php echo $settings['invoice']['invoice_next_number']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="invoice_tax_rate">Default Tax Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="invoice_tax_rate" name="invoice_tax_rate" value="<?php echo $settings['invoice']['invoice_tax_rate']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="invoice_payment_terms">Payment Terms (days) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="invoice_payment_terms" name="invoice_payment_terms" value="<?php echo $settings['invoice']['invoice_payment_terms']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="invoice_late_fee_percentage">Late Fee Percentage <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="invoice_late_fee_percentage" name="invoice_late_fee_percentage" value="<?php echo $settings['invoice']['invoice_late_fee_percentage']; ?>" required>
                                </div>
                                <button type="submit" name="update_invoice_settings" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                        
                        <!-- Backup Settings Tab -->
                        <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="backup_frequency">Backup Frequency</label>
                                    <select class="form-control" id="backup_frequency" name="backup_frequency">
                                        <option value="daily" <?php echo $settings['backup']['backup_frequency'] == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo $settings['backup']['backup_frequency'] == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo $settings['backup']['backup_frequency'] == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="backup_retention">Backup Retention (days)</label>
                                    <input type="number" class="form-control" id="backup_retention" name="backup_retention" value="<?php echo $settings['backup']['backup_retention']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="backup_location">Backup Location</label>
                                    <select class="form-control" id="backup_location" name="backup_location">
                                        <option value="local" <?php echo $settings['backup']['backup_location'] == 'local' ? 'selected' : ''; ?>>Local</option>
                                        <option value="cloud" <?php echo $settings['backup']['backup_location'] == 'cloud' ? 'selected' : ''; ?>>Cloud</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_backup_settings" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-success" id="run_backup">Run Backup Now</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test email button
    const testEmailBtn = document.getElementById('test_email');
    if (testEmailBtn) {
        testEmailBtn.addEventListener('click', function() {
            // Show loading
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            this.disabled = true;
            
            // Send AJAX request to test email
            fetch('../ajax/settings.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=test_email'
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                this.innerHTML = 'Test Email';
                this.disabled = false;
                
                // Show result
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Error sending test email: ' + data.message);
                }
            })
            .catch(error => {
                // Reset button
                this.innerHTML = 'Test Email';
                this.disabled = false;
                
                // Show error
                alert('Error sending test email: ' + error.message);
            });
        });
    }
    
    // Run backup button
    const runBackupBtn = document.getElementById('run_backup');
    if (runBackupBtn) {
        runBackupBtn.addEventListener('click', function() {
            // Show loading
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...';
            this.disabled = true;
            
            // Send AJAX request to run backup
            fetch('../ajax/settings.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=run_backup'
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                this.innerHTML = 'Run Backup Now';
                this.disabled = false;
                
                // Show result
                if (data.success) {
                    alert('Backup completed successfully!');
                } else {
                    alert('Error running backup: ' + data.message);
                }
            })
            .catch(error => {
                // Reset button
                this.innerHTML = 'Run Backup Now';
                this.disabled = false;
                
                // Show error
                alert('Error running backup: ' + error.message);
            });
        });
    }
});
</script>

<?php 




//include_once '../includes/footer.php';
require_once dirname(__FILE__) . '/../includes/footer.php';

?>
