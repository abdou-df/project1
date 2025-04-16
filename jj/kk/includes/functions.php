<?php
/**
 * Helper functions for the Garage Management System
 */

// Define default currency symbol if not defined elsewhere (e.g., in config.php)
if (!defined('DEFAULT_CURRENCY_SYMBOL')) {
    define('DEFAULT_CURRENCY_SYMBOL', '$');
}
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'Y-m-d'); // Example date format
}

/**
 * Sanitize user input
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific URL with optional message
 * @param string $url - URL to redirect to
 * @param string $message - Optional message to display after redirect
 * @param string $type - Message type (success, danger, warning, info)
 */
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 * @return boolean
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * @return boolean
 */
function is_admin() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

/**
 * Check if user is a manager
 * @return boolean
 */
function is_manager() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'manager');
}

/**
 * Check if user is a mechanic
 * @return boolean
 */
function is_mechanic() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'mechanic');
}

/**
 * Check if user is a customer
 * @return boolean
 */
function is_customer() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer');
}

/**
 * Check if user has a specific role
 * @param string $role - Role to check
 * @return boolean
 */
function hasRole($role) {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role);
}

/**
 * Format date to a readable format
 * @param string $date - Date to format
 * @param string $format - Format to use
 * @return string - Formatted date
 */
function formatDate($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Format currency
 * @param float $amount - Amount to format
 * @param string $symbol - Currency symbol
 * @return string - Formatted currency
 */
function formatCurrency($amount, $symbol = DEFAULT_CURRENCY_SYMBOL) {
    return $symbol . number_format($amount, 2);
}

/**
 * Generate a random string
 * @param int $length - Length of the string
 * @return string - Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Generate a unique ID
 * @param string $prefix - Prefix for the ID
 * @return string - Unique ID
 */
function generateUniqueId($prefix = '') {
    return uniqid($prefix) . generateRandomString(5);
}

/**
 * Check if a file exists and is readable
 * @param string $file - File path
 * @return boolean
 */
function fileExists($file) {
    return file_exists($file) && is_readable($file);
}

/**
 * Get file extension
 * @param string $filename - Filename
 * @return string - File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Upload a file
 * @param array $file - File data from $_FILES
 * @param string $destination - Destination directory
 * @param array $allowedTypes - Allowed file types
 * @param int $maxSize - Maximum file size in bytes
 * @return array - Status and message/filename
 */
function uploadFile($file, $destination, $allowedTypes = [], $maxSize = 5242880) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Error uploading file.'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['status' => false, 'message' => 'File is too large.'];
    }
    
    // Check file type
    $fileType = getFileExtension($file['name']);
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return ['status' => false, 'message' => 'File type not allowed.'];
    }
    
    // Create destination directory if it doesn't exist
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Generate a unique filename
    $filename = generateUniqueId() . '.' . $fileType;
    $targetPath = $destination . $filename;
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['status' => true, 'filename' => $filename];
    } else {
        return ['status' => false, 'message' => 'Failed to move uploaded file.'];
    }
}

/**
 * Display a flash message
 * @param string $message - Message to display
 * @param string $type - Message type (success, danger, warning, info)
 */
function flashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Display flash message if exists
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['flash_message']);
    }
}

/**
 * Check if a string is a valid date
 * @param string $date - Date to check
 * @param string $format - Format to check against
 * @return boolean
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Calculate age from date of birth
 * @param string $dob - Date of birth
 * @return int - Age
 */
function calculateAge($dob) {
    return date_diff(date_create($dob), date_create('today'))->y;
}

/**
 * Get status badge HTML
 * @param string $status - Status
 * @return string - HTML for the badge
 */
function getStatusBadge($status) {
    $badgeClass = 'bg-secondary';
    
    switch (strtolower($status)) {
        case 'active':
        case 'completed':
        case 'paid':
            $badgeClass = 'bg-success';
            break;
        case 'pending':
        case 'in_progress':
        case 'in progress':
            $badgeClass = 'bg-warning';
            break;
        case 'cancelled':
        case 'inactive':
        case 'overdue':
            $badgeClass = 'bg-danger';
            break;
        case 'partial':
            $badgeClass = 'bg-info';
            break;
    }
    
    return "<span class='badge {$badgeClass}'>{$status}</span>";
}

/**
 * Get Bootstrap badge class for quotation status
 * @param string $status - Quotation status
 * @return string - CSS class name
 */
function get_quotation_status_badge_class($status) {
    switch (strtolower($status ?? 'draft')) {
        case 'sent': return 'info';
        case 'accepted': return 'success';
        case 'rejected': return 'danger';
        case 'expired': return 'warning';
        case 'draft':
        default: return 'secondary';
    }
}

/**
 * Truncate text to a specific length
 * @param string $text - Text to truncate
 * @param int $length - Maximum length
 * @param string $append - Text to append if truncated
 * @return string - Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . $append;
    }
    return $text;
}

/**
 * Get pagination HTML
 * @param int $totalRecords - Total number of records
 * @param int $recordsPerPage - Records per page
 * @param int $currentPage - Current page
 * @param string $url - Base URL for pagination links
 * @return string - Pagination HTML
 */
function getPagination($totalRecords, $recordsPerPage = RECORDS_PER_PAGE, $currentPage = 1, $url = '?') {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($currentPage - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($currentPage + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}
