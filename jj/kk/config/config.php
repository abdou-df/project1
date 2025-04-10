<?php
/**
 * Main configuration file for the Garage Management System
 */

// Application settings
define('APP_NAME', 'Garage Master');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/garage');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'garage_db');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Default settings
define('DEFAULT_LANGUAGE', 'en');
define('DEFAULT_TIMEZONE', 'UTC');
define('RECORDS_PER_PAGE', 10);

// Email settings
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@example.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@example.com');
define('SMTP_FROM_NAME', 'Garage Master');

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
require_once 'database.php';

// Load constants
require_once 'constants.php';
