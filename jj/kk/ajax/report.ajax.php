<?php
/**
 * Report AJAX Handler
 * Handles all AJAX requests related to reports and analytics
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Report.php';
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
        case 'get_sales_report':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Get parameters
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : date('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : date('Y-m-d');
            $group_by = isset($_POST['group_by']) ? sanitize($_POST['group_by']) : 'day'; // day, week, month, year
            
            // In a real application, you would fetch from database
            $sales_data = [
                'summary' => [
                    'total_sales' => 25000.00,
                    'total_services' => 150,
                    'total_parts' => 300,
                    'average_ticket' => 166.67,
                    'top_service' => 'Oil Change',
                    'top_part' => 'Oil Filter'
                ],
                'chart_data' => [
                    'labels' => ['2025-03-15', '2025-03-16', '2025-03-17', '2025-03-18', '2025-03-19', '2025-03-20', '2025-03-21'],
                    'services' => [1200, 900, 1500, 1100, 1300, 1400, 1600],
                    'parts' => [500, 400, 600, 450, 550, 500, 700]
                ],
                'services' => [
                    [
                        'name' => 'Oil Change',
                        'count' => 45,
                        'revenue' => 4050.00
                    ],
                    [
                        'name' => 'Brake Service',
                        'count' => 30,
                        'revenue' => 6000.00
                    ]
                ],
                'parts' => [
                    [
                        'name' => 'Oil Filter',
                        'quantity' => 45,
                        'revenue' => 719.55
                    ],
                    [
                        'name' => 'Brake Pads',
                        'quantity' => 60,
                        'revenue' => 1799.40
                    ]
                ],
                'payment_methods' => [
                    [
                        'method' => PAYMENT_CREDIT_CARD,
                        'count' => 80,
                        'amount' => 15000.00
                    ],
                    [
                        'method' => PAYMENT_CASH,
                        'count' => 40,
                        'amount' => 6000.00
                    ],
                    [
                        'method' => PAYMENT_DEBIT_CARD,
                        'count' => 30,
                        'amount' => 4000.00
                    ]
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $sales_data;
            break;
            
        case 'get_service_report':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Get parameters
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : date('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : date('Y-m-d');
            $technician_id = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
            
            // In a real application, you would fetch from database
            $service_data = [
                'summary' => [
                    'total_appointments' => 200,
                    'completed' => 180,
                    'cancelled' => 15,
                    'no_show' => 5,
                    'average_duration' => 75, // minutes
                    'satisfaction_rate' => 4.8
                ],
                'chart_data' => [
                    'labels' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'appointments' => [35, 40, 38, 42, 35, 10, 0],
                    'completion_rate' => [95, 92, 94, 90, 93, 100, 0]
                ],
                'services' => [
                    [
                        'name' => 'Oil Change',
                        'count' => 45,
                        'average_duration' => 60,
                        'satisfaction_rate' => 4.9
                    ],
                    [
                        'name' => 'Brake Service',
                        'count' => 30,
                        'average_duration' => 90,
                        'satisfaction_rate' => 4.8
                    ]
                ],
                'technicians' => [
                    [
                        'name' => 'Mike Smith',
                        'appointments' => 60,
                        'completed' => 58,
                        'average_duration' => 70,
                        'satisfaction_rate' => 4.9
                    ],
                    [
                        'name' => 'John Johnson',
                        'appointments' => 55,
                        'completed' => 52,
                        'average_duration' => 75,
                        'satisfaction_rate' => 4.7
                    ]
                ],
                'peak_hours' => [
                    [
                        'hour' => 9,
                        'count' => 35
                    ],
                    [
                        'hour' => 10,
                        'count' => 40
                    ],
                    [
                        'hour' => 11,
                        'count' => 38
                    ]
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $service_data;
            break;
            
        case 'get_inventory_report':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Get parameters
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : date('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : date('Y-m-d');
            $category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
            
            // In a real application, you would fetch from database
            $inventory_data = [
                'summary' => [
                    'total_parts' => 500,
                    'total_value' => 45000.00,
                    'low_stock' => 15,
                    'out_of_stock' => 3,
                    'turnover_rate' => 2.5
                ],
                'chart_data' => [
                    'labels' => ['Filters', 'Brakes', 'Oil', 'Belts', 'Batteries'],
                    'quantity' => [100, 80, 120, 60, 40],
                    'value' => [5000, 12000, 3000, 4000, 8000]
                ],
                'categories' => [
                    [
                        'name' => PART_CATEGORY_FILTER,
                        'parts_count' => 50,
                        'total_value' => 5000.00,
                        'turnover_rate' => 3.0
                    ],
                    [
                        'name' => PART_CATEGORY_BRAKE,
                        'parts_count' => 40,
                        'total_value' => 12000.00,
                        'turnover_rate' => 2.0
                    ]
                ],
                'movements' => [
                    [
                        'date' => '2025-03-21',
                        'part_number' => 'OF-123',
                        'name' => 'Oil Filter',
                        'type' => 'received',
                        'quantity' => 30,
                        'value' => 255.00
                    ],
                    [
                        'date' => '2025-03-21',
                        'part_number' => 'OF-123',
                        'name' => 'Oil Filter',
                        'type' => 'used',
                        'quantity' => 5,
                        'value' => 42.50
                    ]
                ],
                'suppliers' => [
                    [
                        'name' => 'Auto Parts Inc',
                        'parts_count' => 200,
                        'total_value' => 20000.00,
                        'orders' => 15
                    ],
                    [
                        'name' => 'Parts Express',
                        'parts_count' => 150,
                        'total_value' => 15000.00,
                        'orders' => 12
                    ]
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $inventory_data;
            break;
            
        case 'get_customer_report':
            // Check if user has permission
            if (!isAdmin() && !isManager()) {
                throw new Exception('Permission denied');
            }
            
            // Get parameters
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : date('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : date('Y-m-d');
            
            // In a real application, you would fetch from database
            $customer_data = [
                'summary' => [
                    'total_customers' => 500,
                    'new_customers' => 50,
                    'active_customers' => 400,
                    'average_spend' => 250.00,
                    'satisfaction_rate' => 4.8
                ],
                'chart_data' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'new_customers' => [40, 35, 50, 45, 48, 50],
                    'revenue' => [10000, 9500, 12500, 11000, 12000, 12500]
                ],
                'segments' => [
                    [
                        'name' => 'VIP',
                        'count' => 50,
                        'total_spend' => 25000.00,
                        'average_spend' => 500.00,
                        'visits' => 200
                    ],
                    [
                        'name' => 'Regular',
                        'count' => 300,
                        'total_spend' => 60000.00,
                        'average_spend' => 200.00,
                        'visits' => 600
                    ],
                    [
                        'name' => 'Occasional',
                        'count' => 150,
                        'total_spend' => 15000.00,
                        'average_spend' => 100.00,
                        'visits' => 150
                    ]
                ],
                'retention' => [
                    '30_days' => 85,
                    '60_days' => 75,
                    '90_days' => 70,
                    '180_days' => 60,
                    '365_days' => 45
                ],
                'top_customers' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'total_spend' => 2500.00,
                        'visits' => 12,
                        'last_visit' => '2025-03-15'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'total_spend' => 2200.00,
                        'visits' => 10,
                        'last_visit' => '2025-03-18'
                    ]
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $customer_data;
            break;
            
        case 'get_financial_report':
            // Check if user has permission
            if (!isAdmin()) {
                throw new Exception('Permission denied');
            }
            
            // Get parameters
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : date('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : date('Y-m-d');
            
            // In a real application, you would fetch from database
            $financial_data = [
                'summary' => [
                    'revenue' => 25000.00,
                    'costs' => 15000.00,
                    'profit' => 10000.00,
                    'margin' => 40.00,
                    'outstanding' => 2500.00
                ],
                'chart_data' => [
                    'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    'revenue' => [6000, 7000, 6500, 5500],
                    'costs' => [3600, 4200, 3900, 3300],
                    'profit' => [2400, 2800, 2600, 2200]
                ],
                'revenue_sources' => [
                    [
                        'source' => 'Services',
                        'amount' => 15000.00,
                        'percentage' => 60
                    ],
                    [
                        'source' => 'Parts',
                        'amount' => 10000.00,
                        'percentage' => 40
                    ]
                ],
                'costs_breakdown' => [
                    [
                        'category' => 'Parts Inventory',
                        'amount' => 8000.00,
                        'percentage' => 53.33
                    ],
                    [
                        'category' => 'Labor',
                        'amount' => 5000.00,
                        'percentage' => 33.33
                    ],
                    [
                        'category' => 'Overhead',
                        'amount' => 2000.00,
                        'percentage' => 13.34
                    ]
                ],
                'invoices' => [
                    'total' => 200,
                    'paid' => 180,
                    'pending' => 15,
                    'overdue' => 5,
                    'average_days_to_pay' => 7
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $financial_data;
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
