<?php
// Create Invoice page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$customers = [
    ['id' => 1, 'name' => 'John Smith'],
    ['id' => 2, 'name' => 'Sarah Williams'],
    ['id' => 3, 'name' => 'Michael Brown'],
    ['id' => 4, 'name' => 'Emily Davis'],
    ['id' => 5, 'name' => 'Robert Johnson']
];

$vehicles = [
    ['id' => 1, 'customer_id' => 1, 'name' => 'Toyota Camry (2019)'],
    ['id' => 2, 'customer_id' => 2, 'name' => 'Honda Accord (2020)'],
    ['id' => 3, 'customer_id' => 3, 'name' => 'Ford F-150 (2018)'],
    ['id' => 4, 'customer_id' => 4, 'name' => 'Nissan Altima (2021)'],
    ['id' => 5, 'customer_id' => 5, 'name' => 'Chevrolet Malibu (2017)'],
    ['id' => 6, 'customer_id' => 1, 'name' => 'Hyundai Sonata (2020)']
];

$services = [
    ['id' => 1, 'name' => 'Oil Change', 'price' => 49.99],
    ['id' => 2, 'name' => 'Brake Service', 'price' => 149.99],
    ['id' => 3, 'name' => 'Tire Rotation', 'price' => 29.99],
    ['id' => 4, 'name' => 'Engine Tune-Up', 'price' => 199.99],
    ['id' => 5, 'name' => 'AC Service', 'price' => 89.99]
];

$parts = [
    ['id' => 1, 'name' => 'Oil Filter', 'price' => 12.99],
    ['id' => 2, 'name' => 'Brake Pad Set', 'price' => 49.99],
    ['id' => 3, 'name' => 'Engine Oil 5W-30', 'price' => 8.99],
    ['id' => 4, 'name' => 'Spark Plug', 'price' => 6.99],
    ['id' => 5, 'name' => 'Wiper Blade', 'price' => 15.99]
];

// Get invoice ID from URL if editing
$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$invoice = null;

// If editing, fetch invoice data
if ($invoice_id) {
    // In a real application, this would be fetched from the database
    // For demonstration, we'll use dummy data
    $invoice = [
        'id' => 1,
        'invoice_number' => 'INV-2023-001',
        'customer_id' => 1,
        'vehicle_id' => 1,
        'issue_date' => '2023-03-10',
        'due_date' => '2023-03-25',
        'subtotal' => 49.99,
        'tax_rate' => 8.00,
        'tax_amount' => 4.00,
        'discount_amount' => 0.00,
        'total_amount' => 53.99,
        'notes' => 'Regular oil change service',
        'items' => [
            [
                'type' => 'service',
                'id' => 1,
                'description' => 'Oil Change',
                'quantity' => 1,
                'unit_price' => 49.99,
                'subtotal' => 49.99
            ]
        ]
    ];
}

// Calculate next invoice number
$next_invoice_number = 'INV-' . date('Y') . '-' . sprintf('%03d', 7);
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3"><?php echo $invoice_id ? 'Edit Invoice' : 'Create New Invoice'; ?></h2>
    <a href="index.php?page=billing" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Invoices
    </a>
</div>

