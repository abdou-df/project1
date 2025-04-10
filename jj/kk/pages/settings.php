<?php
/**
 * Settings Page
 * Allows administrators to configure system settings
 */

// Start session and include required files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// Check if user is logged in and has admin privileges

// Initialize variables
$success_message = '';
$error_message = '';
$pdo = null; // PLACEHOLDER: Assign your PDO or mysqli connection object here

// --- Define available settings sections ---
$available_sections = [
    'general-settings', 
    'other-settings', 
    'email-setting', 
    'access-rights', 
    'business-hours', 
    'stripe-settings', 
    'branch-setting'
];

// --- Determine active section ---
$active_section = $_GET['section'] ?? 'general-settings';
if (!in_array($active_section, $available_sections)) {
    $active_section = 'general-settings'; // Default to general if invalid section requested
}


// --- Helper function to get settings (Replace with your actual implementation) ---
function get_setting($key, $default = null) {
    global $pdo; // Use your DB connection object
    // PLACEHOLDER: Replace with your actual DB query to fetch a setting value by its key
    // Example using PDO:
    /*
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings_table WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return ($result !== false) ? $result : $default;
    } catch (PDOException $e) {
        // Log error or handle appropriately
        return $default;
    }
    */
    // --- Mock implementation for demonstration ---
    $mock_settings = [
        'company_name' => 'My Garage',
        'company_address' => '123 Main St, Anytown',
        'company_phone' => '555-1234',
        'company_email' => 'info@mygarage.com',
        'company_website' => 'https://mygarage.com',
        'company_tax_id' => 'VAT12345',
        'company_logo_path' => '/jj/kk/assets/uploads/logos/default_logo.png', // Example path
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380001',
        'timezone' => 'Asia/Kolkata',
        'language' => 'en',
        'date_format' => 'yyyy-mm-dd',
        'currency' => 'INR',
        'currency_position' => 'before',
        'theme_mode' => 'light',
        'accent_color' => '#4361ee',
        'rtl_support' => 'false',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_username' => 'user@example.com',
        // 'smtp_password' => '********', 
        'smtp_encryption' => 'tls',
        'email_from_name' => 'My Garage Notifications',
        'email_from_address' => 'noreply@mygarage.com'
    ];
    return isset($mock_settings[$key]) ? $mock_settings[$key] : $default;
    // --- End Mock implementation ---
}

// --- Helper function to update settings (Replace with your actual implementation) ---
function update_setting($key, $value) {
    global $pdo; // Use your DB connection object
    // PLACEHOLDER: Replace with your actual DB query to update or insert a setting
    // Example using PDO (UPSERT logic might be needed):
    /*
    try {
        $stmt = $pdo->prepare("INSERT INTO settings_table (setting_key, setting_value) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        // Log error or handle appropriately
        return false; 
    }
    */
    // --- Mock implementation for demonstration ---
     error_log("Mock Update Setting: {$key} = {$value}"); // Log mock updates
     return true; // Assume success
    // --- End Mock implementation ---
}

