<?php
// Invoices page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$invoices = [
    [
        'id' => 1,
        'invoice_no' => 'INV-2023-001',
        'customer' => 'Bendial Joseph',
        'customer_id' => 1,
        'date' => '2023-03-15',
        'due_date' => '2023-04-15',
        'amount' => 120.00,
        'tax' => 12.00,
        'total' => 132.00,
        'status' => 'paid',
        'payment_method' => 'Credit Card',
        'payment_date' => '2023-03-20'
    ],
    [
        'id' => 2,
        'invoice_no' => 'INV-2023-002',
        'customer' => 'Peter Parker',
        'customer_id' => 2,
        'date' => '2023-02-20',
        'due_date' => '2023-03-20',
        'amount' => 350.00,
        'tax' => 35.00,
        'total' => 385.00,
        'status' => 'pending',
        'payment_method' => '',
        'payment_date' => ''
    ],
    [
        'id' => 3,
        'invoice_no' => 'INV-2023-003',
        'customer' => 'Regina Cooper',
        'customer_id' => 3,
        'date' => '2023-01-10',
        'due_date' => '2023-02-10',
        'amount' => 80.00,
        'tax' => 8.00,
        'total' => 88.00,
        'status' => 'paid',
        'payment_method' => 'Bank Transfer',
        'payment_date' => '2023-01-15'
    ],
    [
        'id' => 4,
        'invoice_no' => 'INV-2023-004',
        'customer' => 'John Smith',
        'customer_id' => 4,
        'date' => '2023-03-05',
        'due_date' => '2023-04-05',
        'amount' => 200.00,
        'tax' => 20.00,
        'total' => 220.00,
        'status' => 'overdue',
        'payment_method' => '',
        'payment_date' => ''
    ],
    [
        'id' => 5,
        'invoice_no' => 'INV-2023-005',
        'customer' => 'Jane Doe',
        'customer_id' => 5,
        'date' => '2023-03-25',
        'due_date' => '2023-04-25',
        'amount' => 150.00,
        'tax' => 15.00,
        'total' => 165.00,
        'status' => 'pending',
        'payment_method' => '',
        'payment_date' => ''
    ]
];

// Pagination
$totalInvoices = count($invoices);
$invoicesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $invoicesPerPage;
$paginatedInvoices = array_slice($invoices, $startIndex, $invoicesPerPage);
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Invoices <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addInvoiceModal"><i class="fas fa-plus"></i></button></h2>
    <div class="d-flex">
        <div class="dropdown me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="recordsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                10
            </button>
            <ul class="dropdown-menu" aria-labelledby="recordsDropdown">
                <li><a class="dropdown-item" href="#">10</a></li>
                <li><a class="dropdown-item" href="#">25</a></li>
                <li><a class="dropdown-item" href="#">50</a></li>
                <li><a class="dropdown-item" href="#">100</a></li>
            </ul>
        </div>
        <div class="text-muted mt-2">
            Showing 1 - <?php echo min($invoicesPerPage, $totalInvoices); ?> of <?php echo $totalInvoices; ?>
        </div>
    </div>
</div>

