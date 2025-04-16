<?php
// Include the original PHP code at the top
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';
require_once dirname(__FILE__) . '/../includes/database.php';

// Initialize database connection
$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed");
}

// Add CSS links
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="assets/css/user-styles.css">';

// Initialize variables
$users = [];
$pending_users = [];
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
            // Delete user from database
            $delete_sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete user';
            }
            break;
            
        case 'activate':
            // Activate user in database
            $activate_sql = "UPDATE users SET status = ? WHERE id = ?";
            $active_status = STATUS_ACTIVE;
            $stmt = $conn->prepare($activate_sql);
            $stmt->bindParam(1, $active_status, PDO::PARAM_STR);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User activated successfully';
            } else {
                $_SESSION['error'] = 'Failed to activate user';
            }
            break;
            
        case 'deactivate':
            // Deactivate user in database
            $deactivate_sql = "UPDATE users SET status = ? WHERE id = ?";
            $inactive_status = STATUS_INACTIVE;
            $stmt = $conn->prepare($deactivate_sql);
            $stmt->bindParam(1, $inactive_status, PDO::PARAM_STR);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User deactivated successfully';
            } else {
                $_SESSION['error'] = 'Failed to deactivate user';
            }
            break;
        
        // Add cases for approve/reject
        case 'approve':
            // Approve user (set status to active)
            $approve_sql = "UPDATE users SET status = ? WHERE id = ? AND status = ?";
            $active_status = 'active';
            $pending_status = 'pending_approval';
            $stmt = $conn->prepare($approve_sql);
            $stmt->bindParam(1, $active_status, PDO::PARAM_STR);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $pending_status, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User approved successfully';
                // TODO: Add notification logic here (e.g., email the user)
            } else {
                $_SESSION['error'] = 'Failed to approve user';
            }
            break;

        case 'reject':
            // Reject user (set status to rejected or delete)
            // Option 1: Set status to rejected
            $reject_sql = "UPDATE users SET status = ? WHERE id = ? AND status = ?";
            $rejected_status = 'rejected';
            $pending_status = 'pending_approval';
            $stmt = $conn->prepare($reject_sql);
            $stmt->bindParam(1, $rejected_status, PDO::PARAM_STR);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $pending_status, PDO::PARAM_STR);
            
            // Option 2: Delete the user record (uncomment if preferred)
            // $reject_sql = "DELETE FROM users WHERE id = ? AND status = ?";
            // $pending_status = 'pending_approval';
            // $stmt = $conn->prepare($reject_sql);
            // $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            // $stmt->bindParam(2, $pending_status, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'User registration rejected';
            } else {
                $_SESSION['error'] = 'Failed to reject user registration';
            }
            break;
    }
    // Redirect after POST to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit;
}

// --- Fetch Pending Approval Users --- 
$current_user_role = $_SESSION['user_role'] ?? null; 
$can_approve = ($current_user_role === 'admin' || $current_user_role === 'manager');

if ($can_approve) {
    $pending_sql = "SELECT id, username, email, first_name, last_name, role, requested_at FROM users WHERE status = ?";
    $pending_params = ['pending_approval'];

    // Admins see all pending users. Managers only see pending employees.
    if ($current_user_role === 'manager') {
        $pending_sql .= " AND role = ?";
        $pending_params[] = 'employee';
    }
    
    $pending_sql .= " ORDER BY requested_at ASC";

    try {
        $pending_stmt = $conn->prepare($pending_sql);
        $pending_stmt->execute($pending_params);
        $pending_users = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching pending users: " . $e->getMessage());
        // Handle error appropriately, maybe set a session message
        $_SESSION['error'] = "Could not fetch pending approval list.";
        $pending_users = [];
    }
}
// --- End Fetch Pending Approval Users ---

// Build SQL query based on filters
$sql = "SELECT u.*, 
        CASE 
            WHEN u.role = 'admin' OR u.role = 'employee' THEN 'employee' 
            ELSE 'customer' 
        END AS type 
        FROM users u WHERE 1=1";

