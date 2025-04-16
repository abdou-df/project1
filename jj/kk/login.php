<?php
session_start();

// Include configuration and helper files
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if registration was successful
$registration_success = false;
if (isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true) {
    $registration_success = true;
    $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : 'Registration successful! You can now log in.';
    
    // Clear session variables
    unset($_SESSION['registration_success']);
    unset($_SESSION['success_message']);
}

// Process login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check if user exists with the provided email
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $user = dbQuerySingle($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = generateRandomToken(32);
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store token in database
                $tokenSql = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                dbQuery($tokenSql, [$user['id'], $token, date('Y-m-d H:i:s', $expiry)]);
                
                // Set cookie
                setcookie('remember_token', $token, $expiry, '/');
            }
            
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Check for "remember me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Verify token
    $sql = "SELECT u.* FROM users u 
            JOIN user_tokens t ON u.id = t.user_id 
            WHERE t.token = ? AND t.expires_at > NOW() 
            LIMIT 1";
    $user = dbQuerySingle($sql, [$token]);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        
        header("Location: index.php");
        exit();
    }
}

/**
 * Generate a random token
 * @param int $length Length of the token
 * @return string Random token
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Garage Management System</title>
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Garage Master Logo">
                <h4>GARAGE MASTER</h4>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($registration_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="social-login">
                <button class="social-btn" title="Login with Google">
                    <i class="fab fa-google" style="color: #DB4437;"></i>
                </button>
                <button class="social-btn" title="Login with Facebook">
                    <i class="fab fa-facebook-f" style="color: #4267B2;"></i>
                </button>
                <button class="social-btn" title="Login with Apple">
                    <i class="fab fa-apple" style="color: #000000;"></i>
                </button>
            </div>
            
            <div class="mt-3 text-center">
                <a href="forgot-password.php">
                    <i class="fas fa-key me-1"></i>Forgot Password?
                </a>
            </div>
            
            <div class="mt-3 text-center">
                <p>Don't have an account? <a href="register.php"><i class="fas fa-user-plus me-1"></i>Register</a></p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/login.js"></script>
</body>
</html>