<form id="invoiceForm" method="post" action="#">
    <div class="row">
        <!-- Left column - Invoice details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Invoice Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="invoiceNumber" class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="invoiceNumber" name="invoice_number" value="<?php echo $invoice ? $invoice['invoice_number'] : $next_invoice_number; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="invoiceStatus" class="form-label">Status</label>
                            <select class="form-select" id="invoiceStatus" name="status">
                                <option value="draft" <?php echo ($invoice && $invoice['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="unpaid" <?php echo ($invoice && $invoice['status'] == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paid" <?php echo ($invoice && $invoice['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="cancelled" <?php echo ($invoice && $invoice['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="issueDate" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="issueDate" name="issue_date" value="<?php echo $invoice ? $invoice['issue_date'] : date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="due_date" value="<?php echo $invoice ? $invoice['due_date'] : date('Y-m-d', strtotime('+15 days')); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer" class="form-label">Customer</label>
                            <select class="form-select" id="customer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" <?php echo ($invoice && $invoice['customer_id'] == $customer['id']) ? 'selected' : ''; ?>><?php echo $customer['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle" class="form-label">Vehicle</label>
                            <select class="form-select" id="vehicle" name="vehicle_id" required>
                                <option value="">Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>" data-customer="<?php echo $vehicle['customer_id']; ?>" <?php echo ($invoice && $invoice['vehicle_id'] == $vehicle['id']) ? 'selected' : ''; ?>><?php echo $vehicle['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice items -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="addServiceBtn">
                            <i class="fas fa-plus me-1"></i> Add Service
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addPartBtn">
                            <i class="fas fa-plus me-1"></i> Add Part
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="invoiceItemsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th width="100px">Quantity</th>
                                    <th width="150px">Unit Price</th>
                                    <th width="150px">Subtotal</th>
                                    <th width="50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($invoice && !empty($invoice['items'])): ?>
                                <?php foreach ($invoice['items'] as $index => $item): ?>
                                <tr>
                                    <td>
                                        <select class="form-select item-select" name="items[<?php echo $index; ?>][item_id]" data-type="<?php echo $item['type']; ?>">
                                            <?php if ($item['type'] == 'service'): ?>
                                            <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>" <?php echo ($item['id'] == $service['id']) ? 'selected' : ''; ?>><?php echo $service['name']; ?></option>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <?php foreach ($parts as $part): ?>
                                            <option value="<?php echo $part['id']; ?>" <?php echo ($item['id'] == $part['id']) ? 'selected' : ''; ?>><?php echo $part['name']; ?></option>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <input type="hidden" name="items[<?php echo $index; ?>][type]" value="<?php echo $item['type']; ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[<?php echo $index; ?>][description]" value="<?php echo $item['description']; ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="items[<?php echo $index; ?>][quantity]" value="<?php echo $item['quantity']; ?>" min="1">
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control item-price" name="items[<?php echo $index; ?>][unit_price]" value="<?php echo $item['unit_price']; ?>" step="0.01" min="0">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control item-subtotal" name="items[<?php echo $index; ?>][subtotal]" value="<?php echo $item['subtotal']; ?>" step="0.01" min="0" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr class="no-items-row">
                                    <td colspan="6" class="text-center py-3">No items added yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $invoice ? $invoice['notes'] : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right column - Summary -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<span id="subtotalAmount"><?php echo $invoice ? number_format($invoice['subtotal'], 2) : '0.00'; ?></span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <span>Tax Rate:</span>
                                <div class="input-group input-group-sm d-inline-flex w-auto">
                                    <input type="number" class="form-control form-control-sm" id="taxRate" name="tax_rate" value="<?php echo $invoice ? $invoice['tax_rate'] : '8.00'; ?>" step="0.01" min="0" style="width: 70px;">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <span>$<span id="taxAmount"><?php echo $invoice ? number_format($invoice['tax_amount'], 2) : '0.00'; ?></span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <span>Discount:</span>
                                <div class="input-group input-group-sm d-inline-flex w-auto">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control form-control-sm" id="discountAmount" name="discount_amount" value="<?php echo $invoice ? $invoice['discount_amount'] : '0.00'; ?>" step="0.01" min="0" style="width: 70px;">
                                </div>
                            </div>
                            <span>-$<span id="discountDisplay"><?php echo $invoice ? number_format($invoice['discount_amount'], 2) : '0.00'; ?></span></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>$<span id="totalAmount"><?php echo $invoice ? number_format($invoice['total_amount'], 2) : '0.00'; ?></span></strong>
                        </div>
                    </div>
                    <input type="hidden" id="subtotalInput" name="subtotal" value="<?php echo $invoice ? $invoice['subtotal'] : '0.00'; ?>">
                    <input type="hidden" id="taxAmountInput" name="tax_amount" value="<?php echo $invoice ? $invoice['tax_amount'] : '0.00'; ?>">
                    <input type="hidden" id="totalAmountInput" name="total_amount" value="<?php echo $invoice ? $invoice['total_amount'] : '0.00'; ?>">
                </div>
                <div class="card-footer bg-light">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> <?php echo $invoice_id ? 'Update Invoice' : 'Create Invoice'; ?>
                        </button>
                        <?php if ($invoice_id): ?>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i> Print Invoice
                        </a>
                        <a href="#" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope me-2"></i> Email Invoice
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="serviceSelect" class="form-label">Select Service</label>
                    <select class="form-select" id="serviceSelect">
                        <option value="">Select Service</option>
                        <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>"><?php echo $service['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="serviceDescription" class="form-label">Description</label>
                    <input type="text" class="form-control" id="serviceDescription">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="serviceQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="serviceQuantity" value="1" min="1">
                    </div>
                    <div class="col-md-6">
                        <label for="servicePrice" class="form-label">Unit Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="servicePrice" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addServiceItemBtn">Add Service</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Part Modal -->
<div class="modal fade" id="addPartModal" tabindex="-1" aria-labelledby="addPartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPartModalLabel">Add Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="partSelect" class="form-label">Select Part</label>
                    <select class="form-select" id="partSelect">
                        <option value="">Select Part</option>
                        <?php foreach ($parts as $part): ?>
                        <option value="<?php echo $part['id']; ?>" data-price="<?php echo $part['price']; ?>"><?php echo $part['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="partDescription" class="form-label">Description</label>
                    <input type="text" class="form-control" id="partDescription">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="partQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="partQuantity" value="1" min="1">
                    </div>
                    <div class="col-md-6">
                        <label for="partPrice" class="form-label">Unit Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="partPrice" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addPartItemBtn">Add Part</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Filter vehicles based on selected customer
    document.getElementById('customer').addEventListener('change', function() {
        var customerId = this.value;
        var vehicleSelect = document.getElementById('vehicle');
        var options = vehicleSelect.querySelectorAll('option');
        
        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            if (option.value === '') {
                continue; // Skip the placeholder option
            }
            
            if (option.getAttribute('data-customer') === customerId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
        
        // Reset vehicle selection
        vehicleSelect.value = '';
    });
    
    // Handle add service button
    document.getElementById('addServiceBtn').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
        modal.show();
    });
    
    // Handle add part button
    document.getElementById('addPartBtn').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('addPartModal'));
        modal.show();
    });
    
    // Handle service selection
    document.getElementById('serviceSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var price = selectedOption.getAttribute('data-price');
        var description = selectedOption.text;
        
        document.getElementById('servicePrice').value = price;
        document.getElementById('serviceDescription').value = description;
    });
    
    // Handle part selection
    document.getElementById('partSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var price = selectedOption.getAttribute('data-price');
        var description = selectedOption.text;
        
        document.getElementById('partPrice').value = price;
        document.getElementById('partDescription').value = description;
    });
    
    // Add service item to invoice
    document.getElementById('addServiceItemBtn').addEventListener('click', function() {
        var serviceSelect = document.getElementById('serviceSelect');
        var serviceId = serviceSelect.value;
        var serviceName = serviceSelect.options[serviceSelect.selectedIndex].text;
        var description = document.getElementById('serviceDescription').value;
        var quantity = document.getElementById('serviceQuantity').value;
        var price = document.getElementById('servicePrice').value;
        var subtotal = (quantity * price).toFixed(2);
        
        if (!serviceId) {
            alert('Please select a service');
            return;
        }
        
        addInvoiceItem('service', serviceId, serviceName, description, quantity, price, subtotal);
        
        // Close modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addServiceModal'));
        modal.hide();
        
        // Reset form
        serviceSelect.value = '';
        document.getElementById('serviceDescription').value = '';
        document.getElementById('serviceQuantity').value = '1';
        document.getElementById('servicePrice').value = '';
    });
    
    // Add part item to invoice
    document.getElementById('addPartItemBtn').addEventListener('click', function() {
        var partSelect = document.getElementById('partSelect');
        var partId = partSelect.value;
        var partName = partSelect.options[partSelect.selectedIndex].text;
        var description = document.getElementById('partDescription').value;
        var quantity = document.getElementById('partQuantity').value;
        var price = document.getElementById('partPrice').value;
        var subtotal = (quantity * price).toFixed(2);
        
        if (!partId) {
            alert('Please select a part');
            return;
        }
        
        addInvoiceItem('part', partId, partName, description, quantity, price, subtotal);
        
        // Close modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addPartModal'));
        modal.hide();
        
        // Reset form
        partSelect.value = '';
        document.getElementById('partDescription').value = '';
        document.getElementById('partQuantity').value = '1';
        document.getElementById('partPrice').value = '';
    });
    
    // Function to add item to invoice
    function addInvoiceItem(type, itemId, itemName, description, quantity, price, subtotal) {
        var table = document.getElementById('invoiceItemsTable');
        var tbody = table.querySelector('tbody');
        var noItemsRow = tbody.querySelector('.no-items-row');
        
        if (noItemsRow) {
            noItemsRow.remove();
        }
        
        var rowCount = tbody.querySelectorAll('tr').length;
        var newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <select class="form-select item-select" name="items[${rowCount}][item_id]" data-type="${type}">
                    <option value="${itemId}" selected>${itemName}</option>
                </select>
                <input type="hidden" name="items[${rowCount}][type]" value="${type}">
            </td>
            <td>
                <input type="text" class="form-control" name="items[${rowCount}][description]" value="${description}">
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${rowCount}][quantity]" value="${quantity}" min="1">
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control item-price" name="items[${rowCount}][unit_price]" value="${price}" step="0.01" min="0">
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control item-subtotal" name="items[${rowCount}][subtotal]" value="${subtotal}" step="0.01" min="0" readonly>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        
        // Add event listeners to new row
        var quantityInput = newRow.querySelector('.item-quantity');
        var priceInput = newRow.querySelector('.item-price');
        var subtotalInput = newRow.querySelector('.item-subtotal');
        var removeButton = newRow.querySelector('.remove-item-btn');
        
        quantityInput.addEventListener('input', function() {
            updateItemSubtotal(this, priceInput, subtotalInput);
        });
        
        priceInput.addEventListener('input', function() {
            updateItemSubtotal(quantityInput, this, subtotalInput);
        });
        
        removeButton.addEventListener('click', function() {
            newRow.remove();
            updateInvoiceTotals();
            
            if (tbody.querySelectorAll('tr').length === 0) {
                var emptyRow = document.createElement('tr');
                emptyRow.className = 'no-items-row';
                emptyRow.innerHTML = '<td colspan="6" class="text-center py-3">No items added yet</td>';
                tbody.appendChild(emptyRow);
            }
        });
        
        updateInvoiceTotals();
    }
    
    // Function to update item subtotal
    function updateItemSubtotal(quantityInput, priceInput, subtotalInput) {
        var quantity = parseFloat(quantityInput.value) || 0;
        var price = parseFloat(priceInput.value) || 0;
        var subtotal = (quantity * price).toFixed(2);
        
        subtotalInput.value = subtotal;
        updateInvoiceTotals();
    }
    
    // Function to update invoice totals
    function updateInvoiceTotals() {
        var subtotalInputs = document.querySelectorAll('.item-subtotal');
        var subtotal = 0;
        
        subtotalInputs.forEach(function(input) {
            subtotal += parseFloat(input.value) || 0;
        });
        
        var taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        var discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
        
        var taxAmount = (subtotal * taxRate / 100).toFixed(2);
        var totalAmount = (subtotal + parseFloat(taxAmount) - discountAmount).toFixed(2);
        
        document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
        document.getElementById('taxAmount').}