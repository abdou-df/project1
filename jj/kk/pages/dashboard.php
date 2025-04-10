<?php
// Dashboard page
require_once 'includes/database.php';

// Debug database connection
$db = getDbConnection();
if (!$db) {
    die("خطأ في الاتصال بقاعدة البيانات");
}

// Check if database exists
$dbCheckQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'";
$dbResult = $db->query($dbCheckQuery);
if (!$dbResult || $dbResult->num_rows === 0) {
    die("قاعدة البيانات '" . DB_NAME . "' غير موجودة");
}

// --- Fetch Simple Stats (Based on Initial Debug Code) ---
$simpleStats = [
    'customers' => 0,
    'users' => 0,
    'suppliers' => 0,
    'inventory' => 0, // Renamed from products for consistency with cards
    'services' => 0,
    'sales' => 0, // Sales count will be simple total count for now
];

$tablesForSimpleStats = [
    'customers' => 'customers',
    'users' => 'users',
    'suppliers' => 'suppliers',
    'inventory' => 'inventory',
    'services' => 'services',
    'sales' => 'sales'
];

foreach ($tablesForSimpleStats as $key => $table) {
    $tableExists = $db->query("SHOW TABLES LIKE '$table'");
    if ($tableExists && $tableExists->num_rows > 0) {
        $countResult = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
        $simpleStats[$key] = (int)$count;
        error_log("SimpleStats: Fetched $key ($table) count: $count"); // Keep logging
    } else {
        error_log("SimpleStats: Table '$table' not found for key '$key'.");
    }
}
// --- End Fetch Simple Stats ---


/*
// --- Fetch Service Specific Counts (Free vs Paid based on price) ---
$stats['free_services'] = 0;
$stats['paid_services'] = 0;

$servicesTableExists = $db->query("SHOW TABLES LIKE 'services'");
if ($servicesTableExists && $servicesTableExists->num_rows > 0) {
    // Assumes price = 0 means free, price > 0 means paid
    $servicesCountSql = "SELECT 
                            SUM(CASE WHEN price = 0.00 THEN 1 ELSE 0 END) as free_count,
                            SUM(CASE WHEN price > 0.00 THEN 1 ELSE 0 END) as paid_count
                         FROM services";
    $servicesCountResult = $db->query($servicesCountSql);
    if ($servicesCountResult) {
        $counts = $servicesCountResult->fetch_assoc();
        $stats['free_services'] = (int)($counts['free_count'] ?? 0);
        $stats['paid_services'] = (int)($counts['paid_count'] ?? 0);
        // Recalculate total based on this specific query if needed, although simpleStats['services'] should match
        // $stats['services'] = $stats['free_services'] + $stats['paid_services']; 
        error_log("Fetched service counts - Free: {$stats['free_services']}, Paid: {$stats['paid_services']}");
        $servicesCountResult->free();
    } else {
        error_log("Error executing services free/paid count query: (" . $db->errno . ") " . $db->error . " | SQL: $servicesCountSql");
    }
} else {
     error_log("Table 'services' not found for free/paid count.");
}
// --- End Fetch Service Specific Counts ---
*/


/*
// Function to check if table exists (Keep for potential future use, but commented out calls)
function tableExists($tableName) {
// ... (rest of function as it was, or remove if definitely not using getDashboardStats)
}
*/

/*
// Function to get dashboard statistics (Commented out as it seems problematic)
function getDashboardStats() {
    // ... (entire function body) ...
}
*/

// Get dashboard statistics - DISABLED, using $simpleStats instead
// $stats = getDashboardStats();
$stats = $simpleStats; // Use the simple stats collected earlier

// Debug output (kept for quick browser view if needed, but error log is better)
// error_log("Final stats passed to page (using simpleStats): " . print_r($stats, true));

// Calculate service percentages for the donut chart (Use newly fetched counts)
$totalServices = $stats['services'] ?? 0; // Total count from initial simple fetch
$freeServices = $stats['free_services'] ?? 0;
$paidServices = $stats['paid_services'] ?? 0;

