<?php
header('Content-Type: application/json');
session_start(); // Access session variables like user ID

// Include necessary files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php'; // Ensure user is authenticated and authorized

// Check if user is logged in (basic check, enhance with role/permission check if needed)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Check if it's a POST request and content type is JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    empty($_SERVER['CONTENT_TYPE']) || 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Get the JSON payload from the request body
$jsonPayload = file_get_contents('php://input');
$data = json_decode($jsonPayload, true);

// Validate input: Check if 'ids' array exists and is not empty
if (!isset($data['ids']) || !is_array($data['ids']) || empty($data['ids'])) {
    echo json_encode(['success' => false, 'message' => 'No quotation IDs provided.']);
    exit;
}

// Sanitize IDs to ensure they are integers
$quotationIds = array_map('intval', $data['ids']);
$quotationIds = array_filter($quotationIds, function($id) { return $id > 0; }); // Remove non-positive IDs

if (empty($quotationIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid quotation IDs provided.']);
    exit;
}

// --- Database Connection --- START ---
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database Connection Error (AJAX): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}
// --- Database Connection --- END ---

// --- Deletion Logic --- START ---
try {
    // Prepare the SQL statement with placeholders for IDs
    $placeholders = implode(',', array_fill(0, count($quotationIds), '?'));
    $sql = "DELETE FROM quotations WHERE id IN ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    
    // Execute the statement with the array of IDs
    $stmt->execute($quotationIds);
    
    $deletedCount = $stmt->rowCount();

    if ($deletedCount > 0) {
        echo json_encode(['success' => true, 'message' => "Successfully deleted {$deletedCount} quotation(s)."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching quotations found or could not be deleted.']);
    }

} catch (PDOException $e) {
    error_log("Error deleting quotations: " . $e->getMessage());
    // Provide a generic error message in production
    echo json_encode(['success' => false, 'message' => 'An error occurred during deletion. Please try again.']); 
}
// --- Deletion Logic --- END ---

?> 