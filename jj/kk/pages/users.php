<?php
// Include the original PHP code at the top
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// Add CSS links
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="assets/css/user-styles.css">';

// Initialize variables
$users = [];
$total_users = 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'all';
$items_per_page = ITEMS_PER_PAGE;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    switch ($action) {
        case 'delete':
            // Delete user
            // In real application, you would delete from database
            $_SESSION['success'] = 'User deleted successfully';
            break;
            
        case 'activate':
            // Activate user
            // In real application, you would update database
            $_SESSION['success'] = 'User activated successfully';
            break;
            
        case 'deactivate':
            // Deactivate user
            // In real application, you would update database
            $_SESSION['success'] = 'User deactivated successfully';
            break;
    }
}

// Get users data (same as the original file)
$users = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@garage.com',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role' => ROLE_ADMIN,
        'phone' => '555-123-4567',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 19:30:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'employee'
    ],
    [
        'id' => 2,
        'username' => 'manager',
        'email' => 'manager@garage.com',
        'first_name' => 'Manager',
        'last_name' => 'User',
        'role' => ROLE_MANAGER,
        'phone' => '555-234-5678',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 18:45:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'employee'
    ],
    [
        'id' => 3,
        'username' => 'mechanic1',
        'email' => 'mechanic1@garage.com',
        'first_name' => 'John',
        'last_name' => 'Smith',
        'role' => ROLE_EMPLOYEE,
        'phone' => '555-345-6789',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 17:15:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'employee'
    ],
    [
        'id' => 4,
        'username' => 'mechanic2',
        'email' => 'mechanic2@garage.com',
        'first_name' => 'Mike',
        'last_name' => 'Johnson',
        'role' => ROLE_EMPLOYEE,
        'phone' => '555-456-7890',
        'status' => STATUS_INACTIVE,
        'last_login' => '2025-03-19 16:30:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'employee'
    ],
    [
        'id' => 5,
        'username' => 'customer1',
        'email' => 'customer1@example.com',
        'first_name' => 'Sarah',
        'last_name' => 'Williams',
        'role' => ROLE_CUSTOMER,
        'phone' => '555-567-8901',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 19:00:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'customer'
    ],
    [
        'id' => 6,
        'username' => 'customer2',
        'email' => 'customer2@example.com',
        'first_name' => 'David',
        'last_name' => 'Brown',
        'role' => ROLE_CUSTOMER,
        'phone' => '555-678-9012',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 15:30:00',
        'created_at' => '2025-01-01 00:00:00',
        'type' => 'customer'
    ]
];

// Filter users based on type
if ($type !== 'all') {
    $users = array_filter($users, function($user) use ($type) {
        return $user['type'] === $type;
    });
}

// Filter users based on search criteria
if ($search || $role || $status) {
    $filtered_users = [];
    foreach ($users as $user) {
        $match = true;
        
        if ($search) {
            $search_str = strtolower($search);
            $user_str = strtolower($user['username'] . ' ' . 
                                 $user['email'] . ' ' . 
                                 $user['first_name'] . ' ' . 
                                 $user['last_name'] . ' ' . 
                                 $user['phone']);
            if (strpos($user_str, $search_str) === false) {
                $match = false;
            }
        }
        
        if ($role && $user['role'] !== $role) {
            $match = false;
        }
        
        if ($status && $user['status'] !== $status) {
            $match = false;
        }
        
        if ($match) {
            $filtered_users[] = $user;
        }
    }
    $users = $filtered_users;
}

$total_users = count($users);
$total_pages = ceil($total_users / $items_per_page);
$start_index = ($page - 1) * $items_per_page;
$users = array_slice($users, $start_index, $items_per_page);

// Get user type for page title
$page_title = 'All Users';
if ($type === 'employee') {
    $page_title = 'Employees';
} elseif ($type === 'customer') {
    $page_title = 'Customers';
}
?>

