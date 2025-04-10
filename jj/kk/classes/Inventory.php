<?php
/**
 * Inventory Class
 * Handles inventory operations and management
 */
class Inventory {
    private $db;
    private $conn;
    
    // Part properties
    private $id;
    private $part_number;
    private $name;
    private $description;
    private $category;
    private $supplier_id;
    private $cost_price;
    private $selling_price;
    private $quantity;
    private $reorder_level;
    private $location;
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
     * Get part by ID
     * 
     * @param int $id Part ID
     * @return bool True if part found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM parts WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setPartProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get part by part number
     * 
     * @param string $part_number Part number
     * @return bool True if part found, false otherwise
     */
    public function getByPartNumber($part_number) {
        $query = "SELECT * FROM parts WHERE part_number = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $part_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setPartProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate a unique part number
     * 
     * @param string $category Part category
     * @return string Unique part number
     */
    public function generatePartNumber($category = '') {
        // Format: CAT-YYYYMM-XXXX where XXXX is a sequential number
        $date_part = date('Ym');
        
        // Use first 3 letters of category or 'PRT' if not provided
        $category_prefix = !empty($category) ? strtoupper(substr($category, 0, 3)) : 'PRT';
        
        // Get the last part number with this prefix
        $query = "SELECT MAX(part_number) as max_number FROM parts WHERE part_number LIKE ?";
        $stmt = $this->conn->prepare($query);
        $search_pattern = $category_prefix . "-" . $date_part . "-%";
        $stmt->bind_param("s", $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['max_number']) {
            // Extract the sequential part
            $parts = explode('-', $row['max_number']);
            $seq_num = intval(end($parts)) + 1;
        } else {
            // Start with 1
            $seq_num = 1;
        }
        
        // Format the sequential part with leading zeros
        $seq_part = str_pad($seq_num, 4, '0', STR_PAD_LEFT);
        
        // Combine to form the part number
        return $category_prefix . "-" . $date_part . "-" . $seq_part;
    }
    
    /**
     * Create a new part
     * 
     * @param array $data Part data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Generate part number if not provided
        $part_number = isset($data['part_number']) ? $data['part_number'] : $this->generatePartNumber($data['category']);
        
        // Set default status if not provided
        $status = isset($data['status']) ? $data['status'] : PART_STATUS_ACTIVE;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO parts (
                  part_number, name, description, category, 
                  supplier_id, cost_price, selling_price, quantity, 
                  reorder_level, location, status, created_at
              ) VALUES (
                  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
              )";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssssiiddiiss",
            $part_number,
            $data['name'],
            $data['description'],
            $data['category'],
            $data['supplier_id'],
            $data['cost_price'],
            $data['selling_price'],
            $data['quantity'],
            $data['reorder_level'],
            $data['location'],
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
     * Update part
     * 
     * @param array $data Part data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if part exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE parts SET 
                  name = ?, 
                  description = ?, 
                  category = ?, 
                  supplier_id = ?, 
                  cost_price = ?, 
                  selling_price = ?, 
                  quantity = ?, 
                  reorder_level = ?, 
                  location = ?, 
                  status = ?, 
                  updated_at = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "sssiddisssi",
            $data['name'],
            $data['description'],
            $data['category'],
            $data['supplier_id'],
            $data['cost_price'],
            $data['selling_price'],
            $data['quantity'],
            $data['reorder_level'],
            $data['location'],
            $data['status'],
            $updated_at,
            $this->id
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete part
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if part exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM parts WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all parts
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of parts
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT p.*, s.name as supplier_name 
                 FROM parts p
                 LEFT JOIN suppliers s ON p.supplier_id = s.id
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category'])) {
                $query .= " AND p.category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
            
            // Filter by supplier
            if (isset($filters['supplier_id'])) {
                $query .= " AND p.supplier_id = ?";
                $params[] = $filters['supplier_id'];
                $types .= "i";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND p.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (part number, name, description)
            if (isset($filters['search'])) {
                $query .= " AND (p.part_number LIKE ? OR p.name LIKE ? OR p.description LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "sss";
            }
            
            // Filter by low stock
            if (isset($filters['low_stock']) && $filters['low_stock']) {
                $query .= " AND p.quantity <= p.reorder_level";
            }
        }
        
        // Add order by
        $query .= " ORDER BY p.name ASC";
        
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
        
        // Fetch all parts
        $parts = [];
        while ($row = $result->fetch_assoc()) {
            $parts[] = $row;
        }
        
        return $parts;
    }
    
    /**
     * Count parts
     * 
     * @param array $filters Optional filters
     * @return int Number of parts
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM parts p WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category'])) {
                $query .= " AND p.category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
            
            // Filter by supplier
            if (isset($filters['supplier_id'])) {
                $query .= " AND p.supplier_id = ?";
                $params[] = $filters['supplier_id'];
                $types .= "i";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND p.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (part number, name, description)
            if (isset($filters['search'])) {
                $query .= " AND (p.part_number LIKE ? OR p.name LIKE ? OR p.description LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "sss";
            }
            
            // Filter by low stock
            if (isset($filters['low_stock']) && $filters['low_stock']) {
                $query .= " AND p.quantity <= p.reorder_level";
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
     * Get part categories
     * 
     * @return array Array of part categories
     */
    public function getCategories() {
        // Prepare query
        $query = "SELECT DISTINCT category FROM parts ORDER BY category ASC";
        
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
     * Get low stock parts
     * 
     * @param int $limit Limit results
     * @return array Array of low stock parts
     */
    public function getLowStockParts($limit = 10) {
        // Prepare query
        $query = "SELECT p.*, s.name as supplier_name 
                 FROM parts p
                 LEFT JOIN suppliers s ON p.supplier_id = s.id
                 WHERE p.quantity <= p.reorder_level AND p.status = ?
                 ORDER BY (p.reorder_level - p.quantity) DESC
                 LIMIT ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set active status
        $active_status = PART_STATUS_ACTIVE;
        
        // Bind parameters
        $stmt->bind_param("si", $active_status, $limit);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all low stock parts
        $low_stock_parts = [];
        while ($row = $result->fetch_assoc()) {
            $low_stock_parts[] = $row;
        }
        
        return $low_stock_parts;
    }
    
    /**
     * Add stock to part
     * 
     * @param int $quantity Quantity to add
     * @param string $notes Notes about the stock addition
     * @return bool True on success, false on failure
     */
    public function addStock($quantity, $notes = '') {
        // Check if part exists
        if (!$this->id) {
            return false;
        }
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Current timestamp
            $updated_at = date('Y-m-d H:i:s');
            
            // Update part quantity
            $query = "UPDATE parts SET 
                      quantity = quantity + ?, 
                      updated_at = ? 
                      WHERE id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bind_param("isi", $quantity, $updated_at, $this->id);
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to update part quantity: " . $stmt->error);
            }
            
            // Log stock movement
            $query = "INSERT INTO stock_movements (
                      part_id, movement_type, quantity, notes, created_at
                  ) VALUES (
                      ?, ?, ?, ?, ?
                  )";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Set movement type
            $movement_type = STOCK_MOVEMENT_IN;
            
            // Current timestamp
            $created_at = date('Y-m-d H:i:s');
            
            // Bind parameters
            $stmt->bind_param(
                "isiss",
                $this->id,
                $movement_type,
                $quantity,
                $notes,
                $created_at
            );
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to log stock movement: " . $stmt->error);
            }
            
