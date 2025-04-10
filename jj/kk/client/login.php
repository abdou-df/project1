<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check if it's a staff login
        $sql = "SELECT id, username, password, email, first_name, last_name, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_type'] = 'staff';
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = generate_random_string(64);
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $sql = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user['id'], $token, $expires);
                    $stmt->execute();
                    
                    setcookie('remember_token', $token, strtotime('+30 days'), '/');
                }
                
                // Redirect based on role
                if (in_array($user['role'], ['admin', 'manager', 'mechanic', 'receptionist'])) {
                    redirect('admin/index.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            // Check if it's a customer login
            $sql = "SELECT id, password, email, first_name, last_name FROM customers WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $customer = $result->fetch_assoc();
                
                if (password_verify($password, $customer['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $customer['id'];
                    $_SESSION['user_email'] = $customer['email'];
                    $_SESSION['user_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                    $_SESSION['user_type'] = 'customer';
                    
                    redirect('dashboard.php');
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-form-container">
                    <h2>Login to Your Account</h2>
                    <p>Welcome back! Please login to access your account.</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="post" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <div class="form-group form-checkbox">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                            <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </form>
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Register</a></p>
                    </div>
                </div>
                <div class="auth-image">
                    <img src="images/login-image.jpg" alt="Login">
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

