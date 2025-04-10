<?php
/**
 * Service AJAX Handler
 * Handles all AJAX requests related to services and appointments
 */

// Start session and include required files
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../classes/Service.php';
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
        case 'get_services':
            // Get services list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
            $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
            
            // In a real application, you would fetch from database
            $services = [
                [
                    'id' => 1,
                    'name' => 'Oil Change',
                    'category' => SERVICE_MAINTENANCE,
                    'description' => 'Full synthetic oil change service',
                    'duration' => 60, // minutes
                    'price' => 89.99,
                    'status' => SERVICE_ACTIVE
                ],
                // Add more services
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'services' => $services,
                'total' => count($services),
                'page' => $page,
                'total_pages' => ceil(count($services) / $limit)
            ];
            break;
            
        case 'get_appointments':
            // Get appointments list with pagination and filters
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : ITEMS_PER_PAGE;
            $start_date = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : '';
            $end_date = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : '';
            $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
            $technician_id = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
            
            // In a real application, you would fetch from database
            $appointments = [
                [
                    'id' => 1,
                    'date' => '2025-03-21',
                    'time' => '09:00:00',
                    'customer' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'phone' => '+1234567890'
                    ],
                    'vehicle' => [
                        'id' => 1,
                        'make' => 'Toyota',
                        'model' => 'Camry',
                        'year' => 2020,
                        'license_plate' => 'ABC123'
                    ],
                    'service' => [
                        'id' => 1,
                        'name' => 'Oil Change',
                        'duration' => 60
                    ],
                    'technician' => [
                        'id' => 1,
                        'name' => 'Mike Smith'
                    ],
                    'status' => APPOINTMENT_SCHEDULED,
                    'notes' => 'Customer prefers morning appointments'
                ],
                // Add more appointments
            ];
            
            $response['success'] = true;
            $response['data'] = [
                'appointments' => $appointments,
                'total' => count($appointments),
                'page' => $page,
                'total_pages' => ceil(count($appointments) / $limit)
            ];
            break;
            
        case 'get_available_slots':
            // Get available appointment slots for a given date
            $date = isset($_POST['date']) ? sanitize($_POST['date']) : '';
            $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
            $technician_id = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
            
            if (empty($date)) {
                throw new Exception('Date is required');
            }
            
            if ($service_id <= 0) {
                throw new Exception('Invalid service ID');
            }
            
            // In a real application, you would:
            // 1. Get service duration
            // 2. Get working hours for the date
            // 3. Get existing appointments
            // 4. Calculate available slots
            
            $slots = [
                ['time' => '09:00', 'available' => true],
                ['time' => '10:00', 'available' => true],
                ['time' => '11:00', 'available' => false],
                ['time' => '13:00', 'available' => true],
                ['time' => '14:00', 'available' => true],
                ['time' => '15:00', 'available' => true],
                ['time' => '16:00', 'available' => true]
            ];
            
            $response['success'] = true;
            $response['data'] = $slots;
            break;
            
        case 'schedule_appointment':
            // Schedule new appointment
            // Validate required fields
            $required_fields = ['date', 'time', 'customer_id', 'vehicle_id', 'service_id'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Get and sanitize input
            $appointment_data = [
                'date' => sanitize($_POST['date']),
                'time' => sanitize($_POST['time']),
                'customer_id' => (int)$_POST['customer_id'],
                'vehicle_id' => (int)$_POST['vehicle_id'],
                'service_id' => (int)$_POST['service_id'],
                'technician_id' => isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0,
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // Validate date and time
            if (strtotime($appointment_data['date']) < strtotime('today')) {
                throw new Exception('Invalid appointment date');
            }
            
            // In a real application, you would:
            // 1. Check if slot is still available
            // 2. Insert into database
            // 3. Send confirmation email
            // 4. Return the new appointment ID
            
            $response['success'] = true;
            $response['message'] = 'Appointment scheduled successfully';
            $response['data'] = ['id' => 1]; // New appointment ID
            break;
            
        case 'update_appointment':
            // Update existing appointment
            $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
            
            if ($appointment_id <= 0) {
                throw new Exception('Invalid appointment ID');
            }
            
            // Get and sanitize input
            $appointment_data = [
                'date' => isset($_POST['date']) ? sanitize($_POST['date']) : '',
                'time' => isset($_POST['time']) ? sanitize($_POST['time']) : '',
                'status' => isset($_POST['status']) ? sanitize($_POST['status']) : '',
                'technician_id' => isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0,
                'notes' => isset($_POST['notes']) ? sanitize($_POST['notes']) : ''
            ];
            
            // In a real application, you would:
            // 1. Validate all fields
            // 2. Check if new slot is available (if date/time changed)
            // 3. Update database
            // 4. Send notification if necessary
            
            $response['success'] = true;
            $response['message'] = 'Appointment updated successfully';
            break;
            
        case 'cancel_appointment':
            // Cancel appointment
            $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
            $reason = isset($_POST['reason']) ? sanitize($_POST['reason']) : '';
            
            if ($appointment_id <= 0) {
                throw new Exception('Invalid appointment ID');
            }
            
            // In a real application, you would:
            // 1. Check if appointment can be cancelled (not too late)
            // 2. Update status in database
            // 3. Send notification to customer
            // 4. Free up the time slot
            
            $response['success'] = true;
            $response['message'] = 'Appointment cancelled successfully';
            break;
            
        case 'get_technicians':
            // Get available technicians for a service
            $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
            $date = isset($_POST['date']) ? sanitize($_POST['date']) : '';
            
            // In a real application, you would fetch technicians qualified for the service
            $technicians = [
                [
                    'id' => 1,
                    'name' => 'Mike Smith',
                    'specialties' => [SERVICE_MAINTENANCE, SERVICE_REPAIR],
                    'available' => true
                ],
                [
                    'id' => 2,
                    'name' => 'John Johnson',
                    'specialties' => [SERVICE_DIAGNOSTIC, SERVICE_REPAIR],
                    'available' => true
                ]
            ];
            
            $response['success'] = true;
            $response['data'] = $technicians;
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