// Default filter: only show active or inactive users, exclude pending/rejected unless specified
if (!isset($_GET['show_status']) || !in_array($_GET['show_status'], ['pending_approval', 'rejected', 'all'])) {
    $sql .= " AND (u.status = ? OR u.status = ?)";
    $params[] = 'active';
    $params[] = 'inactive';
} else if (isset($_GET['show_status']) && $_GET['show_status'] !== 'all') {
    // Allow showing specific statuses like pending or rejected if requested via URL
    $sql .= " AND u.status = ?";
    $params[] = $_GET['show_status'];
}
// Note: If show_status=all, no status filter is applied here.

if ($search) {
    $search_param = "%$search%";
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($role) {
    $sql .= " AND u.role = ?";
    $params[] = $role;
}

if ($type !== 'all') {
    if ($type === 'employee') {
        $sql .= " AND (u.role = 'admin' OR u.role = 'employee')";
    } else if ($type === 'customer') {
        $sql .= " AND u.role = 'customer'";
    }
}

// Count total users for pagination
$count_sql = "SELECT COUNT(*) as total FROM users u WHERE 1=1";

// Apply default status filter to count query as well
if (!isset($_GET['show_status']) || !in_array($_GET['show_status'], ['pending_approval', 'rejected', 'all'])) {
    $count_sql .= " AND (u.status = ? OR u.status = ?)";
} else if (isset($_GET['show_status']) && $_GET['show_status'] !== 'all') {
    $count_sql .= " AND u.status = ?";
}

// Add the same WHERE conditions as the main query for other filters
if ($search) {
    $count_sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
}

if ($role) {
    $count_sql .= " AND u.role = ?";
}

if ($type !== 'all') {
    if ($type === 'employee') {
        $count_sql .= " AND (u.role = 'admin' OR u.role = 'employee')";
    } else if ($type === 'customer') {
        $count_sql .= " AND u.role = 'customer'";
    }
}

$stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    foreach ($params as $index => $param) {
        $stmt->bindValue($index + 1, $param, PDO::PARAM_STR);
    }
}

$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_users = $row['total'];

// Add pagination to main query
$sql .= " ORDER BY u.id DESC LIMIT ?, ?";
$offset = ($page - 1) * $items_per_page;
$params[] = $offset;
$params[] = $items_per_page;

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $param_count = count($params);
    for ($i = 0; $i < $param_count - 2; $i++) {
        // Bind string parameters for search, role, status
        $stmt->bindValue($i + 1, $params[$i], PDO::PARAM_STR);
    }
    
    // Bind integer parameters for pagination (limit and offset)
    $stmt->bindValue($param_count - 1, $params[$param_count - 2], PDO::PARAM_INT); // offset
    $stmt->bindValue($param_count, $params[$param_count - 1], PDO::PARAM_INT); // limit
}

// Execute the statement and check for errors
if ($stmt->execute()) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Log error if execution failed
    error_log("Error executing user list query: " . implode(" | ", $stmt->errorInfo()));
    $users = []; // Ensure users is an empty array on failure
    // Optionally display a user-friendly message
    // $_SESSION['error'] = 'Could not retrieve user list due to a database error.'; 
}

