<?php
/**
 * Inventory AJAX Handler
 * Handles all AJAX requests related to parts inventory
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Inventory.php';
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
        case 'get_parts':
            // Get parts list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $search = isset($_POST['search']) ? sanitize($_POST['search']) : '';
            $category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
            $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
            
            // In a real application, you would fetch from database
            $parts = [
                [
                    'id' => 1,
                    'name' => 'Oil Filter',
                    'part_number' => 'OF-123',
                    'category' => PART_CATEGORY_FILTER,
                    'description' => 'High-quality oil filter',
                    'price' => 15.99,
                    'cost' => 8.50,
                    'quantity' => 25,
                    'min_quantity' => 10,
                    'location' => 'A1-B2',
                    'supplier' => [
                        'id' => 1,
                        'name' => 'Auto Parts Inc'
                    ],
                    'status' => PART_STATUS_ACTIVE,
                    'last_ordered' => '2025-02-15',
                    'last_received' => '2025-02-18'
                ],
                // Add more parts
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'parts' => $parts,
                'total' => count($parts),
                'page' => $page,
                'total_pages' => ceil(count($parts) / $limit)
            ];
            break;
            
        case 'get_part':
            // Get single part details
            $part_id = isset($_POST['part_id']) ? (int)$_POST['part_id'] : 0;
            
            if ($part_id <= 0) {
                throw new Exception('Invalid part ID');
            }
            
            // In a real application, you would fetch from database
            $part = [
                'id' => $part_id,
                'name' => 'Oil Filter',
                'part_number' => 'OF-123',
                'category' => PART_CATEGORY_FILTER,
                'description' => 'High-quality oil filter',
                'specifications' => 'Compatible with most Toyota vehicles',
                'price' => 15.99,
                'cost' => 8.50,
                'markup' => 88.12, // percentage
                'quantity' => 25,
                'min_quantity' => 10,
                'max_quantity' => 50,
                'location' => 'A1-B2',
                'supplier' => [
                    'id' => 1,
                    'name' => 'Auto Parts Inc',
                    'contact' => 'John Smith',
                    'phone' => '+1234567890',
                    'email' => 'john@autoparts.com'
                ],
                'status' => PART_STATUS_ACTIVE,
                'last_ordered' => '2025-02-15',
                'last_received' => '2025-02-18',
                'warranty' => '12 months',
                'notes' => 'Popular item, keep well stocked',
                'history' => [
                    [
                        'date' => '2025-02-18',
                        'type' => 'received',
                        'quantity' => 30,
                        'price' => 8.50,
                        'reference' => 'PO-2025-001'
                    ],
                    [
                        'date' => '2025-02-20',
                        'type' => 'used',
                        'quantity' => 5,
                        'reference' => 'INV-2025-001'
                    ]
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $part;
            break;
            
        case 'add_part':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['name', 'part_number', 'category', 'price', 'cost', 'quantity'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $part_data = [
                'name' => sanitize($_POST['name']),
                'part_number' => sanitize($_POST['part_number']),
                'category' => sanitize($_POST['category']),
                'description' => isset($_POST['description']) ? sanitize($_POST['description']) : '',
                'specifications' => isset($_POST['specifications']) ? sanitize($_POST['specifications']) : '',
                'price' => (float)$_POST['price'],
                'cost' => (float)$_POST['cost'],
                'quantity' => (int)$_POST['quantity'],
                'min_quantity' => isset($_POST['min_quantity']) ? (int)$_POST['min_quantity'] : 0,
                'max_quantity' => isset($_POST['max_quantity']) ? (int)$_POST['max_quantity'] : 0,
                'location' => isset($_POST['location']) ? sanitize($_POST['location']) : '',
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'warranty' => isset($_POST['warranty']) ? sanitize($_POST['warranty']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // Validate data
            if ($part_data['price'] <= 0) {
                throw new Exception('Price must be greater than zero');
            }
            
            if ($part_data['cost'] <= 0) {
                throw new Exception('Cost must be greater than zero');
            }
            
            if ($part_data['quantity'] < 0) {
                throw new Exception('Quantity cannot be negative');
            }
            
            // In a real application, you would:
            // 1. Check if part number is unique
            // 2. Insert into database
            // 3. Create initial inventory transaction
            // 4. Return the new part ID
            
            $response['success'] = true;
            $response['message'] = 'Part added successfully';
            $response['data'] = ['id' => 1]; // New part ID
            break;
            
        case 'update_part':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate part ID
            $part_id = isset($_POST['part_id']) ? (int)$_POST['part_id'] : 0;
            if ($part_id <= 0) {
                throw new Exception('Invalid part ID');
            }
            
            // Get and sanitize input
            $part_data = [
                'name' => isset($_POST['name']) ? sanitize($_POST['name']) : '',
                'description' => isset($_POST['description']) ? sanitize($_POST['description']) : '',
                'specifications' => isset($_POST['specifications']) ? sanitize($_POST['specifications']) : '',
                'price' => isset($_POST['price']) ? (float)$_POST['price'] : 0,
                'cost' => isset($_POST['cost']) ? (float)$_POST['cost'] : 0,
                'min_quantity' => isset($_POST['min_quantity']) ? (int)$_POST['min_quantity'] : 0,
                'max_quantity' => isset($_POST['max_quantity']) ? (int)$_POST['max_quantity'] : 0,
                'location' => isset($_POST['location']) ? sanitize($_POST['location']) : '',
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'status' => isset($_POST['status']) ? sanitize($_POST['status']) : '',
                'warranty' => isset($_POST['warranty']) ? sanitize($_POST['warranty']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // In a real application, you would:
            // 1. Validate all fields
            // 2. Update database
            // 3. Log changes
            
            $response['success'] = true;
            $response['message'] = 'Part updated successfully';
            break;
            
        case 'adjust_quantity':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['part_id', 'quantity', 'type', 'reason'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $adjustment_data = [
                'part_id' => (int)$_POST['part_id'],
                'quantity' => (int)$_POST['quantity'],
                'type' => sanitize($_POST['type']), // 'add' or 'subtract'
                'reason' => sanitize($_POST['reason']),
                'reference' => isset($_POST['reference']) ? sanitize($_POST['reference']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // In a real application, you would:
            // 1. Check if adjustment is valid
            // 2. Update quantity in database
            // 3. Create inventory transaction
            // 4. Check if new quantity is below minimum
            
            $response['success'] = true;
            $response['message'] = 'Quantity adjusted successfully';
            break;
            
        case 'get_low_stock':
            // Get parts with quantity below minimum
            // In a real application, you would fetch from database
            $low_stock = [
                [
                    'id' => 1,
                    'name' => 'Oil Filter',
                    'part_number' => 'OF-123',
                    'quantity' => 5,
                    'min_quantity' => 10,
                    'supplier' => 'Auto Parts Inc',
                    'last_ordered' => '2025-02-15'
                ],
                // Add more parts
            ];
            
            $response['success'] = true;
            $response['data'] = $low_stock;
            break;
            
        case 'get_inventory_value':
            // Get total inventory value
            $category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
            
            // In a real application, you would calculate from database
            $inventory_value = [
                'total_cost' => 25000.00,
                'total_retail' => 45000.00,
                'potential_profit' => 20000.00,
                'by_category' => [
                    PART_CATEGORY_FILTER => [
                        'cost' => 5000.00,
                        'retail' => 9000.00
                    ],
                    // Add more categories
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $inventory_value;
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
