<?php
/**
 * Notification Class
 * Handles notification operations and management
 */
class Notification {
    private $db;
    private $conn;
    
    // Notification properties
    private $id;
    private $user_id;
    private $title;
    private $message;
    private $type;
    private $related_to;
    private $related_id;
    private $is_read;
    private $created_at;
    private $read_at;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get notification by ID
     * 
     * @param int $id Notification ID
     * @return bool True if notification found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM notifications WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setNotificationProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new notification
     * 
     * @param array $data Notification data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Set default is_read status
        $is_read = isset($data['is_read']) ? $data['is_read'] : 0;
        
        // Current timestamp
        $created_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "INSERT INTO notifications (
                  user_id, title, message, type, 
                  related_to, related_id, is_read, created_at
              ) VALUES (
                  ?, ?, ?, ?, ?, ?, ?, ?
              )";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "issssiss",
            $data['user_id'],
            $data['title'],
            $data['message'],
            $data['type'],
            $data['related_to'],
            $data['related_id'],
            $is_read,
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
     * Create notifications for multiple users
     * 
     * @param array $user_ids Array of user IDs
     * @param array $data Notification data
     * @return bool True on success, false on failure
     */
    public function createForMultipleUsers($user_ids, $data) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            foreach ($user_ids as $user_id) {
                // Set user ID
                $data['user_id'] = $user_id;
                
                // Create notification
                if (!$this->create($data)) {
                    throw new Exception("Failed to create notification for user ID: " . $user_id);
                }
            }
            
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
     * Create notifications for users with specific role
     * 
     * @param string $role User role
     * @param array $data Notification data
     * @return bool True on success, false on failure
     */
    public function createForRole($role, $data) {
        // Get users with the specified role
        $query = "SELECT id FROM users WHERE role = ? AND status = ?";
        $stmt = $this->conn->prepare($query);
        $active_status = USER_STATUS_ACTIVE;
        $stmt->bind_param("ss", $role, $active_status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch user IDs
        $user_ids = [];
        while ($row = $result->fetch_assoc()) {
            $user_ids[] = $row['id'];
        }
        
        // Create notifications for users
        if (!empty($user_ids)) {
            return $this->createForMultipleUsers($user_ids, $data);
        }
        
        return false;
    }
    
    /**
     * Create notification for all active users
     * 
     * @param array $data Notification data
     * @return bool True on success, false on failure
     */
    public function createForAllUsers($data) {
        // Get all active users
        $query = "SELECT id FROM users WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $active_status = USER_STATUS_ACTIVE;
        $stmt->bind_param("s", $active_status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch user IDs
        $user_ids = [];
        while ($row = $result->fetch_assoc()) {
            $user_ids[] = $row['id'];
        }
        
        // Create notifications for users
        if (!empty($user_ids)) {
            return $this->createForMultipleUsers($user_ids, $data);
        }
        
        return false;
    }
    
    /**
     * Mark notification as read
     * 
     * @return bool True on success, false on failure
     */
    public function markAsRead() {
        // Check if notification exists
        if (!$this->id) {
            return false;
        }
        
        // Check if already read
        if ($this->is_read) {
            return true;
        }
        
        // Current timestamp
        $read_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE notifications SET is_read = 1, read_at = ? WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("si", $read_at, $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            $this->is_read = 1;
            $this->read_at = $read_at;
            return true;
        }
        
        return false;
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $user_id User ID
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($user_id) {
        // Current timestamp
        $read_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE notifications SET is_read = 1, read_at = ? WHERE user_id = ? AND is_read = 0";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("si", $read_at, $user_id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete notification
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if notification exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM notifications WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete all notifications for a user
     * 
     * @param int $user_id User ID
     * @return bool True on success, false on failure
     */
    public function deleteAllForUser($user_id) {
        // Prepare query
        $query = "DELETE FROM notifications WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete old notifications
     * 
     * @param int $days Number of days to keep notifications
     * @return bool True on success, false on failure
     */
    public function deleteOldNotifications($days = 30) {
        // Calculate cutoff date
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Prepare query
        $query = "DELETE FROM notifications WHERE created_at < ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("s", $cutoff_date);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get all notifications for a user
     * 
     * @param int $user_id User ID
     * @param bool $include_read Include read notifications
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of notifications
     */
    public function getForUser($user_id, $include_read = true, $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        
        $params = [$user_id];
        $types = "i";
        
        // Filter by read status if needed
        if (!$include_read) {
            $query .= " AND is_read = 0";
        }
        
        // Add order by
        $query .= " ORDER BY created_at DESC";
        
        // Add limit and offset
        if ($limit > 0) {
            $query .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= "ii";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all notifications
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    /**
     * Count unread notifications for a user
     * 
     * @param int $user_id User ID
     * @return int Number of unread notifications
     */
    public function countUnread($user_id) {
        // Prepare query
        $query = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $user_id);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    /**
     * Create appointment notification
     * 
     * @param int $appointment_id Appointment ID
     * @param string $action Action (created, updated, cancelled, reminder)
     * @return bool True on success, false on failure
     */
    public function createAppointmentNotification($appointment_id, $action) {
        // Get appointment details
        $query = "SELECT a.*, c.name as customer_name, v.make, v.model, v.year, 
                 s.name as service_name, u.id as technician_id
                 FROM appointments a
                 LEFT JOIN customers c ON a.customer_id = c.id
                 LEFT JOIN vehicles v ON a.vehicle_id = v.id
                 LEFT JOIN services s ON a.service_id = s.id
                 LEFT JOIN users u ON a.technician_id = u.id
                 WHERE a.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $appointment = $result->fetch_assoc();
        
        // Set notification data based on action
        switch ($action) {
            case 'created':
                $title = 'New Appointment';
                $message = "New appointment scheduled for {$appointment['customer_name']} on " . 
                           date('d/m/Y', strtotime($appointment['appointment_date'])) . " at " . 
                           date('h:i A', strtotime($appointment['appointment_time'])) . " for " . 
                           $appointment['service_name'] . ".";
                break;
                
            case 'updated':
                $title = 'Appointment Updated';
                $message = "Appointment for {$appointment['customer_name']} has been updated. " . 
                           "Now scheduled for " . date('d/m/Y', strtotime($appointment['appointment_date'])) . 
                           " at " . date('h:i A', strtotime($appointment['appointment_time'])) . ".";
                break;
                
            case 'cancelled':
                $title = 'Appointment Cancelled';
                $message = "Appointment for {$appointment['customer_name']} on " . 
                           date('d/m/Y', strtotime($appointment['appointment_date'])) . " at " . 
                           date('h:i A', strtotime($appointment['appointment_time'])) . " has been cancelled.";
                break;
                
            case 'reminder':
                $title = 'Appointment Reminder';
                $message = "Reminder: You have an appointment with {$appointment['customer_name']} tomorrow at " . 
                           date('h:i A', strtotime($appointment['appointment_time'])) . " for " . 
                           $appointment['service_name'] . ".";
                break;
                
            default:
                return false;
        }
        
        // Create notification data
        $notification_data = [
            'title' => $title,
            'message' => $message,
            'type' => 'appointment',
            'related_to' => 'appointments',
            'related_id' => $appointment_id
        ];
        
        // Create notification for technician if assigned
        if (!empty($appointment['technician_id'])) {
            $notification_data['user_id'] = $appointment['technician_id'];
            $this->create($notification_data);
        }
        
        // Create notification for managers
        return $this->createForRole(USER_ROLE_MANAGER, $notification_data);
    }
    
    /**
     * Create invoice notification
     * 
     * @param int $invoice_id Invoice ID
     * @param string $action Action (created, paid, overdue)
     * @return bool True on success, false on failure
     */
    public function createInvoiceNotification($invoice_id, $action) {
        // Get invoice details
        $query = "SELECT i.*, c.name as customer_name
                 FROM invoices i
                 LEFT JOIN customers c ON i.customer_id = c.id
                 WHERE i.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $invoice = $result->fetch_assoc();
        
        // Set notification data based on action
        switch ($action) {
            case 'created':
                $title = 'New Invoice';
                $message = "New invoice #{$invoice['invoice_number']} created for {$invoice['customer_name']} " . 
                           "with a total amount of " . number_format($invoice['total_amount'], 2) . ".";
                break;
                
            case 'paid':
                $title = 'Invoice Paid';
                $message = "Invoice #{$invoice['invoice_number']} for {$invoice['customer_name']} " . 
                           "has been marked as paid.";
                break;
                
            case 'overdue':
                $title = 'Overdue Invoice';
                $message = "Invoice #{$invoice['invoice_number']} for {$invoice['customer_name']} " . 
                           "is now overdue. Due date was " . date('d/m/Y', strtotime($invoice['due_date'])) . ".";
                break;
                
            default:
                return false;
        }
        
        // Create notification data
        $notification_data = [
            'title' => $title,
            'message' => $message,
            'type' => 'invoice',
            'related_to' => 'invoices',
            'related_id' => $invoice_id
        ];
        
        // Create notification for managers and admin
        $roles = [USER_ROLE_MANAGER, USER_ROLE_ADMIN];
        
        foreach ($roles as $role) {
            $this->createForRole($role, $notification_data);
        }
        
        return true;
    }
    
    /**
     * Create inventory notification
     * 
     * @param int $part_id Part ID
     * @param string $action Action (low_stock, restock)
     * @return bool True on success, false on failure
     */
    public function createInventoryNotification($part_id, $action) {
        // Get part details
        $query = "SELECT * FROM parts WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $part_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $part = $result->fetch_assoc();
        
        // Set notification data based on action
        switch ($action) {
            case 'low_stock':
                $title = 'Low Stock Alert';
                $message = "Part '{$part['name']}' (#{$part['part_number']}) is running low on stock. " . 
                           "Current quantity: {$part['quantity']}, Reorder level: {$part['reorder_level']}.";
                break;
                
            case 'restock':
                $title = 'Restock Notification';
                $message = "Part '{$part['name']}' (#{$part['part_number']}) has been restocked. " . 
                           "Current quantity: {$part['quantity']}.";
                break;
                
            default:
                return false;
        }
        
        // Create notification data
        $notification_data = [
            'title' => $title,
            'message' => $message,
            'type' => 'inventory',
            'related_to' => 'parts',
            'related_id' => $part_id
        ];
        
        // Create notification for inventory managers
        return $this->createForRole(USER_ROLE_INVENTORY_MANAGER, $notification_data);
    }
    
    /**
     * Set notification properties from database row
     * 
     * @param array $row Database row
     */
    private function setNotificationProperties($row) {
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->title = $row['title'];
        $this->message = $row['message'];
        $this->type = $row['type'];
        $this->related_to = $row['related_to'];
        $this->related_id = $row['related_id'];
        $this->is_read = $row['is_read'];
        $this->created_at = $row['created_at'];
        $this->read_at = $row['read_at'];
    }
    
    /**
     * Get notification ID
     * 
     * @return int Notification ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get user ID
     * 
     * @return int User ID
     */
    public function getUserId() {
        return $this->user_id;
    }
    
    /**
     * Get notification title
     * 
     * @return string Notification title
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Get notification message
     * 
     * @return string Notification message
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * Get notification type
     * 
     * @return string Notification type
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Get related entity type
     * 
     * @return string Related entity type
     */
    public function getRelatedTo() {
        return $this->related_to;
    }
    
    /**
     * Get related entity ID
     * 
     * @return int Related entity ID
     */
    public function getRelatedId() {
        return $this->related_id;
    }
    
    /**
     * Check if notification is read
     * 
     * @return bool True if notification is read, false otherwise
     */
    public function isRead() {
        return $this->is_read == 1;
    }
    
    /**
     * Get notification created date
     * 
     * @return string Notification created date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    /**
     * Get notification read date
     * 
     * @return string Notification read date
     */
    public function getReadAt() {
        return $this->read_at;
    }
    
    /**
     * Get time ago string
     * 
     * @return string Time ago string
     */
    public function getTimeAgo() {
        $time = strtotime($this->created_at);
        $time_diff = time() - $time;
        
        if ($time_diff < 60) {
            return 'Just now';
        } elseif ($time_diff < 3600) {
            $minutes = floor($time_diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 86400) {
            $hours = floor($time_diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 604800) {
            $days = floor($time_diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('d/m/Y', $time);
        }
    }
    
    /**
     * Get notification icon class based on type
     * 
     * @return string Icon class
     */
    public function getIconClass() {
        switch ($this->type) {
            case 'appointment':
                return 'fa-calendar';
            case 'invoice':
                return 'fa-file-invoice-dollar';
            case 'inventory':
                return 'fa-boxes';
            case 'customer':
                return 'fa-user';
            case 'vehicle':
                return 'fa-car';
            case 'service':
                return 'fa-wrench';
            case 'system':
                return 'fa-cog';
            default:
                return 'fa-bell';
        }
    }
}
?>