// --- Handle form submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_successful = false; 
    $submitted_section = $active_section; // Assume submission came from the currently active section
    
    // --- Update General Settings ---
    if (isset($_POST['update_general_settings'])) {
        $submitted_section = 'general-settings';
        $company_name = sanitize($_POST['company_name']);
        $company_address = sanitize($_POST['company_address']);
        $company_phone = sanitize($_POST['company_phone']);
        $company_email = sanitize($_POST['company_email']);
        $company_website = sanitize($_POST['company_website'] ?? '');
        $company_tax_id = sanitize($_POST['company_tax_id'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $postal_code = sanitize($_POST['postal_code'] ?? '');
        
        // Validate required fields
        if (empty($company_name) || empty($company_address) || empty($company_phone) || empty($company_email)) {
            $error_message = 'Please fill in all required business information fields.';
        } else {
            // Handle Logo Upload
            $logo_path = get_setting('company_logo_path'); // Get current logo path
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = dirname(__FILE__) . '/../assets/uploads/logos/'; // Ensure this directory exists and is writable
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
                $max_size = 1 * 1024 * 1024; // 1MB

                if (in_array($_FILES['company_logo']['type'], $allowed_types) && $_FILES['company_logo']['size'] <= $max_size) {
                    $file_extension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'company_logo_' . time() . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $destination)) {
                        $logo_path = '/jj/kk/assets/uploads/logos/' . $new_filename; // Relative web path to store
                    } else {
                        $error_message = 'Failed to upload company logo.';
                    }
                } else {
                    $error_message = 'Invalid file type or size for company logo (Max 1MB, PNG/JPG/GIF).';
                }
            }

            // Update settings in database if no upload error occurred
            if (empty($error_message)) {
                $update_success = true;
                $update_success &= update_setting('company_name', $company_name);
                $update_success &= update_setting('company_address', $company_address);
                $update_success &= update_setting('company_phone', $company_phone);
                $update_success &= update_setting('company_email', $company_email);
                $update_success &= update_setting('company_website', $company_website);
                $update_success &= update_setting('company_tax_id', $company_tax_id);
                $update_success &= update_setting('company_logo_path', $logo_path); 
                $update_success &= update_setting('city', $city);
                $update_success &= update_setting('state', $state);
                $update_success &= update_setting('postal_code', $postal_code);

                if ($update_success) {
            $success_message = 'General settings updated successfully!';
                    $action_successful = true;
                } else {
                    $error_message = 'Failed to update one or more general settings in the database.';
                }
            }
        }
    } 
    // --- Update Localization Settings ---
    elseif (isset($_POST['update_other_settings'])) {
        $submitted_section = 'other-settings';
        $timezone = sanitize($_POST['timezone']);
        $language = sanitize($_POST['language']);
        $date_format = sanitize($_POST['date_format']);
        $currency = sanitize($_POST['currency']);
        $currency_position = sanitize($_POST['currency_position']);
        $rtl_support = isset($_POST['rtl_support']) ? 'true' : 'false'; 
        $theme_mode = sanitize($_POST['theme_mode'] ?? 'light'); 

        if (empty($timezone)) {
             $error_message = 'Please select a timezone.';
        } else {
            $update_success = true;
            $update_success &= update_setting('timezone', $timezone);
            $update_success &= update_setting('language', $language);
            $update_success &= update_setting('date_format', $date_format);
            $update_success &= update_setting('currency', $currency);
            $update_success &= update_setting('currency_position', $currency_position);
            $update_success &= update_setting('rtl_support', $rtl_support);
            $update_success &= update_setting('theme_mode', $theme_mode); 

            if ($update_success) {
                $success_message = 'Localization & Appearance settings updated successfully.';
                 $action_successful = true;
            } else {
                $error_message = 'Failed to update one or more localization/appearance settings.';
            }
        }
    } 
    // --- Update Email Settings ---
    elseif (isset($_POST['update_email_settings'])) {
        $submitted_section = 'email-setting';
        $smtp_host = sanitize($_POST['smtp_host']);
        $smtp_port = (int)$_POST['smtp_port'];
        $smtp_username = sanitize($_POST['smtp_username']);
        $smtp_password_input = $_POST['smtp_password'];
        $smtp_encryption = sanitize($_POST['smtp_encryption']);
        $email_from_name = sanitize($_POST['email_from_name']);
        $email_from_address = sanitize($_POST['email_from_address'] ?? ''); 
        
        if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($email_from_name)) {
             $error_message = 'Please fill in all required email configuration fields.';
        } else {
            $update_success = true;
            $update_success &= update_setting('smtp_host', $smtp_host);
            $update_success &= update_setting('smtp_port', $smtp_port);
            $update_success &= update_setting('smtp_username', $smtp_username);
            $update_success &= update_setting('smtp_encryption', $smtp_encryption);
            $update_success &= update_setting('email_from_name', $email_from_name);
            
            if (!empty($smtp_password_input)) {
                $encrypted_password = $smtp_password_input; // Replace with actual encryption
                $update_success &= update_setting('smtp_password', $encrypted_password); 
            }
            
            if ($update_success) {
                 $success_message = 'Email settings updated successfully.';
                 $action_successful = true;
        } else {
                 $error_message = 'Failed to update email settings.';
            }
        }
    }
    // --- Send Test Email ---
    elseif (isset($_POST['send_test_email_action'])) {
        $submitted_section = 'email-setting'; // Stay on email tab after test
        $test_email_address = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
        
        if (!$test_email_address) {
            $error_message = 'Please enter a valid email address to send the test email.';
        } else {
            $host = get_setting('smtp_host');
            $port = get_setting('smtp_port');
            $user = get_setting('smtp_username');
            $pass = get_setting('smtp_password'); // Needs decryption
            $encr = get_setting('smtp_encryption');
            $from_name = get_setting('email_from_name');
            $from_email = $user; 

            $mail_sent = false;
            /* // PHPMailer Integration Placeholder
            require 'path/to/PHPMailer/src/Exception.php';
            // ... rest of PHPMailer requires and code ...
            $mail_sent = true; // if successful
            $error_message = "Mailer Error: {$mail->ErrorInfo}"; // if failed
            */

            // Mock result 
            if (true) { // Replace 'true' with $mail_sent
                $success_message = 'Test email sent successfully to ' . htmlspecialchars($test_email_address);
                // $action_successful = true; // No real data change, maybe don't mark as successful for reload?
            } else {
                if(empty($error_message)) $error_message = 'Failed to send test email. Check logs or configuration.';
            }
        }
    }
    // PLACEHOLDER: Add handlers for other forms 

    // If an action changed data, update the active section for page reload
    if ($action_successful) {
       $active_section = $submitted_section; 
    }
}

