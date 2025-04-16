<?php
// Include required files
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
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
    die("Database connection failed. Please try again later.");
}
// --- Database Connection --- END ---

// --- Fetch Data for Form --- START ---
// Fetch Customers
$customers = [];
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM customers ORDER BY last_name, first_name");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching customers: " . $e->getMessage());
    // Handle error appropriately, maybe show a message
}

// Fetch Services (for adding items)
$services = [];
try {
    $stmt = $pdo->query("SELECT id, name, price FROM services WHERE status = 'active' ORDER BY name");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
}

// Fetch Inventory Parts (for adding items)
$parts = [];
try {
    $stmt = $pdo->query("SELECT id, name, selling_price FROM inventory WHERE quantity > 0 ORDER BY name"); // Only show parts in stock
    $parts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching inventory parts: " . $e->getMessage());
}
// --- Fetch Data for Form --- END ---

// --- Define Defaults if not set (Ideally set in config or settings) ---
if (!defined('DEFAULT_TAX_RATE')) {
    define('DEFAULT_TAX_RATE', 0.00); // Example: 0% tax rate
}
if (!defined('DEFAULT_TERMS_CONDITIONS')) {
    define('DEFAULT_TERMS_CONDITIONS', "- Prices are valid for 30 days.\n- Payment due upon completion."); // Example terms
}
// --- Define Defaults --- END ---

// --- Form Submission Logic --- START ---
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Implement form submission logic
    // 1. Validate all inputs (customer, dates, items, totals etc.)
    // 2. Sanitize data
    // 3. Generate unique quotation_number
    // 4. Start transaction
    // 5. Insert into `quotations` table
    // 6. Get the new quotation ID
    // 7. Loop through items and insert into `quotation_items` table
    // 8. Commit transaction
    // 9. Redirect on success (e.g., to quotation-details or quotation list)
    // 10. Rollback and display errors on failure
    
    // Placeholder for now
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $issueDate = filter_input(INPUT_POST, 'issue_date');
    $validUntil = filter_input(INPUT_POST, 'valid_until');
    // ... other fields ...
    
    // Example validation
    if (empty($customerId)) {
        $errors[] = "Customer is required.";
    }
    if (empty($issueDate)) {
        $errors[] = "Issue date is required.";
    }
    // ... more validation ...

    if (empty($errors)) {
        // --- Database Insertion Logic (Placeholder) ---
        try {
            $pdo->beginTransaction();
            
            // Generate Quotation Number (example logic)
            $prefix = 'QT-' . date('Y');
            $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(quotation_number, 8) AS UNSIGNED)) as max_num FROM quotations WHERE quotation_number LIKE '$prefix%'");
            $maxNum = $stmt->fetchColumn() ?? 0;
            $newNum = $maxNum + 1;
            $quotationNumber = $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);

            // Insert into quotations table
            $sqlQuote = "INSERT INTO quotations (quotation_number, customer_id, vehicle_id, issue_date, valid_until, subtotal, tax_rate, tax_amount, discount_type, discount_amount, total_amount, status, notes, terms_conditions, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtQuote = $pdo->prepare($sqlQuote);
            
            // --- Get all POST data and bind --- (Simplified - needs proper fetching and sanitizing)
            $stmtQuote->execute([
                $quotationNumber,
                $_POST['customer_id'],
                $_POST['vehicle_id'] ?? null, // Assuming vehicle_id is optional
                $_POST['issue_date'],
                $_POST['valid_until'] ?? null,
                $_POST['subtotal'] ?? 0,
                $_POST['tax_rate'] ?? 0,
                $_POST['tax_amount'] ?? 0,
                $_POST['discount_type'] ?? null,
                $_POST['discount_amount'] ?? 0,
                $_POST['total_amount'] ?? 0,
                $_POST['status'] ?? 'draft',
                $_POST['notes'] ?? null,
                $_POST['terms_conditions'] ?? null,
                $_SESSION['user_id'] // Logged in user ID
            ]);
            
            $quotationId = $pdo->lastInsertId();

            // Insert into quotation_items table (Loop through $_POST['items'])
            $sqlItem = "INSERT INTO quotation_items (quotation_id, item_type, item_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtItem = $pdo->prepare($sqlItem);
            
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    // Basic validation for item
                    if (!empty($item['description']) && isset($item['quantity']) && isset($item['unit_price'])) {
                         $stmtItem->execute([
                            $quotationId,
                            $item['item_type'], // 'service' or 'part'
                            $item['item_id'] ?? null, // ID of service/part if selected from dropdown
                            $item['description'],
                            $item['quantity'],
                            $item['unit_price'],
                            $item['total_price'] ?? ($item['quantity'] * $item['unit_price']) // Calculate if not provided
                        ]);
                    }
                }
            }

            $pdo->commit();
            flashMessage("Quotation #{$quotationNumber} created successfully!", 'success');
            redirect("index.php?page=quotation-details&id={$quotationId}"); // Redirect to details page
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error creating quotation: " . $e->getMessage());
            $errors[] = "Failed to create quotation. Please check the details and try again. Error: " . $e->getMessage(); // Show specific error during development
        }
    }
    // If errors exist, they will be displayed below the form
}
// --- Form Submission Logic --- END ---

