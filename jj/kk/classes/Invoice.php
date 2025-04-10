<?php
/**
 * Invoice Class
 * Handles invoice operations and management
 */
class Invoice {
    private $db;
    private $conn;
    
    // Invoice properties
    private $id;
    private $invoice_number;
    private $customer_id;
    private $vehicle_id;
    private $user_id;
    private $invoice_date;
    private $due_date;
    private $subtotal;
    private $tax_rate;
    private $tax_amount;
    private $discount_amount;
    private $total_amount;
    private $payment_method;
    private $payment_date;
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
     * Get invoice by ID
     * 
     * @param int $id Invoice ID
     * @return bool True if invoice found, false otherwise
     */
    public function getById($id) {
        $query = "SELECT * FROM invoices WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setInvoiceProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get invoice by invoice number
     * 
     * @param string $invoice_number Invoice number
     * @return bool True if invoice found, false otherwise
     */
    public function getByInvoiceNumber($invoice_number) {
        $query = "SELECT * FROM invoices WHERE invoice_number = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->setInvoiceProperties($row);
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate a unique invoice number
     * 
     * @return string Unique invoice number
     */
    public function generateInvoiceNumber() {
        // Format: INV-YYYYMMDD-XXXX where XXXX is a sequential number
        $date_part = date('Ymd');
        
        // Get the last invoice number with this date prefix
        $query = "SELECT MAX(invoice_number) as max_number FROM invoices WHERE invoice_number LIKE ?";
        $stmt = $this->conn->prepare($query);
        $search_pattern = "INV-" . $date_part . "-%";
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
        
        // Combine to form the invoice number
        return "INV-" . $date_part . "-" . $seq_part;
    }
    
    /**
     * Create a new invoice
     * 
     * @param array $data Invoice data
     * @return bool True on success, false on failure
     */
    public function create($data) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Generate invoice number if not provided
            $invoice_number = isset($data['invoice_number']) ? $data['invoice_number'] : $this->generateInvoiceNumber();
            
            // Set default status if not provided
            $status = isset($data['status']) ? $data['status'] : INVOICE_STATUS_PENDING;
            
            // Calculate tax amount
            $tax_amount = ($data['subtotal'] * $data['tax_rate']) / 100;
            
            // Calculate total amount
            $total_amount = $data['subtotal'] + $tax_amount - $data['discount_amount'];
            
            // Current timestamp
            $created_at = date('Y-m-d H:i:s');
            
            // Prepare query
            $query = "INSERT INTO invoices (
                      invoice_number, customer_id, vehicle_id, user_id, 
                      invoice_date, due_date, subtotal, tax_rate, tax_amount, 
                      discount_amount, total_amount, payment_method, payment_date, 
                      notes, status, created_at
                  ) VALUES (
                      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                  )";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bind_param(
                "siiiissddddsssss",
                $invoice_number,
                $data['customer_id'],
                $data['vehicle_id'],
                $data['user_id'],
                $data['invoice_date'],
                $data['due_date'],
                $data['subtotal'],
                $data['tax_rate'],
                $tax_amount,
                $data['discount_amount'],
                $total_amount,
                $data['payment_method'],
                $data['payment_date'],
                $data['notes'],
                $status,
                $created_at
            );
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to create invoice: " . $stmt->error);
            }
            
            $this->id = $this->conn->insert_id;
            
            // Add invoice items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addInvoiceItem($item);
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
     * Add an invoice item
     * 
     * @param array $item Item data
     * @return bool True on success, false on failure
     */
    public function addInvoiceItem($item) {
        // Check if invoice exists
        if (!$this->id) {
            return false;
        }
        
        // Calculate item total
        $item_total = $item['quantity'] * $item['price'];
        
        // Prepare query
        $query = "INSERT INTO invoice_items (
                  invoice_id, item_type, item_id, description, 
                  quantity, price, total
              ) VALUES (
                  ?, ?, ?, ?, ?, ?, ?
              )";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "isisdd",
            $this->id,
            $item['item_type'],
            $item['item_id'],
            $item['description'],
            $item['quantity'],
            $item['price'],
            $item_total
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Update invoice
     * 
     * @param array $data Invoice data
     * @return bool True on success, false on failure
     */
    public function update($data) {
        // Check if invoice exists
        if (!$this->id) {
            return false;
        }
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Calculate tax amount
            $tax_amount = ($data['subtotal'] * $data['tax_rate']) / 100;
            
            // Calculate total amount
            $total_amount = $data['subtotal'] + $tax_amount - $data['discount_amount'];
            
            // Current timestamp
            $updated_at = date('Y-m-d H:i:s');
            
            // Prepare query
            $query = "UPDATE invoices SET 
                      customer_id = ?, 
                      vehicle_id = ?, 
                      user_id = ?, 
                      invoice_date = ?, 
                      due_date = ?, 
                      subtotal = ?, 
                      tax_rate = ?, 
                      tax_amount = ?, 
                      discount_amount = ?, 
                      total_amount = ?, 
                      payment_method = ?, 
                      payment_date = ?, 
                      notes = ?, 
                      status = ?, 
                      updated_at = ? 
                      WHERE id = ?";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bind_param(
                "iiissdddddssssi",
                $data['customer_id'],
                $data['vehicle_id'],
                $data['user_id'],
                $data['invoice_date'],
                $data['due_date'],
                $data['subtotal'],
                $data['tax_rate'],
                $tax_amount,
                $data['discount_amount'],
                $total_amount,
                $data['payment_method'],
                $data['payment_date'],
                $data['notes'],
                $data['status'],
                $updated_at,
                $this->id
            );
            
            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Failed to update invoice: " . $stmt->error);
            }
            
            // Update invoice items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $this->deleteInvoiceItems();
                
                // Add new items
                foreach ($data['items'] as $item) {
                    $this->addInvoiceItem($item);
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
     * Delete invoice items
     * 
     * @return bool True on success, false on failure
     */
    public function deleteInvoiceItems() {
        // Check if invoice exists
        if (!$this->id) {
            return false;
        }
        
        // Prepare query
        $query = "DELETE FROM invoice_items WHERE invoice_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete invoice
     * 
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Check if invoice exists
        if (!$this->id) {
            return false;
        }
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Delete invoice items
            $this->deleteInvoiceItems();
            
            // Delete invoice
            $query = "DELETE FROM invoices WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $this->id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete invoice: " . $stmt->error);
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
     * Get all invoices
     * 
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of invoices
     */
    public function getAll($filters = [], $limit = 0, $offset = 0) {
        // Start with base query
        $query = "SELECT i.*, c.name as customer_name, 
                 v.make, v.model, v.year, v.license_plate, 
                 u.name as user_name 
                 FROM invoices i
                 LEFT JOIN customers c ON i.customer_id = c.id
                 LEFT JOIN vehicles v ON i.vehicle_id = v.id
                 LEFT JOIN users u ON i.user_id = u.id
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by customer
            if (isset($filters['customer_id'])) {
                $query .= " AND i.customer_id = ?";
                $params[] = $filters['customer_id'];
                $types .= "i";
            }
            
            // Filter by vehicle
            if (isset($filters['vehicle_id'])) {
                $query .= " AND i.vehicle_id = ?";
                $params[] = $filters['vehicle_id'];
                $types .= "i";
            }
            
            // Filter by user
            if (isset($filters['user_id'])) {
                $query .= " AND i.user_id = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND i.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by date range
            if (isset($filters['start_date'])) {
                $query .= " AND i.invoice_date >= ?";
                $params[] = $filters['start_date'];
                $types .= "s";
            }
            
            if (isset($filters['end_date'])) {
                $query .= " AND i.invoice_date <= ?";
                $params[] = $filters['end_date'];
                $types .= "s";
            }
            
            // Filter by search term (invoice number, customer name)
            if (isset($filters['search'])) {
                $query .= " AND (i.invoice_number LIKE ? OR c.name LIKE ?)";
                $search_term = "%" . $filters['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ss";
            }
        }
        
        // Add order by
        $query .= " ORDER BY i.invoice_date DESC, i.id DESC";
        
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
        
        // Fetch all invoices
        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        
        return $invoices;
    }
    
    /**
     * Count invoices
     * 
     * @param array $filters Optional filters
     * @return int Number of invoices
     */
    public function countAll($filters = []) {
        // Start with base query
        $query = "SELECT COUNT(*) as total FROM invoices i
                 LEFT JOIN customers c ON i.customer_id = c.id
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by customer
            if (isset($filters['customer_id'])) {
                $query .= " AND i.customer_id = ?";
                $params[] = $filters['customer_id'];
                $types .= "i";
            }
            
            // Filter by vehicle
            if (isset($filters['vehicle_id'])) {
                $query .= " AND i.vehicle_id = ?";
                $params[] = $filters['vehicle_id'];
                $types .= "i";
            }
            
            // Filter by user
            if (isset($filters['user_id'])) {
                $query .= " AND i.user_id = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND i.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by date range
            if (isset($filters['start_date'])) {
                $query .= " AND i.invoice_date >= ?";
                $params[] = $filters['start_date'];
                $types .= "s";
            }
            
            if (isset($filters['end_date'])) {
                $query .= " AND i.invoice_date <= ?";
                $params[] = $filters['end_date'];
                $types .= "s";
            }
            
            // Filter by search term (invoice number, customer name)
            if (isset($filters['search'])) {
                $query .= " AND (i.invoice_number LIKE ? OR c.name LIKE ?)";
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
     * Get invoice items
     * 
     * @return array Array of invoice items
     */
    public function getItems() {
        // Check if invoice exists
        if (!$this->id) {
            return [];
        }
        
        // Prepare query
        $query = "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $this->id);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all items
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    /**
     * Mark invoice as paid
     * 
     * @param string $payment_method Payment method
     * @param string $payment_date Payment date (YYYY-MM-DD)
     * @param string $notes Payment notes
     * @return bool True on success, false on failure
     */
    public function markAsPaid($payment_method, $payment_date = null, $notes = '') {
        // Check if invoice exists
        if (!$this->id) {
            return false;
        }
        
        // Set payment date to current date if not provided
        if (empty($payment_date)) {
            $payment_date = date('Y-m-d');
        }
        
        // Current timestamp
        $updated_at = date('Y-m-d H:i:s');
        
        // Prepare query
        $query = "UPDATE invoices SET 
                  status = ?, 
                  payment_method = ?, 
                  payment_date = ?, 
                  notes = CONCAT(notes, ?), 
                  updated_at = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set paid status
        $paid_status = INVOICE_STATUS_PAID;
        
        // Format payment notes
        $payment_notes = empty($notes) ? '' : "\n\nPayment Notes: " . $notes;
        
        // Bind parameters
        $stmt->bind_param(
            "sssssi",
            $paid_status,
            $payment_method,
            $payment_date,
            $payment_notes,
            $updated_at,
            $this->id
        );
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Get invoice statistics
     * 
     * @param string $period Period (daily, weekly, monthly, yearly)
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Invoice statistics
     */
    public function getStatistics($period = 'monthly', $start_date = null, $end_date = null) {
        // Set default dates if not provided
        if (empty($start_date)) {
            // Default to first day of current month
            $start_date = date('Y-m-01');
        }
        
        if (empty($end_date)) {
            // Default to current date
            $end_date = date('Y-m-d');
        }
        
        // Determine group by clause based on period
        switch ($period) {
            case 'daily':
                $group_by = "DATE(invoice_date)";
                $date_format = "%Y-%m-%d";
                break;
            case 'weekly':
                $group_by = "YEARWEEK(invoice_date, 1)";
                $date_format = "%Y-%u";
                break;
            case 'yearly':
                $group_by = "YEAR(invoice_date)";
                $date_format = "%Y";
                break;
            case 'monthly':
            default:
                $group_by = "YEAR(invoice_date), MONTH(invoice_date)";
                $date_format = "%Y-%m";
                break;
        }
        
        // Prepare query
        $query = "SELECT 
                 DATE_FORMAT(invoice_date, ?) as period,
                 COUNT(*) as total_invoices,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_invoices,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_invoices,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as overdue_invoices,
                 SUM(total_amount) as total_amount,
                 SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as paid_amount
                 FROM invoices
                 WHERE invoice_date BETWEEN ? AND ?
                 GROUP BY {$group_by}
                 ORDER BY invoice_date ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Set statuses
        $paid_status = INVOICE_STATUS_PAID;
        $pending_status = INVOICE_STATUS_PENDING;
        $overdue_status = INVOICE_STATUS_OVERDUE;
        
        // Bind parameters
        $stmt->bind_param(
            "sssssss",
            $date_format,
            $paid_status,
            $pending_status,
            $overdue_status,
            $paid_status,
            $start_date,
            $end_date
        );
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch statistics
        $statistics = [];
        while ($row = $result->fetch_assoc()) {
            $statistics[] = $row;
        }
        
        return $statistics;
    }
    
    /**
     * Set invoice properties from database row
     * 
     * @param array $row Database row
     */
    private function setInvoiceProperties($row) {
        $this->id = $row['id'];
        $this->invoice_number = $row['invoice_number'];
        $this->customer_id = $row['customer_id'];
        $this->vehicle_id = $row['vehicle_id'];
        $this->user_id = $row['user_id'];
        $this->invoice_date = $row['invoice_date'];
        $this->due_date = $row['due_date'];
        $this->subtotal = $row['subtotal'];
        $this->tax_rate = $row['tax_rate'];
        $this->tax_amount = $row['tax_amount'];
        $this->discount_amount = $row['discount_amount'];
        $this->total_amount = $row['total_amount'];
        $this->payment_method = $row['payment_method'];
        $this->payment_date = $row['payment_date'];
        $this->notes = $row['notes'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    /**
     * Get invoice ID
     * 
     * @return int Invoice ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get invoice number
     * 
     * @return string Invoice number
     */
    public function getInvoiceNumber() {
        return $this->invoice_number;
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
     * Get vehicle ID
     * 
     * @return int Vehicle ID
     */
    public function getVehicleId() {
        return $this->vehicle_id;
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
     * Get invoice date
     * 
     * @return string Invoice date
     */
    public function getInvoiceDate() {
        return $this->invoice_date;
    }
    
    /**
     * Get due date
     * 
     * @return string Due date
     */
    public function getDueDate() {
        return $this->due_date;
    }
    
    /**
     * Get subtotal
     * 
     * @return float Subtotal
     */
    public function getSubtotal() {
        return $this->subtotal;
    }
    
    /**
     * Get tax rate
     * 
     * @return float Tax rate
     */
    public function getTaxRate() {
        return $this->tax_rate;
    }
    
    /**
     * Get tax amount
     * 
     * @return float Tax amount
     */
    public function getTaxAmount() {
        return $this->tax_amount;
    }
    
    /**
     * Get discount amount
     * 
     * @return float Discount amount
     */
    public function getDiscountAmount() {
        return $this->discount_amount;
    }
    
    /**
     * Get total amount
     * 
     * @return float Total amount
     */
    public function getTotalAmount() {
        return $this->total_amount;
    }
    
    /**
     * Get payment method
     * 
     * @return string Payment method
     */
    public function getPaymentMethod() {
        return $this->payment_method;
    }
    
    /**
     * Get payment date
     * 
     * @return string Payment date
     */
    public function getPaymentDate() {
        return $this->payment_date;
    }
    
    /**
     * Get notes
     * 
     * @return string Notes
     */
    public function getNotes() {
        return $this->notes;
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
     * Format amount
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency symbol
     * @return string Formatted amount
     */
    public function formatAmount($amount, $currency = '$') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Check if invoice is paid
     * 
     * @return bool True if invoice is paid, false otherwise
     */
    public function isPaid() {
        return $this->status === INVOICE_STATUS_PAID;
    }
    
    /**
     * Check if invoice is pending
     * 
     * @return bool True if invoice is pending, false otherwise
     */
    public function isPending() {
        return $this->status === INVOICE_STATUS_PENDING;
    }
    
    /**
     * Check if invoice is overdue
     * 
     * @return bool True if invoice is overdue, false otherwise
     */
    public function isOverdue() {
        return $this->status === INVOICE_STATUS_OVERDUE;
    }
    
    /**
     * Check if invoice is due
     * 
     * @return bool True if invoice is due, false otherwise
     */
    public function isDue() {
        if ($this->isPaid()) {
            return false;
        }
        
        $today = date('Y-m-d');
        return $this->due_date <= $today;
    }
}
?>
