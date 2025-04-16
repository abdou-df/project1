<?php
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

// Fetch services from database
$servicesQuery = "SELECT * FROM services WHERE status = 'active'";
$stmt = $pdo->prepare($servicesQuery);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if 'category' column exists in the services table
$columnExists = false;
try {
    $columnsQuery = "SHOW COLUMNS FROM services LIKE 'category'";
    $stmt = $pdo->prepare($columnsQuery);
    $stmt->execute();
    $columnExists = ($stmt->rowCount() > 0);
} catch (PDOException $e) {
    $columnExists = false;
}

// Get service categories (only if the column exists)
$categoriesResult = [];
if ($columnExists) {
    try {
        $categoriesQuery = "SELECT DISTINCT category FROM services WHERE status = 'active'";
        $stmt = $pdo->prepare($categoriesQuery);
        $stmt->execute();
        $categoriesResult = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // If any error occurs, we'll use default categories
        $categoriesResult = [];
    }
}

// If no categories in database or column doesn't exist, create default ones
$categories = [];
if (empty($categoriesResult)) {
    $categories = ['Maintenance', 'Repair', 'Performance', 'Comfort'];
} else {
    $categories = $categoriesResult;
}

// Get service statistics
$statsQuery = "SELECT 
    COUNT(*) as total_services,
    SUM(CASE WHEN price = 0 THEN 1 ELSE 0 END) as free_services,
    SUM(CASE WHEN price > 0 THEN 1 ELSE 0 END) as paid_services,
    AVG(price) as avg_price,
    MIN(price) as min_price,
    MAX(price) as max_price,
    AVG(duration) as avg_duration
    FROM services WHERE status = 'active'";
$stmt = $pdo->prepare($statsQuery);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get most popular services (based on appointments)
try {
    $popularQuery = "SELECT s.id, s.name, COUNT(a.id) as appointment_count 
        FROM services s
        LEFT JOIN appointments a ON s.id = a.service_id
        WHERE s.status = 'active'
        GROUP BY s.id
        ORDER BY appointment_count DESC
        LIMIT 5";
    $stmt = $pdo->prepare($popularQuery);
    $stmt->execute();
    $popularServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If there's an error (like appointments table doesn't exist), use empty array
    $popularServices = [];
}

// Pagination
$servicesPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$totalServices = count($services);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $servicesPerPage;
$paginatedServices = array_slice($services, $startIndex, $servicesPerPage);

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Apply search filter if provided
if (!empty($searchTerm) || !empty($categoryFilter)) {
    $filteredServices = [];
    foreach ($services as $service) {
        $matchesSearch = empty($searchTerm) || 
            stripos($service['name'], $searchTerm) !== false || 
            stripos($service['description'], $searchTerm) !== false;
        
        // Check category only if the column exists and category filter is specified
        $matchesCategory = empty($categoryFilter) || 
            (!$columnExists) || 
            (isset($service['category']) && $service['category'] == $categoryFilter);
        
        if ($matchesSearch && $matchesCategory) {
            $filteredServices[] = $service;
        }
    }
    $totalServices = count($filteredServices);
    $paginatedServices = array_slice($filteredServices, $startIndex, $servicesPerPage);
}

