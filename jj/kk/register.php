<?php
// Register page for new customers
session_start();

// Include configuration and functions
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Initialize variables
$errors = [];
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitize($_POST['state']) : '';
    $zip_code = isset($_POST['zip_code']) ? sanitize($_POST['zip_code']) : '';
    
    // Validate form data
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists in customers table
    if (empty($errors) && recordExists('customers', 'email', $email)) {
        $errors[] = 'Email already registered. Please use a different email or login.';
    }
    
    // If no errors, register the customer
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate username from first and last name
        $username = strtolower($first_name . $last_name . rand(100, 999));
        
        // Prepare customer data for insertion
        $customerData = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip_code
        ];
        
        // Insert customer data
        $customerId = insertRecord('customers', $customerData);
        
        if ($customerId) {
            // Prepare user data - create a customer account in users table
            $userData = [
                'username' => $username,
                'password' => $hashed_password,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 'receptionist', // This should be 'customer' - using receptionist as placeholder since customers role isn't in enum
                'phone' => $phone,
                'status' => 'active'
            ];
            
            // Insert user data
            $userId = insertRecord('users', $userData);
            
            if ($userId) {
                $success = true;
                
                // Redirect to login page after successful registration
                $_SESSION['registration_success'] = true;
                $_SESSION['success_message'] = 'Registration successful! You can now login with your email and password.';
                header('Location: login.php');
                exit;
            } else {
                // If user insert failed, delete the customer record
                deleteRecord('customers', 'id', $customerId);
                $errors[] = 'Registration failed. Please try again later.';
            }
        } else {
            $errors[] = 'Registration failed. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Auto Care Garage</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/register.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header text-white text-center py-3">
                        <h2 class="mb-0">Create an Account</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Progress bar -->
                        <div class="progress-container">
                            <div class="step-indicator">
                                <div class="step active">Personal Info</div>
                                <div class="step">Account Setup</div>
                                <div class="step">Address Details</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 33%"></div>
                            </div>
                        </div>
                        
                        <form method="post" action="" id="registrationForm">
                            <!-- Section 1: Personal Information -->
                            <div class="form-section active">
                                <h4 class="mb-4">Personal Information</h4>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="first_name" name="first_name" value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="last_name" name="last_name" value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-envelope text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-phone text-muted"></i>
                                            </span>
                                            <input type="tel" class="form-control border-start-0" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                </div>
                            </div>
                            
                            <!-- Section 2: Account Setup -->
                            <div class="form-section">
                                <h4 class="mb-4">Account Setup</h4>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" required>
                                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                                                <i class="fas fa-eye" id="togglePassword"></i>
                                            </span>
                                        </div>
                                        <div class="password-strength" id="password-strength"></div>
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0 border-end-0" id="confirm_password" name="confirm_password" required>
                                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                                                <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                                            </span>
                                        </div>
                                        <div id="password-match-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-outline-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                </div>
                            </div>
                            
                            <!-- Section 3: Address Details -->
                            <div class="form-section">
                                <h4 class="mb-4">Address Details</h4>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-home text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" id="address" name="address" value="<?php echo isset($address) ? $address : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label for="city" class="form-label">City</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-city text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="city" name="city" value="<?php echo isset($city) ? $city : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label for="state" class="form-label">State</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-map-marker-alt text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="state" name="state" value="<?php echo isset($state) ? $state : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-map-pin text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="zip_code" name="zip_code" value="<?php echo isset($zip_code) ? $zip_code : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a></label>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-outline-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Register
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <p>Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login here</a></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/register.js"></script>
</body>
</html>
