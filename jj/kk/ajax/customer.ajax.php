<?php
/**
 * Customer AJAX Handler
 * Handles all AJAX requests related to customers
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Customer.php';
require_once '../classes/Database.php';

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not permitted');
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get the action from the request
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Handle different actions
    switch ($action) {
        case 'get_customers':
            // Get customers list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $search = isset($_POST['search']) ? sanitize($_POST['search']) : '';
            $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
            
            // In a real application, you would fetch from database
            $customers = [
                [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '+1234567890',
                    'address' => '123 Main St',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10001',
                    'status' => CUSTOMER_ACTIVE,
                    'vehicles_count' => 2,
                    'last_visit' => '2025-02-15',
                    'total_spent' => 1250.50
                ],
                // Add more dummy customers here
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'customers' => $customers,
                'total' => count($customers),
                'page' => $page,
                'total_pages' => ceil(count($customers) / $limit)
            ];
            break;
            
        case 'get_customer':
            // Get single customer details
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            
            if ($customer_id <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // In a real application, you would fetch from database
            $customer = [
                'id' => $customer_id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'status' => CUSTOMER_ACTIVE,
                'notes' => 'VIP customer',
                'created_at' => '2025-01-01',
                'vehicles' => [
                    [
                        'id' => 1,
                        'make' => 'Toyota',
                        'model' => 'Camry',
                        'year' => 2020,
                        'license_plate' => 'ABC123'
                    ],
                    // Add more vehicles
                ],
                'appointments' => [
                    [
                        'id' => 1,
                        'date' => '2025-02-15',
                        'service' => 'Oil Change',
                        'status' => APPOINTMENT_COMPLETED,
                        'amount' => 89.99
                    ],
                    // Add more appointments
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $customer;
            break;
            
        case 'add_customer':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['name', 'email', 'phone'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $customer_data = [
                'name' => sanitize($_POST['name']),
                'email' => sanitize($_POST['email']),
                'phone' => sanitize($_POST['phone']),
                'address' => isset($_POST['address']) ? sanitize($_POST['address']) : '',
                'city' => isset($_POST['city']) ? sanitize($_POST['city']) : '',
                'state' => isset($_POST['state']) ? sanitize($_POST['state']) : '',
                'zip' => isset($_POST['zip']) ? sanitize($_POST['zip']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // Validate email
            if (!filter_var($customer_data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            // In a real application, you would:
            // 1. Check if email is unique
            // 2. Insert into database
            // 3. Return the new customer ID
            
            $response['success'] = true;
            $response['message'] = 'Customer added successfully';
            $response['data'] = ['id' => 1]; // New customer ID
            break;
            
        case 'update_customer':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate customer ID
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            if ($customer_id <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // Get and sanitize input
            $customer_data = [
                'name' => isset($_POST['name']) ? sanitize($_POST['name']) : '',
                'email' => isset($_POST['email']) ? sanitize($_POST['email']) : '',
                'phone' => isset($_POST['phone']) ? sanitize($_POST['phone']) : '',
                'address' => isset($_POST['address']) ? sanitize($_POST['address']) : '',
                'city' => isset($_POST['city']) ? sanitize($_POST['city']) : '',
                'state' => isset($_POST['state']) ? sanitize($_POST['state']) : '',
                'zip' => isset($_POST['zip']) ? sanitize($_POST['zip']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : '',
                'status' => isset($_POST['status']) ? sanitize($_POST['status']) : ''
            ];
            
            // Validate email if provided
            if (!empty($customer_data['email']) && !filter_var($customer_data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            // In a real application, you would:
            // 1. Validate all fields
            // 2. Check if email is unique (if changed)
            // 3. Update database
            
            $response['success'] = true;
            $response['message'] = 'Customer updated successfully';
            break;
            
        case 'delete_customer':
            // Check if user has permission
            if (!isAdmin()) {
                throw new Exception('Permission denied');
            }
            
            // Validate customer ID
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            if ($customer_id <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // In a real application, you would:
            // 1. Check if customer can be deleted (no active appointments/invoices)
            // 2. Delete from database or mark as deleted
            
            $response['success'] = true;
            $response['message'] = 'Customer deleted successfully';
            break;
            
        case 'get_customer_vehicles':
            // Get customer's vehicles
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            
            if ($customer_id <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // In a real application, you would fetch from database
            $vehicles = [
                [
                    'id' => 1,
                    'make' => 'Toyota',
                    'model' => 'Camry',
                    'year' => 2020,
                    'license_plate' => 'ABC123',
                    'status' => VEHICLE_ACTIVE,
                    'last_service' => '2025-02-15'
                ],
                // Add more vehicles
            ];
            
            $response['success'] = true;
            $response['data'] = $vehicles;
            break;
            
        case 'get_customer_history':
            // Get customer's service history
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            
            if ($customer_id <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // In a real application, you would fetch from database
            $history = [
                'appointments' => [
                    [
                        'id' => 1,
                        'date' => '2025-02-15',
                        'vehicle' => 'Toyota Camry',
                        'service' => 'Oil Change',
                        'status' => APPOINTMENT_COMPLETED,
                        'amount' => 89.99
                    ],
                    // Add more appointments
                ],
                'invoices' => [
                    [
                        'id' => 1,
                        'date' => '2025-02-15',
                        'amount' => 89.99,
                        'status' => INVOICE_PAID,
                        'payment_method' => PAYMENT_CREDIT_CARD
                    ],
                    // Add more invoices
                ],
                'total_spent' => 1250.50,
                'total_visits' => 5,
                'preferred_service_day' => 'Saturday',
                'notes' => 'Regular customer, prefers morning appointments'
            ];
            
            $response['success'] = true;
            $response['data'] = $history;
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
