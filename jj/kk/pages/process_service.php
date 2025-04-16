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
            addService($pdo);
            break;
        case 'edit':
            editService($pdo);
            break;
        case 'delete':
            deleteService($pdo);
            break;
        default:
            header('Location: services.php?error=invalid_action');
            exit;
    }
}

// Add a new service
function addService($pdo) {
    // Validate input
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;
    $status = $_POST['status'] ?? 'active';
    $category = $_POST['category'] ?? null;
    
    if (empty($name) || $duration <= 0) {
        header('Location: services.php?error=invalid_input');
        exit;
    }
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/images/services/';
        
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
        $sql = "INSERT INTO services (name, description, price, duration, status, category, image) 
                VALUES (:name, :description, :price, :duration, :status, :category, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':duration' => $duration,
            ':status' => $status,
            ':category' => $category,
            ':image' => $imagePath
        ]);
        
        header('Location: services.php?success=service_added');
        exit;
    } catch (PDOException $e) {
        header('Location: services.php?error=database_error');
        exit;
    }
}

// Edit an existing service
function editService($pdo) {
    // Validate input
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;
    $status = $_POST['status'] ?? 'active';
    $category = $_POST['category'] ?? null;
    
    if ($id <= 0 || empty($name) || $duration <= 0) {
        header('Location: services.php?error=invalid_input');
        exit;
    }
    
    try {
        // Get current service data
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$service) {
            header('Location: services.php?error=service_not_found');
            exit;
        }
        
        // Handle image upload
        $imagePath = $service['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/images/services/';
            
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
        $sql = "UPDATE services 
                SET name = :name, description = :description, price = :price, 
                    duration = :duration, status = :status, category = :category";
        
        // Only update image if a new one was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $sql .= ", image = :image";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':duration' => $duration,
            ':status' => $status,
            ':category' => $category,
            ':id' => $id
        ];
        
        // Add image parameter if needed
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $params[':image'] = $imagePath;
        }
        
        $stmt->execute($params);
        
        header('Location: services.php?success=service_updated');
        exit;
    } catch (PDOException $e) {
        header('Location: services.php?error=database_error');
        exit;
    }
}

// Delete a service
function deleteService($pdo) {
    // Validate input
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    if ($id <= 0) {
        header('Location: services.php?error=invalid_input');
        exit;
    }
    
    try {
        // Get service image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Delete image file if it exists
        if ($service && $service['image'] && file_exists($service['image'])) {
            unlink($service['image']);
        }
        
        header('Location: services.php?success=service_deleted');
        exit;
    } catch (PDOException $e) {
        header('Location: services.php?error=database_error');
        exit;
    }
}