// Apply sorting
if (!empty($sortBy)) {
    usort($paginatedServices, function($a, $b) use ($sortBy) {
        switch ($sortBy) {
            case 'name_asc':
                return strcmp($a['name'], $b['name']);
            case 'name_desc':
                return strcmp($b['name'], $a['name']);
            case 'price_asc':
                return $a['price'] - $b['price'];
            case 'price_desc':
                return $b['price'] - $a['price'];
            case 'duration_asc':
                return $a['duration'] - $b['duration'];
            case 'duration_desc':
                return $b['duration'] - $a['duration'];
            default:
                return 0;
        }
    });
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Services</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/services.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Our Services</h1>
                <p>Professional auto care services to keep your vehicle running smoothly</p>
            </div>
            <button class="add-service-btn" id="addServiceBtn">
                <i class="fas fa-plus"></i> Add Service
            </button>
        </header>

        <!-- Statistics Dashboard -->
        <div class="stats-dashboard">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_services']; ?></h3>
                    <p>Total Services</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['free_services']; ?></h3>
                    <p>Free Services</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['avg_price'], 2); ?></h3>
                    <p>Average Price</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo round($stats['avg_duration']); ?> min</h3>
                    <p>Average Duration</p>
                </div>
            </div>
        </div>

        <!-- Popular Services -->
        <?php if (!empty($popularServices)): ?>
        <div class="popular-services">
            <h2>Most Popular Services</h2>
            <div class="popular-services-list">
                <?php foreach ($popularServices as $service): ?>
                <div class="popular-service-item">
                    <span class="service-name"><?php echo $service['name']; ?></span>
                    <span class="appointment-count"><?php echo $service['appointment_count']; ?> appointments</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="tools-section">
            <div class="search-bar">
                <form action="" method="GET" id="searchForm">
                    <input type="text" id="searchInput" name="search" placeholder="Search services..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="filter-sort">
                <div class="filter">
                    <select id="categoryFilter" name="category" form="searchForm">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category; ?>" <?php echo ($categoryFilter == $category) ? 'selected' : ''; ?>>
                            <?php echo $category; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="sort">
                    <select id="sortBy" name="sort" form="searchForm">
                        <option value="name_asc" <?php echo ($sortBy == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo ($sortBy == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo ($sortBy == 'price_asc') ? 'selected' : ''; ?>>Price (Low-High)</option>
                        <option value="price_desc" <?php echo ($sortBy == 'price_desc') ? 'selected' : ''; ?>>Price (High-Low)</option>
                        <option value="duration_asc" <?php echo ($sortBy == 'duration_asc') ? 'selected' : ''; ?>>Duration (Short-Long)</option>
                        <option value="duration_desc" <?php echo ($sortBy == 'duration_desc') ? 'selected' : ''; ?>>Duration (Long-Short)</option>
                    </select>
                </div>
                
                <div class="records-display">
                    <select id="recordsPerPage" name="per_page" form="searchForm">
                        <option value="10" <?php echo ($servicesPerPage == 10) ? 'selected' : ''; ?>>10 / page</option>
                        <option value="25" <?php echo ($servicesPerPage == 25) ? 'selected' : ''; ?>>25 / page</option>
                        <option value="50" <?php echo ($servicesPerPage == 50) ? 'selected' : ''; ?>>50 / page</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="service-counter">
            Showing <?php echo ($totalServices > 0) ? $startIndex + 1 : 0; ?> - <?php echo min($startIndex + $servicesPerPage, $totalServices); ?> of <?php echo $totalServices; ?> services
        </div>

        <div class="services-grid">
            <?php if (empty($paginatedServices)): ?>
                <div class="no-services">
                    <i class="fas fa-search"></i>
                    <p>No services found. Try adjusting your search criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($paginatedServices as $service): ?>
                <div class="service-card" data-category="<?php echo isset($service['category']) ? $service['category'] : 'Uncategorized'; ?>" data-id="<?php echo $service['id']; ?>">
                    <div class="service-image">
                        <?php if (isset($service['image']) && !empty($service['image'])): ?>
                            <img src="<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>">
                        <?php else: ?>
                            <img src="assets/images/services/default-service.jpg" alt="<?php echo $service['name']; ?>">
                        <?php endif; ?>
                        <div class="service-price">
                            <?php if ($service['price'] > 0): ?>
                                <span>$<?php echo number_format($service['price'], 2); ?></span>
                            <?php else: ?>
                                <span class="free-badge">Free</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($columnExists): ?>
                        <div class="service-category">
                            <span><?php echo isset($service['category']) ? $service['category'] : 'Uncategorized'; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="service-info">
                        <h3><?php echo $service['name']; ?></h3>
                        <p><?php echo $service['description']; ?></p>
                        <div class="service-meta">
                            <span class="service-duration">
                                <i class="far fa-clock"></i> <?php echo $service['duration']; ?> min
                            </span>
                        </div>
                        <div class="service-actions">
                            <button class="edit-btn" data-id="<?php echo $service['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-btn" data-id="<?php echo $service['id']; ?>">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalServices > $servicesPerPage): ?>
        <div class="pagination">
            <button class="pagination-btn prev-btn <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>"
                <?php if ($currentPage > 1): ?>
                onclick="window.location.href='?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&sort=<?php echo $sortBy; ?>&per_page=<?php echo $servicesPerPage; ?>'"
                <?php endif; ?>>
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            
            <div class="page-numbers">
                <?php for ($i = 1; $i <= ceil($totalServices / $servicesPerPage); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&sort=<?php echo $sortBy; ?>&per_page=<?php echo $servicesPerPage; ?>" 
                   class="page-number <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            
            <button class="pagination-btn next-btn <?php echo ($currentPage >= ceil($totalServices / $servicesPerPage)) ? 'disabled' : ''; ?>"
                <?php if ($currentPage < ceil($totalServices / $servicesPerPage)): ?>
                onclick="window.location.href='?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&sort=<?php echo $sortBy; ?>&per_page=<?php echo $servicesPerPage; ?>'"
                <?php endif; ?>>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modals -->
    <!-- Add Service Modal -->
    <div class="modal" id="addServiceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Service</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addServiceForm" action="process_service.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Service Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <?php if ($columnExists): ?>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="duration">Duration (minutes)</label>
                            <input type="number" id="duration" name="duration" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Service Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="submit" form="addServiceForm" class="save-btn">Save Service</button>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal" id="editServiceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Service</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm" action="process_service.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="editServiceId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editName">Service Name</label>
                            <input type="text" id="editName" name="name" required>
                        </div>
                        <?php if ($columnExists): ?>
                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <select id="editCategory" name="category" required>
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editPrice">Price ($)</label>
                            <input type="number" id="editPrice" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editDuration">Duration (minutes)</label>
                            <input type="number" id="editDuration" name="duration" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editImage">Service Image</label>
                        <input type="file" id="editImage" name="image" accept="image/*">
                        <small>Leave empty to keep current image</small>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="submit" form="editServiceForm" class="save-btn">Update Service</button>
            </div>
        </div>
    </div>

    <!-- Delete Service Modal -->
    <div class="modal" id="deleteServiceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Service</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this service? This action cannot be undone.</p>
                <form id="deleteServiceForm" action="process_service.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="deleteServiceId" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="submit" form="deleteServiceForm" class="delete-confirm-btn">Delete</button>
            </div>
        </div>
    </div>
    
    <!-- Overlay backdrop for modals -->
    <div class="overlay" id="overlay"></div>

    <!-- JavaScript -->
    <script src="assets/js/services.js"></script>
</body>
</html>