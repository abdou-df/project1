<?php
/**
 * Vehicle AJAX Handler
 * Handles all AJAX requests related to vehicles
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Vehicle.php';
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
        case 'get_vehicles':
            // Get vehicles list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $search = isset($_POST['search']) ? sanitize($_POST['search']) : '';
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            $type = isset($_POST['type']) ? sanitize($_POST['type']) : '';
            
            // In a real application, you would fetch from database
            // For demonstration, we'll return dummy data
            $vehicles = [
                [
                    'id' => 1,
                    'make' => 'Toyota',
                    'model' => 'Camry',
                    'year' => 2020,
                    'license_plate' => 'ABC123',
                    'vin' => '1HGCM82633A123456',
                    'color' => 'Silver',
                    'customer_id' => 1,
                    'customer_name' => 'John Doe',
                    'type' => VEHICLE_SEDAN,
                    'mileage' => 45000,
                    'last_service' => '2025-02-15'
                ],
                // Add more dummy vehicles here
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'vehicles' => $vehicles,
                'total' => count($vehicles),
                'page' => $page,
                'total_pages' => ceil(count($vehicles) / $limit)
            ];
            break;
            
        case 'get_vehicle':
            // Get single vehicle details
            $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
            
            if ($vehicle_id <= 0) {
                throw new Exception('Invalid vehicle ID');
            }
            
            // In a real application, you would fetch from database
            $vehicle = [
                'id' => $vehicle_id,
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => 2020,
                'license_plate' => 'ABC123',
                'vin' => '1HGCM82633A123456',
                'color' => 'Silver',
                'customer_id' => 1,
                'customer_name' => 'John Doe',
                'type' => VEHICLE_SEDAN,
                'mileage' => 45000,
                'last_service' => '2025-02-15',
                'service_history' => [
                    [
                        'date' => '2025-02-15',
                        'service' => 'Oil Change',
                        'mileage' => 45000,
                        'cost' => 89.99
                    ],
                    // Add more service history
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $vehicle;
            break;
            
        case 'add_vehicle':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['make', 'model', 'year', 'license_plate', 'customer_id'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $vehicle_data = [
                'make' => sanitize($_POST['make']),
                'model' => sanitize($_POST['model']),
                'year' => (int)$_POST['year'],
                'license_plate' => sanitize($_POST['license_plate']),
                'vin' => isset($_POST['vin']) ? sanitize($_POST['vin']) : '',
                'color' => isset($_POST['color']) ? sanitize($_POST['color']) : '',
                'customer_id' => (int)$_POST['customer_id'],
                'type' => isset($_POST['type']) ? sanitize($_POST['type']) : VEHICLE_SEDAN,
                'mileage' => isset($_POST['mileage']) ? (int)$_POST['mileage'] : 0
            ];
            
            // Validate data
            if ($vehicle_data['year'] < 1900 || $vehicle_data['year'] > date('Y') + 1) {
                throw new Exception('Invalid year');
            }
            
            if ($vehicle_data['customer_id'] <= 0) {
                throw new Exception('Invalid customer ID');
            }
            
            // In a real application, you would:
            // 1. Check if license plate is unique
            // 2. Insert into database
            // 3. Return the new vehicle ID
            
            $response['success'] = true;
            $response['message'] = 'Vehicle added successfully';
            $response['data'] = ['id' => 1]; // New vehicle ID
            break;
            
        case 'update_vehicle':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate vehicle ID
            $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
            if ($vehicle_id <= 0) {
                throw new Exception('Invalid vehicle ID');
            }
            
            // Get and sanitize input
            $vehicle_data = [
                'make' => isset($_POST['make']) ? sanitize($_POST['make']) : '',
                'model' => isset($_POST['model']) ? sanitize($_POST['model']) : '',
                'year' => isset($_POST['year']) ? (int)$_POST['year'] : 0,
                'license_plate' => isset($_POST['license_plate']) ? sanitize($_POST['license_plate']) : '',
                'vin' => isset($_POST['vin']) ? sanitize($_POST['vin']) : '',
                'color' => isset($_POST['color']) ? sanitize($_POST['color']) : '',
                'type' => isset($_POST['type']) ? sanitize($_POST['type']) : '',
                'mileage' => isset($_POST['mileage']) ? (int)$_POST['mileage'] : 0
            ];
            
            // In a real application, you would:
            // 1. Validate all fields
            // 2. Check if license plate is unique (if changed)
            // 3. Update database
            
            $response['success'] = true;
            $response['message'] = 'Vehicle updated successfully';
            break;
            
        case 'delete_vehicle':
            // Check if user has permission
            if (!isAdmin()) {
                throw new Exception('Permission denied');
            }
            
            // Validate vehicle ID
            $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
            if ($vehicle_id <= 0) {
                throw new Exception('Invalid vehicle ID');
            }
            
            // In a real application, you would:
            // 1. Check if vehicle can be deleted (no active appointments/invoices)
            // 2. Delete from database or mark as deleted
            
            $response['success'] = true;
            $response['message'] = 'Vehicle deleted successfully';
            break;
            
        case 'get_service_history':
            // Get vehicle service history
            $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
            
            if ($vehicle_id <= 0) {
                throw new Exception('Invalid vehicle ID');
            }
            
            // In a real application, you would fetch from database
            $service_history = [
                [
                    'date' => '2025-02-15',
                    'service' => 'Oil Change',
                    'mileage' => 45000,
                    'cost' => 89.99,
                    'technician' => 'John Smith',
                    'notes' => 'Regular maintenance'
                ],
                // Add more service history
            ];
            
            $response['success'] = true;
            $response['data'] = $service_history;
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
