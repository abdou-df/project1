<?php
// Customers page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$customers = [
    [
        'id' => 1,
        'name' => 'Bendial Joseph',
        'email' => 'bendial.joseph@gmail.com',
        'phone' => '+1 (123) 456-7890',
        'address' => '123 Main St, New York, NY 10001',
        'vehicles' => 3,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-01-15'
    ],
    [
        'id' => 2,
        'name' => 'Peter Parker',
        'email' => 'peter.parker@gmail.com',
        'phone' => '+1 (234) 567-8901',
        'address' => '456 Park Ave, New York, NY 10002',
        'vehicles' => 1,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-02-20'
    ],
    [
        'id' => 3,
        'name' => 'Regina Cooper',
        'email' => 'regina.cooper@gmail.com',
        'phone' => '+1 (345) 678-9012',
        'address' => '789 Broadway, New York, NY 10003',
        'vehicles' => 2,
        'status' => 'inactive',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-03-10'
    ],
    [
        'id' => 4,
        'name' => 'John Smith',
        'email' => 'john.smith@gmail.com',
        'phone' => '+1 (456) 789-0123',
        'address' => '101 5th Ave, New York, NY 10004',
        'vehicles' => 1,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-04-05'
    ],
    [
        'id' => 5,
        'name' => 'Jane Doe',
        'email' => 'jane.doe@gmail.com',
        'phone' => '+1 (567) 890-1234',
        'address' => '202 6th Ave, New York, NY 10005',
        'vehicles' => 2,
        'status' => 'active',
        'image' => 'assets/images/default-user.png',
        'created_at' => '2023-05-15'
    ]
];

// Pagination
$totalCustomers = count($customers);
$customersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $customersPerPage;
$paginatedCustomers = array_slice($customers, $startIndex, $customersPerPage);
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Customers <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus"></i></button></h2>
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
            Showing 1 - <?php echo min($customersPerPage, $totalCustomers); ?> of <?php echo $totalCustomers; ?>
        </div>
    </div>
</div>

<!-- Search bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search..." aria-label="Search">
            <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
        </div>
    </div>
</div>

<!-- Customers table -->
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
                        <th>CUSTOMER</th>
                        <th>CONTACT</th>
                        <th>ADDRESS</th>
                        <th>VEHICLES</th>
                        <th>STATUS</th>
                        <th>CREATED</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedCustomers as $customer): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $customer['id']; ?>">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $customer['image']; ?>" alt="<?php echo $customer['name']; ?>" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <div><?php echo $customer['name']; ?></div>
                                    <small class="text-muted"><?php echo $customer['email']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo $customer['phone']; ?></div>
                        </td>
                        <td>
                            <div><?php echo $customer['address']; ?></div>
                        </td>
                        <td>
                            <div class="badge bg-info"><?php echo $customer['vehicles']; ?></div>
                        </td>
                        <td>
                            <?php if ($customer['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" type="button" id="dropdownMenuButton<?php echo $customer['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $customer['id']; ?>">
                                    <li><a class="dropdown-item" href="index.php?page=customer-details&id=<?php echo $customer['id']; ?>"><i class="fas fa-eye me-2"></i> View</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editCustomerModal" data-id="<?php echo $customer['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal" data-id="<?php echo $customer['id']; ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
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
<?php if ($totalCustomers > $customersPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= ceil($totalCustomers / $customersPerPage); $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= ceil($totalCustomers / $customersPerPage)) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < ceil($totalCustomers / $customersPerPage)) ? '?page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    <input type="hidden" id="editCustomerId" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editImage" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="editImage" name="image">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateCustomerBtn">Update Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Customer Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCustomerModalLabel">Delete Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
                <input type="hidden" id="deleteCustomerId">
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
    
    // Handle edit customer modal
    document.querySelectorAll('[data-bs-target="#editCustomerModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var customerId = this.getAttribute('data-id');
            document.getElementById('editCustomerId').value = customerId;
            
            // In a real application, you would fetch the customer data from the server
            // For demonstration, we'll use dummy data
            var customer = <?php echo json_encode($customers[0]); ?>;
            
            document.getElementById('editName').value = customer.name;
            document.getElementById('editEmail').value = customer.email;
            document.getElementById('editPhone').value = customer.phone;
            document.getElementById('editAddress').value = customer.address;
            document.getElementById('editStatus').value = customer.status;
        });
    });
    
    // Handle delete customer modal
    document.querySelectorAll('[data-bs-target="#deleteCustomerModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var customerId = this.getAttribute('data-id');
            document.getElementById('deleteCustomerId').value = customerId;
        });
    });
    
    // Handle save customer button
    document.getElementById('saveCustomerBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
        modal.hide();
        
        // Show success message
        alert('Customer added successfully!');
    });
    
    // Handle update customer button
    document.getElementById('updateCustomerBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
        modal.hide();
        
        // Show success message
        alert('Customer updated successfully!');
    });
    
    // Handle confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // In a real application, you would submit the delete request to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
        modal.hide();
        
        // Show success message
        alert('Customer deleted successfully!');
    });
</script>
