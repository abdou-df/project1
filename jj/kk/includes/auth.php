<?php
/**
 * Authentication and Authorization Functions
 * 
 * This file contains functions for user authentication, authorization,
 * session management, and permission checks.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database functions if not already included
require_once __DIR__ . '/database.php';

/**
 * Authenticate a user with email and password
 * 
 * @param string $email User email
 * @param string $password User password (plain text)
 * @return array|bool User data array on success, false on failure
 */
function authenticateUser($email, $password) {
    // Sanitize inputs
    $email = sanitize($email);
    
    // Query to get user with the provided email
    $sql = "SELECT * FROM users WHERE email = ? AND status = ? LIMIT 1";
    $active_status = 'active';
    $user = dbQuerySingle($sql, [$email, $active_status]);
    
    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove sensitive data before returning
            unset($user['password']);
            return $user;
        }
    }
    
    return false;
}

/**
 * Log in a user and create session
 * 
 * @param array $user User data
 * @param bool $remember Whether to remember the user (set long-term cookie)
 * @return bool True on success
 */
function loginUser($user, $remember = false) {
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = generateRememberToken();
        $expiry = time() + (86400 * 30); // 30 days
        
        // Store token in database
        storeRememberToken($user['id'], $token, $expiry);
        
        // Set cookie
        setcookie('remember_token', $token, $expiry, '/', '', false, true);
    }
    
    // Ya no actualizamos last_login porque la columna no existe
    // updateLastLogin($user['id']);
    
    return true;
}

/**
 * Generate a secure token for "remember me" functionality
 * 
 * @return string Secure token
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Store remember token in database
 * 
 * @param int $user_id User ID
 * @param string $token Remember token
 * @param int $expiry Expiry timestamp
 * @return bool True on success
 */
function storeRememberToken($user_id, $token, $expiry) {
    // First, delete any existing tokens for this user
    $sql = "DELETE FROM user_tokens WHERE user_id = ? AND token = ?";
    dbQuery($sql, [$user_id, $token]);
    
    // Insert new token
    $tokenData = [
        'user_id' => $user_id,
        'token' => $token,
        'expires_at' => date('Y-m-d H:i:s', $expiry)
    ];
    
    return insertRecord('user_tokens', $tokenData);
}

/**
 * Update user's last login timestamp - DESACTIVADO TEMPORALMENTE
 * Esta función está desactivada porque la columna last_login no existe en la tabla users
 * 
 * @param int $user_id User ID
 * @return bool True on success
 */
function updateLastLogin($user_id) {
    // No hacemos nada ya que la columna no existe
    return true;
    
    // Original code:
    // $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    // $result = dbQuery($sql, [$user_id]);
    // return $result !== false;
}

/**
 * Log user activity - simplified version without using activity_logs table
 * 
 * @param int $user_id User ID
 * @param string $action Action performed
 * @param string $details Additional details
 * @return bool True always as we're not actually logging for now
 */
function logUserActivity($user_id, $action, $details = '') {
    // We're not actually logging for now to avoid errors
    // This would normally insert into activity_logs table
    return true;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    // Check if user session exists
    if (isset($_SESSION['user_id'])) {
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Check for remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $user = getUserByRememberToken($_COOKIE['remember_token']);
        if ($user) {
            // Log in the user
            loginUser($user);
            return true;
        }
    }
    
    return false;
}

/**
 * Get user by remember token
 * 
 * @param string $token Remember token
 * @return array|bool User data on success, false on failure
 */
function getUserByRememberToken($token) {
    // Get current time
    $now = date('Y-m-d H:i:s');
    
    // Query to get valid token
    $sql = "SELECT u.* FROM users u
            JOIN user_tokens t ON u.id = t.user_id
            WHERE t.token = ? AND t.expires_at > ? AND u.status = ?
            LIMIT 1";
    
    $active_status = 'active';
    $user = dbQuerySingle($sql, [$token, $now, $active_status]);
    
    if ($user) {
        // Remove sensitive data before returning
        unset($user['password']);
        return $user;
    }
    
    return false;
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logoutUser() {
    // Delete remember token if exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        if (isset($_SESSION['user_id'])) {
            deleteRememberToken($_SESSION['user_id'], $token);
        }
        
        // Expire the cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy the session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Delete remember token from database
 * 
 * @param int $user_id User ID
 * @param string $token Remember token
 * @return bool True on success
 */
function deleteRememberToken($user_id, $token) {
    $sql = "DELETE FROM user_tokens WHERE user_id = ? AND token = ?";
    $result = dbQuery($sql, [$user_id, $token]);
    
    return $result !== false;
}

/**
 * Check if current user is an admin
 * 
 * @return bool True if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current user is a manager
 * 
 * @return bool True if user is manager
 */
function isManager() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'manager';
}

/**
 * Check if current user is a mechanic
 * 
 * @return bool True if user is mechanic
 */
function isMechanic() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'mechanic';
}

/**
 * Check if current user is a receptionist
 * 
 * @return bool True if user is receptionist
 */
function isReceptionist() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'receptionist';
}

/**
 * Check if current user is an inventory manager
 * 
 * @return bool True if user is inventory manager
 */
function isInventoryManager() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'inventory_manager';
}

/**
 * Check if current user is a customer
 * 
 * @return bool True if user is customer
 */
function isCustomer() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

/**
 * Check if user has permission to access a specific resource
 * 
 * @param string $permission Permission key
 * @return bool True if user has permission
 */
function hasPermission($permission) {
    // Make sure user is logged in
    if (!isLoggedIn()) {
        return false;
    }
    
    // Get user role
    $role = $_SESSION['user_role'];
    
    // Define permissions for each role
    $permissions = [
        'admin' => [
            'view_dashboard', 'manage_users', 'manage_customers', 'manage_vehicles',
            'manage_appointments', 'manage_services', 'manage_inventory',
            'manage_invoices', 'view_reports', 'system_settings'
        ],
        'manager' => [
            'view_dashboard', 'view_users', 'manage_customers', 'manage_vehicles',
            'manage_appointments', 'manage_services', 'view_inventory',
            'manage_invoices', 'view_reports'
        ],
        'mechanic' => [
            'view_dashboard', 'view_customers', 'view_vehicles', 'update_appointments',
            'view_services', 'view_inventory', 'view_invoices'
        ],
        'receptionist' => [
            'view_dashboard', 'view_customers', 'view_vehicles', 'manage_appointments',
            'view_services', 'view_invoices'
        ],
        'inventory_manager' => [
            'view_dashboard', 'manage_inventory', 'view_invoices'
        ],
        'customer' => [
            'view_dashboard', 'view_own_vehicles', 'view_own_appointments',
            'view_own_invoices'
        ]
    ];
    
    // Check if role exists in permissions array
    if (!isset($permissions[$role])) {
        return false;
    }
    
    // Check if permission exists for the role
    return in_array($permission, $permissions[$role]);
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    $user_id = getCurrentUserId();
    
    if (!$user_id) {
        return null;
    }
    
    // Get user data from database
    $user = getUserById($user_id);
    
    // If user not found or inactive, log out
    if (!$user || $user['status'] !== 'active') {
        logoutUser();
        return null;
    }
    
    return $user;
}

/**
 * Get user by ID
 * 
 * @param int $user_id User ID
 * @return array|bool User data on success, false on failure
 */
function getUserById($user_id) {
    $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
    $user = dbQuerySingle($sql, [$user_id]);
    
    if ($user) {
        // Remove sensitive data before returning
        unset($user['password']);
        return $user;
    }
    
    return false;
}
