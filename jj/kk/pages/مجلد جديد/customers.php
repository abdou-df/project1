<?php
// Customers page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$customers = [
    [
        'id' => 1,
        'name' => 'Bendial Joseph',
        'email' => 'bendial.joseph@gmail.com',
        'phone' => '+1 (123) 456-7890',
        'address' => '123 Main St, New York, NY 10001',
        'vehicles' => 3,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-01-15'
    ],
    [
        'id' => 2,
        'name' => 'Peter Parker',
        'email' => 'peter.parker@gmail.com',
        'phone' => '+1 (234) 567-8901',
        'address' => '456 Park Ave, New York, NY 10002',
        'vehicles' => 1,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-02-20'
    ],
    [
        'id' => 3,
        'name' => 'Regina Cooper',
        'email' => 'regina.cooper@gmail.com',
        'phone' => '+1 (345) 678-9012',
        'address' => '789 Broadway, New York, NY 10003',
        'vehicles' => 2,
        'status' => 'inactive',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-03-10'
    ],
    [
        'id' => 4,
        'name' => 'John Smith',
        'email' => 'john.smith@gmail.com',
        'phone' => '+1 (456) 789-0123',
        'address' => '101 5th Ave, New York, NY 10004',
        'vehicles' => 1,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-04-05'
    ],
    [
        'id' => 5,
        'name' => 'Jane Doe',
        'email' => 'jane.doe@gmail.com',
        'phone' => '+1 (567) 890-1234',
        'address' => '202 6th Ave, New York, NY 10005',
        'vehicles' => 2,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-05-15'
    ]
];

// Pagination
$totalCustomers = count($customers);
$customersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $customersPerPage;
$paginatedCustomers = array_slice($customers, $startIndex, $customersPerPage);

// Filter by status if provided
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
if ($statusFilter !== 'all') {
    $filteredCustomers = array_filter($customers, function($customer) use ($statusFilter) {
        return $customer['status'] === $statusFilter;
    });
    $paginatedCustomers = array_slice($filteredCustomers, $startIndex, $customersPerPage);
    $totalCustomers = count($filteredCustomers);
}

