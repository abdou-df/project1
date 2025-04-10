<?php
/**
 * Constants file for the Garage Management System
 */

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_EMPLOYEE', 'employee');
define('ROLE_CUSTOMER', 'customer');

// Status constants
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_COMPLETED', 'completed');
define('STATUS_CANCELLED', 'cancelled');

// Service status
define('SERVICE_PENDING', 'pending');
define('SERVICE_IN_PROGRESS', 'in_progress');
define('SERVICE_COMPLETED', 'completed');
define('SERVICE_CANCELLED', 'cancelled');

// Payment status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_PARTIAL', 'partial');
define('PAYMENT_OVERDUE', 'overdue');

// Payment methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_CARD', 'card');
define('PAYMENT_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_CHEQUE', 'cheque');

// Vehicle types
define('VEHICLE_SEDAN', 'sedan');
define('VEHICLE_SUV', 'suv');
define('VEHICLE_TRUCK', 'truck');
define('VEHICLE_VAN', 'van');
define('VEHICLE_MOTORCYCLE', 'motorcycle');
define('VEHICLE_OTHER', 'other');

// Vehicle transmission types
define('TRANSMISSION_MANUAL', 'manual');
define('TRANSMISSION_AUTOMATIC', 'automatic');
define('TRANSMISSION_CVT', 'cvt');

// Fuel types
define('FUEL_GASOLINE', 'gasoline');
define('FUEL_DIESEL', 'diesel');
define('FUEL_ELECTRIC', 'electric');
define('FUEL_HYBRID', 'hybrid');
define('FUEL_OTHER', 'other');

// Service categories
define('SERVICE_CAT_MAINTENANCE', 'maintenance');
define('SERVICE_CAT_REPAIR', 'repair');
define('SERVICE_CAT_DIAGNOSTIC', 'diagnostic');
define('SERVICE_CAT_BODY_WORK', 'body_work');
define('SERVICE_CAT_OTHER', 'other');

// Inventory categories
define('INVENTORY_CAT_ENGINE', 'engine');
define('INVENTORY_CAT_TRANSMISSION', 'transmission');
define('INVENTORY_CAT_BRAKES', 'brakes');
define('INVENTORY_CAT_SUSPENSION', 'suspension');
define('INVENTORY_CAT_ELECTRICAL', 'electrical');
define('INVENTORY_CAT_BODY', 'body');
define('INVENTORY_CAT_FLUIDS', 'fluids');
define('INVENTORY_CAT_OTHER', 'other');

// Time slots (in minutes)
define('TIME_SLOT_15', 15);
define('TIME_SLOT_30', 30);
define('TIME_SLOT_60', 60);

// File upload limits (in bytes)
define('MAX_FILE_SIZE', 5242880); // 5MB
define('MAX_TOTAL_FILES_SIZE', 20971520); // 20MB

// Pagination
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Date formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Currency
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('DECIMAL_PLACES', 2);

// Tax settings
define('DEFAULT_TAX_RATE', 8.00);
define('ENABLE_TAX', true);

// Invoice settings
define('INVOICE_PREFIX', 'INV-');
define('INVOICE_NUMBER_LENGTH', 6);
define('INVOICE_DUE_DAYS', 15);

// Email settings
define('EMAIL_FROM_NAME', 'Auto Care Garage');
define('EMAIL_FROM_ADDRESS', 'noreply@autocare.com');
define('ENABLE_EMAIL_NOTIFICATIONS', true);

// System settings
define('SYSTEM_NAME', 'Auto Care Garage Management System');
define('SYSTEM_VERSION', '1.0.0');
define('ENABLE_DEBUG', false);
define('MAINTENANCE_MODE', false);

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 2592000); // 30 days

// Security settings
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
define('REQUIRE_STRONG_PASSWORD', true);

// API settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TIMEOUT', 30); // seconds

// Company information
define('COMPANY_NAME', 'Auto Care Garage');
define('COMPANY_ADDRESS', '123 Garage Street, Auto City, AC 12345');
define('COMPANY_PHONE', '+1 (123) 456-7890');
define('COMPANY_EMAIL', 'info@autocare.com');
define('COMPANY_WEBSITE', 'www.autocare.com');
define('COMPANY_TAX_ID', 'TAX123456789');
define('COMPANY_LOGO', 'assets/images/logo.png');



// Inventory part status
define('PART_STATUS_ACTIVE', 'active');
define('PART_STATUS_INACTIVE', 'inactive');
define('PART_STATUS_DISCONTINUED', 'discontinued');
define('PART_STATUS_BACKORDERED', 'backordered');