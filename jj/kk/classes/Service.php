<?php
/**
 * Service Class
 * Handles service operations and management
 */
class Service {
    private $db;
    private $conn;
    
    // Service properties
    private $id;
    private $name;
    private $description;
    private $price;
    private $duration;
    private $category;
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
     * Get service by ID
     * 
     * @param int $id Service ID
     * @return bool True if service found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM services WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setServiceProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new service
     * 
     * @param array $data Service data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Set default status if not provided
        $status = isset($data['status']) ? $data['status'] : SERVICE_STATUS_ACTIVE;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO services (name, description, price, duration, category, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssdisss",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['duration'],
            $data['category'],
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
     * Update service
     * 
     * @param array $data Service data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if service exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE services SET 
                  name = ?, 
                  description = ?, 
                  price = ?, 
                  duration = ?, 
                  category = ?, 
                  status = ?, 
                  updated_at = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssdisssi",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['duration'],
            $data['category'],
            $data['status'],
            $updated_at,
            $this->id
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete service
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if service exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM services WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all services
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of services
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT * FROM services WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category'])) {
                $query .= " AND category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name, description)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR description LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ss";
            }
            
            // Filter by price range
            if (isset($filters['min_price'])) {
                $query .= " AND price >= ?";
                $params[] = $filters['min_price'];
                $types .= "d";
            }
            
            if (isset($filters['max_price'])) {
                $query .= " AND price <= ?";
                $params[] = $filters['max_price'];
                $types .= "d";
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
        
        // Fetch all services
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Count services
     * 
     * @param array $filters Optional filters
     * @return int Number of services
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM services WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category'])) {
                $query .= " AND category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name, description)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR description LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ss";
            }
            
            // Filter by price range
            if (isset($filters['min_price'])) {
                $query .= " AND price >= ?";
                $params[] = $filters['min_price'];
                $types .= "d";
            }
            
            if (isset($filters['max_price'])) {
                $query .= " AND price <= ?";
                $params[] = $filters['max_price'];
                $types .= "d";
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
     * Get service categories
     * 
     * @return array Array of service categories
     */
    public function getCategories() {
        // Prepare query
        $query = "SELECT DISTINCT category FROM services ORDER BY category ASC";
        
        // Execute query
        $result = $this->conn->query($query);
        
        // Fetch all categories
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
    
    /**
     * Get popular services
     * 
     * @param int $limit Limit results
     * @return array Array of popular services
     */
    public function getPopularServices($limit = 5) {
        // Prepare query
        $query = "SELECT s.*, COUNT(a.id) as appointment_count 
                 FROM services s
                 LEFT JOIN appointments a ON s.id = a.service_id
                 WHERE s.status = ?
                 GROUP BY s.id
                 ORDER BY appointment_count DESC
                 LIMIT ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set active status
        $active_status = SERVICE_STATUS_ACTIVE;
        
        // Bind parameters
        $stmt->bind_param("si", $active_status, $limit);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all popular services
        $popular_services = [];
        while ($row = $result->fetch_assoc()) {
            $popular_services[] = $row;
        }
        
        return $popular_services;
    }
    
    /**
     * Set service properties from database row
     * 
     * @param array $row Database row
     */
    private function setServiceProperties($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->duration = $row['duration'];
        $this->category = $row['category'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get service ID
     * 
     * @return int Service ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get service name
     * 
     * @return string Service name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get service description
     * 
     * @return string Service description
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Get service price
     * 
     * @return float Service price
     */
    public function getPrice() {
        return $this->price;
    }
    
    /**
     * Get service duration
     * 
     * @return int Service duration in minutes
     */
    public function getDuration() {
        return $this->duration;
    }
    
    /**
     * Get service category
     * 
     * @return string Service category
     */
    public function getCategory() {
        return $this->category;
    }
    
    /**
     * Get service status
     * 
     * @return string Service status
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get service created date
     * 
     * @return string Service created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get service updated date
     * 
     * @return string Service updated date
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    /**
     * Format service price
     * 
     * @param string $currency Currency symbol
     * @return string Formatted price
     */
    public function getFormattedPrice($currency = '$') {
        return $currency . number_format($this->price, 2);
    }
    
    /**
     * Format service duration
     * 
     * @return string Formatted duration
     */
    public function getFormattedDuration() {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0) {
            return $hours . ' hr ' . ($minutes > 0 ? $minutes . ' min' : '');
        }
        
        return $minutes . ' min';
    }
    
    /**
     * Check if service is active
     * 
     * @return bool True if service is active, false otherwise
     */
    public function isActive() {
        return $this->status === SERVICE_STATUS_ACTIVE;
    }
}
?>