// Ensure totalServices used for percentage is consistent if recalculation needed
$totalServicesForPercentage = ($totalServices > 0) ? $totalServices : ($freeServices + $paidServices); // Use the most reliable total

$freePercentage = ($totalServicesForPercentage > 0) ? round(($freeServices / $totalServicesForPercentage) * 100) : 0;
$paidPercentage = ($totalServicesForPercentage > 0) ? round(($paidServices / $totalServicesForPercentage) * 100) : 0;
// Ensure percentages add up correctly, adjust if needed due to rounding
if ($totalServicesForPercentage > 0 && ($freePercentage + $paidPercentage) != 100) {
    // Adjust the larger percentage slightly if rounding caused mismatch
    if ($freePercentage > $paidPercentage) {
        $freePercentage = 100 - $paidPercentage;
    } else {
        $paidPercentage = 100 - $freePercentage;
    }
}

// Initialize empty arrays for other data
$recentCustomers = [];
$calendarEvents = []; // This was for the current month, we might not need it anymore
$recentActivities = []; // Assuming you might add this later based on commented out code
$upcomingAppointments = []; // Array for upcoming appointments

try {
    // Get recent customers (Keep this part, assuming dbQuery works)
    // if (tableExists('customers')) { // Comment out tableExists check if function is fully commented
    if (isset($stats['customers'])) { // Check if the key exists from simple fetch
        // Corrected SQL: Select first_name, last_name instead of name. Removed image.
        $recentCustomersSql = "SELECT id, first_name, last_name, email
                              FROM customers
                              ORDER BY created_at DESC
                              LIMIT 3";
        // Assuming dbQuery function exists and works from includes/database.php
        // --- Using direct query for consistency and because dbQuery might be unreliable ---
        $recentCustomersResult = $db->query($recentCustomersSql);
        if ($recentCustomersResult) {
            while ($row = $recentCustomersResult->fetch_assoc()) {
                $recentCustomers[] = $row;
            }
            $recentCustomersResult->free();
            error_log("Fetched " . count($recentCustomers) . " recent customers using direct query.");
        } else {
             // Remove direct error output, rely on error_log
             error_log("Error fetching recent customers using direct query: (" . $db->errno . ") " . $db->error . " | SQL: $recentCustomersSql");
             $recentCustomers = []; // Reset to empty on error
        }
        /* // Comment out potentially problematic dbQuery call
        if (function_exists('dbQuery')) {
             $recentCustomers = dbQuery($recentCustomersSql);
             if ($recentCustomers === false) {
                 error_log("Error fetching recent customers using dbQuery.");
                 $recentCustomers = []; // Reset to empty on error
             }
        } else {
            error_log("Function dbQuery does not exist. Cannot fetch recent customers.");
        }
        */
            } else {
         error_log("Cannot fetch recent customers because 'customers' table count was not retrieved.");
    }

    // Get upcoming appointments (e.g., next 5 starting from today)
    $appointmentsTableExists = $db->query("SHOW TABLES LIKE 'appointments'"); // Correct table name
    if ($appointmentsTableExists && $appointmentsTableExists->num_rows > 0) {
        // --- Restore Original Query Logic --- 
        // Fetch upcoming appointments (date >= today)
        $upcomingAppointmentsSql = "SELECT id, customer_id, service_id, date, start_time, notes
                                    FROM appointments 
                                    WHERE date >= CURDATE() -- Get events from today onwards
                                    ORDER BY date ASC, start_time ASC -- Order by date then time
                                    LIMIT 5"; 

        // Use direct query method
        $result = $db->query($upcomingAppointmentsSql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $upcomingAppointments[] = $row;
            }
            $result->free(); // Free the result set
            error_log("Fetched " . count($upcomingAppointments) . " upcoming appointments using direct query from 'appointments' table.");
        } else {
            // Remove direct error output, rely on error_log
            error_log("Error executing upcoming appointments query: (" . $db->errno . ") " . $db->error . " | SQL: $upcomingAppointmentsSql");
            $upcomingAppointments = []; // Ensure it's an empty array on error
        }
    } else {
        // Remove direct error output, rely on error_log
        error_log("Table 'appointments' does not exist. Cannot fetch upcoming appointments.");
    }

    // --- Fetch appointments for the current month for the calendar ---
    $currentYear = date('Y');
    $currentMonth = date('m');
    $calendarAppointments = [];
    $eventDates = []; // Array to hold dates with events

    $calendarTableExists = $db->query("SHOW TABLES LIKE 'appointments'");
    if ($calendarTableExists && $calendarTableExists->num_rows > 0) {
        $calendarSql = "SELECT DISTINCT DATE(date) as event_day 
                        FROM appointments 
                        WHERE YEAR(date) = ? AND MONTH(date) = ?";
        $stmt = $db->prepare($calendarSql);
        if($stmt) {
            $stmt->bind_param("ss", $currentYear, $currentMonth);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $eventDates[] = $row['event_day'];
                }
                $result->free();
                error_log("Fetched " . count($eventDates) . " unique event dates for calendar month $currentYear-$currentMonth.");
            } else {
                 error_log("Error executing calendar events query: (" . $stmt->errno . ") " . $stmt->error);
            }
            $stmt->close();
        } else {
             error_log("Error preparing calendar events query: (" . $db->errno . ") " . $db->error);
        }
    }


    // --- End fetching calendar appointments ---

    // --- Fetch Inventory Alerts (Low Stock Items) ---
    $lowStockItems = [];
    $inventoryTableExists = $db->query("SHOW TABLES LIKE 'inventory'");
    if ($inventoryTableExists && $inventoryTableExists->num_rows > 0) {
        $lowStockSql = "SELECT id, name, quantity, reorder_level 
                        FROM inventory 
                        WHERE quantity <= reorder_level 
                        ORDER BY quantity ASC 
                        LIMIT 5"; // Limit to show top 5 low stock items
        $lowStockResult = $db->query($lowStockSql);
        if ($lowStockResult) {
            while($row = $lowStockResult->fetch_assoc()) {
                $lowStockItems[] = $row;
            }
            $lowStockResult->free();
            error_log("Fetched " . count($lowStockItems) . " low stock items.");
        } else {
            error_log("Error executing low stock items query: (" . $db->errno . ") " . $db->error . " | SQL: $lowStockSql");
        }
    } else {
        error_log("Table 'inventory' does not exist. Cannot fetch low stock items.");
    }
    // --- End Fetch Inventory Alerts ---

    // --- Fetch Appointment Status Counts ---
    $appointmentStatusCounts = ['in_progress' => 0, 'confirmed_today' => 0];
    if ($appointmentsTableExists && $appointmentsTableExists->num_rows > 0) { // Use check from earlier
        $statusSql = "SELECT 
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                        SUM(CASE WHEN status = 'confirmed' AND date = CURDATE() THEN 1 ELSE 0 END) as confirmed_today_count
                      FROM appointments";
        $statusResult = $db->query($statusSql);
        if ($statusResult) {
            $counts = $statusResult->fetch_assoc();
            $appointmentStatusCounts['in_progress'] = (int)($counts['in_progress_count'] ?? 0);
            $appointmentStatusCounts['confirmed_today'] = (int)($counts['confirmed_today_count'] ?? 0);
            $statusResult->free();
             error_log("Fetched appointment status counts - In Progress: {$appointmentStatusCounts['in_progress']}, Confirmed Today: {$appointmentStatusCounts['confirmed_today']}");
        } else {
             error_log("Error executing appointment status count query: (" . $db->errno . ") " . $db->error . " | SQL: $statusSql");
        }
    }
    // --- End Fetch Appointment Status Counts ---

} catch (Exception $e) {
    // Remove direct error output, rely on error_log
    error_log('Dashboard error fetching data (Exception): ' . $e->getMessage());
}

