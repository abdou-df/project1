<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get parameters
$date = isset($_GET['date']) ? sanitize_input($_GET['date']) : '';
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

if (empty($date) || empty($service_id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Get available times
$available_times = get_available_times($date, $service_id);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($available_times);
?>

