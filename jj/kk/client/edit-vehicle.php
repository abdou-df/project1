<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to edit your vehicle.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can edit vehicles.', 'error');
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

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $make = sanitize_input($_POST['make']);
    $model = sanitize_input($_POST['model']);
    $year = sanitize_input($_POST['year']);
    $license_plate = sanitize_input($_POST['license_plate']);
    $vin = sanitize_input($_POST['vin']);
    $color = sanitize_input($_POST['color']);
    $mileage = sanitize_input($_POST['mileage']);
    $transmission = sanitize_input($_POST['transmission']);
    $fuel_type = sanitize_input($_POST['fuel_type']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validate input
    if (empty($make) || empty($model) || empty($year) || empty($license_plate)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($year) || $year < 1900 || $year > date('Y') + 1) {
        $error = 'Please enter a valid year.';
    } elseif (!empty($mileage) && (!is_numeric($mileage) || $mileage < 0)) {
        $error = 'Please enter a valid mileage.';
    } else {
        // Check if license plate already exists (excluding current vehicle)
        $sql = "SELECT id FROM vehicles WHERE license_plate = ? AND id != ? AND customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $license_plate, $vehicle_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'You already have a vehicle with this license plate.';
        } else {
            // Update vehicle
            $sql = "UPDATE vehicles SET make = ?, model = ?, year = ?, license_plate = ?, vin = ?, 
                    color = ?, mileage = ?, transmission = ?, fuel_type = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssisssi", $make, $model, $year, $license_plate, $vin, $color, $mileage, $transmission, $fuel_type, $notes, $vehicle_id);
            
            if ($stmt->execute()) {
                $success = 'Vehicle updated successfully!';
                // Redirect to vehicles page
                redirect('vehicles.php', 'Vehicle updated successfully!', 'success');
            } else {
                $error = 'Failed to update vehicle. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Edit Vehicle</h1>
            <p>Update your vehicle information</p>
        </div>
    </section>
    
    <section class="form-section">
        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="edit-vehicle.php?id=<?php echo $vehicle_id; ?>" method="post" class="form">
                    <div class="form-group-title">
                        <h2>Vehicle Information</h2>
                        <p>Please update your vehicle details</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="make">Make <span class="required">*</span></label>
                            <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($vehicle['make']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model <span class="required">*</span></label>
                            <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year <span class="required">*</span></label>
                            <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo htmlspecialchars($vehicle['year']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="license_plate">License Plate <span class="required">*</span></label>
                            <input type="text" id="license_plate" name="license_plate" value="<?php echo htmlspecialchars($vehicle['license_plate']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vin">VIN (Vehicle Identification Number)</label>
                            <input type="text" id="vin" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>" maxlength="17">
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($vehicle['color']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mileage">Mileage</label>
                            <input type="number" id="mileage" name="mileage" min="0" value="<?php echo htmlspecialchars($vehicle['mileage']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission">
                                <option value="">-- Select Transmission --</option>
                                <option value="automatic" <?php echo ($vehicle['transmission'] == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                                <option value="manual" <?php echo ($vehicle['transmission'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type">
                            <option value="">-- Select Fuel Type --</option>
                            <option value="gasoline" <?php echo ($vehicle['fuel_type'] == 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                            <option value="diesel" <?php echo ($vehicle['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo ($vehicle['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="hybrid" <?php echo ($vehicle['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($vehicle['notes']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Vehicle</button>
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

