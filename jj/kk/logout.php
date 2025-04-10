<?php
/**
 * Logout Page
 * Handles user logout and session destruction
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Log the logout activity
if (isLoggedIn()) {
    // Get user information before logging out
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'Unknown';
    $user_role = $_SESSION['user_role'] ?? 'Unknown';
    
    // Log the activity
    logUserActivity($user_id, 'logout', "User {$user_name} ({$user_role}) logged out");
}

// Call the logout function
logoutUser();

// Redirect to login page with a logout message
header('Location: login.php?logout=success');
exit;
?>
