<?php
// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Function to check if user is staff (admin or employee)
function is_staff() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'employee']);
}

// Function to check if user is customer
function is_customer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'customer';
}

// Function to redirect with message
function redirect($url, $message = '', $message_type = 'info') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
    }
    header("Location: $url");
    exit;
}

// Function to display message
function display_message() {
    if (isset($_SESSION['message'])) {
        $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        $output = '<div class="alert alert-' . $message_type . '">';
        $output .= $_SESSION['message'];
        $output .= '</div>';
        
        // Clear the message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return $output;
    }
    return '';
}

// Function to generate random string
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Function to format date
function format_date($date, $format = 'Y-m-d') {
    global $settings;
    if (isset($settings['date_format'])) {
        $format = $settings['date_format'];
    }
    return date($format, strtotime($date));
}

// Function to format time
function format_time($time, $format = 'H:i:s') {
    global $settings;
    if (isset($settings['time_format'])) {
        $format = $settings['time_format'];
    }
    return date($format, strtotime($time));
}

// Function to format currency
function format_currency($amount) {
    global $settings;
    $symbol = '$';
    if (isset($settings['currency_symbol'])) {
        $symbol = $settings['currency_symbol'];
    }
    return $symbol . number_format($amount, 2);
}

// Function to get available appointment times
function get_available_times($date, $service_id) {
    global $conn;
    
    // Get service duration
    $sql = "SELECT duration FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $duration = $service['duration'];
    
    // Get business hours for the day
    $day_of_week = strtolower(date('l', strtotime($date)));
    $sql = "SELECT setting_value FROM settings WHERE setting_key = 'business_hours'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $business_hours = json_decode($row['setting_value'], true);
    
    if ($business_hours[$day_of_week] == 'closed') {
        return [];
    }
    
    list($start_time, $end_time) = explode('-', $business_hours[$day_of_week]);
    
    // Generate time slots
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    $interval = 30 * 60; // 30 minutes in seconds
    
    $times = [];
    for ($time = $start; $time <= $end - $duration * 60; $time += $interval) {
        $times[] = date('H:i', $time);
    }
    
    // Get booked appointments for the day
    $sql = "SELECT start_time, end_time FROM appointments WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_times = [];
    while ($row = $result->fetch_assoc()) {
        $appointment_start = strtotime($row['start_time']);
        $appointment_end = strtotime($row['end_time']);
        
        for ($time = $start; $time <= $end; $time += $interval) {
            $slot_start = $time;
            $slot_end = $time + $duration * 60;
            
            // Check if slot overlaps with appointment
            if ($slot_start < $appointment_end && $slot_end > $appointment_start) {
                $booked_times[] = date('H:i', $time);
            }
        }
    }
    
    // Remove booked times
    $available_times = array_diff($times, $booked_times);
    
    return $available_times;
}
?>

