<?php
// Include required files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// --- Database Connection --- START ---
$host = DB_HOST; // Use constants from config.php
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Optional: set default fetch mode
} catch (PDOException $e) {
    // Log the error instead of dying in production
    error_log("Database Connection Error: " . $e->getMessage()); 
    // Display a user-friendly message
    die("Database connection failed. Please try again later or contact support."); 
}
// --- Database Connection --- END ---

// Pagination setup
$invoicesPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $invoicesPerPage;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Base query
$baseQuery = "FROM invoices 
              LEFT JOIN customers ON invoices.customer_id = customers.id 
              WHERE 1=1";

// Add search filter if provided
if (!empty($search)) {
    $baseQuery .= " AND (
        invoices.invoice_number LIKE :search
        OR customers.first_name LIKE :search
        OR customers.last_name LIKE :search
        OR CONCAT(customers.first_name, ' ', customers.last_name) LIKE :search
    )";
}

// Add status filter if provided
if (!empty($statusFilter)) {
    $baseQuery .= " AND status = :status";
}

// Determine the sort order
$orderBy = match($sortBy) {
    'date_asc' => 'invoices.issue_date ASC',
    'amount_desc' => 'invoices.total_amount DESC',
    'amount_asc' => 'invoices.total_amount ASC',
    default => 'invoices.issue_date DESC', // date_desc is default
};

$baseQuery .= " ORDER BY " . $orderBy;

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total " . $baseQuery;
$stmt = $pdo->prepare($countQuery);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

if (!empty($statusFilter)) {
    $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
}

$stmt->execute();
$totalCount = $stmt->fetch()['total'];
$totalPages = ceil($totalCount / $invoicesPerPage);

// Get paginated invoices
$query = "SELECT invoices.*, customers.first_name as customer_first_name, customers.last_name as customer_last_name " . $baseQuery . " LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

