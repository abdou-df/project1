<?php
/**
 * Vehicle Class
 * Handles vehicle operations and management
 */
class Vehicle {
    private $db;
    private $conn;
    
    // Vehicle properties
    private $id;
    private $customer_id;
    private $make;
    private $model;
    private $year;
    private $license_plate;
    private $vin;
    private $color;
    private $mileage;
    private $engine;
    private $transmission;
    private $notes;
    private $status;
    private $created_at;
    private $updated_at;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get vehicle by ID
     * 
     * @param int $id Vehicle ID
     * @return bool True if vehicle found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM vehicles WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setVehicleProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get vehicle by license plate
     * 
     * @param string $license_plate Vehicle license plate
     * @return bool True if vehicle found, false otherwise
     */
    public function getByLicensePlate($license_plate) {
        $query = "SELECT * FROM vehicles WHERE license_plate = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $license_plate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setVehicleProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new vehicle
     * 
     * @param array $data Vehicle data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Set default status if not provided
        $status = isset($data['status']) ? $data['status'] : VEHICLE_STATUS_ACTIVE;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO vehicles (customer_id, make, model, year, license_plate, vin, color, 
                  mileage, engine, transmission, notes, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "issssssssssss",
            $data['customer_id'],
            $data['make'],
            $data['model'],
            $data['year'],
            $data['license_plate'],
            $data['vin'],
            $data['color'],
            $data['mileage'],
            $data['engine'],
            $data['transmission'],
            $data['notes'],
            $status,
            $created_at
        );
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return true;
        }
        
