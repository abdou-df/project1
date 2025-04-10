<?php
/**
 * User Class
 * Handles user operations and management
 */
class User {
    private $db;
    private $conn;
    
    // User properties
    private $id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $phone;
    private $address;
    private $profile_image;
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
     * Get user by ID
     * 
     * @param int $id User ID
     * @return bool True if user found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setUserProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return bool True if user found, false otherwise
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setUserProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default status if not provided
        $status = isset($data['status']) ? $data['status'] : USER_STATUS_ACTIVE;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO users (name, email, password, role, phone, address, profile_image, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "sssssssss",
            $data['name'],
            $data['email'],
            $hashed_password,
            $data['role'],
            $data['phone'],
            $data['address'],
            $data['profile_image'],
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
     * Update user
     * 
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if user exists
        if (!$this->id) {
            return false;
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Start with base query
        $query = "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, 
                 address = ?, status = ?, updated_at = ?";
        
        // Parameters array
        $params = [
            $data['name'],
            $data['email'],
            $data['role'],
            $data['phone'],
            $data['address'],
            $data['status'],
            $updated_at
        ];
        
        // Parameter types
        $types = "sssssss";
        
        // Add profile image if provided
        if (!empty($data['profile_image'])) {
            $query .= ", profile_image = ?";
            $params[] = $data['profile_image'];
            $types .= "s";
        }
        
        // Add password if provided
        if (!empty($data['password'])) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        // Complete the query
        $query .= " WHERE id = ?";
        $params[] = $this->id;
        $types .= "i";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters dynamically
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete user
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if user exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM users WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Change user password
     * 
     * @param string $current_password Current password
     * @param string $new_password New password
     * @return bool True on success, false on failure
     */
    public function changePassword($current_password, $new_password) {
        // Check if user exists
        if (!$this->id) {
            return false;
        }
        
        // Verify current password
        if (!password_verify($current_password, $this->password)) {
            return false;
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE users SET password = ?, updated_at = ? WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("ssi", $hashed_password, $updated_at, $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all users
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of users
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by role
            if (isset($filters['role'])) {
                $query .= " AND role = ?";
                $params[] = $filters['role'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name or email)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR email LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ss";
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
        
        // Fetch all users
        $users = [];
        while ($row = $result->fetch_assoc()) {
            // Remove password for security
            unset($row['password']);
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Count users
     * 
     * @param array $filters Optional filters
     * @return int Number of users
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by role
            if (isset($filters['role'])) {
                $query .= " AND role = ?";
                $params[] = $filters['role'];
                $types .= "s";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by search term (name or email)
            if (isset($filters['search'])) {
                $query .= " AND (name LIKE ? OR email LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ss";
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
     * Verify user password
     * 
     * @param string $password Password to verify
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    /**
     * Set user properties from database row
     * 
     * @param array $row Database row
     */
    private function setUserProperties($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->role = $row['role'];
        $this->phone = $row['phone'];
        $this->address = $row['address'];
        $this->profile_image = $row['profile_image'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get user ID
     * 
     * @return int User ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get user name
     * 
     * @return string User name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get user email
     * 
     * @return string User email
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * Get user role
     * 
     * @return string User role
     */
    public function getRole() {
        return $this->role;
    }
    
    /**
     * Get user phone
     * 
     * @return string User phone
     */
    public function getPhone() {
        return $this->phone;
    }
    
    /**
     * Get user address
     * 
     * @return string User address
     */
    public function getAddress() {
        return $this->address;
    }
    
    /**
     * Get user profile image
     * 
     * @return string User profile image
     */
    public function getProfileImage() {
        return $this->profile_image;
    }
    
    /**
     * Get user status
     * 
     * @return string User status
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get user created date
     * 
     * @return string User created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get user updated date
     * 
     * @return string User updated date
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string $role Role to check
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole($role) {
        return $this->role === $role;
    }
    
    /**
     * Check if user is active
     * 
     * @return bool True if user is active, false otherwise
     */
    public function isActive() {
        return $this->status === USER_STATUS_ACTIVE;
    }
}
?>
