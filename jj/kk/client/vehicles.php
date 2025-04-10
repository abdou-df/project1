<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to view your vehicles.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can access this page.', 'error');
}

$customer_id = $_SESSION['user_id'];

// Get customer vehicles
$sql = "SELECT * FROM vehicles WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$vehicles_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vehicles - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>My Vehicles</h1>
            <p>Manage your registered vehicles</p>
        </div>
    </section>
    
    <section class="vehicles-section">
        <div class="container">
            <?php echo display_message(); ?>
            
            <div class="section-actions">
                <a href="add-vehicle.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Vehicle
                </a>
            </div>
            
            <?php if ($vehicles_result->num_rows > 0): ?>
                <div class="vehicles-grid">
                    <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="vehicle-info">
                                <h3><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                                <p class="license-plate"><?php echo htmlspecialchars($vehicle['license_plate']); ?></p>
                                <div class="vehicle-details">
                                    <?php if (!empty($vehicle['mileage'])): ?>
                                        <p><i class="fas fa-tachometer-alt"></i> <?php echo number_format($vehicle['mileage']); ?> miles</p>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['color'])): ?>
                                        <p><i class="fas fa-palette"></i> <?php echo htmlspecialchars($vehicle['color']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['transmission'])): ?>
                                        <p><i class="fas fa-cog"></i> <?php echo ucfirst($vehicle['transmission']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['fuel_type'])): ?>
                                        <p><i class="fas fa-gas-pump"></i> <?php echo ucfirst($vehicle['fuel_type']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['vin'])): ?>
                                        <p><i class="fas fa-fingerprint"></i> VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="vehicle-actions">
                                    <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm">Edit</a>
                                    <a href="vehicle-history.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-outline">Service History</a>
                                    <a href="delete-vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h3>No Vehicles</h3>
                    <p>You haven't added any vehicles to your account yet.</p>
                    <a href="add-vehicle.php" class="btn btn-primary">Add a Vehicle</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