        return false;
    }
    
    /**
     * Update vehicle
     * 
     * @param array $data Vehicle data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if vehicle exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE vehicles SET 
                  customer_id = ?, 
                  make = ?, 
                  model = ?, 
                  year = ?, 
                  license_plate = ?, 
                  vin = ?, 
                  color = ?, 
                  mileage = ?, 
                  engine = ?, 
                  transmission = ?, 
                  notes = ?, 
                  status = ?, 
                  updated_at = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "issssssssssssi",
            $data['customer_id'],
            $data['make'],
            $data['model'],
            $data['year'],
            $data['license_plate'],
            $data['vin'],
            $data['color'],
            $data['mileage'],
            $data['engine'],
            $data['transmission'],
            $data['notes'],
            $data['status'],
            $updated_at,
            $this->id
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete vehicle
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if vehicle exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM vehicles WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all vehicles
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of vehicles
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT v.*, c.name as customer_name 
                 FROM vehicles v 
                 LEFT JOIN customers c ON v.customer_id = c.id 
                 WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by customer ID
            if (isset($filters['customer_id'])) {
                $query .= " AND v.customer_id = ?";
                $params[] = $filters['customer_id'];
                $types .= "i";
            }
            
            // Filter by make
            if (isset($filters['make'])) {
                $query .= " AND v.make = ?";
                $params[] = $filters['make'];
                $types .= "s";
            }
            
            // Filter by model
            if (isset($filters['model'])) {
                $query .= " AND v.model = ?";
                $params[] = $filters['model'];
                $types .= "s";
            }
            
            // Filter by year
            if (isset($filters['year'])) {
                $query .= " AND v.year = ?";
                $params[] = $filters['year'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND v.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (make, model, license plate)
            if (isset($filters['search'])) {
                $query .= " AND (v.make LIKE ? OR v.model LIKE ? OR v.license_plate LIKE ? OR v.vin LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ssss";
            }
        }
        
        // Add order by
        $query .= " ORDER BY v.make ASC, v.model ASC";
        
        // Add limit and offset
        if ($limit > 0) {
            $query .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= "ii";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters if any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all vehicles
        $vehicles = [];
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
        
        return $vehicles;
    }
    
    /**
     * Count vehicles
     * 
     * @param array $filters Optional filters
     * @return int Number of vehicles
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM vehicles WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by customer ID
            if (isset($filters['customer_id'])) {
                $query .= " AND customer_id = ?";
                $params[] = $filters['customer_id'];
                $types .= "i";
            }
            
            // Filter by make
            if (isset($filters['make'])) {
                $query .= " AND make = ?";
                $params[] = $filters['make'];
                $types .= "s";
            }
            
            // Filter by model
            if (isset($filters['model'])) {
                $query .= " AND model = ?";
                $params[] = $filters['model'];
                $types .= "s";
            }
            
            // Filter by year
            if (isset($filters['year'])) {
                $query .= " AND year = ?";
                $params[] = $filters['year'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (make, model, license plate)
            if (isset($filters['search'])) {
                $query .= " AND (make LIKE ? OR model LIKE ? OR license_plate LIKE ? OR vin LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ssss";
            }
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters if any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    /**
     * Get vehicle service history
     * 
     * @return array Array of service records
     */
    public function getServiceHistory() {
        // Check if vehicle exists
        if (!$this->id) {
            return [];
        }
        
        // Prepare query
        $query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, 
                 s.name as service_name, s.description as service_description, 
                 u.name as technician_name, i.id as invoice_id, i.total_amount
                 FROM appointments a
                 LEFT JOIN services s ON a.service_id = s.id
                 LEFT JOIN users u ON a.technician_id = u.id
                 LEFT JOIN invoices i ON a.id = i.appointment_id
                 WHERE a.vehicle_id = ?
                 ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all service records
        $service_history = [];
        while ($row = $result->fetch_assoc()) {
            $service_history[] = $row;
        }
        
        return $service_history;
    }
    
    /**
     * Get customer details
     * 
     * @return array|null Customer details or null if not found
     */
    public function getCustomer() {
        // Check if vehicle exists
        if (!$this->id || !$this->customer_id) {
            return null;
        }
        
        // Prepare query
        $query = "SELECT * FROM customers WHERE id = ? LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->customer_id);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Update vehicle mileage
     * 
     * @param int $mileage New mileage
     * @return bool True on success, false on failure
     */
    public function updateMileage($mileage) {
        // Check if vehicle exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE vehicles SET mileage = ?, updated_at = ? WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ssi", $mileage, $updated_at, $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            $this->mileage = $mileage;
            return true;
        }
        
        return false;
    }
    
    /**
     * Set vehicle properties from database row
     * 
     * @param array $row Database row
     */
    private function setVehicleProperties($row) {
        $this->id = $row['id'];
        $this->customer_id = $row['customer_id'];
        $this->make = $row['make'];
        $this->model = $row['model'];
        $this->year = $row['year'];
        $this->license_plate = $row['license_plate'];
        $this->vin = $row['vin'];
        $this->color = $row['color'];
        $this->mileage = $row['mileage'];
        $this->engine = $row['engine'];
        $this->transmission = $row['transmission'];
        $this->notes = $row['notes'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get vehicle ID
     * 
     * @return int Vehicle ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get customer ID
     * 
     * @return int Customer ID
     */
    public function getCustomerId() {
        return $this->customer_id;
    }
    
    /**
     * Get vehicle make
     * 
     * @return string Vehicle make
     */
    public function getMake() {
        return $this->make;
    }
    
    /**
     * Get vehicle model
     * 
     * @return string Vehicle model
     */
    public function getModel() {
        return $this->model;
    }
    
    /**
     * Get vehicle year
     * 
     * @return string Vehicle year
     */
    public function getYear() {
        return $this->year;
    }
    
    /**
     * Get vehicle license plate
     * 
     * @return string Vehicle license plate
     */
    public function getLicensePlate() {
        return $this->license_plate;
    }
    
    /**
     * Get vehicle VIN
     * 
     * @return string Vehicle VIN
     */
    public function getVin() {
        return $this->vin;
    }
    
    /**
     * Get vehicle color
     * 
     * @return string Vehicle color
     */
    public function getColor() {
        return $this->color;
    }
    
    /**
     * Get vehicle mileage
     * 
     * @return string Vehicle mileage
     */
    public function getMileage() {
        return $this->mileage;
    }
    
    /**
     * Get vehicle engine
     * 
     * @return string Vehicle engine
     */
    public function getEngine() {
        return $this->engine;
    }
    
    /**
     * Get vehicle transmission
     * 
     * @return string Vehicle transmission
     */
    public function getTransmission() {
        return $this->transmission;
    }
    
    /**
     * Get vehicle notes
     * 
     * @return string Vehicle notes
     */
    public function getNotes() {
        return $this->notes;
    }
    
    /**
     * Get vehicle status
     * 
     * @return string Vehicle status
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get vehicle created date
     * 
     * @return string Vehicle created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get vehicle updated date
     * 
     * @return string Vehicle updated date
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    /**
     * Get vehicle full name (year make model)
     * 
     * @return string Vehicle full name
     */
    public function getFullName() {
        return $this->year . ' ' . $this->make . ' ' . $this->model;
    }
    
    /**
     * Check if vehicle is active
     * 
     * @return bool True if vehicle is active, false otherwise
     */
    public function isActive() {
        return $this->status === VEHICLE_STATUS_ACTIVE;
    }
}
?>
