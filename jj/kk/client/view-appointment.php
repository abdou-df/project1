<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to view appointment details.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can access this page.', 'error');
}

// Check if appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('dashboard.php', 'Invalid appointment ID.', 'error');
}

$appointment_id = intval($_GET['id']);
$customer_id = $_SESSION['user_id'];

// Get appointment details
$sql = "SELECT a.*, s.name as service_name, s.description as service_description, s.price as service_price, 
               v.make, v.model, v.year, v.license_plate 
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        JOIN vehicles v ON a.vehicle_id = v.id 
        WHERE a.id = ? AND a.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('dashboard.php', 'Appointment not found.', 'error');
}

$appointment = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Appointment Details</h1>
            <p>View your appointment information</p>
        </div>
    </section>
    
    <section class="appointment-details-section">
        <div class="container">
            <?php echo display_message(); ?>
            
            <div class="appointment-details-container">
                <div class="appointment-details-card">
                    <div class="appointment-header">
                        <div class="appointment-status status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </div>
                        <div class="appointment-id">
                            Appointment #<?php echo $appointment_id; ?>
                        </div>
                    </div>
                    
                    <div class="appointment-info">
                        <div class="info-group">
                            <h3>Service Information</h3>
                            <div class="info-item">
                                <span class="info-label">Service:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Description:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['service_description']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Price:</span>
                                <span class="info-value"><?php echo format_currency($appointment['service_price']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <h3>Schedule Information</h3>
                            <div class="info-item">
                                <span class="info-label">Date:</span>
                                <span class="info-value"><?php echo format_date($appointment['date']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Time:</span>
                                <span class="info-value">
                                    <?php echo date('h:i A', strtotime($appointment['start_time'])); ?> - 
                                    <?php echo date('h:i A', strtotime($appointment['end_time'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <h3>Vehicle Information</h3>
                            <div class="info-item">
                                <span class="info-label">Vehicle:</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($appointment['year'] . ' ' . $appointment['make'] . ' ' . $appointment['model']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">License Plate:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['license_plate']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($appointment['notes'])): ?>
                            <div class="info-group">
                                <h3>Additional Notes</h3>
                                <div class="info-item notes">
                                    <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="appointment-actions">
                        <a href="appointments.php" class="btn btn-outline">Back to Appointments</a>
                        
                        <?php if ($appointment['status'] == 'scheduled'): ?>
                            <a href="cancel-appointment.php?id=<?php echo $appointment_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel Appointment</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="appointment-sidebar">
                    <div class="info-card">
                        <h3><i class="fas fa-info-circle"></i> Appointment Information</h3>
                        <p>If you need to make changes to your appointment, please contact us at least 24 hours in advance.</p>
                        <p>For any questions or concerns, please call us at <?php echo $settings['garage_phone'] ?? '555-123-4567'; ?>.</p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-map-marker-alt"></i> Our Location</h3>
                        <p><?php echo $settings['garage_address'] ?? '123 Repair Street, Fixitville, CA 12345'; ?></p>
                        <div class="map-placeholder">
                            <img src="images/map-placeholder.jpg" alt="Map">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