            // Update part quantity property
            $this->quantity += $quantity;
            
            // Commit transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove stock from part
     * 
     * @param int $quantity Quantity to remove
     * @param string $notes Notes about the stock removal
     * @return bool True on success, false on failure
     */
    public function removeStock($quantity, $notes = '') {
        // Check if part exists
        if (!$this->id) {
            return false;
        }
        
        // Check if enough stock
        if ($this->quantity < $quantity) {
            return false;
        }
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Current timestamp
            $updated_at = date('Y-m-d H:i:s');
            
            // Update part quantity
            $query = "UPDATE parts SET 
                      quantity = quantity - ?, 
                      updated_at = ? 
                      WHERE id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bind_param("isi", $quantity, $updated_at, $this->id);
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to update part quantity: " . $stmt->error);
            }
            
            // Log stock movement
            $query = "INSERT INTO stock_movements (
                      part_id, movement_type, quantity, notes, created_at
                  ) VALUES (
                      ?, ?, ?, ?, ?
                  )";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Set movement type
            $movement_type = STOCK_MOVEMENT_OUT;
            
            // Current timestamp
            $created_at = date('Y-m-d H:i:s');
            
            // Bind parameters
            $stmt->bind_param(
                "isiss",
                $this->id,
                $movement_type,
                $quantity,
                $notes,
                $created_at
            );
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to log stock movement: " . $stmt->error);
            }
            
            // Update part quantity property
            $this->quantity -= $quantity;
            
            // Commit transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get stock movements for part
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Array of stock movements
     */
    public function getStockMovements($start_date = null, $end_date = null) {
        // Check if part exists
        if (!$this->id) {
            return [];
        }
        
        // Start with base query
        $query = "SELECT * FROM stock_movements WHERE part_id = ?";
        
        $params = [$this->id];
        $types = "i";
        
        // Add date filters if provided
        if (!empty($start_date)) {
            $query .= " AND created_at >= ?";
            $params[] = $start_date . ' 00:00:00';
            $types .= "s";
        }
        
        if (!empty($end_date)) {
            $query .= " AND created_at <= ?";
            $params[] = $end_date . ' 23:59:59';
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all stock movements
        $movements = [];
        while ($row = $result->fetch_assoc()) {
            $movements[] = $row;
        }
        
        return $movements;
    }
    
    /**
     * Get part usage statistics
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Part usage statistics
     */
    public function getUsageStatistics($start_date = null, $end_date = null) {
        // Check if part exists
        if (!$this->id) {
            return [];
        }
        
        // Set default dates if not provided
        if (empty($start_date)) {
            // Default to first day of current month
            $start_date = date('Y-m-01');
        }
        
        if (empty($end_date)) {
            // Default to current date
            $end_date = date('Y-m-d');
        }
        
        // Prepare query
        $query = "SELECT 
                 DATE(created_at) as date,
                 SUM(CASE WHEN movement_type = ? THEN quantity ELSE 0 END) as stock_in,
                 SUM(CASE WHEN movement_type = ? THEN quantity ELSE 0 END) as stock_out
                 FROM stock_movements
                 WHERE part_id = ? AND created_at BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set movement types
        $movement_in = STOCK_MOVEMENT_IN;
        $movement_out = STOCK_MOVEMENT_OUT;
        
        // Format date range
        $start_datetime = $start_date . ' 00:00:00';
        $end_datetime = $end_date . ' 23:59:59';
        
        // Bind parameters
        $stmt->bind_param(
            "ssiss",
            $movement_in,
            $movement_out,
            $this->id,
            $start_datetime,
            $end_datetime
        );
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch usage statistics
        $statistics = [];
        while ($row = $result->fetch_assoc()) {
            $statistics[] = $row;
        }
        
        return $statistics;
    }
    
    /**
     * Set part properties from database row
     * 
     * @param array $row Database row
     */
    private function setPartProperties($row) {
        $this->id = $row['id'];
        $this->part_number = $row['part_number'];
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->category = $row['category'];
        $this->supplier_id = $row['supplier_id'];
        $this->cost_price = $row['cost_price'];
        $this->selling_price = $row['selling_price'];
        $this->quantity = $row['quantity'];
        $this->reorder_level = $row['reorder_level'];
        $this->location = $row['location'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get part ID
     * 
     * @return int Part ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get part number
     * 
     * @return string Part number
     */
    public function getPartNumber() {
        return $this->part_number;
    }
    
    /**
     * Get part name
     * 
     * @return string Part name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get part description
     * 
     * @return string Part description
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Get part category
     * 
     * @return string Part category
     */
    public function getCategory() {
        return $this->category;
    }
    
    /**
     * Get supplier ID
     * 
     * @return int Supplier ID
     */
    public function getSupplierId() {
        return $this->supplier_id;
    }
    
    /**
     * Get cost price
     * 
     * @return float Cost price
     */
    public function getCostPrice() {
        return $this->cost_price;
    }
    
    /**
     * Get selling price
     * 
     * @return float Selling price
     */
    public function getSellingPrice() {
        return $this->selling_price;
    }
    
    /**
     * Get quantity
     * 
     * @return int Quantity
     */
    public function getQuantity() {
        return $this->quantity;
    }
    
    /**
     * Get reorder level
     * 
     * @return int Reorder level
     */
    public function getReorderLevel() {
        return $this->reorder_level;
    }
    
    /**
     * Get location
     * 
     * @return string Location
     */
    public function getLocation() {
        return $this->location;
    }
    
    /**
     * Get status
     * 
     * @return string Status
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get created date
     * 
     * @return string Created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get updated date
     * 
     * @return string Updated date
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    /**
     * Format price
     * 
     * @param float $price Price to format
     * @param string $currency Currency symbol
     * @return string Formatted price
     */
    public function formatPrice($price, $currency = '$') {
        return $currency . number_format($price, 2);
    }
    
    /**
     * Check if part is in stock
     * 
     * @return bool True if part is in stock, false otherwise
     */
    public function isInStock() {
        return $this->quantity > 0;
    }
    
    /**
     * Check if part is low on stock
     * 
     * @return bool True if part is low on stock, false otherwise
     */
    public function isLowStock() {
        return $this->quantity <= $this->reorder_level;
    }
    
    /**
     * Calculate profit margin
     * 
     * @return float Profit margin percentage
     */
    public function getProfitMargin() {
        if ($this->cost_price <= 0) {
            return 0;
        }
        
        $profit = $this->selling_price - $this->cost_price;
        return ($profit / $this->cost_price) * 100;
    }
    
    /**
     * Calculate stock value
     * 
     * @param string $price_type Type of price to use (cost, selling)
     * @return float Stock value
     */
    public function getStockValue($price_type = 'cost') {
        $price = ($price_type === 'selling') ? $this->selling_price : $this->cost_price;
        return $price * $this->quantity;
    }
}
?>