// Search functionality
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchQuery)) {
    $searchResults = array_filter($customers, function($customer) use ($searchQuery) {
        return (
            stripos($customer['name'], $searchQuery) !== false ||
            stripos($customer['email'], $searchQuery) !== false ||
            stripos($customer['phone'], $searchQuery) !== false ||
            stripos($customer['address'], $searchQuery) !== false
        );
    });
    $paginatedCustomers = array_slice($searchResults, $startIndex, $customersPerPage);
    $totalCustomers = count($searchResults);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <link rel="stylesheet" href="assets/css/customers.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
</head>
<body>
    
 
        <!-- <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-car-side"></i>
                <span>AutoManager</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="active"><a href="#"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="#"><i class="fas fa-car"></i> Vehicles</a></li>
                    <li><a href="#"><i class="fas fa-wrench"></i> Services</a></li>
                    <li><a href="#"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
             -->
        
        <!-- Main conte  nt area -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-search">
                    <form action="" method="GET">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <?php if(!empty($searchQuery)): ?>
                                <a href="customers.php" class="clear-search"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <button type="submit">Search</button>
                        </div>
                    </form>
                </div>
                <div class="header-actions">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="user-profile">
                        <img src="assets/images/default-user.png" alt="Admin">
                        <span>Admin User</span>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <div class="page-content">
                <!-- Page header with stats -->
                <div class="page-header">
                    <div class="title-section">
                        <h1>Customers</h1>
                        <p>Manage your customer database</p>
                    </div>
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-details">
                                <h3><?php echo $totalCustomers; ?></h3>
                                <p>Total Customers</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon active"><i class="fas fa-user-check"></i></div>
                            <div class="stat-details">
                                <h3><?php 
                                    echo count(array_filter($customers, function($c) { 
                                        return $c['status'] === 'active'; 
                                    })); 
                                ?></h3>
                                <p>Active Customers</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon inactive"><i class="fas fa-user-times"></i></div>
                            <div class="stat-details">
                                <h3><?php 
                                    echo count(array_filter($customers, function($c) { 
                                        return $c['status'] === 'inactive'; 
                                    })); 
                                ?></h3>
                                <p>Inactive Customers</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action bar -->
                <div class="action-bar">
                    <div class="filter-controls">
                        <div class="filter-dropdown">
                            <label for="status-filter">Filter by status:</label>
                            <select id="status-filter" onchange="window.location = this.value;">
                                <option value="customers.php" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="customers.php?status=active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                                <option value="customers.php?status=inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                            </select>
                        </div>
                        <div class="filter-dropdown">
                            <label for="records-per-page">Show:</label>
                            <select id="records-per-page">
                                <option value="10" selected>10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50">50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn-export"><i class="fas fa-file-export"></i> Export</button>
                        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus"></i> Add Customer</button>
                    </div>
                </div>

                <!-- Customer table -->
                <div class="customer-table-container">
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th class="checkbox-column">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>Vehicles</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedCustomers)): ?>
                                <tr>
                                    <td colspan="8" class="no-results">No customers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paginatedCustomers as $customer): ?>
                                <tr>
                                    <td class="checkbox-column">
                                        <input type="checkbox" value="<?php echo $customer['id']; ?>">
                                    </td>
                                    <td class="customer-info">
                                        <div class="customer-avatar">
                                            <img src="<?php echo $customer['image']; ?>" alt="<?php echo $customer['name']; ?>">
                                        </div>
                                        <div class="customer-details">
                                            <h4><?php echo $customer['name']; ?></h4>
                                            <p><?php echo $customer['email']; ?></p>
                                        </div>
                                    </td>
                                    <td class="contact-info">
                                        <span class="phone"><?php echo $customer['phone']; ?></span>
                                    </td>
                                    <td class="address-info">
                                        <span class="address"><?php echo $customer['address']; ?></span>
                                    </td>
                                    <td class="vehicles-info">
                                        <span class="vehicle-badge"><?php echo $customer['vehicles']; ?></span>
                                    </td>
                                    <td class="status-info">
                                        <span class="status-badge <?php echo $customer['status']; ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                    </td>
                                    <td class="date-info">
                                        <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                    </td>
                                    <td class="actions">
                                        <div class="action-buttons">
                                            <button class="action-btn view" title="View Details" onclick="window.location='index.php?page=customer-details&id=<?php echo $customer['id']; ?>'">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" title="Edit Customer" data-id="<?php echo $customer['id']; ?>" onclick="openEditModal(<?php echo $customer['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="action-btn delete" title="Delete Customer" data-id="<?php echo $customer['id']; ?>" onclick="openDeleteModal(<?php echo $customer['id']; ?>)">
                                                <i class="fas fa-trash-alt"></i>
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
                <?php if ($totalCustomers > $customersPerPage): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <?php echo $startIndex + 1; ?> to <?php echo min($startIndex + count($paginatedCustomers), $totalCustomers); ?> of <?php echo $totalCustomers; ?> entries
                    </div>
                    <div class="pagination">
                        <a href="?page=<?php echo max(1, $currentPage - 1); ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . $statusFilter : ''; ?>" class="page-btn <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min(ceil($totalCustomers / $customersPerPage), $startPage + 4);
                        $startPage = max(1, $endPage - 4);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . $statusFilter : ''; ?>" class="page-btn <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <a href="?page=<?php echo min(ceil($totalCustomers / $customersPerPage), $currentPage + 1); ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . $statusFilter : ''; ?>" class="page-btn <?php echo ($currentPage >= ceil($totalCustomers / $customersPerPage)) ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal" id="addCustomerModal">
        <div class="modal-backdrop" onclick="closeModal('addCustomerModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Customer</h2>
                <button class="close-btn" onclick="closeModal('addCustomerModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm" class="customer-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Profile Image</label>
                        <div class="file-input-container">
                            <input type="file" id="image" name="image" class="file-input">
                            <div class="file-input-button">
                                <i class="fas fa-cloud-upload-alt"></i> Choose File
                            </div>
                            <span class="file-name">No file chosen</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeModal('addCustomerModal')">Cancel</button>
                <button class="save-btn" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal" id="editCustomerModal">
        <div class="modal-backdrop" onclick="closeModal('editCustomerModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <button class="close-btn" onclick="closeModal('editCustomerModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm" class="customer-form">
                    <input type="hidden" id="editCustomerId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editName">Full Name</label>
                            <input type="text" id="editName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="editEmail">Email Address</label>
                            <input type="email" id="editEmail" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editPhone">Phone Number</label>
                            <input type="text" id="editPhone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="editStatus">Status</label>
                            <select id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <textarea id="editAddress" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editImage">Profile Image</label>
                        <div class="file-input-container">
                            <input type="file" id="editImage" name="image" class="file-input">
                            <div class="file-input-button">
                                <i class="fas fa-cloud-upload-alt"></i> Choose File
                            </div>
                            <span class="file-name">Current image</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeModal('editCustomerModal')">Cancel</button>
                <button class="save-btn" id="updateCustomerBtn">Update Customer</button>
            </div>
        </div>
    </div>

    <!-- Delete Customer Modal -->
    <div class="modal" id="deleteCustomerModal">
        <div class="modal-backdrop" onclick="closeModal('deleteCustomerModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Customer</h2>
                <button class="close-btn" onclick="closeModal('deleteCustomerModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="delete-confirmation">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
                    <input type="hidden" id="deleteCustomerId">
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeModal('deleteCustomerModal')">Cancel</button>
                <button class="delete-btn" id="confirmDeleteBtn">Delete Customer</button>
            </div>
        </div>
    </div>

    <script src="assets/js/customers.js"></script>
</body>
</html>