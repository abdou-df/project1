<?php
/**
 * API Entry Point
 * Handles API requests and routes them to the appropriate handler
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include required files
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Customer.php';
require_once '../classes/Vehicle.php';
require_once '../classes/Service.php';
require_once '../classes/Invoice.php';
require_once '../classes/Inventory.php';
require_once '../classes/Report.php';
require_once '../classes/Notification.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Parse the URI to get the endpoint
$uri_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));

// Find the API part in the URI
$api_index = array_search('api', $uri_parts);
if ($api_index === false) {
    sendResponse(404, ['message' => 'API endpoint not found']);
    exit;
}

// Get the endpoint (next part after 'api')
$endpoint = isset($uri_parts[$api_index + 1]) ? $uri_parts[$api_index + 1] : '';

// Get the resource ID if provided (next part after endpoint)
$resource_id = isset($uri_parts[$api_index + 2]) ? $uri_parts[$api_index + 2] : null;

// Get the action if provided (next part after resource ID)
$action = isset($uri_parts[$api_index + 3]) ? $uri_parts[$api_index + 3] : null;

// Parse query parameters
$query_params = [];
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $query_params);
}

// Get request body for POST, PUT requests
$data = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendResponse(400, ['message' => 'Invalid JSON data']);
            exit;
        }
    } else {
        // If no JSON data, use POST/PUT data
        $data = $method === 'POST' ? $_POST : $_PUT;
    }
}

// Authenticate API request
// For simplicity, using API key authentication
$api_key = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : null;

// In a real application, you would validate the API key against a database
// For now, using a simple check with a constant defined in config
if (!defined('API_KEY') || $api_key !== API_KEY) {
    // Check if this is a public endpoint that doesn't require authentication
    $public_endpoints = ['login'];
    if (!in_array($endpoint, $public_endpoints)) {
        sendResponse(401, ['message' => 'Unauthorized. Invalid API key']);
        exit;
    }
}

// Route the request to the appropriate handler
switch ($endpoint) {
    case 'vehicles':
        require_once 'vehicles.php';
        break;
        
    case 'customers':
        require_once 'customers.php';
        break;
        
    case 'services':
        require_once 'services.php';
        break;
        
    case 'invoices':
        require_once 'invoices.php';
        break;
        
    case 'inventory':
        require_once 'inventory.php';
        break;
        
    case 'login':
        handleLogin($data);
        break;
        
    default:
        sendResponse(404, ['message' => 'Endpoint not found']);
        break;
}

/**
 * Handle login request
 * 
 * @param array $data Request data
 */
function handleLogin($data) {
    // Check if required fields are provided
    if (!isset($data['email']) || !isset($data['password'])) {
        sendResponse(400, ['message' => 'Email and password are required']);
        return;
    }
    
    // Sanitize input
    $email = sanitize($data['email']);
    $password = $data['password'];
    
    // Create User object
    $user = new User();
    
    // Attempt to authenticate user
    $result = $user->authenticate($email, $password);
    
    if ($result) {
        // Get user data
        $user_data = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'api_key' => generateApiKey($user->getId())
        ];
        
        sendResponse(200, [
            'message' => 'Login successful',
            'user' => $user_data
        ]);
    } else {
        sendResponse(401, ['message' => 'Invalid credentials']);
    }
}

/**
 * Generate API key for user
 * 
 * @param int $user_id User ID
 * @return string API key
 */
function generateApiKey($user_id) {
    // In a real application, you would generate a secure API key and store it in the database
    // For simplicity, using a hash of user ID and a secret key
    $secret_key = defined('API_SECRET') ? API_SECRET : 'garage_management_api_secret';
    return hash('sha256', $user_id . $secret_key . time());
}

/**
 * Send JSON response
 * 
 * @param int $status_code HTTP status code
 * @param array $data Response data
 */
function sendResponse($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}
?>
