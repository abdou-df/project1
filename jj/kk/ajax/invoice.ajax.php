<?php
/**
 * Invoice AJAX Handler
 * Handles all AJAX requests related to invoices and payments
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Invoice.php';
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
        case 'get_invoices':
            // Get invoices list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
            $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : '';
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : '';
            
            // In a real application, you would fetch from database
            $invoices = [
                [
                    'id' => 1,
                    'invoice_number' => 'INV-2025-001',
                    'date' => '2025-03-21',
                    'customer' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'email' => 'john@example.com'
                    ],
                    'vehicle' => [
                        'id' => 1,
                        'make' => 'Toyota',
                        'model' => 'Camry',
                        'license_plate' => 'ABC123'
                    ],
                    'services' => [
                        [
                            'id' => 1,
                            'name' => 'Oil Change',
                            'price' => 89.99,
                            'quantity' => 1
                        ]
                    ],
                    'parts' => [
                        [
                            'id' => 1,
                            'name' => 'Oil Filter',
                            'price' => 15.99,
                            'quantity' => 1
                        ]
                    ],
                    'subtotal' => 105.98,
                    'tax' => 8.48,
                    'total' => 114.46,
                    'status' => INVOICE_PAID,
                    'payment_method' => PAYMENT_CREDIT_CARD,
                    'payment_date' => '2025-03-21'
                ],
                // Add more invoices
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'invoices' => $invoices,
                'total' => count($invoices),
                'page' => $page,
                'total_pages' => ceil(count($invoices) / $limit)
            ];
            break;
            
        case 'get_invoice':
            // Get single invoice details
            $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
            
            if ($invoice_id <= 0) {
                throw new Exception('Invalid invoice ID');
            }
            
            // In a real application, you would fetch from database
            $invoice = [
                'id' => $invoice_id,
                'invoice_number' => 'INV-2025-001',
                'date' => '2025-03-21',
                'due_date' => '2025-04-04',
                'customer' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '+1234567890',
                    'address' => '123 Main St',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10001'
                ],
                'vehicle' => [
                    'id' => 1,
                    'make' => 'Toyota',
                    'model' => 'Camry',
                    'year' => 2020,
                    'license_plate' => 'ABC123',
                    'vin' => '1HGCM82633A123456'
                ],
                'services' => [
                    [
                        'id' => 1,
                        'name' => 'Oil Change',
                        'description' => 'Full synthetic oil change service',
                        'price' => 89.99,
                        'quantity' => 1,
                        'total' => 89.99
                    ]
                ],
                'parts' => [
                    [
                        'id' => 1,
                        'name' => 'Oil Filter',
                        'part_number' => 'OF-123',
                        'price' => 15.99,
                        'quantity' => 1,
                        'total' => 15.99
                    ]
                ],
                'subtotal' => 105.98,
                'tax_rate' => 0.08,
                'tax' => 8.48,
                'total' => 114.46,
                'status' => INVOICE_PAID,
                'payment_method' => PAYMENT_CREDIT_CARD,
                'payment_date' => '2025-03-21',
                'notes' => 'Regular maintenance service',
                'terms' => 'Payment due within 14 days',
                'technician' => [
                    'id' => 1,
                    'name' => 'Mike Smith'
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $invoice;
            break;
            
        case 'create_invoice':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['customer_id', 'vehicle_id', 'services', 'date'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $invoice_data = [
                'customer_id' => (int)$_POST['customer_id'],
                'vehicle_id' => (int)$_POST['vehicle_id'],
                'services' => json_decode($_POST['services'], true),
                'parts' => isset($_POST['parts']) ? json_decode($_POST['parts'], true) : [],
                'date' => sanitize($_POST['date']),
                'due_date' => isset($_POST['due_date']) ? sanitize($_POST['due_date']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : '',
                'technician_id' => isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0
            ];
            
            // Validate services array
            if (!is_array($invoice_data['services']) || empty($invoice_data['services'])) {
                throw new Exception('At least one service is required');
            }
            
            // In a real application, you would:
            // 1. Calculate totals
            // 2. Generate invoice number
            // 3. Insert into database
            // 4. Generate PDF
            // 5. Send email to customer
            
            $response['success'] = true;
            $response['message'] = 'Invoice created successfully';
            $response['data'] = [
                'id' => 1,
                'invoice_number' => 'INV-2025-001'
            ];
            break;
            
        case 'update_invoice':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate invoice ID
            $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
            if ($invoice_id <= 0) {
                throw new Exception('Invalid invoice ID');
            }
            
            // Get and sanitize input
            $invoice_data = [
                'services' => isset($_POST['services']) ? json_decode($_POST['services'], true) : null,
                'parts' => isset($_POST['parts']) ? json_decode($_POST['parts'], true) : null,
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : null,
                'status' => isset($_POST['status']) ? sanitize($_POST['status']) : null,
                'due_date' => isset($_POST['due_date']) ? sanitize($_POST['due_date']) : null
            ];
            
            // In a real application, you would:
            // 1. Validate all fields
            // 2. Recalculate totals if items changed
            // 3. Update database
            // 4. Regenerate PDF if necessary
            
            $response['success'] = true;
            $response['message'] = 'Invoice updated successfully';
            break;
            
        case 'delete_invoice':
            // Check if user has permission
            if (!isAdmin()) {
                throw new Exception('Permission denied');
            }
            
            // Validate invoice ID
            $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
            if ($invoice_id <= 0) {
                throw new Exception('Invalid invoice ID');
            }
            
            // In a real application, you would:
            // 1. Check if invoice can be deleted (not paid)
            // 2. Delete from database or mark as deleted
            
            $response['success'] = true;
            $response['message'] = 'Invoice deleted successfully';
            break;
            
        case 'record_payment':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Validate required fields
            $required_fields = ['invoice_id', 'amount', 'payment_method'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $payment_data = [
                'invoice_id' => (int)$_POST['invoice_id'],
                'amount' => (float)$_POST['amount'],
                'payment_method' => sanitize($_POST['payment_method']),
                'payment_date' => isset($_POST['payment_date']) ? sanitize($_POST['payment_date']) : date('Y-m-d'),
                'reference' => isset($_POST['reference']) ? sanitize($_POST['reference']) : '',
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // In a real application, you would:
            // 1. Validate payment amount against invoice total
            // 2. Record payment in database
            // 3. Update invoice status
            // 4. Generate receipt
            // 5. Send confirmation email
            
            $response['success'] = true;
            $response['message'] = 'Payment recorded successfully';
            break;
            
        case 'send_invoice':
            // Send invoice to customer
            $invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
            
            if ($invoice_id <= 0) {
                throw new Exception('Invalid invoice ID');
            }
            
            // In a real application, you would:
            // 1. Generate PDF if not exists
            // 2. Send email with PDF attachment
            // 3. Record email sent in database
            
            $response['success'] = true;
            $response['message'] = 'Invoice sent successfully';
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
