<?php
// Employees page with modern design
// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data

$employees = [
    [
        'id' => 1,
        'name' => 'John Smith',
        'email' => 'john.smith@garage.com',
        'phone' => '+1 (123) 456-7890',
        'position' => 'Manager',
        'department' => 'Administration',
        'join_date' => '2020-01-15',
        'salary' => 5000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 2,
        'name' => 'Mike Johnson',
        'email' => 'mike.johnson@garage.com',
        'phone' => '+1 (234) 567-8901',
        'position' => 'Senior Mechanic',
        'department' => 'Service',
        'join_date' => '2020-03-10',
        'salary' => 4000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 3,
        'name' => 'Sarah Williams',
        'email' => 'sarah.williams@garage.com',
        'phone' => '+1 (345) 678-9012',
        'position' => 'Receptionist',
        'department' => 'Front Desk',
        'join_date' => '2021-05-20',
        'salary' => 3000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 4,
        'name' => 'David Wilson',
        'email' => 'david.wilson@garage.com',
        'phone' => '+1 (456) 789-0123',
        'position' => 'Mechanic',
        'department' => 'Service',
        'join_date' => '2021-08-15',
        'salary' => 3500.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 5,
        'name' => 'Emily Brown',
        'email' => 'emily.brown@garage.com',
        'phone' => '+1 (567) 890-1234',
        'position' => 'Accountant',
        'department' => 'Finance',
        'join_date' => '2022-01-10',
        'salary' => 4200.00,
        'status' => 'inactive',
        'image' => 'assets/images/default-user.png'
    ]
];

// Pagination
$totalEmployees = count($employees);
$employeesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $employeesPerPage;
$paginatedEmployees = array_slice($employees, $startIndex, $employeesPerPage);

