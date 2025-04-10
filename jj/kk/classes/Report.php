<?php
/**
 * Report Class
 * Handles report generation and management
 */
class Report {
    private $db;
    private $conn;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Generate sales report
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param string $group_by Group by (daily, weekly, monthly, yearly)
     * @param array $filters Optional filters
     * @return array Sales report data
     */
    public function generateSalesReport($start_date, $end_date, $group_by = 'monthly', $filters = []) {
        // Determine group by clause based on period
        switch ($group_by) {
            case 'daily':
                $group_by_clause = "DATE(i.invoice_date)";
                $date_format = "%Y-%m-%d";
                break;
            case 'weekly':
                $group_by_clause = "YEARWEEK(i.invoice_date, 1)";
                $date_format = "%Y-%u";
                break;
            case 'yearly':
                $group_by_clause = "YEAR(i.invoice_date)";
                $date_format = "%Y";
                break;
            case 'monthly':
            default:
                $group_by_clause = "YEAR(i.invoice_date), MONTH(i.invoice_date)";
                $date_format = "%Y-%m";
                break;
        }
        
        // Start with base query
        $query = "SELECT 
                 DATE_FORMAT(i.invoice_date, ?) as period,
                 COUNT(i.id) as total_invoices,
                 SUM(i.total_amount) as total_sales,
                 SUM(i.tax_amount) as total_tax,
                 SUM(i.discount_amount) as total_discount,
                 AVG(i.total_amount) as average_sale
                 FROM invoices i
                 LEFT JOIN customers c ON i.customer_id = c.id
                 LEFT JOIN users u ON i.user_id = u.id
                 WHERE i.invoice_date BETWEEN ? AND ?";
        
        $params = [$date_format, $start_date, $end_date];
        $types = "sss";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by status
            if (isset($filters['status'])) {
                $query .= " AND i.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            // Filter by payment method
            if (isset($filters['payment_method'])) {
                $query .= " AND i.payment_method = ?";
                $params[] = $filters['payment_method'];
                $types .= "s";
            }
            
            // Filter by user
            if (isset($filters['user_id'])) {
                $query .= " AND i.user_id = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
        }
        
        // Add group by and order by
        $query .= " GROUP BY {$group_by_clause} ORDER BY i.invoice_date ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate service report
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param array $filters Optional filters
     * @return array Service report data
     */
    public function generateServiceReport($start_date, $end_date, $filters = []) {
        // Start with base query
        $query = "SELECT 
                 s.id, s.name, s.category, s.price,
                 COUNT(a.id) as total_appointments,
                 SUM(ii.total) as total_revenue
                 FROM services s
                 LEFT JOIN appointments a ON s.id = a.service_id AND a.appointment_date BETWEEN ? AND ?
                 LEFT JOIN invoice_items ii ON ii.item_type = 'service' AND ii.item_id = s.id
                 LEFT JOIN invoices i ON ii.invoice_id = i.id AND i.invoice_date BETWEEN ? AND ?
                 WHERE s.status = ?";
        
        $params = [$start_date, $end_date, $start_date, $end_date, SERVICE_STATUS_ACTIVE];
        $types = "sssss";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category'])) {
                $query .= " AND s.category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
        }
        
        // Add group by and order by
        $query .= " GROUP BY s.id ORDER BY total_appointments DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate inventory report
     * 
     * @param array $filters Optional filters
     * @return array Inventory report data
     */
    public function generateInventoryReport($filters = []) {
        // Start with base query
        $query = "SELECT 
                 p.id, p.part_number, p.name, p.category, 
                 p.quantity, p.reorder_level, p.cost_price, p.selling_price,
                 (p.quantity * p.cost_price) as stock_value,
                 (p.selling_price - p.cost_price) as unit_profit,
                 ((p.selling_price - p.cost_price) / p.cost_price * 100) as profit_margin,
                 s.name as supplier_name
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
            
            // Filter by low stock
            if (isset($filters['low_stock']) && $filters['low_stock']) {
                $query .= " AND p.quantity <= p.reorder_level";
            }
        }
        
        // Add order by
        $query .= " ORDER BY p.category ASC, p.name ASC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters if any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate customer report
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param array $filters Optional filters
     * @return array Customer report data
     */
    public function generateCustomerReport($start_date, $end_date, $filters = []) {
        // Start with base query
        $query = "SELECT 
                 c.id, c.name, c.email, c.phone, c.city, c.state,
                 COUNT(DISTINCT i.id) as total_invoices,
                 COUNT(DISTINCT v.id) as total_vehicles,
                 COUNT(DISTINCT a.id) as total_appointments,
                 SUM(i.total_amount) as total_spent,
                 MAX(i.invoice_date) as last_invoice_date
                 FROM customers c
                 LEFT JOIN invoices i ON c.id = i.customer_id AND i.invoice_date BETWEEN ? AND ?
                 LEFT JOIN vehicles v ON c.id = v.customer_id
                 LEFT JOIN appointments a ON c.id = a.customer_id AND a.appointment_date BETWEEN ? AND ?
                 WHERE c.status = ?";
        
        $params = [$start_date, $end_date, $start_date, $end_date, CUSTOMER_STATUS_ACTIVE];
        $types = "sssss";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by city
            if (isset($filters['city'])) {
                $query .= " AND c.city = ?";
                $params[] = $filters['city'];
                $types .= "s";
            }
            
            // Filter by state
            if (isset($filters['state'])) {
                $query .= " AND c.state = ?";
                $params[] = $filters['state'];
                $types .= "s";
            }
            
            // Filter by minimum spent
            if (isset($filters['min_spent'])) {
                $query .= " HAVING total_spent >= ?";
                $params[] = $filters['min_spent'];
                $types .= "d";
            }
        } else {
            $query .= " GROUP BY c.id";
        }
        
        // Add order by
        $query .= " ORDER BY total_spent DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate technician performance report
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param array $filters Optional filters
     * @return array Technician performance report data
     */
    public function generateTechnicianReport($start_date, $end_date, $filters = []) {
        // Start with base query
        $query = "SELECT 
                 u.id, u.name, u.email,
                 COUNT(a.id) as total_appointments,
                 SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as completed_appointments,
                 SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as pending_appointments,
                 AVG(TIMESTAMPDIFF(MINUTE, a.appointment_time, a.completion_time)) as avg_service_time,
                 COUNT(DISTINCT c.id) as total_customers
                 FROM users u
                 LEFT JOIN appointments a ON u.id = a.technician_id AND a.appointment_date BETWEEN ? AND ?
                 LEFT JOIN customers c ON a.customer_id = c.id
                 WHERE u.role = ?";
        
        // Set appointment statuses and technician role
        $completed_status = APPOINTMENT_STATUS_COMPLETED;
        $pending_status = APPOINTMENT_STATUS_PENDING;
        $technician_role = USER_ROLE_TECHNICIAN;
        
        $params = [$completed_status, $pending_status, $start_date, $end_date, $technician_role];
        $types = "sssss";
        
        // Add filters if provided
        if (!empty($filters)) {
            // Filter by specific technician
            if (isset($filters['technician_id'])) {
                $query .= " AND u.id = ?";
                $params[] = $filters['technician_id'];
                $types .= "i";
            }
        }
        
        // Add group by and order by
        $query .= " GROUP BY u.id ORDER BY total_appointments DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate vehicle service history report
     * 
     * @param int $vehicle_id Vehicle ID
     * @return array Vehicle service history report data
     */
    public function generateVehicleServiceReport($vehicle_id) {
        // Start with base query
        $query = "SELECT 
                 a.id as appointment_id, a.appointment_date, a.appointment_time,
                 a.completion_time, a.status as appointment_status,
                 s.name as service_name, s.category as service_category,
                 u.name as technician_name,
                 i.invoice_number, i.total_amount, i.status as invoice_status,
                 v.make, v.model, v.year, v.license_plate, v.vin,
                 c.name as customer_name, c.phone as customer_phone
                 FROM appointments a
                 LEFT JOIN services s ON a.service_id = s.id
                 LEFT JOIN users u ON a.technician_id = u.id
                 LEFT JOIN invoices i ON a.invoice_id = i.id
                 LEFT JOIN vehicles v ON a.vehicle_id = v.id
                 LEFT JOIN customers c ON v.customer_id = c.id
                 WHERE a.vehicle_id = ?
                 ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("i", $vehicle_id);
        
        // Execute query
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch report data
        $report_data = [];
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        
        return $report_data;
    }
    
    /**
     * Generate dashboard summary
     * 
     * @return array Dashboard summary data
     */
    public function generateDashboardSummary() {
        $summary = [];
        
        // Get current date
        $today = date('Y-m-d');
        $current_month_start = date('Y-m-01');
        $current_month_end = date('Y-m-t');
        $last_month_start = date('Y-m-01', strtotime('-1 month'));
        $last_month_end = date('Y-m-t', strtotime('-1 month'));
        
        // Get total customers
        $query = "SELECT COUNT(*) as total FROM customers WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $active_status = CUSTOMER_STATUS_ACTIVE;
        $stmt->bind_param("s", $active_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['total_customers'] = $row['total'];
        
        // Get total vehicles
        $query = "SELECT COUNT(*) as total FROM vehicles";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        $summary['total_vehicles'] = $row['total'];
        
        // Get today's appointments
        $query = "SELECT COUNT(*) as total FROM appointments WHERE appointment_date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['todays_appointments'] = $row['total'];
        
        // Get pending appointments
        $query = "SELECT COUNT(*) as total FROM appointments WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $pending_status = APPOINTMENT_STATUS_PENDING;
        $stmt->bind_param("s", $pending_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['pending_appointments'] = $row['total'];
        
        // Get current month sales
        $query = "SELECT SUM(total_amount) as total FROM invoices WHERE invoice_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $current_month_start, $current_month_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['current_month_sales'] = $row['total'] ?? 0;
        
        // Get last month sales
        $query = "SELECT SUM(total_amount) as total FROM invoices WHERE invoice_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $last_month_start, $last_month_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['last_month_sales'] = $row['total'] ?? 0;
        
        // Get sales growth percentage
        if ($summary['last_month_sales'] > 0) {
            $summary['sales_growth'] = (($summary['current_month_sales'] - $summary['last_month_sales']) / $summary['last_month_sales']) * 100;
        } else {
            $summary['sales_growth'] = 0;
        }
        
        // Get unpaid invoices
        $query = "SELECT COUNT(*) as total, SUM(total_amount) as amount FROM invoices WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $pending_status = INVOICE_STATUS_PENDING;
        $stmt->bind_param("s", $pending_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['unpaid_invoices_count'] = $row['total'];
        $summary['unpaid_invoices_amount'] = $row['amount'] ?? 0;
        
        // Get low stock parts
        $query = "SELECT COUNT(*) as total FROM parts WHERE quantity <= reorder_level AND status = ?";
        $stmt = $this->conn->prepare($query);
        $active_status = PART_STATUS_ACTIVE;
        $stmt->bind_param("s", $active_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['low_stock_parts'] = $row['total'];
        
        // Get total inventory value
        $query = "SELECT SUM(quantity * cost_price) as total FROM parts WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $active_status = PART_STATUS_ACTIVE;
        $stmt->bind_param("s", $active_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary['inventory_value'] = $row['total'] ?? 0;
        
        return $summary;
    }
    
    /**
     * Export report to CSV
     * 
     * @param array $data Report data
     * @param string $filename Filename
     * @return string CSV content
     */
    public function exportToCsv($data, $filename = 'report.csv') {
        if (empty($data)) {
            return false;
        }
        
        // Open output buffer
        ob_start();
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Get the column headers from the first row
        $headers = array_keys($data[0]);
        
        // Output the column headings
        fputcsv($output, $headers);
        
        // Output each row of data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        // Get the content from the output buffer
        $content = ob_get_clean();
        
        // Return the CSV content
        return $content;
    }
    
    /**
     * Format currency
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency symbol
     * @return string Formatted amount
     */
    public function formatCurrency($amount, $currency = '$') {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Format percentage
     * 
     * @param float $value Value to format
     * @param int $decimals Number of decimal places
     * @return string Formatted percentage
     */
    public function formatPercentage($value, $decimals = 2) {
        return number_format($value, $decimals) . '%';
    }
    
    /**
     * Format date
     * 
     * @param string $date Date to format
     * @param string $format Date format
     * @return string Formatted date
     */
    public function formatDate($date, $format = 'Y-m-d') {
        return date($format, strtotime($date));
    }
}
?>