// --- Load ALL settings from database AFTER potential updates ---
// Use the helper function which should now fetch from DB (or uses mock data)
$settings = [
    'general' => [
        'company_name' => get_setting('company_name', 'Default Company Name'),
        'company_address' => get_setting('company_address', ''),
        'company_phone' => get_setting('company_phone', ''),
        'company_email' => get_setting('company_email', ''),
        'company_website' => get_setting('company_website', ''),
        'company_tax_id' => get_setting('company_tax_id', ''),
        'company_logo_path' => get_setting('company_logo_path', '/jj/kk/assets/images/default-user.png'),
        'city' => get_setting('city', ''),
        'state' => get_setting('state', ''),
        'postal_code' => get_setting('postal_code', '')
    ],
    'email' => [
        'smtp_host' => get_setting('smtp_host', ''),
        'smtp_port' => get_setting('smtp_port', '587'),
        'smtp_username' => get_setting('smtp_username', ''),
        'smtp_password' => '', // Blank for security
        'smtp_encryption' => get_setting('smtp_encryption', 'tls'),
        'from_name' => get_setting('email_from_name', 'Garage System'),
        'from_email' => get_setting('email_from_address', ''),
    ],
    'other' => [
        'timezone' => get_setting('timezone', 'UTC'),
        'language' => get_setting('language', 'en'),
        'date_format' => get_setting('date_format', 'yyyy-mm-dd'),
        'currency' => get_setting('currency', 'USD'),
        'currency_position' => get_setting('currency_position', 'before'),
        'rtl_support' => get_setting('rtl_support', 'false'),
        'theme_mode' => get_setting('theme_mode', 'light'),
        'accent_color' => get_setting('accent_color', '#4361ee')
    ]
];

$pageTitle = "System Settings";
?>

