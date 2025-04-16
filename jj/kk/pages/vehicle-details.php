<?php
// Database connection
$host = 'localhost';
$dbname = 'garage_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addVehicle($pdo);
            break;
        case 'edit':
            editVehicle($pdo);
            break;
        case 'delete':
            deleteVehicle($pdo);
            break;
        default:
            header('Location: index.php?page=vehicles&error=invalid_action');
            exit;
    }
}

// Add a new vehicle
function addVehicle($pdo) {
    // Validate input
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = isset($_POST['year']) ? (int) $_POST['year'] : 0;
    $license_plate = $_POST['license_plate'] ?? '';
    $vin = $_POST['vin'] ?? '';
    $color = $_POST['color'] ?? '';
    $mileage = isset($_POST['mileage']) ? (int) $_POST['mileage'] : 0;
    $transmission = $_POST['transmission'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $customer_id = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
    $status = $_POST['status'] ?? 'active';
    
    if (empty($make) || empty($model) || $year <= 0 || empty($license_plate) || $customer_id <= 0) {
        header('Location: index.php?page=vehicles&error=invalid_input');
        exit;
    }
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/images/vehicles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = $uploadFile;
        }
    }
    
    try {
        // Insert into database
        $sql = "INSERT INTO vehicles (make, model, year, license_plate, vin, color, mileage, transmission, fuel_type, customer_id, status, image) 
                VALUES (:make, :model, :year, :license_plate, :vin, :color, :mileage, :transmission, :fuel_type, :customer_id, :status, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':make' => $make,
            ':model' => $model,
            ':year' => $year,
            ':license_plate' => $license_plate,
            ':vin' => $vin,
            ':color' => $color,
            ':mileage' => $mileage,
            ':transmission' => $transmission,
            ':fuel_type' => $fuel_type,
            ':customer_id' => $customer_id,
            ':status' => $status,
            ':image' => $imagePath
        ]);
        
        header('Location: index.php?page=vehicles&success=vehicle_added');
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?page=vehicles&error=database_error');
        exit;
    }
}

// Edit an existing vehicle
function editVehicle($pdo) {
    // Validate input
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = isset($_POST['year']) ? (int) $_POST['year'] : 0;
    $license_plate = $_POST['license_plate'] ?? '';
    $vin = $_POST['vin'] ?? '';
    $color = $_POST['color'] ?? '';
    $mileage = isset($_POST['mileage']) ? (int) $_POST['mileage'] : 0;
    $transmission = $_POST['transmission'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $customer_id = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
    $status = $_POST['status'] ?? 'active';
    
    if ($id <= 0 || empty($make) || empty($model) || $year <= 0 || empty($license_plate) || $customer_id <= 0) {
        header('Location: index.php?page=vehicles&error=invalid_input');
        exit;
    }
    
    try {
        // Get current vehicle data
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vehicle) {
            header('Location: index.php?page=vehicles&error=vehicle_not_found');
            exit;
        }
        
        // Handle image upload
        $imagePath = $vehicle['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/images/vehicles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Delete old image if it exists
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $imagePath = $uploadFile;
            }
        }
        
        // Update database
        $sql = "UPDATE vehicles 
                SET make = :make, model = :model, year = :year, license_plate = :license_plate, 
                    vin = :vin, color = :color, mileage = :mileage, transmission = :transmission, 
                    fuel_type = :fuel_type, customer_id = :customer_id, status = :status";
        
        // Only update image if a new one was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $sql .= ", image = :image";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':make' => $make,
            ':model' => $model,
            ':year' => $year,
            ':license_plate' => $license_plate,
            ':vin' => $vin,
            ':color' => $color,
            ':mileage' => $mileage,
            ':transmission' => $transmission,
            ':fuel_type' => $fuel_type,
            ':customer_id' => $customer_id,
            ':status' => $status,
            ':id' => $id
        ];
        
        // Add image parameter if needed
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $params[':image'] = $imagePath;
        }
        
        $stmt->execute($params);
        
        header('Location: index.php?page=vehicles&success=vehicle_updated');
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?page=vehicles&error=database_error');
        exit;
    }
}

// Delete a vehicle
function deleteVehicle($pdo) {
    // Validate input
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    if ($id <= 0) {
        header('Location: index.php?page=vehicles&error=invalid_input');
        exit;
    }
    
    try {
        // Get vehicle image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Delete image file if it exists
        if ($vehicle && $vehicle['image'] && file_exists($vehicle['image'])) {
            unlink($vehicle['image']);
        }
        
        header('Location: index.php?page=vehicles&success=vehicle_deleted');
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?page=vehicles&error=database_error');
        exit;
    }
}
?>