if (!empty($statusFilter)) {
    $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $invoicesPerPage, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll();

// Get summary statistics
$statsQuery = "SELECT 
               COUNT(*) as total_invoices,
               SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
               SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
               SUM(total_amount) as total_amount
               FROM invoices";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch();

// Get recent customers for the modal
$customersQuery = "SELECT id, first_name, last_name FROM customers ORDER BY id DESC LIMIT 10";
$customersStmt = $pdo->query($customersQuery);
$customers = $customersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/invoice.css">
</head>
<body>
    <div class="container">

        <main class="content">
            <header class="top-bar">
                <div class="search-global">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search globally...">
                </div>
                <div class="top-actions">
                    <button class="notifications"><i class="fas fa-bell"></i></button>
                    <button class="messages"><i class="fas fa-envelope"></i></button>
                    <div class="user-dropdown">
                        <img src="user-avatar.jpg" alt="User">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </header>

            <div class="page-header">
                <div>
                    <h1>Invoices</h1>
                    <p>Manage your invoices and payments</p>
                </div>
                <button class="btn-primary" id="createInvoiceBtn">
                    <i class="fas fa-plus"></i> Create Invoice
                </button>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total_invoices']) ?></div>
                    <div class="stat-label">Total Invoices</div>
                    <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['paid_count']) ?></div>
                    <div class="stat-label">Paid</div>
                    <div class="stat-icon paid"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['pending_count']) ?></div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['overdue_count']) ?></div>
                    <div class="stat-label">Overdue</div>
                    <div class="stat-icon overdue"><i class="fas fa-exclamation-circle"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">$<?= number_format($stats['total_amount'], 2) ?></div>
                    <div class="stat-label">Total Amount</div>
                    <div class="stat-icon total"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>

            <div class="card filter-card">
                <form action="invoices.php" method="GET" id="filterForm">
                    <div class="filter-group">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search invoices..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Statuses</option>
                            <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="overdue" <?= $statusFilter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sort">Sort By</label>
                        <select name="sort" id="sort">
                            <option value="date_desc" <?= $sortBy === 'date_desc' ? 'selected' : '' ?>>Date (Newest)</option>
                            <option value="date_asc" <?= $sortBy === 'date_asc' ? 'selected' : '' ?>>Date (Oldest)</option>
                            <option value="amount_desc" <?= $sortBy === 'amount_desc' ? 'selected' : '' ?>>Amount (High-Low)</option>
                            <option value="amount_asc" <?= $sortBy === 'amount_asc' ? 'selected' : '' ?>>Amount (Low-High)</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="invoices.php" class="btn-reset">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                    <input type="hidden" name="per_page" value="<?= $invoicesPerPage ?>">
                    <input type="hidden" name="page" value="1">
                </form>
            </div>

            <div class="card">
                <div class="table-header">
                    <div class="table-actions">
                        <div class="selected-actions">
                            <span id="selectedCount">0</span> selected
                            <button class="btn-icon" id="bulkDeleteBtn" disabled>
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button class="btn-icon" id="bulkExportBtn" disabled>
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="per-page">
                        <label for="per-page-select">Show</label>
                        <select id="per-page-select" name="per_page">
                            <option value="10" <?= $invoicesPerPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $invoicesPerPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $invoicesPerPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $invoicesPerPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span>entries</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="checkbox-col">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($invoices) > 0): ?>
                                <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" data-id="<?= $invoice['id'] ?>">
                                    </td>
                                    <td>
                                        <a href="invoice-details.php?id=<?= $invoice['id'] ?>" class="invoice-number">
                                            <?= htmlspecialchars($invoice['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td class="customer-cell">
                                        <a href="customer-details.php?id=<?= $invoice['customer_id'] ?>" class="customer-name">
                                            <?= htmlspecialchars($invoice['customer_first_name'] . ' ' . $invoice['customer_last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($invoice['issue_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($invoice['due_date'])) ?></td>
                                    <td class="amount-cell">$<?= number_format($invoice['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $invoice['status'] ?>">
                                            <?= ucfirst($invoice['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="actions-dropdown">
                                            <button class="btn-icon dropdown-toggle">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="invoice-details.php?id=<?= $invoice['id'] ?>" class="dropdown-item">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="edit-invoice.php?id=<?= $invoice['id'] ?>" class="dropdown-item">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="#" class="dropdown-item print-invoice" data-id="<?= $invoice['id'] ?>">
                                                    <i class="fas fa-print"></i> Print
                                                </a>
                                                <a href="download-invoice.php?id=<?= $invoice['id'] ?>" class="dropdown-item">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                                <a href="#" class="dropdown-item send-invoice" data-id="<?= $invoice['id'] ?>">
                                                    <i class="fas fa-paper-plane"></i> Send
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item text-danger delete-invoice" data-id="<?= $invoice['id'] ?>">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-results">
                                        <div class="empty-state">
                                            <i class="fas fa-file-invoice"></i>
                                            <h3>No invoices found</h3>
                                            <p>Try changing your search criteria or create a new invoice</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="showing-entries">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $invoicesPerPage, $totalCount) ?> of <?= $totalCount ?> entries
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <a href="?page=1&per_page=<?= $invoicesPerPage ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-link <?= $currentPage == 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?= max(1, $currentPage - 1) ?>&per_page=<?= $invoicesPerPage ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-link <?= $currentPage == 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($startPage + 4, $totalPages);
                        
                        if ($endPage - $startPage < 4 && $startPage > 1) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?page=<?= $i ?>&per_page=<?= $invoicesPerPage ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <a href="?page=<?= min($totalPages, $currentPage + 1) ?>&per_page=<?= $invoicesPerPage ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-link <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?= $totalPages ?>&per_page=<?= $invoicesPerPage ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-link <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Invoice Modal -->
    <div class="modal" id="createInvoiceModal">
        <div class="modal-content large-modal">
            <div class="modal-header">
                <h2>Create New Invoice</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="createInvoiceForm" action="save-invoice.php" method="POST">
                    <div class="form-section">
                        <h3>Invoice Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_id">Customer</label>
                                <select id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="invoice_date">Invoice Date</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="invoice_date" name="issue_date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <h3>Invoice Items</h3>
                            <button type="button" id="addItemBtn" class="btn-secondary">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                        
                        <div class="invoice-items-table">
                            <table id="invoiceItemsTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <input type="text" name="items[0][name]" placeholder="Item name" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][description]" placeholder="Description">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="item-quantity" value="1" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][price]" class="item-price" value="0.00" step="0.01" required>
                                        </td>
                                        <td>
                                            <span class="item-total">$0.00</span>
                                            <input type="hidden" name="items[0][total]" value="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn-icon remove-item">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="invoice-summary">
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="summary-item">
                            <span>Tax (10%):</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <div class="summary-item total">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                        <input type="hidden" name="subtotal" id="subtotal_input" value="0.00">
                        <input type="hidden" name="tax_amount" id="tax_input" value="0.00">
                        <input type="hidden" name="total_amount" id="total_input" value="0.00">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelInvoiceBtn">Cancel</button>
                <button type="button" class="btn-primary" id="saveInvoiceBtn">Save Invoice</button>
            </div>
        </div>
    </div>

    <!-- Delete Invoice Modal -->
    <div class="modal" id="deleteInvoiceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Invoice</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Are you sure you want to delete this invoice? This action cannot be undone.</p>
                </div>
                <input type="hidden" id="deleteInvoiceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
    
    <script src="assets/js/invoice.js"></script>
</body>
</html>