<div class="user-section fade-in">
    <div class="container py-4">
        <!-- Alert Messages (if any) -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-toast success" id="successAlert">
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-message"><?php echo $_SESSION['success']; ?></div>
                <button type="button" class="alert-close" onclick="document.getElementById('successAlert').style.display='none';">×</button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Page header with improved styling -->
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">
                    <span class="text-primary"><i class="fas fa-users me-2"></i></span><?php echo $page_title; ?>
                </h2>
                <p class="text-muted">Manage <?php echo strtolower($page_title); ?> across your system</p>
            </div>
            <a href="index.php?page=add-user" class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-plus-circle me-2"></i> Add New <?php echo $type === 'customer' ? 'Customer' : ($type === 'employee' ? 'Employee' : 'User'); ?>
            </a>
        </div>

        <!-- Tab navigation for user types -->
        <div class="tab-navigation mb-4">
            <a href="?type=all" class="tab-item <?php echo $type === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-users me-1"></i> All Users
            </a>
            <a href="?type=employee" class="tab-item <?php echo $type === 'employee' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie me-1"></i> Employees
            </a>
            <a href="?type=customer" class="tab-item <?php echo $type === 'customer' ? 'active' : ''; ?>">
                <i class="fas fa-user-tag me-1"></i> Customers
            </a>
        </div>

        <!-- User stats with improved cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Total Users</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_users; ?></h3>
                                <p class="text-muted small mb-0">Active management</p>
                            </div>
                            <div class="bg-primary-light rounded-circle p-3">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Active Users</h6>
                                <h3 class="mb-0 fw-bold counter"><?php 
                                    $active_users = array_filter($users, function($user) { 
                                        return $user['status'] === STATUS_ACTIVE; 
                                    });
                                    echo count($active_users); 
                                ?></h3>
                                <p class="text-muted small mb-0">Currently working</p>
                            </div>
                            <div class="bg-success-light rounded-circle p-3">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Inactive Users</h6>
                                <h3 class="mb-0 fw-bold counter"><?php 
                                    $inactive_users = array_filter($users, function($user) { 
                                        return $user['status'] === STATUS_INACTIVE; 
                                    });
                                    echo count($inactive_users); 
                                ?></h3>
                                <p class="text-muted small mb-0">Pending activation</p>
                            </div>
                            <div class="bg-warning-light rounded-circle p-3">
                                <i class="fas fa-user-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and filter with improved styling -->
        <div class="card border-0 shadow-sm mb-4 hover-card">
            <div class="card-header bg-white py-3 d-flex align-items-center">
                <i class="fas fa-filter text-primary me-2"></i>
                <h5 class="mb-0 text-primary">Search & Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, email, phone..." value="<?php echo $search; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="<?php echo ROLE_ADMIN; ?>" <?php echo ($role === ROLE_ADMIN) ? 'selected' : ''; ?>>Administrator</option>
                                <option value="<?php echo ROLE_MANAGER; ?>" <?php echo ($role === ROLE_MANAGER) ? 'selected' : ''; ?>>Manager</option>
                                <option value="<?php echo ROLE_EMPLOYEE; ?>" <?php echo ($role === ROLE_EMPLOYEE) ? 'selected' : ''; ?>>Employee</option>
                                <option value="<?php echo ROLE_CUSTOMER; ?>" <?php echo ($role === ROLE_CUSTOMER) ? 'selected' : ''; ?>>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="<?php echo STATUS_ACTIVE; ?>" <?php echo ($status === STATUS_ACTIVE) ? 'selected' : ''; ?>>Active</option>
                                <option value="<?php echo STATUS_INACTIVE; ?>" <?php echo ($status === STATUS_INACTIVE) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- User list with improved table styling -->
        <div class="card border-0 shadow-sm hover-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <i class="fas fa-user-friends text-primary me-2"></i>
                    <h5 class="mb-0 text-primary d-inline-block">User List</h5>
                    <span class="badge bg-primary rounded-pill ms-2"><?php echo $total_users; ?></span>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2" title="Export users to CSV">
                        <i class="fas fa-file-export me-1"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary" title="Print user list">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr class="bg-light">
                                <th width="40px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th width="120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo $user['id']; ?>">
                                            <label class="form-check-label"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-primary-light rounded-circle">
                                                <span class="avatar-text text-primary"><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h6>
                                                <small class="text-muted"><?php echo $user['email']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['phone']; ?></td>
                                    <td>
                                        <?php
                                        switch ($user['role']) {
                                            case ROLE_ADMIN:
                                                echo '<span class="badge rounded-pill bg-primary">Administrator</span>';
                                                break;
                                            case ROLE_MANAGER:
                                                echo '<span class="badge rounded-pill bg-info">Manager</span>';
                                                break;
                                            case ROLE_EMPLOYEE:
                                                echo '<span class="badge rounded-pill bg-success">Employee</span>';
                                                break;
                                            case ROLE_CUSTOMER:
                                                echo '<span class="badge rounded-pill bg-secondary">Customer</span>';
                                                break;
                                            default:
                                                echo '<span class="badge rounded-pill bg-secondary">User</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === STATUS_ACTIVE): ?>
                                            <span class="status-indicator active"></span>
                                            <span class="badge rounded-pill bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="status-indicator inactive"></span>
                                            <span class="badge rounded-pill bg-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-muted me-1 small"></i>
                                            <small class="text-muted"><?php echo date('d M Y H:i', strtotime($user['last_login'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link p-0" type="button" id="userActions<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userActions<?php echo $user['id']; ?>">
                                                <li><a class="dropdown-item" href="index.php?page=edit-user&id=<?php echo $user['id']; ?>"><i class="fas fa-edit me-2 text-info"></i>Edit</a></li>
                                                <?php if ($user['status'] === STATUS_ACTIVE): ?>
                                                    <li><a class="dropdown-item" href="#" onclick="deactivateUser(<?php echo $user['id']; ?>); return false;"><i class="fas fa-user-slash me-2 text-warning"></i>Deactivate</a></li>
                                                <?php else: ?>
                                                    <li><a class="dropdown-item" href="#" onclick="activateUser(<?php echo $user['id']; ?>); return false;"><i class="fas fa-user-check me-2 text-success"></i>Activate</a></li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#" onclick="deleteUser(<?php echo $user['id']; ?>); return false;"><i class="fas fa-trash me-2 text-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="py-5">
                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                            <h5>No users found</h5>
                                            <p class="text-muted">Try adjusting your search or filter to find what you're looking for.</p>
                                            <a href="?type=<?php echo $type; ?>" class="btn btn-outline-primary">Clear filters</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Bottom card footer with bulk actions -->
            <?php if (count($users) > 0): ?>
            <div class="card-footer bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <select class="form-select me-2" style="width: auto;">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button class="btn btn-outline-primary">Apply</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end text-muted small">
                        Showing <?php echo min($items_per_page, count($users)); ?> of <?php echo $total_users; ?> users
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination with improved styling -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($page > 1) ? '?page=' . ($page - 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&status=' . urlencode($status) . '&type=' . urlencode($type) : '#'; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php 
                // Calculate pagination range
                $start_page = max(1, min($page - 2, $total_pages - 4));
                $end_page = min($total_pages, max(5, $page + 2));
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&type=<?php echo urlencode($type); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($page < $total_pages) ? '?page=' . ($page + 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&status=' . urlencode($status) . '&type=' . urlencode($type) : '#'; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
// Handle select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('tbody .form-check-input');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});

// Helper function to create and submit form
function submitAction(action, userId) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '';
    
    var actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);
    
    var userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = userId;
    form.appendChild(userIdInput);
    
    document.body.appendChild(form);
    form.submit();
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        submitAction('delete', userId);
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        submitAction('activate', userId);
    }
}

function deactivateUser(userId) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        submitAction('deactivate', userId);
    }
}

// Animation for counter elements
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.innerText);
        let count = 0;
        const duration = 2000; // 2 seconds
        const interval = 50; // Update every 50ms
        const steps = duration / interval;
        const increment = target / steps;
        
        const timer = setInterval(() => {
            count += increment;
            if (count >= target) {
                counter.innerText = target;
                clearInterval(timer);
            } else {
                counter.innerText = Math.floor(count);
            }
        }, interval);
    });
});

