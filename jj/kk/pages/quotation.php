<?php
// Include required files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php'; // Ensure this is included
require_once dirname(__FILE__) . '/../includes/auth.php'; // Ensure user is authenticated

// --- Database Connection --- START ---
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage()); 
    die("Database connection failed. Please try again later or contact support."); 
}
// --- Database Connection --- END ---

// --- Helper Functions --- START ---
// Removed - These functions are now in includes/functions.php
// if (!function_exists('format_date')) { ... }
// if (!function_exists('format_currency')) { ... }
// if (!function_exists('get_status_badge_class')) { ... } 
// --- Helper Functions --- END ---

// --- Pagination, Search, Filter, Sort Setup --- START ---
$quotesPerPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10; // Default 10 per page
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $quotesPerPage;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_desc'; // Default sort

$queryParams = []; // For binding values
$baseQuery = "FROM quotations q LEFT JOIN customers c ON q.customer_id = c.id WHERE 1=1";
$whereClauses = [];

// Add search filter
if (!empty($search)) {
    $whereClauses[] = "(q.quotation_number LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search OR CONCAT(c.first_name, ' ', c.last_name) LIKE :search)";
    $queryParams[':search'] = "%$search%";
}

// Add status filter
if (!empty($statusFilter)) {
    $whereClauses[] = "q.status = :status";
    $queryParams[':status'] = $statusFilter;
}

if (!empty($whereClauses)) {
    $baseQuery .= " AND " . implode(" AND ", $whereClauses);
}

// Determine the sort order
$orderBy = match($sortBy) {
    'created_asc' => 'q.created_at ASC',
    'valid_desc' => 'q.valid_until DESC',
    'valid_asc' => 'q.valid_until ASC',
    'amount_desc' => 'q.total_amount DESC',
    'amount_asc' => 'q.total_amount ASC',
    default => 'q.created_at DESC', // created_desc is default
};
$baseQuery .= " ORDER BY " . $orderBy;

// Get total count for pagination
$countQuery = "SELECT COUNT(q.id) as total " . $baseQuery;
$stmt = $pdo->prepare($countQuery);
$stmt->execute($queryParams);
$totalCount = $stmt->fetch()['total'] ?? 0;
$totalPages = ceil($totalCount / $quotesPerPage);

// Get paginated quotations
$query = "SELECT q.*, c.first_name as customer_first_name, c.last_name as customer_last_name " . $baseQuery . " LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

// Bind pagination parameters
$queryParams[':offset'] = $offset;
$queryParams[':limit'] = $quotesPerPage;

// Bind all parameters (search, status, pagination)
foreach ($queryParams as $key => $value) {
    $paramType = ($key === ':offset' || $key === ':limit') ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $paramType);
}

$stmt->execute();
$quotations = $stmt->fetchAll();

// Quotation statuses for the filter dropdown
$quotationStatuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

// --- Pagination, Search, Filter, Sort Setup --- END ---

?>

