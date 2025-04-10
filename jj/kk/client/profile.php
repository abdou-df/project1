<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to access your profile.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('admin/index.php');
}

$customer_id = $_SESSION['user_id'];

// Get customer information
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

$error = '';
$success = '';

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $city = sanitize_input($_POST['city']);
    $state = sanitize_input($_POST['state']);
    $zip = sanitize_input($_POST['zip']);
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (excluding current user)
        $sql = "SELECT id FROM customers WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address is already registered.';
        } else {
            // Update customer information
            $sql = "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                    address = ?, city = ?, state = ?, zip = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $phone, $address, $city, $state, $zip, $customer_id);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                
                $success = 'Profile updated successfully!';
                
                // Refresh customer data
                $sql = "SELECT * FROM customers WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $customer = $result->fetch_assoc();
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Process password change form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields.';
    } elseif ($new_password != $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        // Verify current password
        if (password_verify($current_password, $customer['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $sql = "UPDATE customers SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $customer_id);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Profile Settings</h1>
            <p>Manage your account information</p>
        </div>
    </section>
    
    <section class="profile-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h3>
                            <p><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                    </div>
                    <nav class="dashboard-nav">
                        <ul>
                            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                            <li><a href="vehicles.php"><i class="fas fa-car"></i> My Vehicles</a></li>
                            <li><a href="invoices.php"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
                            <li class="active"><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="dashboard-content">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="profile-tabs">
                        <div class="tab-header">
                            <button class="tab-btn active" data-tab="personal">Personal Information</button>
                            <button class="tab-btn" data-tab="security">Security</button>
                            <button class="tab-btn" data-tab="preferences">Preferences</button>
                        </div>
                        
                        <div class="tab-content active" id="personal-tab">
                            <div class="form-container">
                                <form action="profile.php" method="post" class="form">
                                    <input type="hidden" name="update_profile" value="1">
                                    
                                    <div class="form-group-title">
                                        <h2>Personal Information</h2>
                                        <p>Update your personal details</p>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="first_name">First Name <span class="required">*</span></label>
                                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="last_name">Last Name <span class="required">*</span></label>
                                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="email">Email <span class="required">*</span></label>
                                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone Number <span class="required">*</span></label>
                                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="zip">ZIP Code</label>
                                            <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($customer['zip'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="security-tab">
                            <div class="form-container">
                                <form action="profile.php" method="post" class="form">
                                    <input type="hidden" name="change_password" value="1">
                                    
                                    <div class="form-group-title">
                                        <h2>Change Password</h2>
                                        <p>Update your account password</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="current_password">Current Password <span class="required">*</span></label>
                                        <div class="input-group">
                                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                                            <input type="password" id="current_password" name="current_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">New Password <span class="required">*</span></label>
                                        <div class="input-group">
                                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                                            <input type="password" id="new_password" name="new_password" required>
                                        </div>
                                        <div class="password-requirements">
                                            <p>Password must be at least 6 characters long.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                                        <div class="input-group">
                                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                                            <input type="password" id="confirm_password" name="confirm_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="preferences-tab">
                            <div class="form-container">
                                <form action="profile.php" method="post" class="form">
                                    <input type="hidden" name="update_preferences" value="1">
                                    
                                    <div class="form-group-title">
                                        <h2>Notification Preferences</h2>
                                        <p>Manage how you receive notifications</p>
                                    </div>
                                    
                                    <div class="form-group form-checkbox">
                                        <input type="checkbox" id="email_notifications" name="email_notifications" <?php echo (isset($customer['email_notifications']) && $customer['email_notifications'] == 1) ? 'checked' : ''; ?>>
                                        <label for="email_notifications">Email Notifications</label>
                                    </div>
                                    
                                    <div class="form-group form-checkbox">
                                        <input type="checkbox" id="sms_notifications" name="sms_notifications" <?php echo (isset($customer['sms_notifications']) && $customer['sms_notifications'] == 1) ? 'checked' : ''; ?>>
                                        <label for="sms_notifications">SMS Notifications</label>
                                    </div>
                                    
                                    <div class="form-group form-checkbox">
                                        <input type="checkbox" id="appointment_reminders" name="appointment_reminders" <?php echo (isset($customer['appointment_reminders']) && $customer['appointment_reminders'] == 1) ? 'checked' : ''; ?>>
                                        <label for="appointment_reminders">Appointment Reminders</label>
                                    </div>
                                    
                                    <div class="form-group form-checkbox">
                                        <input type="checkbox" id="promotional_emails" name="promotional_emails" <?php echo (isset($customer['promotional_emails']) && $customer['promotional_emails'] == 1) ? 'checked' : ''; ?>>
                                        <label for="promotional_emails">Promotional Emails</label>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and content
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>

