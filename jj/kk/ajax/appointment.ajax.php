<?php
/**
 * Appointment AJAX Controller
 * Handles all appointment-related AJAX requests
 */

// Include required files
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Appointment.php';
require_once '../classes/Response.php';

// Initialize response object
$response = new Response();

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response->setError('Invalid request method');
    $response->send();
    exit;
}

// Get the action from the request
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Handle different actions
switch ($action) {
    case 'authenticate':
        authenticateAppointment();
        break;
    case 'create':
        createAppointment();
        break;
    case 'update':
        updateAppointment();
        break;
    case 'delete':
        deleteAppointment();
        break;
    case 'get':
        getAppointment();
        break;
    case 'list':
        listAppointments();
        break;
    default:
        $response->setError('Invalid action');
        $response->send();
        break;
}

/**
 * Authenticate an appointment by a worker
 * This marks the appointment as authenticated/approved by the assigned mechanic
 */
function authenticateAppointment() {
    global $response;
    
    // Check if the user is logged in and has appropriate permissions
    // In a real application, you would validate user role and permissions here
    
    // Get appointment ID and worker notes from the request
    $appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $workerNotes = isset($_POST['worker_notes']) ? trim($_POST['worker_notes']) : '';
    
    // Validate the appointment ID
    if ($appointmentId <= 0) {
        $response->setError('Invalid appointment ID');
        $response->send();
        return;
    }
    
    try {
        // In a real application, you would update the appointment in the database
        // For demonstration, we'll simulate a successful update
        
        // Create a database connection
        $db = new Database();
        $conn = $db->getConnection();
        
        // Prepare the update statement
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET status = 'authenticated', 
                notes = CONCAT(IFNULL(notes, ''), '\n\nWorker Authentication Notes: ', ?)
            WHERE id = ?
        ");
        
        // Bind parameters and execute
        $stmt->bind_param("si", $workerNotes, $appointmentId);
        $result = $stmt->execute();
        
        // Check if the update was successful
        if ($result) {
            // Get the updated appointment details
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $appointment = $stmt->get_result()->fetch_assoc();
            
            // Set success response
            $response->setSuccess('Appointment authenticated successfully');
            $response->setData('appointment', $appointment);
        } else {
            // Set error response
            $response->setError('Failed to authenticate appointment');
        }
        
        // Close the statement and connection
        $stmt->close();
        $db->closeConnection();
    } catch (Exception $e) {
        // Set error response
        $response->setError('An error occurred: ' . $e->getMessage());
    }
    
    // Send the response
    $response->send();
}

/**
 * Create a new appointment
 */
function createAppointment() {
    global $response;
    
    // Implementation for creating a new appointment
    // This would be implemented in a real application
    
    $response->setError('Not implemented yet');
    $response->send();
}

/**
 * Update an existing appointment
 */
function updateAppointment() {
    global $response;
    
    // Implementation for updating an appointment
    // This would be implemented in a real application
    
    $response->setError('Not implemented yet');
    $response->send();
}

/**
 * Delete an appointment
 */
function deleteAppointment() {
    global $response;
    
    // Implementation for deleting an appointment
    // This would be implemented in a real application
    
    $response->setError('Not implemented yet');
    $response->send();
}

/**
 * Get a single appointment by ID
 */
function getAppointment() {
    global $response;
    
    // Implementation for retrieving a single appointment
    // This would be implemented in a real application
    
    $response->setError('Not implemented yet');
    $response->send();
}

/**
 * List appointments with optional filters
 */
function listAppointments() {
    global $response;
    
    // Implementation for listing appointments
    // This would be implemented in a real application
    
    $response->setError('Not implemented yet');
    $response->send();
}