<!DOCTYPE html>
<html lang="<?php echo explode('/', $settings['other']['language'])[0]; ?>" 
      dir="<?php echo ($settings['other']['rtl_support'] === 'true') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Garage Management System</title>
    <link rel="stylesheet" href="/jj/kk/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/jj/kk/assets/css/styles.css">
    <link rel="stylesheet" href="/jj/kk/assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/jj/kk/assets/css/settings.css">
    <script>
        // Apply theme and accent color immediately
        const savedTheme = <?php echo json_encode($settings['other']['theme_mode']); ?> || localStorage.getItem('theme') || 'light';
        const savedAccentColor = <?php echo json_encode($settings['other']['accent_color']); ?> || localStorage.getItem('accent-color') || '#4361ee';
        
        if (savedTheme === 'dark' || (savedTheme === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        document.documentElement.style.setProperty('--primary-color', savedAccentColor);
    </script>
</head>
<body class="<?php echo ($settings['other']['theme_mode'] === 'dark') ? 'dark' : ''; ?>">
    <div class="settings-container">
        <!-- Sidebar -->
        <div class="settings-sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-cogs"></i>
                    <span>Settings</span>
                </div>
                <button class="mobile-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="sidebar-user">
                <div class="user-avatar">
                    <img src="<?php echo htmlspecialchars($settings['general']['company_logo_path']); ?>?t=<?php echo time(); ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <div class="user-info">
                    <h6>Admin User</h6>
                    <span>Administrator</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'general-settings') ? 'active' : ''; ?>" 
                                id="general-settings-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#general-settings" 
                                type="button" role="tab"
                                data-section="general-settings">
                            <i class="fas fa-building"></i>
                            <span>General</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'other-settings') ? 'active' : ''; ?>" 
                                id="other-settings-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#other-settings" 
                                type="button" role="tab"
                                data-section="other-settings">
                            <i class="fas fa-globe"></i>
                            <span>Localization</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'email-setting') ? 'active' : ''; ?>" 
                                id="email-setting-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#email-setting" 
                                type="button" role="tab"
                                data-section="email-setting">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'access-rights') ? 'active' : ''; ?>" 
                                id="access-rights-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#access-rights" 
                                type="button" role="tab" 
                                data-section="access-rights" disabled>
                            <i class="fas fa-lock"></i>
                            <span>Access Rights</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'business-hours') ? 'active' : ''; ?>" 
                                id="business-hours-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#business-hours" 
                                type="button" role="tab" 
                                data-section="business-hours" disabled>
                            <i class="fas fa-clock"></i>
                            <span>Business Hours</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'stripe-settings') ? 'active' : ''; ?>" 
                                id="stripe-settings-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#stripe-settings" 
                                type="button" role="tab" 
                                data-section="stripe-settings" disabled>
                            <i class="fab fa-stripe-s"></i>
                            <span>Stripe</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo ($active_section == 'branch-setting') ? 'active' : ''; ?>" 
                                id="branch-setting-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#branch-setting" 
                                type="button" role="tab" 
                                data-section="branch-setting" disabled>
                            <i class="fas fa-code-branch"></i>
                            <span>Branch</span>
                        </button>
                    </li>
                </ul>
            </nav>
            
             <div class="sidebar-footer" style="display: none;">
                <div class="theme-toggle">
                    <span>Theme</span>
                    <div class="toggle-switch">
                        <input type="checkbox" id="theme-switch">
                        <label for="theme-switch"></label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="settings-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p>Configure and manage your system preferences</p>
            </div>
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="tab-content" id="settingsTabsContent">
                <div class="tab-pane fade <?php echo ($active_section == 'general-settings') ? 'show active' : ''; ?>" 
                     id="general-settings" role="tabpanel" aria-labelledby="general-settings-tab">
                    <form method="post" action="?section=general-settings" enctype="multipart/form-data">
                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Business Information</h2>
                                    <p>Update your company details</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="system_name">System Name<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-building"></i>
                                                <input type="text" id="system_name" name="company_name" value="<?php echo htmlspecialchars($settings['general']['company_name']); ?>" required>
                                            </div>
                                            <small>Appears in title/reports</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-envelope"></i>
                                                <input type="email" id="company_email" name="company_email" value="<?php echo htmlspecialchars($settings['general']['company_email']); ?>" required>
                                            </div>
                                            <small>Primary contact email</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Phone<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-phone"></i>
                                                <input type="tel" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars($settings['general']['company_phone']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_website">Website</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-globe"></i>
                                                <input type="url" id="company_website" name="company_website" value="<?php echo htmlspecialchars($settings['general']['company_website']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_tax_id">Tax ID</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-receipt"></i>
                                                <input type="text" id="company_tax_id" name="company_tax_id" value="<?php echo htmlspecialchars($settings['general']['company_tax_id']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_logo">Logo</label>
                                            <div class="current-logo mb-2">
                                                <small>Current:</small>
                                                <img src="<?php echo htmlspecialchars($settings['general']['company_logo_path']); ?>?t=<?php echo time(); ?>" alt="Logo" style="max-height: 40px; margin-left: 10px;">
                                            </div>
                                            <div class="file-upload">
                                                <input type="file" id="company_logo" name="company_logo" class="file-input" accept="image/*">
                                                <div class="file-preview">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                    <span>Choose new file</span>
                                                </div>
                                            </div>
                                            <small>Max 1MB</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Address</h2>
                                    <p>Update location details</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="company_address">Street Address<span class="required">*</span></label>
                                            <div class="input-with-icon textarea">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <textarea id="company_address" name="company_address" rows="2" required><?php echo htmlspecialchars($settings['general']['company_address']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-city"></i>
                                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($settings['general']['city']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state">State/Province</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-map"></i>
                                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($settings['general']['state']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_code">Postal/ZIP Code</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-mail-bulk"></i>
                                                <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($settings['general']['postal_code']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="reset" class="btn btn-light">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" name="update_general_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save General
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane fade <?php echo ($active_section == 'other-settings') ? 'show active' : ''; ?>" 
                     id="other-settings" role="tabpanel" aria-labelledby="other-settings-tab">
                    <form method="post" action="?section=other-settings">
                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Timezone & Formats</h2>
                                    <p>Set preferences</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="timezone">Timezone<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-globe-americas"></i>
                                                <select id="timezone" name="timezone" required>
                                                    <option value="Asia/Kolkata" <?php echo ($settings['other']['timezone'] == 'Asia/Kolkata') ? 'selected' : ''; ?>>Asia/Kolkata</option>
                                                    <option value="America/New_York" <?php echo ($settings['other']['timezone'] == 'America/New_York') ? 'selected' : ''; ?>>America/New_York</option>
                                                    <option value="Europe/London" <?php echo ($settings['other']['timezone'] == 'Europe/London') ? 'selected' : ''; ?>>Europe/London</option>
                                                    <option value="UTC" <?php echo ($settings['other']['timezone'] == 'UTC') ? 'selected' : ''; ?>>UTC</option>
                                                </select>
                                            </div>
                                            <small>All dates and times will be displayed in this timezone</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_format">Date Format</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                                <select id="date_format" name="date_format">
                                                    <option value="yyyy-mm-dd" <?php echo ($settings['other']['date_format'] == 'yyyy-mm-dd') ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                                    <option value="dd-mm-yyyy" <?php echo ($settings['other']['date_format'] == 'dd-mm-yyyy') ? 'selected' : ''; ?>>DD-MM-YYYY</option>
                                                    <option value="M d, Y" <?php echo ($settings['other']['date_format'] == 'M d, Y') ? 'selected' : ''; ?>>Mon DD, YYYY</option>
                                                </select>
                                            </div>
                                            <small>Format for displaying dates throughout the system</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="language">Language</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-globe"></i>
                                                <select id="language" name="language">
                                                    <option value="en" <?php echo ($settings['other']['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                                                    <option value="fr" <?php echo ($settings['other']['language'] == 'fr') ? 'selected' : ''; ?>>Français</option>
                                                    <option value="ar" <?php echo ($settings['other']['language'] == 'ar') ? 'selected' : ''; ?>>العربية</option>
                                                </select>
                                            </div>
                                            <small>Choose your preferred language</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rtl_support">RTL Support</label>
                                            <div class="toggle-container">
                                                <div class="toggle-switch">
                                                    <input type="checkbox" id="rtl_support" name="rtl_support" <?php echo ($settings['other']['rtl_support'] === 'true') ? 'checked' : ''; ?>>
                                                    <label for="rtl_support"></label>
                                                </div>
                                                <span>Enable RTL</span>
                                            </div>
                                            <small>For languages like Arabic, Hebrew, etc.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Currency</h2>
                                    <p>Set default currency</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency">Currency</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-dollar-sign"></i>
                                                <select id="currency" name="currency">
                                                    <option value="USD" <?php echo ($settings['other']['currency'] == 'USD') ? 'selected' : ''; ?>>USD ($)</option>
                                                    <option value="INR" <?php echo ($settings['other']['currency'] == 'INR') ? 'selected' : ''; ?>>INR (₹)</option>
                                                    <option value="EUR" <?php echo ($settings['other']['currency'] == 'EUR') ? 'selected' : ''; ?>>EUR (€)</option>
                                                </select>
                                            </div>
                                            <small>Used for all financial calculations and reports</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency_position">Position</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-text-width"></i>
                                                <select id="currency_position" name="currency_position">
                                                    <option value="before" <?php echo ($settings['other']['currency_position'] == 'before') ? 'selected' : ''; ?>>Before ($100)</option>
                                                    <option value="after" <?php echo ($settings['other']['currency_position'] == 'after') ? 'selected' : ''; ?>>After (100$)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Appearance</h2>
                                    <p>Customize look & feel</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Theme Mode</label>
                                            <div class="theme-options">
                                                <div class="theme-option">
                                                    <input type="radio" name="theme_mode" id="light_mode" value="light" <?php echo ($settings['other']['theme_mode'] == 'light') ? 'checked' : ''; ?>>
                                                    <label for="light_mode">
                                                        <div class="option-icon"><i class="fas fa-sun"></i></div>
                                                        <span>Light</span>
                                                    </label>
                                                </div>
                                                <div class="theme-option">
                                                    <input type="radio" name="theme_mode" id="dark_mode" value="dark" <?php echo ($settings['other']['theme_mode'] == 'dark') ? 'checked' : ''; ?>>
                                                    <label for="dark_mode">
                                                        <div class="option-icon"><i class="fas fa-moon"></i></div>
                                                        <span>Dark</span>
                                                    </label>
                                                </div>
                                                <div class="theme-option">
                                                    <input type="radio" name="theme_mode" id="auto_mode" value="auto" <?php echo ($settings['other']['theme_mode'] == 'auto') ? 'checked' : ''; ?>>
                                                    <label for="auto_mode">
                                                        <div class="option-icon"><i class="fas fa-adjust"></i></div>
                                                        <span>Auto</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <small>Select theme or let system decide based on OS preference.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Accent Color</label>
                                            <div class="color-options">
                                                <div class="color-option" style="background-color: #4361ee;" data-color="#4361ee"></div>
                                                <div class="color-option" style="background-color: #2ecc71;" data-color="#2ecc71"></div>
                                                <div class="color-option" style="background-color: #e74c3c;" data-color="#e74c3c"></div>
                                            </div>
                                            <small>Managed locally</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn btn-light">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" name="update_other_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Localization
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane fade <?php echo ($active_section == 'email-setting') ? 'show active' : ''; ?>" 
                     id="email-setting" role="tabpanel" aria-labelledby="email-setting-tab">
                    <form method="post" action="?section=email-setting">
                        <div class="settings-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="section-title">
                                    <h2>Email Config</h2>
                                    <p>SMTP server settings</p>
                                </div>
                            </div>
                            <div class="settings-card">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="smtp_host">SMTP Host<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-server"></i>
                                                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['email']['smtp_host']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="smtp_port">Port<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-plug"></i>
                                                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['email']['smtp_port']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="smtp_username">Username<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-user"></i>
                                                <input type="text" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($settings['email']['smtp_username']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="smtp_password">Password <small>(Optional)</small></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-lock"></i>
                                                <input type="password" id="smtp_password" name="smtp_password" value="" placeholder="New password...">
                                                <button type="button" class="password-toggle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="smtp_encryption">Encryption</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-shield-alt"></i>
                                                <select id="smtp_encryption" name="smtp_encryption">
                                                    <option value="tls" <?php echo ($settings['email']['smtp_encryption'] == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                                    <option value="ssl" <?php echo ($settings['email']['smtp_encryption'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                                    <option value="none" <?php echo ($settings['email']['smtp_encryption'] == 'none') ? 'selected' : ''; ?>>None</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email_from_name">From Name<span class="required">*</span></label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-signature"></i>
                                                <input type="text" id="email_from_name" name="email_from_name" value="<?php echo htmlspecialchars($settings['email']['from_name']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-top">
                                    <h5>Test Config</h5>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <div class="form-group mb-0">
                                                <label for="test_email">Send To:</label>
                                            <div class="input-with-icon">
                                                <i class="fas fa-paper-plane"></i>
                                                    <input type="email" id="test_email" name="test_email" placeholder="Enter email...">
                                            </div>
                                        </div>
                                    </div>
                                        <div class="col-md-4">
                                            <button type="submit" name="send_test_email_action" value="1" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-paper-plane"></i> Send Test
                                            </button>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn btn-light">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" name="update_email_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Email
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane fade <?php echo ($active_section == 'access-rights') ? 'show active' : ''; ?>" id="access-rights" role="tabpanel" aria-labelledby="access-rights-tab">
                    <div class="settings-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-lock"></i></div>
                            <div class="section-title">
                                <h2>Permissions</h2>
                                <p>(Not Implemented)</p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <p class="text-muted">Manage roles/permissions here.</p>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" disabled>Save</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade <?php echo ($active_section == 'business-hours') ? 'show active' : ''; ?>" id="business-hours" role="tabpanel" aria-labelledby="business-hours-tab">
                    <div class="settings-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-clock"></i></div>
                            <div class="section-title">
                                <h2>Business Hours</h2>
                                <p>(Not Implemented)</p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <p class="text-muted">Set operating hours here.</p>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" disabled>Save</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade <?php echo ($active_section == 'stripe-settings') ? 'show active' : ''; ?>" id="stripe-settings" role="tabpanel" aria-labelledby="stripe-settings-tab">
                    <div class="settings-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fab fa-stripe-s"></i></div>
                            <div class="section-title">
                                <h2>Stripe</h2>
                                <p>(Not Implemented)</p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <p class="text-muted">Configure Stripe API keys here.</p>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" disabled>Save</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade <?php echo ($active_section == 'branch-setting') ? 'show active' : ''; ?>" id="branch-setting" role="tabpanel" aria-labelledby="branch-setting-tab">
                    <div class="settings-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-code-branch"></i></div>
                            <div class="section-title">
                                <h2>Branch</h2>
                                <p>(Not Implemented)</p>
                            </div>
                        </div>
                        <div class="settings-card">
                            <p class="text-muted">Manage branches here.</p>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" disabled>Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Includes -->
    <script src="/jj/kk/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/jj/kk/assets/js/theme.js"></script>
    <script src="/jj/kk/assets/js/settings.js"></script>
    <script src="/jj/kk/assets/js/sidebar.js"></script>
    <script>
        // JS to sync UI controls with loaded PHP settings
document.addEventListener('DOMContentLoaded', function() {
            // Sync Theme Radio 
            const themeMode = <?php echo json_encode($settings['other']['theme_mode']); ?>;
            if (themeMode) {
                const themeRadio = document.querySelector('input[name="theme_mode"][value="' + themeMode + '"]');
                if(themeRadio) themeRadio.checked = true;
            }
            // Sync RTL Toggle
            const rtlSupport = <?php echo json_encode($settings['other']['rtl_support']); ?>;
            const rtlCheckbox = document.getElementById('rtl_support');
            if(rtlCheckbox) rtlCheckbox.checked = (rtlSupport === 'true');
            // Sync Accent Color Picker Active State
            const accentColor = <?php echo json_encode($settings['other']['accent_color']); ?>;
             if(accentColor) {
                 document.querySelectorAll('.color-option').forEach(opt => {
                     opt.classList.toggle('active', opt.getAttribute('data-color') === accentColor);
                 });
             }
        });
    </script>
</body>
</html>