// Get total counts for statistics
try {
    // Count total users (this should match $total_users from earlier query)
    $total_count_sql = "SELECT COUNT(*) as count FROM users";
    $stmt = $conn->prepare($total_count_sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_users = $row['count'];
    
    // Count active users
    $active_count_sql = "SELECT COUNT(*) as count FROM users WHERE status = ?";
    $stmt = $conn->prepare($active_count_sql);
    $active_status = STATUS_ACTIVE;
    $stmt->bindParam(1, $active_status, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_active_users = $row['count'];
    
    // Count inactive users
    $inactive_count_sql = "SELECT COUNT(*) as count FROM users WHERE status = ?";
    $stmt = $conn->prepare($inactive_count_sql);
    $inactive_status = STATUS_INACTIVE;
    $stmt->bindParam(1, $inactive_status, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_inactive_users = $row['count'];
    
    // Count users by role
    $admin_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($admin_count_sql);
    $admin_role = ROLE_ADMIN;
    $stmt->bindParam(1, $admin_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_admin_users = $row['count'];
    
    $employee_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($employee_count_sql);
    $employee_role = ROLE_EMPLOYEE;
    $stmt->bindParam(1, $employee_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_employee_users = $row['count'];
    
    $customer_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
    $stmt = $conn->prepare($customer_count_sql);
    $customer_role = ROLE_CUSTOMER;
    $stmt->bindParam(1, $customer_role, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_customer_users = $row['count'];
} catch (PDOException $e) {
    error_log("Database error when fetching statistics: " . $e->getMessage());
    // Set default values in case of database error
    $total_active_users = 0;
    $total_inactive_users = 0;
    $total_admin_users = 0;
    $total_employee_users = 0;
    $total_customer_users = 0;
}

// Calculate total pages for pagination
$total_pages = ceil($total_users / $items_per_page);

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

        <!-- Pending Approvals Section -->
        <?php if ($can_approve && !empty($pending_users)): ?>
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-header bg-warning text-dark py-3 d-flex align-items-center">
                <i class="fas fa-user-clock me-2"></i>
                <h5 class="mb-0">Pending User Approvals</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Requested</th>
                                <th width="150px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_users as $pending_user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-secondary-light rounded-circle">
                                            <span class="avatar-text text-secondary"><?php echo substr($pending_user['first_name'] ?? 'U', 0, 1) . substr($pending_user['last_name'] ?? '', 0, 1); ?></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars(($pending_user['first_name'] ?? '') . ' ' . ($pending_user['last_name'] ?? '')); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($pending_user['email'] ?? 'N/A'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($pending_user['username'] ?? 'N/A'); ?></td>
                                <td><span class="badge rounded-pill bg-info"><?php echo htmlspecialchars(ucfirst($pending_user['role'] ?? 'N/A')); ?></span></td>
                                <td><small class="text-muted"><?php echo isset($pending_user['requested_at']) ? date('Y-m-d H:i', strtotime($pending_user['requested_at'])) : 'N/A'; ?></small></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="user_id" value="<?php echo $pending_user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="user_id" value="<?php echo $pending_user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- End Pending Approvals Section -->

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
            <!-- Main stats (first row) -->
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">إجمالي المستخدمين</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_users; ?></h3>
                                <p class="text-muted small mb-0">الإدارة النشطة</p>
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
                                <h6 class="text-muted mb-1 small">المستخدمون النشطون</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_active_users; ?></h3>
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
                                <h6 class="text-muted mb-1 small">المستخدمون غير النشطين</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_inactive_users; ?></h3>
                                <p class="text-muted small mb-0">في انتظار التفعيل</p>
                            </div>
                            <div class="bg-warning-light rounded-circle p-3">
                                <i class="fas fa-user-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User breakdown by role (second row) -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1 small">المسؤولون</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_admin_users; ?></h3>
                                <p class="text-muted small mb-0">مستخدمون بصلاحيات كاملة</p>
                            </div>
                            <div class="bg-info-light rounded-circle p-3">
                                <i class="fas fa-user-shield text-info"></i>
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
                                <h6 class="text-muted mb-1 small">الموظفون</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_employee_users; ?></h3>
                                <p class="text-muted small mb-0">فريق العمل</p>
                            </div>
                            <div class="bg-primary-light rounded-circle p-3">
                                <i class="fas fa-user-tie text-primary"></i>
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
                                <h6 class="text-muted mb-1 small">العملاء</h6>
                                <h3 class="mb-0 fw-bold counter"><?php echo $total_customer_users; ?></h3>
                                <p class="text-muted small mb-0">المستخدمون المسجلون</p>
                            </div>
                            <div class="bg-secondary-light rounded-circle p-3">
                                <i class="fas fa-user-tag text-secondary"></i>
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