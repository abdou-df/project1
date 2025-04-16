<?php
/**
 * AJAX endpoint for fetching user statistics
 * Returns JSON data with current user counts
 */

// Include necessary files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';
require_once dirname(__FILE__) . '/../includes/database.php';

// Check if user is logged in and has permission
if (!isLoggedIn() || !hasPermission('view_users')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Initialize database connection
$conn = getDbConnection();
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Prepare response array
    $stats = [];
    
    // Count total users
    $total_count_sql = "SELECT COUNT(*) as count FROM users";
    $stmt = $conn->prepare($total_count_sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_users'] = $row['count'];
    
    // Count active users
    $active_count_sql = "SELECT COUNT(*) as count FROM users WHERE status = ?";
    $stmt = $conn->prepare($active_count_sql);
    $active_status = STATUS_ACTIVE;
    $stmt->bindParam(1, $active_status, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_active_users'] = $row['count'];
    
    // Count inactive users
    $inactive_count_sql = "SELECT COUNT(*) as count FROM users WHERE status = ?";
    $stmt = $conn->prepare($inactive_count_sql);
    $inactive_status = STATUS_INACTIVE;
    $stmt->bindParam(1, $inactive_status, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_inactive_users'] = $row['count'];
    
    // Count users by role
    $admin_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($admin_count_sql);
    $admin_role = ROLE_ADMIN;
    $stmt->bindParam(1, $admin_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_admin_users'] = $row['count'];
    
    $employee_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($employee_count_sql);
    $employee_role = ROLE_EMPLOYEE;
    $stmt->bindParam(1, $employee_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_employee_users'] = $row['count'];
    
    $customer_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($customer_count_sql);
    $customer_role = ROLE_CUSTOMER;
    $stmt->bindParam(1, $customer_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_customer_users'] = $row['count'];
    
    // Add timestamp for caching purposes
    $stats['timestamp'] = time();
    
    // Return JSON response
    echo json_encode($stats);
    
} catch (PDOException $e) {
    // Log error and return error response
    error_log("Database error when fetching statistics: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error', 'message' => 'Failed to fetch user statistics']);
}