<div class="container-fluid mt-4"> 

    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Quotation Management</h1>
            <p class="text-muted">Manage your price quotations</p>
        </div>
        <a href="index.php?page=create-quotation" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Quotation
        </a>
    </div>

    <!-- Filter and Search Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="index.php" method="GET" id="filterForm">
                <input type="hidden" name="page" value="quotation"> <!-- Keep page context -->
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Quote # or Customer..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <?php foreach ($quotationStatuses as $status): ?>
                                <option value="<?= $status ?>" <?= ($statusFilter === $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="created_desc" <?= $sortBy === 'created_desc' ? 'selected' : '' ?>>Date Created (Newest)</option>
                            <option value="created_asc" <?= $sortBy === 'created_asc' ? 'selected' : '' ?>>Date Created (Oldest)</option>
                            <option value="valid_desc" <?= $sortBy === 'valid_desc' ? 'selected' : '' ?>>Valid Until (Newest)</option>
                            <option value="valid_asc" <?= $sortBy === 'valid_asc' ? 'selected' : '' ?>>Valid Until (Oldest)</option>
                            <option value="amount_desc" <?= $sortBy === 'amount_desc' ? 'selected' : '' ?>>Amount (High-Low)</option>
                            <option value="amount_asc" <?= $sortBy === 'amount_asc' ? 'selected' : '' ?>>Amount (Low-High)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-info me-2"><i class="fas fa-filter"></i> Filter</button>
                        <a href="index.php?page=quotation" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                    </div>
                </div>
                 <input type="hidden" name="per_page" id="hiddenPerPage" value="<?= $quotesPerPage ?>">
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quotations List (<?= $totalCount ?>)</h5>
            <div class="d-flex align-items-center">
                 <span class="me-2">Show</span>
                 <select id="per-page-select" class="form-select form-select-sm" style="width: auto;">
                     <option value="10" <?= $quotesPerPage == 10 ? 'selected' : '' ?>>10</option>
                     <option value="25" <?= $quotesPerPage == 25 ? 'selected' : '' ?>>25</option>
                     <option value="50" <?= $quotesPerPage == 50 ? 'selected' : '' ?>>50</option>
                     <option value="100" <?= $quotesPerPage == 100 ? 'selected' : '' ?>>100</option>
                 </select>
                 <span class="ms-2">entries</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th>Quote #</th>
                            <th>Customer</th>
                            <th>Issued</th>
                            <th>Valid Until</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quotations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No quotations found matching your criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($quotations as $quote): ?>
                                <tr id="quote-row-<?= $quote['id'] ?>">
                                    <td class="text-center"><input type="checkbox" class="form-check-input quote-checkbox" value="<?= $quote['id'] ?>"></td>
                                    <td><a href="index.php?page=quotation-details&id=<?= $quote['id'] ?>"><?= htmlspecialchars($quote['quotation_number'] ?? 'N/A') ?></a></td>
                                    <td><?= htmlspecialchars(trim(($quote['customer_first_name'] ?? '') . ' ' . ($quote['customer_last_name'] ?? ''))) ?: '<span class="text-muted">N/A</span>' ?></td>
                                    <td><?= htmlspecialchars(formatDate($quote['issue_date'])) ?></td>
                                    <td><?= htmlspecialchars(formatDate($quote['valid_until'] ?? null)) ?></td>
                                    <td class="text-end"><?= htmlspecialchars(formatCurrency($quote['total_amount'] ?? 0)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= get_quotation_status_badge_class($quote['status'] ?? 'draft') ?>">
                                            <?= htmlspecialchars(ucfirst($quote['status'] ?? 'Draft')) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?page=quotation-details&id=<?= $quote['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="View Details"><i class="fas fa-eye"></i></a>
                                        <a href="index.php?page=edit-quotation&id=<?= $quote['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger delete-quote-btn" data-id="<?= $quote['id'] ?>" data-quote-number="<?= htmlspecialchars($quote['quotation_number'] ?? '') ?>" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
             <div class="bulk-actions">
                <span id="selectedCount" class="me-2 text-muted">0 selected</span>
                <button class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn" disabled title="Delete Selected">
                    <i class="fas fa-trash-alt"></i> Delete Selected
                </button>
             </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Quotation pagination">
                <ul class="pagination pagination-sm mb-0">
                    <?php 
                    // Generate URL parameters for pagination links, preserving filters
                    $linkParams = ['page' => 'quotation'];
                    if (!empty($search)) $linkParams['search'] = $search;
                    if (!empty($statusFilter)) $linkParams['status'] = $statusFilter;
                    if (!empty($sortBy)) $linkParams['sort'] = $sortBy;
                    $linkParams['per_page'] = $quotesPerPage;
                    ?>
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($linkParams, ['page' => $currentPage - 1])) ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($linkParams, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($linkParams, ['page' => $currentPage + 1])) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <small class="text-muted">Showing <?= $offset + 1 ?> to <?= min($offset + $quotesPerPage, $totalCount) ?> of <?= $totalCount ?> quotations.</small>
        </div>
    </div>

</div>

<!-- Add Modals Here Later (e.g., for delete confirmation) -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const perPageSelect = document.getElementById('per-page-select');
    const hiddenPerPageInput = document.getElementById('hiddenPerPage');
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.quote-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deleteButtons = document.querySelectorAll('.delete-quote-btn');

    // Per Page selector change submits the form
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            hiddenPerPageInput.value = this.value;
            filterForm.submit();
        });
    }

    // Update selected count and bulk delete button state
    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.quote-checkbox:checked');
        const count = selectedCheckboxes.length;
        selectedCountSpan.textContent = count + (count === 1 ? ' selected' : ' selected');
        bulkDeleteBtn.disabled = count === 0;
    }

    // Select all checkbox functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkActions();
        });
    }

    // Individual checkbox change updates bulk actions
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            }
            updateBulkActions();
        });
    });

    // --- Delete Functionality (AJAX Placeholder) ---
    const deleteUrl = 'ajax/delete_quotation.php'; // Needs to be created

    // Individual Delete Buttons
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const quoteId = this.getAttribute('data-id');
            const quoteNumber = this.getAttribute('data-quote-number');
            
            if (confirm(`Are you sure you want to delete Quotation #${quoteNumber} (ID: ${quoteId})? This cannot be undone.`)) {
                // **AJAX Call - Needs implementation**
                fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Optional: Identify AJAX requests server-side
                    },
                    body: JSON.stringify({ ids: [quoteId] }) // Send ID in an array
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        const row = document.getElementById(`quote-row-${quoteId}`);
                        if (row) {
                            row.remove();
                        }
                        // Optional: Show success message (e.g., using a toast notification library)
                        alert('Quotation deleted successfully.'); 
                        // TODO: Update total count, pagination display if necessary
                        // Consider reloading the page or parts of it if counts/pagination change significantly
                        // window.location.reload(); // Simple but less smooth
                    } else {
                        alert('Error deleting quotation: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while trying to delete the quotation.');
                });
            }
        });
    });

    // Bulk Delete Button
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedIds = Array.from(document.querySelectorAll('.quote-checkbox:checked')).map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                alert('Please select at least one quotation to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selectedIds.length} selected quotation(s)? This cannot be undone.`)) {
                // **AJAX Call - Needs implementation**
                 fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: selectedIds }) // Send IDs in an array
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove rows from table
                        selectedIds.forEach(id => {
                            const row = document.getElementById(`quote-row-${id}`);
                            if (row) row.remove();
                        });
                        selectAllCheckbox.checked = false;
                        updateBulkActions();
                        alert('Selected quotations deleted successfully.');
                        // TODO: Update total count, pagination display
                        // window.location.reload(); // Simple option
                    } else {
                        alert('Error deleting quotations: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while trying to delete the quotations.');
                });
            }
        });
    }

    // Initial update in case checkboxes are checked on load (e.g., back button)
    updateBulkActions();
});

</script>
