<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to add a vehicle.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can add vehicles.', 'error');
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['user_id'];
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
        // Check if license plate already exists
        $sql = "SELECT id FROM vehicles WHERE license_plate = ? AND customer_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $license_plate, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'A vehicle with this license plate already exists.';
        } else {
            // Insert vehicle
            $sql = "INSERT INTO vehicles (customer_id, make, model, year, license_plate, vin, color, mileage, transmission, fuel_type, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssisss", $customer_id, $make, $model, $year, $license_plate, $vin, $color, $mileage, $transmission, $fuel_type, $notes);
            
            if ($stmt->execute()) {
                $success = 'Vehicle added successfully!';
                // Redirect to vehicles page
                redirect('vehicles.php', 'Vehicle added successfully!', 'success');
            } else {
                $error = 'Failed to add vehicle. Please try again.';
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
    <title>Add Vehicle - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Add a Vehicle</h1>
            <p>Register a new vehicle to your account</p>
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
                <form action="add-vehicle.php" method="post" class="form">
                    <div class="form-group-title">
                        <h2>Vehicle Information</h2>
                        <p>Please provide your vehicle details</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="make">Make <span class="required">*</span></label>
                            <input type="text" id="make" name="make" placeholder="e.g. Toyota" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model <span class="required">*</span></label>
                            <input type="text" id="model" name="model" placeholder="e.g. Camry" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year <span class="required">*</span></label>
                            <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="e.g. 2020" required>
                        </div>
                        <div class="form-group">
                            <label for="license_plate">License Plate <span class="required">*</span></label>
                            <input type="text" id="license_plate" name="license_plate" placeholder="e.g. ABC123" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vin">VIN (Vehicle Identification Number)</label>
                            <input type="text" id="vin" name="vin" placeholder="e.g. 1HGCM82633A123456" maxlength="17">
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" placeholder="e.g. Silver">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mileage">Mileage</label>
                            <input type="number" id="mileage" name="mileage" min="0" placeholder="e.g. 25000">
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission">
                                <option value="">-- Select Transmission --</option>
                                <option value="automatic">Automatic</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type">
                            <option value="">-- Select Fuel Type --</option>
                            <option value="gasoline">Gasoline</option>
                            <option value="diesel">Diesel</option>
                            <option value="electric">Electric</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any additional information about your vehicle"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Vehicle</button>
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

