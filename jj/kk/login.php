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
            
            // No actualizamos last_login ya que la columna no existe
            // $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            // dbQuery($updateSql, [$user['id']]);
            
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
        
        // No actualizamos last_login ya que la columna no existe
        // $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        // dbQuery($updateSql, [$user['id']]);
        
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
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            height: 60px;
        }
        .btn-primary {
            background-color: #ff6b00;
            border-color: #ff6b00;
        }
        .btn-primary:hover {
            background-color: #e05e00;
            border-color: #e05e00;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Garage Master Logo">
                <h4>GARAGE MASTER</h4>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($registration_success): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
            
            <div class="mt-3 text-center">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
