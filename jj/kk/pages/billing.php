<?php
// Billing page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$invoices = [
    [
        'id' => 1,
        'invoice_number' => 'INV-2023-001',
        'customer_name' => 'John Smith',
        'vehicle' => 'Toyota Camry (2019)',
        'date' => '2023-03-10',
        'due_date' => '2023-03-25',
        'amount' => 149.99,
        'status' => 'Paid',
        'payment_method' => 'Credit Card',
        'payment_date' => '2023-03-10'
    ],
    [
        'id' => 2,
        'invoice_number' => 'INV-2023-002',
        'customer_name' => 'Sarah Williams',
        'vehicle' => 'Honda Accord (2020)',
        'date' => '2023-03-12',
        'due_date' => '2023-03-27',
        'amount' => 289.50,
        'status' => 'Paid',
        'payment_method' => 'Debit Card',
        'payment_date' => '2023-03-15'
    ],
    [
        'id' => 3,
        'invoice_number' => 'INV-2023-003',
        'customer_name' => 'Michael Brown',
        'vehicle' => 'Ford F-150 (2018)',
        'date' => '2023-03-15',
        'due_date' => '2023-03-30',
        'amount' => 425.75,
        'status' => 'Unpaid',
        'payment_method' => null,
        'payment_date' => null
    ],
    [
        'id' => 4,
        'invoice_number' => 'INV-2023-004',
        'customer_name' => 'Emily Davis',
        'vehicle' => 'Nissan Altima (2021)',
        'date' => '2023-03-18',
        'due_date' => '2023-04-02',
        'amount' => 189.99,
        'status' => 'Unpaid',
        'payment_method' => null,
        'payment_date' => null
    ],
    [
        'id' => 5,
        'invoice_number' => 'INV-2023-005',
        'customer_name' => 'Robert Johnson',
        'vehicle' => 'Chevrolet Malibu (2017)',
        'date' => '2023-03-20',
        'due_date' => '2023-04-04',
        'amount' => 349.95,
        'status' => 'Partial',
        'payment_method' => 'Cash',
        'payment_date' => '2023-03-20'
    ],
    [
        'id' => 6,
        'invoice_number' => 'INV-2023-006',
        'customer_name' => 'Jennifer Wilson',
        'vehicle' => 'Hyundai Sonata (2020)',
        'date' => '2023-03-22',
        'due_date' => '2023-04-06',
        'amount' => 129.99,
        'status' => 'Unpaid',
        'payment_method' => null,
        'payment_date' => null
    ]
];

// Pagination
$totalItems = count($invoices);
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $itemsPerPage;
$paginatedItems = array_slice($invoices, $startIndex, $itemsPerPage);

// Calculate totals
$totalPaid = 0;
$totalUnpaid = 0;
$totalOverdue = 0;

foreach ($invoices as $invoice) {
    if ($invoice['status'] === 'Paid') {
        $totalPaid += $invoice['amount'];
    } else {
        $totalUnpaid += $invoice['amount'];
        
        // Check if overdue
        if (strtotime($invoice['due_date']) < time()) {
            $totalOverdue += $invoice['amount'];
        }
    }
}
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Billing & Invoices</h2>
    <a href="index.php?page=create-invoice" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Create Invoice
    </a>
</div>