// Include CSS files
echo '<link rel="stylesheet" href="assets/css/sidebar.css">';
echo '<link rel="stylesheet" href="assets/css/employees.css">'; // Our new CSS file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="employees-container">
    <!-- Page header -->
    <div class="page-header">
        <div class="header-title">
            <h1>Employees</h1>
            <span class="employee-count"><?php echo $totalEmployees; ?> total</span>
        </div>
        <button class="add-btn" id="addEmployeeBtn">
            <i class="fas fa-plus"></i> Add Employee
        </button>
    </div>

    <!-- Search and filter bar -->
    <div class="search-filter-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search employees...">
        </div>
        <div class="filters">
            <div class="filter-item">
                <select id="departmentFilter">
                    <option value="">All Departments</option>
                    <option value="Administration">Administration</option>
                    <option value="Service">Service</option>
                    <option value="Front Desk">Front Desk</option>
                    <option value="Finance">Finance</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="filter-item">
                <button class="filter-btn">
                    <i class="fas fa-filter"></i> More Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Employees cards view -->
    <div class="view-toggle">
        <button class="view-btn active" data-view="card"><i class="fas fa-th"></i></button>
        <button class="view-btn" data-view="table"><i class="fas fa-list"></i></button>
    </div>

    <!-- Card View -->
    <div class="employees-grid" id="cardView">
        <?php foreach ($paginatedEmployees as $employee): ?>
        <div class="employee-card <?php echo $employee['status']; ?>">
            <div class="card-header">
                <div class="employee-status <?php echo $employee['status']; ?>">
                    <?php echo ucfirst($employee['status']); ?>
                </div>
                <div class="card-actions">
                    <button class="action-btn edit-btn" data-id="<?php echo $employee['id']; ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" data-id="<?php echo $employee['id']; ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="employee-avatar">
                    <img src="<?php echo $employee['image']; ?>" alt="<?php echo $employee['name']; ?>">
                </div>
                <div class="employee-info">
                    <h3 class="employee-name"><?php echo $employee['name']; ?></h3>
                    <p class="employee-position"><?php echo $employee['position']; ?></p>
                    <p class="employee-department"><?php echo $employee['department']; ?></p>
                </div>
                <div class="employee-contact">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo $employee['email']; ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo $employee['phone']; ?></span>
                    </div>
                </div>
                <div class="employee-details">
                    <div class="detail-item">
                        <span class="detail-label">Joined</span>
                        <span class="detail-value"><?php echo date('M d, Y', strtotime($employee['join_date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Salary</span>
                        <span class="detail-value">$<?php echo number_format($employee['salary'], 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="view-details-btn" data-id="<?php echo $employee['id']; ?>">
                    View Details
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Table View (hidden by default) -->
    <div class="employees-table" id="tableView" style="display: none;">
        <table>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Contact</th>
                    <th>Join Date</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paginatedEmployees as $employee): ?>
                <tr class="<?php echo $employee['status']; ?>">
                    <td>
                        <input type="checkbox" class="employee-checkbox" value="<?php echo $employee['id']; ?>">
                    </td>
                    <td>
                        <div class="employee-cell">
                            <img src="<?php echo $employee['image']; ?>" alt="<?php echo $employee['name']; ?>" class="employee-avatar-small">
                            <div>
                                <div class="employee-name"><?php echo $employee['name']; ?></div>
                                <div class="employee-email"><?php echo $employee['email']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo $employee['position']; ?></td>
                    <td><?php echo $employee['department']; ?></td>
                    <td><?php echo $employee['phone']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($employee['join_date'])); ?></td>
                    <td>$<?php echo number_format($employee['salary'], 2); ?></td>
                    <td>
                        <span class="status-badge <?php echo $employee['status']; ?>">
                            <?php echo ucfirst($employee['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-btn" data-id="<?php echo $employee['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit-btn" data-id="<?php echo $employee['id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" data-id="<?php echo $employee['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalEmployees > $employeesPerPage): ?>
    <div class="pagination">
        <div class="pagination-info">
            Showing <?php echo $startIndex + 1; ?> - <?php echo min($startIndex + $employeesPerPage, $totalEmployees); ?> of <?php echo $totalEmployees; ?> employees
        </div>
        <div class="pagination-controls">
            <button class="pagination-btn <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>" 
                    <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <?php for ($i = 1; $i <= ceil($totalEmployees / $employeesPerPage); $i++): ?>
            <button class="pagination-btn <?php echo ($i == $currentPage) ? 'active' : ''; ?>" 
                    data-page="<?php echo $i; ?>">
                <?php echo $i; ?>
            </button>
            <?php endfor; ?>
            
            <button class="pagination-btn <?php echo ($currentPage >= ceil($totalEmployees / $employeesPerPage)) ? 'disabled' : ''; ?>"
                    <?php echo ($currentPage >= ceil($totalEmployees / $employeesPerPage)) ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Employee Modal -->
<div class="modal" id="addEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Employee</h2>
            <button class="close-btn" id="closeAddModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addEmployeeForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Administration">Administration</option>
                            <option value="Service">Service</option>
                            <option value="Front Desk">Front Desk</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="join_date">Join Date</label>
                        <input type="date" id="join_date" name="join_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="number" id="salary" name="salary" step="0.01" required>
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
                    <label for="image">Profile Image</label>
                    <div class="file-upload">
                        <input type="file" id="image" name="image">
                        <label for="image" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose File
                        </label>
                        <span class="file-name">No file chosen</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" id="cancelAddBtn">Cancel</button>
            <button class="save-btn" id="saveEmployeeBtn">Save Employee</button>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal" id="editEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Employee</h2>
            <button class="close-btn" id="closeEditModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="editEmployeeForm">
                <input type="hidden" id="editEmployeeId" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editName">Full Name</label>
                        <input type="text" id="editName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" id="editEmail" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editPhone">Phone</label>
                        <input type="text" id="editPhone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="editPosition">Position</label>
                        <input type="text" id="editPosition" name="position" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDepartment">Department</label>
                        <select id="editDepartment" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Administration">Administration</option>
                            <option value="Service">Service</option>
                            <option value="Front Desk">Front Desk</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editJoinDate">Join Date</label>
                        <input type="date" id="editJoinDate" name="join_date" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editSalary">Salary</label>
                        <input type="number" id="editSalary" name="salary" step="0.01" required>
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
                    <label for="editImage">Profile Image</label>
                    <div class="file-upload">
                        <input type="file" id="editImage" name="image">
                        <label for="editImage" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose File
                        </label>
                        <span class="file-name">No file chosen</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" id="cancelEditBtn">Cancel</button>
            <button class="save-btn" id="updateEmployeeBtn">Update Employee</button>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal" id="deleteEmployeeModal">
    <div class="modal-content delete-modal">
        <div class="modal-header">
            <h2>Delete Employee</h2>
            <button class="close-btn" id="closeDeleteModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="delete-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p>Are you sure you want to delete this employee?</p>
            <p class="warning-text">This action cannot be undone.</p>
            <input type="hidden" id="deleteEmployeeId">
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" id="cancelDeleteBtn">Cancel</button>
            <button class="delete-btn" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Overlay for modals -->
<div class="overlay" id="overlay"></div>

<script src="assets/js/employees.js"></script>
</body>
</html>