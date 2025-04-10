<?php
// Invoice details page

// Get invoice ID from URL
$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// In a real application, this would be fetched from the database
// For demonstration, we'll use dummy data
$invoice = [
    'id' => 1,
    'invoice_no' => 'INV-2023-001',
    'customer' => [
        'id' => 1,
        'name' => 'Bendial Joseph',
        'email' => 'bendial.joseph@gmail.com',
        'phone' => '+1 (123) 456-7890',
        'address' => '123 Main St, New York, NY 10001'
    ],
    'date' => '2023-03-15',
    'due_date' => '2023-04-15',
    'subtotal' => 120.00,
    'tax' => 12.00,
    'total' => 132.00,
    'status' => 'paid',
    'payment_method' => 'Credit Card',
    'payment_date' => '2023-03-20',
    'notes' => 'Thank you for your business!',
    'items' => [
        [
            'id' => 1,
            'name' => 'Oil Change',
            'description' => 'Regular oil change service including oil filter replacement',
            'quantity' => 1,
            'price' => 120.00,
            'total' => 120.00
        ]
    ]
];

// If invoice not found, redirect to invoices page
if ($invoiceId === 0) {
    header("Location: index.php?page=invoices");
    exit();
}

// Company information
$company = [
    'name' => 'Garage Management System',
    'address' => '456 Workshop St, New York, NY 10002',
    'phone' => '+1 (987) 654-3210',
    'email' => 'info@garagemanagementsystem.com',
    'website' => 'www.garagemanagementsystem.com',
    'logo' => 'assets/images/logo.png'
];
?>

<!-- Back button -->
<div class="mb-3">
    <a href="index.php?page=invoices" class="btn btn-link ps-0"><i class="fas fa-arrow-left me-2"></i> Back to Invoices</a>
</div>

<!-- Invoice actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Invoice #<?php echo $invoice['invoice_no']; ?></h2>
    <div>
        <button class="btn btn-outline-primary me-2"><i class="fas fa-print me-2"></i> Print</button>
        <button class="btn btn-outline-success me-2"><i class="fas fa-download me-2"></i> Download</button>
        <button class="btn btn-outline-info me-2"><i class="fas fa-paper-plane me-2"></i> Send</button>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i> Edit</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-copy me-2"></i> Duplicate</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Invoice card -->
<div class="card mb-4">
    <div class="card-body">
        <!-- Invoice header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-4">
                    <img src="<?php echo $company['logo']; ?>" alt="<?php echo $company['name']; ?>" height="50">
                    <h4 class="ms-3 mb-0"><?php echo $company['name']; ?></h4>
                </div>
                <div>
                    <p class="mb-1"><?php echo $company['address']; ?></p>
                    <p class="mb-1"><?php echo $company['phone']; ?></p>
                    <p class="mb-1"><?php echo $company['email']; ?></p>
                    <p class="mb-0"><?php echo $company['website']; ?></p>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h4 class="text-uppercase text-primary mb-3">Invoice</h4>
                <p class="mb-1"><strong>Invoice No:</strong> <?php echo $invoice['invoice_no']; ?></p>
                <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($invoice['date'])); ?></p>
                <p class="mb-0"><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?></p>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Customer information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-3">Bill To:</h5>
                <p class="mb-1"><strong><?php echo $invoice['customer']['name']; ?></strong></p>
                <p class="mb-1"><?php echo $invoice['customer']['address']; ?></p>
                <p class="mb-1"><?php echo $invoice['customer']['phone']; ?></p>
                <p class="mb-0"><?php echo $invoice['customer']['email']; ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h5 class="mb-3">Payment Status:</h5>
                <?php if ($invoice['status'] === 'paid'): ?>
                <p class="mb-1"><span class="badge bg-success">Paid</span></p>
                <p class="mb-1"><strong>Payment Method:</strong> <?php echo $invoice['payment_method']; ?></p>
                <p class="mb-0"><strong>Payment Date:</strong> <?php echo date('F d, Y', strtotime($invoice['payment_date'])); ?></p>
                <?php elseif ($invoice['status'] === 'pending'): ?>
                <p class="mb-1"><span class="badge bg-warning">Pending</span></p>
                <p class="mb-0"><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?></p>
                <?php else: ?>
                <p class="mb-1"><span class="badge bg-danger">Overdue</span></p>
                <p class="mb-0"><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Invoice items -->
        <div class="table-responsive mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice['items'] as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['description']; ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end">$<?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-end"><strong>Subtotal</strong></td>
                        <td class="text-end">$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-end"><strong>Tax (10%)</strong></td>
                        <td class="text-end">$<?php echo number_format($invoice['tax'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-end"><strong>Total</strong></td>
                        <td class="text-end"><strong>$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Notes -->
        <div class="row">
            <div class="col-md-8">
                <h5>Notes:</h5>
                <p><?php echo $invoice['notes']; ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($invoice['status'] !== 'paid'): ?>
                <button class="btn btn-success"><i class="fas fa-credit-card me-2"></i> Pay Now</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment history -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payment History</h5>
    </div>
    <div class="card-body">
        <?php if ($invoice['status'] === 'paid'): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($invoice['payment_date'])); ?></td>
                        <td>TXN-<?php echo rand(10000, 99999); ?></td>
                        <td><?php echo $invoice['payment_method']; ?></td>
                        <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">No payment history available.</p>
        <?php endif; ?>
    </div>
</div>