// If no image for customers, use default image
if (is_array($recentCustomers)) {
foreach ($recentCustomers as &$customer) {
    if (empty($customer['image']) || !file_exists($customer['image'])) {
        $customer['image'] = 'assets/images/default-user.png';
    }
}
}

// --- Calendar Variables ---
$year = date('Y');
$month = date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('N', strtotime("$year-$month-01")); // 1 (for Monday) through 7 (for Sunday)
// Adjust firstDayOfMonth for a Sunday start week (common in Arabic calendars)
$firstDayOfMonth = ($firstDayOfMonth % 7); // 0 (Sunday) through 6 (Saturday)
$todayDay = date('d');
$todayMonth = date('m');
$todayYear = date('Y');

// Format month name in Arabic
$monthName = '';
if (class_exists('IntlDateFormatter')) {
    $formatter = new IntlDateFormatter('ar_SA', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMMM');
    $monthName = $formatter->format(mktime(0, 0, 0, $month, 1, $year));
} else {
    $monthName = date('F', mktime(0, 0, 0, $month, 1, $year)); // Fallback English name
}
// --- End Calendar Variables ---
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - نظام إدارة الورشة</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <style>
        /* Add styles for the donut chart segments if not already in dashboard.css */
        .donut-chart {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background: #e9ecef; /* Background for empty state */
        }
        .donut-segment {
            position: absolute;
            width: 100%;
            height: 100%;
            clip-path: polygon(50% 0%, 100% 0%, 100% 100%, 50% 100%, 50% 50%);
            background-color: var(--segment-color);
            transform: rotate(calc(3.6deg * var(--segment-start, 0))); /* Start rotation */
            --segment-size-deg: calc(3.6deg * var(--segment-size));
            clip-path: inset(0 calc(100% - var(--segment-size-deg)) 0 0 round 50%); /* Clip based on size */
        }
        /* Optional: Style for the center text */
       
        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            vertical-align: middle;
        }
        /* Style for calendar */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .calendar-days-header,
        .calendar-days {
            display: contents;
        }
        .calendar-days-header > div {
            text-align: center;
            font-weight: bold;
            font-size: 0.85em;
            color: #6c757d;
            padding-bottom: 5px;
        }
        .calendar-day {
            position: relative;
            text-align: center;
            padding: 8px 0;
            font-size: 0.9em;
            border: 1px solid transparent;
            border-radius: 50%;
            cursor: pointer;
            aspect-ratio: 1 / 1; /* Make days square/round */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }
        .calendar-day.blank {
            visibility: hidden;
        }
        .calendar-day:not(.blank):hover {
            background-color: #f8f9fa;
        }
        .calendar-day.today {
            background-color: #ffc107; /* Bootstrap warning color */
            color: #fff;
            font-weight: bold;
            border-color: #ffc107;
        }
        
        .calendar-day .calendar-event {
            position: absolute;
            bottom: 5px; /* Position dot at the bottom */
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
           background-color: var(--bs-primary);  
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Stats Cards -->
        <div class="stats-dashboard">
            <!-- Row 1: Customers and Employees -->
            <div class="row g-4">
                <!-- Customers Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-customer me-3">
                                    <i class="fa-solid fa-user-group"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">العملاء</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['customers'] ?? 0; ?></h2>
                                    <!-- <small class="text-success">
                                        <i class="fa-solid fa-arrow-up me-1"></i> نشط هذا الشهر
                                    </small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Employees Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-employees me-3">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">الموظفين</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['users'] ?? 0; ?></h2>
                                    <!-- <small class="text-success">
                                        <i class="fa-solid fa-arrow-up me-1"></i> نشط هذا الشهر
                                    </small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Row 2: Sales and Suppliers -->
            <div class="row g-4 mt-4">
                <!-- Sales Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-sales me-3">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">المبيعات</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['sales'] ?? 0; ?></h2>
                                    <!-- Growth requires more complex query, removed for now -->
                                    <!-- <small class="<?php // echo $stats['sales_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <i class="fa-solid fa-<?php // echo $stats['sales_growth'] >= 0 ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                        <?php // echo abs($stats['sales_growth']); ?>% هذا الشهر
                                    </small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Suppliers Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-suppliers me-3">
                                    <i class="fa-solid fa-truck"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">الموردين</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['suppliers'] ?? 0; ?></h2>
                                    <!-- <small class="text-success">
                                        <i class="fa-solid fa-arrow-up me-1"></i> نشط هذا الشهر
                                    </small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Row 3: Products and Services -->
            <div class="row g-4 mt-4">
                <!-- Products Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-products me-3">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">المنتجات</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['inventory'] ?? 0; ?></h2>
                                    <!-- Inventory percentage requires reorder_level, removed for now -->
                                    <!-- <div class="progress mt-2" style="height: 5px; width: 100px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php // echo $stats['inventory_percentage']; ?>%;" 
                                             aria-valuenow="<?php // echo $stats['inventory_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted"><?php // echo $stats['inventory_percentage']; ?>% من سعة المخزون</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Services Stat -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-services me-3">
                                    <i class="fa-solid fa-wrench"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">الخدمات</h6>
                                    <h2 class="mb-0 counter"><?php echo $stats['services'] ?? 0; ?></h2>
                                    <!-- <small class="text-success">
                                        <i class="fa-solid fa-arrow-up me-1"></i> نشط هذا الشهر
                                    </small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Alerts and Appointment Status Row -->
        <div class="row g-4 mt-4">
            <!-- Inventory Alerts -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>تنبيهات المخزون</h5>
                        <a href="/jj/kk/pages/inventory.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-sm">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> عرض المخزون
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($lowStockItems)): ?>
                                <?php foreach($lowStockItems as $item): ?>
                                    <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold fs-sm"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <small class="text-muted d-block">الكمية: <?php echo $item['quantity']; ?> | حد الطلب: <?php echo $item['reorder_level']; ?></small>
                                        </div>
                                        <span class="badge bg-danger rounded-pill">منخفض</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted text-center p-3">لا توجد عناصر تحتاج لإعادة طلب حاليًا.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Appointment Status -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-list-check me-2 text-info"></i>حالة المواعيد اليوم</h5>
                        <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-sm">
                            <i class="fa-solid fa-calendar-days me-1"></i> عرض المواعيد
                        </a>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                            <div class="stat-icon bg-info flex-shrink-0 me-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="fa-solid fa-spinner"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">مواعيد قيد التنفيذ</h6>
                                <h4 class="mb-0 fw-bold"><?php echo $appointmentStatusCounts['in_progress'] ?? 0; ?></h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="stat-icon bg-success flex-shrink-0 me-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">مواعيد مؤكدة لليوم</h6>
                                <h4 class="mb-0 fw-bold"><?php echo $appointmentStatusCounts['confirmed_today'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services and Calendar Row -->
        <div class="row g-4 mt-4">
            <!-- Services Stats (REMOVED) -->
            <!-- 
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-wrench me-2 text-primary"></i>الخدمات</h5>
                        <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-sm">
                            <i class="fa-solid fa-eye me-1"></i> عرض الكل
                        </a>
                    </div>
                    <div class="card-body pt-0">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div class="position-relative chart-container" style="width: 180px; height: 180px;">
                                    <div class="donut-chart">
                                        <?php /* // Donut chart display logic (commented out) 
                                        if ($totalServicesForPercentage > 0): ?>
                                        <?php if ($freePercentage > 0): ?>
                                        <div class="donut-segment" style="--segment-color: var(--bs-primary); --segment-size: <?php echo $freePercentage; ?>%"></div>
                                        <?php endif; ?>
                                        <?php if ($paidPercentage > 0): ?>
                                        <div class="donut-segment" style="--segment-color: var(--bs-warning); --segment-size: <?php echo $paidPercentage; ?>%; --segment-start: <?php echo $freePercentage; ?>%"></div>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <div class="donut-segment" style="--segment-color: #e9ecef; --segment-size: 100%"></div>
                                        <?php endif; */ ?>
                                    </div>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <h2 class="mb-0 counter fw-bold"><?php // echo $totalServices; ?></h2>
                                        <p class="text-muted small mb-0 lh-1">إجمالي الخدمات</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="p-3 service-stat-box border-end">
                                    <h4 class="text-primary mb-1 counter"><?php // echo $freeServices; ?></h4>
                                    <p class="text-muted small mb-0">
                                        <span class="legend-dot bg-primary me-1"></span>
                                        خدمات مجانية
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 service-stat-box">
                                    <h4 class="text-warning mb-1 counter"><?php // echo $paidServices; ?></h4>
                                    <p class="text-muted small mb-0">
                                        <span class="legend-dot bg-warning me-1"></span>
                                        خدمات مدفوعة
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             -->
            
            <!-- Calendar (Now takes full width potentially, or adjust col class) -->
            <div class="col-lg-12"> <!-- Changed to col-lg-12 to take full width -->
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>التقويم</h5>
                        <div>
                            <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 fs-sm">
                                <i class="fa-solid fa-plus me-1"></i> موعد جديد
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="calendar-container">
                            <div class="calendar-header d-flex justify-content-between align-items-center mb-3 px-2">
                                <!-- Add IDs for potential JS later -->
                                <button id="prev-month" class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-right"></i></button>
                                <h6 class="mb-0 fw-semibold"><?php echo $monthName . ' ' . $year; ?></h6>
                                <button id="next-month" class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-left"></i></button>
                            </div>
                            <div class="calendar-grid">
                                <div class="calendar-days-header">
                                    <div>أحد</div>
                                    <div>إثنين</div>
                                    <div>ثلاثاء</div>
                                    <div>أربعاء</div>
                                    <div>خميس</div>
                                    <div>جمعة</div>
                                    <div>سبت</div>
                                </div>
                                <div class="calendar-days">
                                    <?php 
                                    // Add blank days for padding at the start
                                    for ($i = 0; $i < $firstDayOfMonth; $i++) {
                                        echo '<div class="calendar-day blank"></div>';
                                    }

                                    // Loop through the actual days of the month
                                    for ($day = 1; $day <= $daysInMonth; $day++) {
                                        $isToday = ($day == $todayDay && $month == $todayMonth && $year == $todayYear);
                                        $currentDate = sprintf("%s-%s-%02d", $year, $month, $day);
                                        $hasEvent = in_array($currentDate, $eventDates);
                                        
                                        $dayClasses = 'calendar-day';
                                        if ($isToday) {
                                            $dayClasses .= ' today';
                                        }
                                        if ($hasEvent) {
                                            $dayClasses .= ' has-event';
                                        }

                                        echo "<div class=\"$dayClasses\">";
                                        echo "<span>$day</span>";
                                        if ($hasEvent) {
                                            // You can customize the event marker here
                                            echo '<div class="calendar-event bg-primary"></div>';
                                        }
                                        echo "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities and Customers -->
        <div class="row g-4 mt-4">
            <!-- Recent Activities (Replaced by Upcoming Appointments) -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>المواعيد القادمة</h5>
                        <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-sm">
                            <i class="fa-solid fa-calendar-days me-1"></i> عرض التقويم
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php
                            if (isset($upcomingAppointments) && is_array($upcomingAppointments) && !empty($upcomingAppointments)):
                                foreach($upcomingAppointments as $appointment):
                                    // Format date and time for display
                                    $eventDate = new DateTime($appointment['date']);
                                    $startTime = new DateTime($appointment['start_time']);
                                    $today = new DateTime('today');
                                    $tomorrow = new DateTime('tomorrow');
                                    $formattedDate = '';

                                    if ($eventDate->format('Y-m-d') === $today->format('Y-m-d')) {
                                        $formattedDate = 'اليوم';
                                    } elseif ($eventDate->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                                        $formattedDate = 'غداً';
                                    } else {
                                        // Use IntlDateFormatter for localized Arabic date format if Intl extension is enabled
                                        if (class_exists('IntlDateFormatter')) {
                                            // Short date format (e.g., ١٠ مارس ٢٠٢٥)
                                            $formatter = new IntlDateFormatter('ar_SA', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, null, null, 'd MMMM yyyy');
                                            $formattedDate = $formatter->format($eventDate);
                                        } else {
                                            // Fallback basic format
                                            $formattedDate = $eventDate->format('Y-m-d');
                                        }
                                    }

                                    // Format time (e.g., 09:00 ص)
                                    $formattedTime = '';
                                    if (class_exists('IntlDateFormatter')) {
                                        $timeFormatter = new IntlDateFormatter('ar_SA', IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
                                        $formattedTime = $timeFormatter->format($startTime);
                                    } else {
                                        $formattedTime = $startTime->format('H:i'); // Fallback 24-hour format
                                    }

                                    // Use notes as the title for now, handle if notes are empty
                                    $appointmentTitle = !empty($appointment['notes']) ? htmlspecialchars($appointment['notes']) : 'موعد (بدون تفاصيل)';
                            ?>
                            <li class="list-group-item px-4 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold fs-sm"><?php echo $appointmentTitle; ?></span>
                                    <span class="text-muted small"><?php echo $formattedDate . ' - ' . $formattedTime; ?></span>
                                </div>
                                <?php /* Optionally display Customer/Service IDs if needed for debug or reference
                                <small class="text-muted d-block">Customer ID: <?php echo $appointment['customer_id']; ?> | Service ID: <?php echo $appointment['service_id']; ?></small>
                                */ ?>
                            </li>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <li class="list-group-item text-muted text-center p-3">لا توجد مواعيد قادمة مسجلة.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Recent Customers -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <h5 class="mb-0 card-title"><i class="fa-solid fa-users me-2 text-primary"></i>آخر العملاء</h5>
                        <a href="#" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fs-sm">
                            <i class="fa-solid fa-plus me-1"></i> إضافة عميل
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php // Check if $recentCustomers exists and is not empty before looping
                                if (!empty($recentCustomers)):
                                    foreach($recentCustomers as $customer):
                                        // Combine first and last name
                                        $customerFullName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                                        // Set default image path as image column doesn't exist
                                        $customerImage = 'assets/images/default-user.png';
                            ?>
                            <li class="list-group-item px-4 py-3 d-flex align-items-center">
                                <img src="<?php echo $customerImage; ?>" alt="<?php echo htmlspecialchars($customerFullName); ?>" class="rounded-circle me-3" width="40" height="40">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-semibold fs-sm"><?php echo htmlspecialchars($customerFullName); ?></h6>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></small>
                                </div>
                                <!-- Assuming you might add status later -->
                                <!--
                                <div>
                                    <span class="badge bg-<?php // echo $customer['status'] === 'نشط' ? 'success' : 'warning'; ?> rounded-pill fs-xs">
                                        <?php // echo htmlspecialchars($customer['status']); ?>
                                    </span>
                                </div>
                                -->
                            </li>
                            <?php endforeach;
                                else: ?>
                                <li class="list-group-item text-muted text-center p-3">لا يوجد عملاء حديثون.</li>
                                <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Counter Animation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple counter animation
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.innerText);
                let count = 0;
                const duration = 1500; // ms
                const increment = target / (duration / 16); // 60fps
                
                const updateCount = () => {
                    count += increment;
                    if (count < target) {
                        counter.innerText = Math.ceil(count);
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target;
                    }
                };
                
                updateCount();
            });
        });
    </script>
</body>
</html>