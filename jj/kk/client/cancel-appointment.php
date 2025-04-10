<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to cancel an appointment.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can cancel appointments.', 'error');
}

// Check if appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('dashboard.php', 'Invalid appointment ID.', 'error');
}

$appointment_id = intval($_GET['id']);
$customer_id = $_SESSION['user_id'];

// Check if the appointment belongs to the customer
$sql = "SELECT * FROM appointments WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('dashboard.php', 'Appointment not found.', 'error');
}

$appointment = $result->fetch_assoc();

// Check if the appointment can be cancelled (only scheduled appointments)
if ($appointment['status'] != 'scheduled') {
    redirect('view-appointment.php?id=' . $appointment_id, 'This appointment cannot be cancelled.', 'error');
}

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update appointment status to cancelled
    $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        // Log the cancellation
        $sql = "INSERT INTO appointment_logs (appointment_id, user_id, action, notes) VALUES (?, ?, 'cancelled', ?)";
        $stmt = $conn->prepare($sql);
        $notes = "Cancelled by customer";
        $stmt->bind_param("iis", $appointment_id, $customer_id, $notes);
        $stmt->execute();
        
        redirect('dashboard.php', 'Appointment cancelled successfully.', 'success');
    } else {
        redirect('view-appointment.php?id=' . $appointment_id, 'Failed to cancel appointment. Please try again.', 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Appointment - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Cancel Appointment</h1>
            <p>Cancel your scheduled appointment</p>
        </div>
    </section>
    
    <section class="form-section">
        <div class="container">
            <div class="form-container">
                <div class="alert alert-warning">
                    <p><strong>Warning:</strong> You are about to cancel your appointment. This action cannot be undone.</p>
                </div>
                
                <div class="appointment-summary">
                    <h3>Appointment Details</h3>
                    <?php
                    // Get appointment details
                    $sql = "SELECT a.*, s.name as service_name, v.make, v.model, v.year 
                            FROM appointments a 
                            JOIN services s ON a.service_id = s.id 
                            JOIN vehicles v ON a.vehicle_id = v.id 
                            WHERE a.id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $appointment_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $appointment = $result->fetch_assoc();
                    ?>
                    <div class="summary-item">
                        <span class="summary-label">Service:</span>
                        <span class="summary-value"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Vehicle:</span>
                        <span class="summary-value">
                            <?php echo htmlspecialchars($appointment['year'] . ' ' . $appointment['make'] . ' ' . $appointment['model']); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Date:</span>
                        <span class="summary-value"><?php echo format_date($appointment['date']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Time:</span>
                        <span class="summary-value">
                            <?php echo date('h:i A', strtotime($appointment['start_time'])); ?> - 
                            <?php echo date('h:i A', strtotime($appointment['end_time'])); ?>
                        </span>
                    </div>
                </div>
                
                <form action="cancel-appointment.php?id=<?php echo $appointment_id; ?>" method="post" class="form">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                        <a href="view-appointment.php?id=<?php echo $appointment_id; ?>" class="btn btn-outline">Back to Appointment</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

