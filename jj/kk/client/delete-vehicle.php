<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to delete your vehicle.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can delete vehicles.', 'error');
}

// Check if vehicle ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('vehicles.php', 'Invalid vehicle ID.', 'error');
}

$vehicle_id = intval($_GET['id']);
$customer_id = $_SESSION['user_id'];

// Check if the vehicle belongs to the customer
$sql = "SELECT * FROM vehicles WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $vehicle_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('vehicles.php', 'Vehicle not found.', 'error');
}

$vehicle = $result->fetch_assoc();

// Check if the vehicle has any appointments or invoices
$sql = "SELECT COUNT(*) as count FROM appointments WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$appointment_count = $row['count'];

$sql = "SELECT COUNT(*) as count FROM invoices WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$invoice_count = $row['count'];

// Process deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If vehicle has appointments or invoices, we need to handle them
    if ($appointment_count > 0 || $invoice_count > 0) {
        // Option 1: Soft delete by marking the vehicle as inactive
        $sql = "UPDATE vehicles SET status = 'inactive' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $vehicle_id);
        
        if ($stmt->execute()) {
            redirect('vehicles.php', 'Vehicle has been removed from your account.', 'success');
        } else {
            redirect('vehicles.php', 'Failed to delete vehicle. Please try again.', 'error');
        }
    } else {
        // Option 2: Hard delete if no appointments or invoices
        $sql = "DELETE FROM vehicles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $vehicle_id);
        
        if ($stmt->execute()) {
            redirect('vehicles.php', 'Vehicle deleted successfully.', 'success');
        } else {
            redirect('vehicles.php', 'Failed to delete vehicle. Please try again.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Vehicle - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Delete Vehicle</h1>
            <p>Remove a vehicle from your account</p>
        </div>
    </section>
    
    <section class="form-section">
        <div class="container">
            <div class="form-container">
                <div class="alert alert-danger">
                    <p><strong>Warning:</strong> You are about to delete this vehicle. This action cannot be undone.</p>
                    
                    <?php if ($appointment_count > 0 || $invoice_count > 0): ?>
                        <p>This vehicle has <?php echo $appointment_count; ?> appointment(s) and <?php echo $invoice_count; ?> invoice(s) associated with it.</p>
                        <p>The vehicle will be marked as inactive but its records will be preserved for your service history.</p>
                    <?php endif; ?>
                </div>
                
                <div class="vehicle-summary">
                    <h3>Vehicle Details</h3>
                    <div class="summary-item">
                        <span class="summary-label">Make & Model:</span>
                        <span class="summary-value">
                            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">License Plate:</span>
                        <span class="summary-value"><?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                    </div>
                    <?php if (!empty($vehicle['vin'])): ?>
                        <div class="summary-item">
                            <span class="summary-label">VIN:</span>
                            <span class="summary-value"><?php echo htmlspecialchars($vehicle['vin']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($vehicle['color'])): ?>
                        <div class="summary-item">
                            <span class="summary-label">Color:</span>
                            <span class="summary-value"><?php echo htmlspecialchars($vehicle['color']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form action="delete-vehicle.php?id=<?php echo $vehicle_id; ?>" method="post" class="form">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                        <a href="vehicles.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

