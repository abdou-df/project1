<?php
// Main entry point for the application
session_start();

// Include configuration and helper files
require_once 'config/config.php';
require_once 'includes/functions.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garage Management System</title>
   <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
   <link rel="stylesheet" href="assets/css/theme.css"> 
   <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php
// Include the header
include 'includes/header.php';

// Determine which page to load
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Security check - only allow valid pages
$allowed_pages = ['dashboard', 'vehicles', 'vehicle-details', 'customers', 'customer-details', 
                 'appointments', 'create-appointment', 'billing', 'create-invoice', 'inventory', 
                 'add-part', 'reports', 'users', 'settings', 'services', 'quotation', 'invoices',
                 'job-card', 'accounts', 'sales', 'compliances', 'email-templates', 'custom-fields',
                 'employees'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Load the requested page
include 'pages/' . $page . '.php';

// Include the footer
include 'includes/footer.php';
?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>
</html>