?>

<div class="container-fluid mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Create New Quotation</h1>
            <p class="text-muted">Fill in the details below to create a new price quotation.</p>
        </div>
        <a href="index.php?page=quotation" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Quotations List
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> Please fix the following issues:
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="index.php?page=create-quotation" method="POST" id="createQuotationForm">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Quotation Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= (isset($_POST['customer_id']) && $_POST['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer['last_name'] . ', ' . $customer['first_name']) ?> (ID: <?= $customer['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- TODO: Add button to create new customer? -->
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_id" class="form-label">Vehicle (Optional)</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select">
                            <option value="">-- Select Vehicle (Loads after customer selection) --</option>
                            <!-- Options will be loaded via AJAX based on customer selection -->
                        </select>
                        <!-- TODO: Add AJAX to load vehicles based on customer -->
                    </div>
                    <div class="col-md-4">
                        <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" id="issue_date" class="form-control" value="<?= htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="valid_until" class="form-label">Valid Until</label>
                        <input type="date" name="valid_until" id="valid_until" class="form-control" value="<?= htmlspecialchars($_POST['valid_until'] ?? '') ?>">
                    </div>
                     <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="draft" selected>Draft</option> 
                            <option value="sent">Sent</option>
                            <!-- Other statuses might be set later -->
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quotation Items</h5>
                <div>
                    <button type="button" id="addServiceItemBtn" class="btn btn-sm btn-outline-primary"><i class="fas fa-wrench"></i> Add Service</button>
                    <button type="button" id="addPartItemBtn" class="btn btn-sm btn-outline-success"><i class="fas fa-cogs"></i> Add Part</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="quotationItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Description</th>
                                <th style="width: 15%;" class="text-end">Quantity</th>
                                <th style="width: 20%;" class="text-end">Unit Price</th>
                                <th style="width: 20%;" class="text-end">Total Price</th>
                                <th style="width: 5%;"></th> <!-- Actions -->
                            </tr>
                        </thead>
                        <tbody id="quotationItemsBody">
                            <!-- Item rows will be added here dynamically via JavaScript -->
                            <!-- Example Row (hidden, used as template) -->
                            <tr class="item-template" style="display: none;">
                                <td>
                                    <input type="hidden" name="items[][item_type]" class="item-type">
                                    <input type="hidden" name="items[][item_id]" class="item-id">
                                    <textarea name="items[][description]" class="form-control form-control-sm item-description" rows="1" required></textarea>
                                    <select class="form-select form-select-sm item-select" style="display: none;">
                                        <option value="">-- Select --</option>
                                        <!-- Options loaded dynamically -->
                                    </select>
                                </td>
                                <td><input type="number" name="items[][quantity]" class="form-control form-control-sm text-end item-quantity" value="1" step="0.01" min="0" required></td>
                                <td><input type="number" name="items[][unit_price]" class="form-control form-control-sm text-end item-unit-price" value="0.00" step="0.01" min="0" required></td>
                                <td><input type="text" name="items[][total_price]" class="form-control form-control-sm text-end item-total-price" readonly></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                 <div class="row justify-content-end">
                     <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                             <tbody>
                                 <tr>
                                     <th class="text-end">Subtotal:</th>
                                     <td class="text-end" id="quoteSubtotal">0.00</td>
                                     <input type="hidden" name="subtotal" id="subtotalInput" value="0.00">
                                 </tr>
                                 <tr>
                                     <th class="text-end">Tax Rate (%):</th>
                                     <td class="text-end">
                                         <input type="number" name="tax_rate" id="taxRateInput" class="form-control form-control-sm text-end d-inline-block" style="width: 80px;" value="<?= htmlspecialchars(DEFAULT_TAX_RATE ?? 0) ?>" step="0.01" min="0">
                                     </td>
                                 </tr>
                                 <tr>
                                     <th class="text-end">Tax Amount:</th>
                                     <td class="text-end" id="quoteTaxAmount">0.00</td>
                                     <input type="hidden" name="tax_amount" id="taxAmountInput" value="0.00">
                                 </tr>
                                 <tr>
                                     <th class="text-end">Discount:</th>
                                     <td class="text-end">
                                         <div class="input-group input-group-sm justify-content-end">
                                             <input type="number" name="discount_amount" id="discountAmountInput" class="form-control text-end" style="max-width: 100px;" value="0.00" step="0.01" min="0">
                                             <select name="discount_type" id="discountTypeInput" class="form-select" style="max-width: 100px;">
                                                 <option value="fixed" selected>Fixed</option>
                                                 <option value="percentage">%</option>
                                             </select>
                                         </div>
                                     </td>
                                 </tr>
                                 <tr class="fw-bold">
                                     <th class="text-end fs-5">Total:</th>
                                     <td class="text-end fs-5" id="quoteTotalAmount">0.00</td>
                                      <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
                                 </tr>
                             </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
             <div class="card-header bg-light">
                 <h5 class="mb-0">Notes & Terms</h5>
             </div>
             <div class="card-body">
                 <div class="mb-3">
                     <label for="notes" class="form-label">Notes (Internal or for Customer)</label>
                     <textarea name="notes" id="notes" class="form-control" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                 </div>
                 <div>
                     <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                     <textarea name="terms_conditions" id="terms_conditions" class="form-control" rows="4"><?= htmlspecialchars($_POST['terms_conditions'] ?? DEFAULT_TERMS_CONDITIONS ?? '') ?></textarea>
                 </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Save Quotation</button>
        </div>
    </form>
</div>

<!-- Store service and part data for JavaScript -->
<script id="serviceData" type="application/json">
    <?= json_encode($services) ?>
</script>
<script id="partData" type="application/json">
    <?= json_encode($parts) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTableBody = document.getElementById('quotationItemsBody');
    const templateRow = document.querySelector('.item-template');
    const addServiceBtn = document.getElementById('addServiceItemBtn');
    const addPartBtn = document.getElementById('addPartItemBtn');
    const serviceData = JSON.parse(document.getElementById('serviceData').textContent);
    const partData = JSON.parse(document.getElementById('partData').textContent);

    let itemIndex = 0; // Simple index for unique names/ids

    // Function to add a new item row
    function addItemRow(type = 'service') {
        const newRow = templateRow.cloneNode(true);
        newRow.classList.remove('item-template');
        newRow.style.display = ''; // Make it visible
        newRow.dataset.index = itemIndex;

        const itemTypeInput = newRow.querySelector('.item-type');
        const itemIdInput = newRow.querySelector('.item-id');
        const descriptionInput = newRow.querySelector('.item-description');
        const itemSelect = newRow.querySelector('.item-select');
        const quantityInput = newRow.querySelector('.item-quantity');
        const unitPriceInput = newRow.querySelector('.item-unit-price');
        const totalPriceInput = newRow.querySelector('.item-total-price');
        const removeBtn = newRow.querySelector('.remove-item-btn');

        // Update names to be unique
        newRow.querySelectorAll('[name^="items[]"]').forEach(input => {
            input.name = input.name.replace('items[]', `items[${itemIndex}]`);
        });
        
        itemTypeInput.value = type;

        // Configure based on type (service/part)
        if (type === 'service') {
            itemSelect.style.display = 'block';
            descriptionInput.style.display = 'none'; // Hide textarea initially
            itemSelect.innerHTML = '<option value="">-- Select Service --</option>';
            serviceData.forEach(service => {
                itemSelect.innerHTML += `<option value="${service.id}" data-price="${service.price}">${service.name} (${formatCurrencyJS(service.price)})</option>`;
            });
            itemSelect.innerHTML += '<option value="custom">-- Custom Service --</option>';
        } else { // part
             itemSelect.style.display = 'block';
            descriptionInput.style.display = 'none';
            itemSelect.innerHTML = '<option value="">-- Select Part --</option>';
            partData.forEach(part => {
                itemSelect.innerHTML += `<option value="${part.id}" data-price="${part.selling_price}">${part.name} (${formatCurrencyJS(part.selling_price)})</option>`;
            });
             itemSelect.innerHTML += '<option value="custom">-- Custom Part --</option>';
        }

        itemsTableBody.appendChild(newRow);
        itemIndex++;

        // Add event listeners for the new row
        addEventListenersToRow(newRow);
        updateTotals(); 
    }

    // Function to add event listeners to a row
    function addEventListenersToRow(row) {
        const itemSelect = row.querySelector('.item-select');
        const descriptionInput = row.querySelector('.item-description');
        const quantityInput = row.querySelector('.item-quantity');
        const unitPriceInput = row.querySelector('.item-unit-price');
        const removeBtn = row.querySelector('.remove-item-btn');

        itemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value === 'custom') {
                descriptionInput.style.display = 'block';
                descriptionInput.value = '';
                unitPriceInput.value = '0.00';
                descriptionInput.focus();
            } else if (selectedOption.value) {
                descriptionInput.style.display = 'none'; 
                descriptionInput.value = selectedOption.text; // Use selected item text as description
                unitPriceInput.value = parseFloat(selectedOption.dataset.price || 0).toFixed(2);
            } else {
                descriptionInput.style.display = 'none'; 
                descriptionInput.value = '';
                unitPriceInput.value = '0.00';
            }
            row.querySelector('.item-id').value = (selectedOption.value !== 'custom' && selectedOption.value) ? selectedOption.value : '';
            calculateRowTotal(row);
        });

        quantityInput.addEventListener('input', () => calculateRowTotal(row));
        unitPriceInput.addEventListener('input', () => calculateRowTotal(row));

        removeBtn.addEventListener('click', function() {
            row.remove();
            updateTotals();
        });
    }

    // Function to calculate row total
    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        const total = quantity * unitPrice;
        row.querySelector('.item-total-price').value = total.toFixed(2);
        updateTotals();
    }

    // Function to update overall totals
    function updateTotals() {
        let subtotal = 0;
        itemsTableBody.querySelectorAll('tr:not(.item-template)').forEach(row => {
            const rowTotal = parseFloat(row.querySelector('.item-total-price').value) || 0;
            subtotal += rowTotal;
        });

        const taxRate = parseFloat(document.getElementById('taxRateInput').value) || 0;
        const discountAmount = parseFloat(document.getElementById('discountAmountInput').value) || 0;
        const discountType = document.getElementById('discountTypeInput').value;
        
        let discountValue = 0;
        if (discountType === 'percentage') {
            discountValue = (subtotal * discountAmount) / 100;
        } else { // fixed
            discountValue = discountAmount;
        }
        
        const taxableAmount = subtotal - discountValue; // Apply discount before tax? (Check requirements)
        const taxAmount = (taxableAmount * taxRate) / 100;
        const totalAmount = taxableAmount + taxAmount;
        
        document.getElementById('quoteSubtotal').textContent = formatCurrencyJS(subtotal);
        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('quoteTaxAmount').textContent = formatCurrencyJS(taxAmount);
         document.getElementById('taxAmountInput').value = taxAmount.toFixed(2);
        document.getElementById('quoteTotalAmount').textContent = formatCurrencyJS(totalAmount);
        document.getElementById('totalAmountInput').value = totalAmount.toFixed(2);
    }

    // Event listeners for global calculation fields
    document.getElementById('taxRateInput').addEventListener('input', updateTotals);
    document.getElementById('discountAmountInput').addEventListener('input', updateTotals);
    document.getElementById('discountTypeInput').addEventListener('change', updateTotals);

    // Add item buttons
    addServiceBtn.addEventListener('click', () => addItemRow('service'));
    addPartBtn.addEventListener('click', () => addItemRow('part'));

    // TODO: Add AJAX for loading vehicles based on customer selection
    // const customerSelect = document.getElementById('customer_id');
    // const vehicleSelect = document.getElementById('vehicle_id');
    // customerSelect.addEventListener('change', function() { ... load vehicles ... });

    // Initial calculation
    updateTotals();

     // Simple JS currency formatter (use a library for complex needs)
    function formatCurrencyJS(amount) {
        const symbol = '<?= DEFAULT_CURRENCY_SYMBOL ?? '$' ?>';
        return symbol + parseFloat(amount).toFixed(2);
    }
});
</script> 