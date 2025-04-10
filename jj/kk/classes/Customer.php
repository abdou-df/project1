<?php
/**
 * Customer Class
 * Handles customer operations and management
 */
class Customer {
    private $db;
    private $conn;
    
    // Customer properties
    private $id;
    private $name;
    private $email;
    private $phone;
    private $address;
    private $city;
    private $state;
    private $zip;
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
     * Get customer by ID
     * 
     * @param int $id Customer ID
     * @return bool True if customer found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM customers WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setCustomerProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get customer by email
     * 
     * @param string $email Customer email
     * @return bool True if customer found, false otherwise
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM customers WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setCustomerProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get customer by phone
     * 
     * @param string $phone Customer phone
     * @return bool True if customer found, false otherwise
     */
    public function getByPhone($phone) {
        $query = "SELECT * FROM customers WHERE phone = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setCustomerProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new customer
     * 
     * @param array $data Customer data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Set default status if not provided
        $status = isset($data['status']) ? $data['status'] : CUSTOMER_STATUS_ACTIVE;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO customers (name, email, phone, address, city, state, zip, notes, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssssssssss",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['zip'],
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
     * Update customer
     * 
     * @param array $data Customer data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if customer exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE customers SET 
                  name = ?, 
                  email = ?, 
                  phone = ?, 
                  address = ?, 
                  city = ?, 
                  state = ?, 
                  zip = ?, 
                  notes = ?, 
                  status = ?, 
                  updated_at = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssssssssssi",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['zip'],
            $data['notes'],
            $data['status'],
            $updated_at,
            $this->id
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete customer
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if customer exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM customers WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all customers
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of customers
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT * FROM customers WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name, email, phone)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "sss";
            }
            
            // Filter by city
            if (isset($filters['city'])) {
                $query .= " AND city = ?";
                $params[] = $filters['city'];
                $types .= "s";
            }
            
            // Filter by state
            if (isset($filters['state'])) {
                $query .= " AND state = ?";
                $params[] = $filters['state'];
                $types .= "s";
            }
        }
        
        // Add order by
        $query .= " ORDER BY name ASC";
        
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
        
        // Fetch all customers
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        
        return $customers;
    }
    
    /**
     * Count customers
     * 
     * @param array $filters Optional filters
     * @return int Number of customers
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM customers WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name, email, phone)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "sss";
            }
            
            // Filter by city
            if (isset($filters['city'])) {
                $query .= " AND city = ?";
                $params[] = $filters['city'];
                $types .= "s";
            }
            
            // Filter by state
            if (isset($filters['state'])) {
                $query .= " AND state = ?";
                $params[] = $filters['state'];
                $types .= "s";
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
     * Get customer vehicles
     * 
     * @return array Array of vehicles
     */
    public function getVehicles() {
        // Check if customer exists
        if (!$this->id) {
            return [];
        }
        
        // Prepare query
        $query = "SELECT * FROM vehicles WHERE customer_id = ? ORDER BY make ASC, model ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
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
     * Get customer appointments
     * 
     * @param string $status Optional appointment status filter
     * @return array Array of appointments
     */
    public function getAppointments($status = '') {
        // Check if customer exists
        if (!$this->id) {
            return [];
        }
        
        // Start with base query
        $query = "SELECT a.*, v.make, v.model, v.year, v.license_plate, 
                 s.name as service_name, u.name as technician_name 
                 FROM appointments a
                 LEFT JOIN vehicles v ON a.vehicle_id = v.id
                 LEFT JOIN services s ON a.service_id = s.id
                 LEFT JOIN users u ON a.technician_id = u.id
                 WHERE a.customer_id = ?";
        
        // Add status filter if provided
        $params = [$this->id];
        $types = "i";
        
        if (!empty($status)) {
            $query .= " AND a.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all appointments
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        return $appointments;
    }
    
    /**
     * Get customer invoices
     * 
     * @param string $status Optional invoice status filter
     * @return array Array of invoices
     */
    public function getInvoices($status = '') {
        // Check if customer exists
        if (!$this->id) {
            return [];
        }
        
        // Start with base query
        $query = "SELECT i.*, v.make, v.model, v.year, v.license_plate 
                 FROM invoices i
                 LEFT JOIN vehicles v ON i.vehicle_id = v.id
                 WHERE i.customer_id = ?";
        
        // Add status filter if provided
        $params = [$this->id];
        $types = "i";
        
        if (!empty($status)) {
            $query .= " AND i.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY i.invoice_date DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all invoices
        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        
        return $invoices;
    }
    
    /**
     * Get customer total spending
     * 
     * @return float Total spending
     */
    public function getTotalSpending() {
        // Check if customer exists
        if (!$this->id) {
            return 0;
        }
        
        // Prepare query
        $query = "SELECT SUM(total_amount) as total FROM invoices 
                 WHERE customer_id = ? AND status = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set paid status
        $paid_status = INVOICE_STATUS_PAID;
        
        // Bind parameters
        $stmt->bind_param("is", $this->id, $paid_status);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return floatval($row['total'] ?? 0);
    }
    
    /**
     * Set customer properties from database row
     * 
     * @param array $row Database row
     */
    private function setCustomerProperties($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->phone = $row['phone'];
        $this->address = $row['address'];
        $this->city = $row['city'];
        $this->state = $row['state'];
        $this->zip = $row['zip'];
        $this->notes = $row['notes'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get customer ID
     * 
     * @return int Customer ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get customer name
     * 
     * @return string Customer name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get customer email
     * 
     * @return string Customer email
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * Get customer phone
     * 
     * @return string Customer phone
     */
    public function getPhone() {
        return $this->phone;
    }
    
    /**
     * Get customer address
     * 
     * @return string Customer address
     */
    public function getAddress() {
        return $this->address;
    }
    
    /**
     * Get customer city
     * 
     * @return string Customer city
     */
    public function getCity() {
        return $this->city;
    }
    
    /**
     * Get customer state
     * 
     * @return string Customer state
     */
    public function getState() {
        return $this->state;
    }
    
    /**
     * Get customer zip
     * 
     * @return string Customer zip
     */
    public function getZip() {
        return $this->zip;
    }
    
    /**
     * Get customer notes
     * 
     * @return string Customer notes
     */
    public function getNotes() {
        return $this->notes;
    }
    
    /**
     * Get customer status
     * 
     * @return string Customer status
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get customer created date
     * 
     * @return string Customer created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get customer updated date
     * 
     * @return string Customer updated date
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    /**
     * Get customer full address
     * 
     * @return string Customer full address
     */
    public function getFullAddress() {
        return $this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->zip;
    }
    
    /**
     * Check if customer is active
     * 
     * @return bool True if customer is active, false otherwise
     */
    public function isActive() {
        return $this->status === CUSTOMER_STATUS_ACTIVE;
    }
}
?>
