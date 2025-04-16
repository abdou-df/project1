<?php
// Vehicles page
// Include required files
//session_start();
//require_once '../config/config.php';
//require_once '../includes/functions.php';
//require_once '../includes/auth.php';

require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

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

// Get vehicle statistics from database
$statsQuery = "SELECT 
    COUNT(*) as total_vehicles,
    SUM(CASE WHEN v.status = 'active' THEN 1 ELSE 0 END) as active_vehicles,
    SUM(CASE WHEN v.status = 'in_service' THEN 1 ELSE 0 END) as in_service_vehicles,
    SUM(CASE WHEN v.status = 'inactive' THEN 1 ELSE 0 END) as inactive_vehicles,
    COUNT(DISTINCT v.make) as unique_makes,
    COUNT(DISTINCT v.customer_id) as unique_customers,
    AVG(YEAR(CURRENT_DATE) - v.year) as avg_age
    FROM vehicles v";
$stmt = $pdo->prepare($statsQuery);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get vehicle type filter if set
$vehicleType = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$customer = isset($_GET['customer']) ? $_GET['customer'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$make = isset($_GET['make']) ? $_GET['make'] : '';

// Build the query with filters
$query = "SELECT v.*, c.first_name, c.last_name 
          FROM vehicles v 
          LEFT JOIN customers c ON v.customer_id = c.id
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (v.make LIKE :search OR v.model LIKE :search OR v.license_plate LIKE :search OR v.vin LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($vehicleType)) {
    $query .= " AND v.fuel_type = :type";
    $params[':type'] = $vehicleType;
}

if (!empty($customer)) {
    $query .= " AND v.customer_id = :customer";
    $params[':customer'] = $customer;
}

if (!empty($status)) {
    $query .= " AND v.status = :status";
    $params[':status'] = $status;
}

if (!empty($make)) {
    $query .= " AND v.make = :make";
    $params[':make'] = $make;
}

// Get total count for pagination
$countQuery = str_replace("v.*, c.first_name, c.last_name", "COUNT(*) as total", $query);
$stmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalVehicles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pagination
$vehiclesPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $vehiclesPerPage;

// Add pagination to query
$query .= " ORDER BY v.id DESC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $vehiclesPerPage;

// Execute the query
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    if ($key == ':offset' || $key == ':limit') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique vehicle types (fuel types) for filters
$typesQuery = "SELECT DISTINCT fuel_type FROM vehicles WHERE fuel_type IS NOT NULL";
$stmt = $pdo->prepare($typesQuery);
$stmt->execute();
$vehicleTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique makes for filters
$makesQuery = "SELECT DISTINCT make FROM vehicles WHERE make IS NOT NULL";
$stmt = $pdo->prepare($makesQuery);
$stmt->execute();
$makes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get customers for filters
$customersQuery = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM customers ORDER BY full_name";
$stmt = $pdo->prepare($customersQuery);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent service history
$recentServicesQuery = "SELECT v.make, v.model, v.license_plate, s.name as service_name, a.date 
                        FROM appointments a 
                        JOIN vehicles v ON a.vehicle_id = v.id 
                        JOIN services s ON a.service_id = s.id 
                        WHERE a.status = 'completed' 
                        ORDER BY a.date DESC 
                        LIMIT 5";
$stmt = $pdo->prepare($recentServicesQuery);
$stmt->execute();
$recentServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular vehicle makes
$popularMakesQuery = "SELECT make, COUNT(*) as count 
                      FROM vehicles 
                      GROUP BY make 
                      ORDER BY count DESC 
                      LIMIT 5";
$stmt = $pdo->prepare($popularMakesQuery);
$stmt->execute();
$popularMakes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/vehicles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Vehicle Management</h1>
                <p>Manage and track all vehicles in your garage</p>
            </div>
            <button class="add-vehicle-btn" id="addVehicleBtn">
                <i class="fas fa-plus"></i> Add New Vehicle
            </button>
        </header>

        <!-- Dashboard Stats -->
        <div class="stats-dashboard">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_vehicles']; ?></h3>
                    <p>Total Vehicles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['active_vehicles']; ?></h3>
                    <p>Active Vehicles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon service-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['in_service_vehicles']; ?></h3>
                    <p>In Service</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive-icon">
                    <i class="fas fa-car-crash"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['inactive_vehicles']; ?></h3>
                    <p>Inactive Vehicles</p>
                </div>
            </div>
        </div>

        <!-- Advanced Stats -->
        <div class="advanced-stats">
            <div class="stat-card-lg">
                <h3>Vehicle Makes Distribution</h3>
                <div class="chart-container">
                    <canvas id="makesChart"></canvas>
                </div>
            </div>
            <div class="stat-card-lg">
                <h3>Recent Service History</h3>
                <div class="recent-services">
                    <?php if (empty($recentServices)): ?>
                        <p class="no-data">No recent services found</p>
                    <?php else: ?>
                        <?php foreach ($recentServices as $service): ?>
                            <div class="service-item">
                                <div class="service-vehicle">
                                    <i class="fas fa-car"></i>
                                    <span><?php echo $service['make'] . ' ' . $service['model']; ?></span>
                                    <small><?php echo $service['license_plate']; ?></small>
                                </div>
                                <div class="service-details">
                                    <span class="service-name"><?php echo $service['service_name']; ?></span>
                                    <span class="service-date"><?php echo date('M d, Y', strtotime($service['date'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-section">
            <div class="filter-header">
                <h2><i class="fas fa-filter"></i> Search & Filter</h2>
                <button id="toggleFilters" class="toggle-filters-btn">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="filter-body" id="filterBody">
                <form action="" method="GET" id="filterForm">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <div class="search-input">
                                <i class="fas fa-search"></i>
                                <input type="text" id="search" name="search" placeholder="Search by make, model, plate..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="type">Fuel Type</label>
                            <select id="type" name="type">
                                <option value="">All Types</option>
                                <?php foreach ($vehicleTypes as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($vehicleType === $type) ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="make">Make</label>
                            <select id="make" name="make">
                                <option value="">All Makes</option>
                                <?php foreach ($makes as $m): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($make === $m) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="customer">Customer</label>
                            <select id="customer" name="customer">
                                <option value="">All Customers</option>
                                <?php foreach ($customers as $cust): ?>
                                    <option value="<?php echo $cust['id']; ?>" <?php echo ($customer == $cust['id']) ? 'selected' : ''; ?>><?php echo $cust['full_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo ($status === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="in_service" <?php echo ($status === 'in_service') ? 'selected' : ''; ?>>In Service</option>
                                <option value="inactive" <?php echo ($status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="per_page">Per Page</label>
                            <select id="per_page" name="per_page">
                                <option value="10" <?php echo ($vehiclesPerPage == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo ($vehiclesPerPage == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($vehiclesPerPage == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo ($vehiclesPerPage == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="reset-btn" id="resetFilters">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vehicles List -->
        <div class="vehicles-section">
            <div class="vehicles-header">
                <h2><i class="fas fa-car-alt"></i> Vehicle List</h2>
                <div class="vehicles-actions">
                    <button class="action-btn" id="exportBtn">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                    <button class="action-btn" id="printBtn">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid" id="gridViewBtn">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list" id="listViewBtn">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="vehicles-count">
                Showing <?php echo min(($offset + 1), $totalVehicles); ?> - <?php echo min(($offset + $vehiclesPerPage), $totalVehicles); ?> of <?php echo $totalVehicles; ?> vehicles
            </div>

            <!-- Grid View (Default) -->
            <div class="vehicles-grid" id="gridView">
                <?php if (empty($vehicles)): ?>
                    <div class="no-vehicles">
                        <i class="fas fa-car-side"></i>
                        <p>No vehicles found. Try adjusting your search criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="vehicle-card" data-id="<?php echo $vehicle['id']; ?>">
                            <div class="vehicle-status <?php echo $vehicle['status']; ?>">
                                <?php echo ucfirst($vehicle['status']); ?>
                            </div>
                            <div class="vehicle-image">
                                <?php if (isset($vehicle['image']) && !empty($vehicle['image'])): ?>
                                    <img src="<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                                <?php else: ?>
                                    <img src="assets/images/vehicles/default-vehicle.jpg" alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="vehicle-info">
                                <h3><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></h3>
                                <div class="vehicle-meta">
                                    <span class="vehicle-year"><?php echo $vehicle['year']; ?></span>
                                    <span class="vehicle-type"><?php echo ucfirst($vehicle['fuel_type']); ?></span>
                                </div>
                                <div class="vehicle-details">
                                    <div class="detail-item">
                                        <i class="fas fa-hashtag"></i>
                                        <span><?php echo $vehicle['license_plate']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <span><?php echo number_format($vehicle['mileage']); ?> mi</span>
                                    </div>
                                </div>
                                <div class="vehicle-owner">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo $vehicle['first_name'] . ' ' . $vehicle['last_name']; ?></span>
                                </div>
                            </div>
                            <div class="vehicle-actions">
                                <button class="action-btn view-btn" data-id="<?php echo $vehicle['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit-btn" data-id="<?php echo $vehicle['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn service-btn" data-id="<?php echo $vehicle['id']; ?>">
                                    <i class="fas fa-tools"></i>
                                </button>
                                <button class="action-btn delete-btn" data-id="<?php echo $vehicle['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- List View (Alternative) -->
            <div class="vehicles-list" id="listView" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="selectAll">
                                    <label for="selectAll"></label>
                                </div>
                            </th>
                            <th>Vehicle</th>
                            <th>Year</th>
                            <th>License Plate</th>
                            <th>Mileage</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vehicles)): ?>
                            <tr>
                                <td colspan="8" class="no-data">No vehicles found. Try adjusting your search criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" id="vehicle<?php echo $vehicle['id']; ?>" value="<?php echo $vehicle['id']; ?>">
                                            <label for="vehicle<?php echo $vehicle['id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vehicle-cell">
                                            <?php if (isset($vehicle['image']) && !empty($vehicle['image'])): ?>
                                                <img src="<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                                            <?php else: ?>
                                                <img src="assets/images/vehicles/default-vehicle.jpg" alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></strong>
                                                <span><?php echo ucfirst($vehicle['fuel_type']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $vehicle['year']; ?></td>
                                    <td><?php echo $vehicle['license_plate']; ?></td>
                                    <td><?php echo number_format($vehicle['mileage']); ?> mi</td>
                                    <td><?php echo $vehicle['first_name'] . ' ' . $vehicle['last_name']; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $vehicle['status']; ?>">
                                            <?php echo ucfirst($vehicle['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn view-btn" data-id="<?php echo $vehicle['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit-btn" data-id="<?php echo $vehicle['id']; ?>" title="Edit Vehicle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="action-btn service-btn" data-id="<?php echo $vehicle['id']; ?>" title="Schedule Service">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                            <button class="action-btn delete-btn" data-id="<?php echo $vehicle['id']; ?>" title="Delete Vehicle">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalVehicles > $vehiclesPerPage): ?>
                <div class="pagination">
                    <button class="pagination-btn prev-btn <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>"
                        <?php if ($currentPage > 1): ?>
                        onclick="window.location.href='?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($vehicleType); ?>&customer=<?php echo urlencode($customer); ?>&status=<?php echo urlencode($status); ?>&make=<?php echo urlencode($make); ?>&per_page=<?php echo $vehiclesPerPage; ?>'"
                        <?php endif; ?>>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    
                    <div class="page-numbers">
                        <?php 
                        $totalPages = ceil($totalVehicles / $vehiclesPerPage);
                        $startPage = max(1, min($currentPage - 2, $totalPages - 4));
                        $endPage = min($totalPages, max($currentPage + 2, 5));
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($vehicleType); ?>&customer=<?php echo urlencode($customer); ?>&status=<?php echo urlencode($status); ?>&make=<?php echo urlencode($make); ?>&per_page=<?php echo $vehiclesPerPage; ?>" 
                           class="page-number <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                    </div>
                    
                    <button class="pagination-btn next-btn <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>"
                        <?php if ($currentPage < $totalPages): ?>
                        onclick="window.location.href='?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($vehicleType); ?>&customer=<?php echo urlencode($customer); ?>&status=<?php echo urlencode($status); ?>&make=<?php echo urlencode($make); ?>&per_page=<?php echo $vehiclesPerPage; ?>'"
                        <?php endif; ?>>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Vehicle</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
                <form id="deleteVehicleForm" action="process_vehicle.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="deleteVehicleId" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="submit" form="deleteVehicleForm" class="delete-confirm-btn">Delete</button>
            </div>
        </div>
    </div>
    
    <!-- Overlay backdrop for modals -->
    <div class="overlay" id="overlay"></div>

    <!-- Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/vehicles.js"></script>
</body>
</html>