<!-- Search and filter bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search invoices..." aria-label="Search">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Sort by">
                    <option value="date_desc">Date (Newest)</option>
                    <option value="date_asc">Date (Oldest)</option>
                    <option value="amount_desc">Amount (High-Low)</option>
                    <option value="amount_asc">Amount (Low-High)</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Invoices table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>INVOICE #</th>
                        <th>CUSTOMER</th>
                        <th>DATE</th>
                        <th>DUE DATE</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedInvoices as $invoice): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $invoice['id']; ?>">
                            </div>
                        </td>
                        <td>
                            <a href="index.php?page=invoice-details&id=<?php echo $invoice['id']; ?>" class="text-primary fw-bold">
                                <?php echo $invoice['invoice_no']; ?>
                            </a>
                        </td>
                        <td>
                            <a href="index.php?page=customer-details&id=<?php echo $invoice['customer_id']; ?>">
                                <?php echo $invoice['customer']; ?>
                            </a>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($invoice['date'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($invoice['due_date'])); ?></td>
                        <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                        <td>
                            <?php if ($invoice['status'] === 'paid'): ?>
                            <span class="badge bg-success">Paid</span>
                            <?php elseif ($invoice['status'] === 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" type="button" id="dropdownMenuButton<?php echo $invoice['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $invoice['id']; ?>">
                                    <li><a class="dropdown-item" href="index.php?page=invoice-details&id=<?php echo $invoice['id']; ?>"><i class="fas fa-eye me-2"></i> View</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editInvoiceModal" data-id="<?php echo $invoice['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i> Download</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-paper-plane me-2"></i> Send</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal" data-id="<?php echo $invoice['id']; ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
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
<?php if ($totalInvoices > $invoicesPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= ceil($totalInvoices / $invoicesPerPage); $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= ceil($totalInvoices / $invoicesPerPage)) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < ceil($totalInvoices / $invoicesPerPage)) ? '?page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-labelledby="addInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInvoiceModalLabel">Create New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addInvoiceForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer" class="form-label">Customer</label>
                            <select class="form-select" id="customer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <option value="1">Bendial Joseph</option>
                                <option value="2">Peter Parker</option>
                                <option value="3">Regina Cooper</option>
                                <option value="4">John Smith</option>
                                <option value="5">Jane Doe</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="invoiceDate" class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" id="invoiceDate" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="due_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Invoice Items</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="invoiceItemsTable">
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
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" name="items[0][name]" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[0][description]">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="items[0][quantity]" value="1" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-price" name="items[0][price]" value="0.00" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-total" name="items[0][total]" value="0.00" step="0.01" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <button type="button" class="btn btn-sm btn-success" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (10%):</span>
                                        <span id="tax">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="total">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveInvoiceBtn">Save Invoice</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Invoice Modal -->
<div class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInvoiceModalLabel">Delete Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this invoice? This action cannot be undone.</p>
                <input type="hidden" id="deleteInvoiceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
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
    
    // Handle delete invoice modal
    document.querySelectorAll('[data-bs-target="#deleteInvoiceModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var invoiceId = this.getAttribute('data-id');
            document.getElementById('deleteInvoiceId').value = invoiceId;
        });
    });
    
    // Handle invoice item calculations
    function updateInvoiceTotals() {
        var subtotal = 0;
        
        // Calculate each line item total and subtotal
        document.querySelectorAll('.item-quantity, .item-price').forEach(function(input) {
            var row = input.closest('tr');
            var quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            var price = parseFloat(row.querySelector('.item-price').value) || 0;
            var total = quantity * price;
            
            row.querySelector('.item-total').value = total.toFixed(2);
            subtotal += total;
        });
        
        // Calculate tax and total
        var tax = subtotal * 0.1; // 10% tax
        var total = subtotal + tax;
        
        // Update the display
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = '$' + tax.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }
    
    // Add event listeners to quantity and price inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
            updateInvoiceTotals();
        }
    });
    
    // Add new item row
    document.getElementById('addItemBtn').addEventListener('click', function() {
        var tbody = document.querySelector('#invoiceItemsTable tbody');
        var rowCount = tbody.children.length;
        var newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control" name="items[${rowCount}][name]" required>
            </td>
            <td>
                <input type="text" class="form-control" name="items[${rowCount}][description]">
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${rowCount}][quantity]" value="1" min="1" required>
            </td>
            <td>
                <input type="number" class="form-control item-price" name="items[${rowCount}][price]" value="0.00" step="0.01" required>
            </td>
            <td>
                <input type="number" class="form-control item-total" name="items[${rowCount}][total]" value="0.00" step="0.01" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        updateInvoiceTotals();
    });
    
    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.parentElement.classList.contains('remove-item')) {
            var button = e.target.classList.contains('remove-item') ? e.target : e.target.parentElement;
            var row = button.closest('tr');
            
            // Only remove if there's more than one row
            if (document.querySelectorAll('#invoiceItemsTable tbody tr').length > 1) {
                row.remove();
                updateInvoiceTotals();
            }
        }
    });
    
    // Handle save invoice button
    document.getElementById('saveInvoiceBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addInvoiceModal'));
        modal.hide();
        
        // Show success message
        alert('Invoice created successfully!');
    });
    
    // Handle confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // In a real application, you would submit the delete request to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteInvoiceModal'));
        modal.hide();
        
        // Show success message
        alert('Invoice deleted successfully!');
    });
    
    // Initialize calculations
    updateInvoiceTotals();
</script>
