<?php
// Check if user is logged in and has admin privileges
//session_start();
//require_once '../config/config.php';
//require_once '../includes/functions.php';
//require_once '../includes/auth.php';

require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// Add CSS link
echo '<link rel="stylesheet" href="assets/css/user-styles.css">';
echo '<link rel="stylesheet" href="assets/css/sidebar.css">';
echo '<link rel="stylesheet" href="assets/css/style.css">';

// Ensure user has admin privileges
/*if (!isAdmin()) {
    header('Location: index.php?page=403');
    exit;
}
*/
// Initialize variables
$users = [];
$total_users = 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
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

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
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
        'created_at' => '2025-01-01 00:00:00'
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
        'created_at' => '2025-01-01 00:00:00'
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
        'created_at' => '2025-01-01 00:00:00'
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
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 5,
        'username' => 'receptionist',
        'email' => 'receptionist@garage.com',
        'first_name' => 'Sarah',
        'last_name' => 'Williams',
        'role' => ROLE_EMPLOYEE,
        'phone' => '555-567-8901',
        'status' => STATUS_ACTIVE,
        'last_login' => '2025-03-20 19:00:00',
        'created_at' => '2025-01-01 00:00:00'
    ]
];

// Filter users based on search criteria
if ($search || $role || $status) {
    $filtered_users = [];
    foreach ($users as $user) {
        $match = true;
        
        if ($search) {
            $search_str = strtolower($search);
            $user_str = strtolower($user['username'] . ' ' . $user['email'] . ' ' . $user['first_name'] . ' ' . $user['last_name'] . ' ' . $user['phone']);
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
?>

<!-- Page header -->
<div class="user-section">
    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-dark fw-bold"><i class="fas fa-users me-2 text-primary"></i>User Management</h2>
            <a href="index.php?page=add-user" class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-plus me-2"></i> Add New User
            </a>
        </div>

        <!-- User stats -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">Total Users</h6>
                                <h4 class="mb-0 fw-bold counter"><?php echo $total_users; ?></h4>
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
                                <h4 class="mb-0 fw-bold counter"><?php echo count(array_filter($users, function($user) { return $user['status'] === STATUS_ACTIVE; })); ?></h4>
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
                                <h4 class="mb-0 fw-bold counter"><?php echo count(array_filter($users, function($user) { return $user['status'] === STATUS_INACTIVE; })); ?></h4>
                            </div>
                            <div class="bg-warning-light rounded-circle p-3">
                                <i class="fas fa-user-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search users..." value="<?php echo $search; ?>">
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
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="<?php echo STATUS_ACTIVE; ?>" <?php echo ($status === STATUS_ACTIVE) ? 'selected' : ''; ?>>Active</option>
                                <option value="<?php echo STATUS_INACTIVE; ?>" <?php echo ($status === STATUS_INACTIVE) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- User list -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-primary"><i class="fas fa-user-friends me-2"></i>User List</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-file-export me-1"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary">
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
                                            <span class="avatar-text text-primary"><?php echo substr($user['first_name'], 0, 1); ?></span>
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
                                        <span class="badge rounded-pill bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo date('d M Y H:i', strtotime($user['last_login'])); ?></small>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($page > 1) ? '?page=' . ($page - 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&status=' . urlencode($status) : '#'; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($page < $total_pages) ? '?page=' . ($page + 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&status=' . urlencode($status) : '#'; ?>" aria-label="Next">
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
document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('tbody .form-check-input');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);
        
        var userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        form.appendChild(userIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'activate';
        form.appendChild(actionInput);
        
        var userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        form.appendChild(userIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateUser(userId) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'deactivate';
        form.appendChild(actionInput);
        
        var userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        form.appendChild(userIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