<!-- Billing stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Invoices</h6>
                        <h4 class="mb-0"><?php echo $totalItems; ?></h4>
                    </div>
                    <div class="bg-light-primary rounded-circle p-2">
                        <i class="fas fa-file-invoice text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Paid</h6>
                        <h4 class="mb-0">$<?php echo number_format($totalPaid, 2); ?></h4>
                    </div>
                    <div class="bg-light-success rounded-circle p-2">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Unpaid</h6>
                        <h4 class="mb-0">$<?php echo number_format($totalUnpaid, 2); ?></h4>
                    </div>
                    <div class="bg-light-warning rounded-circle p-2">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Overdue</h6>
                        <h4 class="mb-0">$<?php echo number_format($totalOverdue, 2); ?></h4>
                    </div>
                    <div class="bg-light-danger rounded-circle p-2">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and filter bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search invoices..." aria-label="Search">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" placeholder="Date From">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" placeholder="Date To">
            </div>
            <div class="col-md-2">
                <select class="form-select" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <button class="btn btn-outline-primary" type="button"><i class="fas fa-file-export me-2"></i> Export</button>
                </div>
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
                        <th>VEHICLE</th>
                        <th>DATE</th>
                        <th>DUE DATE</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedItems as $invoice): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $invoice['id']; ?>">
                            </div>
                        </td>
                        <td>
                            <a href="index.php?page=invoice-details&id=<?php echo $invoice['id']; ?>" class="text-primary fw-bold">
                                <?php echo $invoice['invoice_number']; ?>
                            </a>
                        </td>
                        <td>
                            <a href="index.php?page=customer-details&id=1">
                                <?php echo $invoice['customer_name']; ?>
                            </a>
                        </td>
                        <td>
                            <a href="index.php?page=vehicle-details&id=1">
                                <?php echo $invoice['vehicle']; ?>
                            </a>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($invoice['date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                        <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                        <td>
                            <?php if ($invoice['status'] === 'Paid'): ?>
                            <span class="badge bg-success">Paid</span>
                            <?php elseif ($invoice['status'] === 'Partial'): ?>
                            <span class="badge bg-warning">Partial</span>
                            <?php elseif (strtotime($invoice['due_date']) < time()): ?>
                            <span class="badge bg-danger">Overdue</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" type="button" id="dropdownMenuButton<?php echo $invoice['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $invoice['id']; ?>">
                                    <li><a class="dropdown-item" href="index.php?page=invoice-details&id=<?php echo $invoice['id']; ?>"><i class="fas fa-eye me-2"></i> View</a></li>
                                    <li><a class="dropdown-item" href="index.php?page=create-invoice&id=<?php echo $invoice['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#recordPaymentModal" data-id="<?php echo $invoice['id']; ?>"><i class="fas fa-money-bill-wave me-2"></i> Record Payment</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i> Email</a></li>
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
<?php if ($totalItems > $itemsPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= ceil($totalItems / $itemsPerPage); $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= ceil($totalItems / $itemsPerPage)) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < ceil($totalItems / $itemsPerPage)) ? '?page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="invoiceId" name="invoice_id">
                    <div class="mb-3">
                        <label for="invoiceAmount" class="form-label">Invoice Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="invoiceAmount" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="amountPaid" class="form-label">Amount Paid</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amountPaid" name="amount_paid" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentReference" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="paymentReference" name="reference" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePaymentBtn">Record Payment</button>
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

<style>
.bg-light-primary {
    background-color: rgba(13, 110, 253, 0.1);
}
.bg-light-success {
    background-color: rgba(25, 135, 84, 0.1);
}
.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1);
}
.bg-light-danger {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>

<script>
    // Handle select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('tbody .form-check-input');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
    
    // Handle record payment modal
    document.querySelectorAll('[data-bs-target="#recordPaymentModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var invoiceId = this.getAttribute('data-id');
            document.getElementById('invoiceId').value = invoiceId;
            
            // In a real application, you would fetch the invoice data from the server
            // For demonstration, we'll use dummy data
            var invoiceAmount = 0;
            <?php foreach ($invoices as $invoice): ?>
            if (<?php echo $invoice['id']; ?> == invoiceId) {
                invoiceAmount = <?php echo $invoice['amount']; ?>;
            }
            <?php endforeach; ?>
            
            document.getElementById('invoiceAmount').value = invoiceAmount.toFixed(2);
            document.getElementById('amountPaid').value = invoiceAmount.toFixed(2);
        });
    });
    
    // Handle delete invoice modal
    document.querySelectorAll('[data-bs-target="#deleteInvoiceModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var invoiceId = this.getAttribute('data-id');
            document.getElementById('deleteInvoiceId').value = invoiceId;
        });
    });
    
    // Handle save payment button
    document.getElementById('savePaymentBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('recordPaymentModal'));
        modal.hide();
        
        // Show success message
        alert('Payment recorded successfully!');
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
</script>