// Add class to style toast messages that automatically disappear
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-toast');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>

<style>
/* Additional inline styles for components not in the main CSS */
.tab-navigation {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: 1.5rem;
    overflow-x: auto;
}

.tab-item {
    padding: 0.75rem 1.25rem;
    color: var(--gray-600);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    font-weight: 500;
    white-space: nowrap;
    transition: var(--transition);
}

.tab-item:hover {
    color: var(--primary);
    border-bottom-color: var(--gray-300);
}

.tab-item.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.alert-toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    display: flex;
    align-items: center;
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius);
    background-color: var(--white);
    box-shadow: var(--shadow-md);
    z-index: 1050;
    animation: slideIn 0.3s ease;
}

.alert-toast.success {
    border-left: 3px solid var(--success);
}

.alert-toast.error {
    border-left: 3px solid var(--danger);
}

.alert-toast.warning {
    border-left: 3px solid var(--warning);
}

.alert-icon {
    margin-right: 0.75rem;
    font-size: 1.25rem;
    color: var(--success);
}

.alert-toast.error .alert-icon {
    color: var(--danger);
}

.alert-toast.warning .alert-icon {
    color: var(--warning);
}

.alert-message {
    flex: 1;
    font-weight: 500;
}

.alert-close {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0;
    margin-left: 0.75rem;
}

.alert-close:hover {
    color: var(--gray-700);
}

.fade-out {
    animation: fadeOut 0.5s ease forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>