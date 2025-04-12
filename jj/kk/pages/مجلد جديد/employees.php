<?php
// Employees page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data

echo '<link rel="stylesheet" href="assets/css/sidebar.css">';
echo '<link rel="stylesheet" href="assets/css/style.css">';






$employees = [
    [
        'id' => 1,
        'name' => 'John Smith',
        'email' => 'john.smith@garage.com',
        'phone' => '+1 (123) 456-7890',
        'position' => 'Manager',
        'department' => 'Administration',
        'join_date' => '2020-01-15',
        'salary' => 5000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 2,
        'name' => 'Mike Johnson',
        'email' => 'mike.johnson@garage.com',
        'phone' => '+1 (234) 567-8901',
        'position' => 'Senior Mechanic',
        'department' => 'Service',
        'join_date' => '2020-03-10',
        'salary' => 4000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 3,
        'name' => 'Sarah Williams',
        'email' => 'sarah.williams@garage.com',
        'phone' => '+1 (345) 678-9012',
        'position' => 'Receptionist',
        'department' => 'Front Desk',
        'join_date' => '2021-05-20',
        'salary' => 3000.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 4,
        'name' => 'David Wilson',
        'email' => 'david.wilson@garage.com',
        'phone' => '+1 (456) 789-0123',
        'position' => 'Mechanic',
        'department' => 'Service',
        'join_date' => '2021-08-15',
        'salary' => 3500.00,
        'status' => 'active',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 5,
        'name' => 'Emily Brown',
        'email' => 'emily.brown@garage.com',
        'phone' => '+1 (567) 890-1234',
        'position' => 'Accountant',
        'department' => 'Finance',
        'join_date' => '2022-01-10',
        'salary' => 4200.00,
        'status' => 'inactive',
        'image' => 'assets/images/default-user.png'
    ]
];

// Pagination
$totalEmployees = count($employees);
$employeesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $employeesPerPage;
$paginatedEmployees = array_slice($employees, $startIndex, $employeesPerPage);
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Employees <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><i class="fas fa-plus"></i></button></h2>
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
            Showing 1 - <?php echo min($employeesPerPage, $totalEmployees); ?> of <?php echo $totalEmployees; ?>
        </div>
    </div>
</div>

<!-- Search and filter bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search employees..." aria-label="Search">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Filter by department">
                    <option value="">All Departments</option>
                    <option value="Administration">Administration</option>
                    <option value="Service">Service</option>
                    <option value="Front Desk">Front Desk</option>
                    <option value="Finance">Finance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Employees table -->
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
                        <th>EMPLOYEE</th>
                        <th>POSITION</th>
                        <th>DEPARTMENT</th>
                        <th>CONTACT</th>
                        <th>JOIN DATE</th>
                        <th>SALARY</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedEmployees as $employee): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $employee['id']; ?>">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $employee['image']; ?>" alt="<?php echo $employee['name']; ?>" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <div><?php echo $employee['name']; ?></div>
                                    <small class="text-muted"><?php echo $employee['email']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $employee['position']; ?></td>
                        <td><?php echo $employee['department']; ?></td>
                        <td><?php echo $employee['phone']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($employee['join_date'])); ?></td>
                        <td>$<?php echo number_format($employee['salary'], 2); ?></td>
                        <td>
                            <?php if ($employee['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" type="button" id="dropdownMenuButton<?php echo $employee['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $employee['id']; ?>">
                                    <li><a class="dropdown-item" href="index.php?page=employee-details&id=<?php echo $employee['id']; ?>"><i class="fas fa-eye me-2"></i> View</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-id="<?php echo $employee['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal" data-id="<?php echo $employee['id']; ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
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
<?php if ($totalEmployees > $employeesPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= ceil($totalEmployees / $employeesPerPage); $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= ceil($totalEmployees / $employeesPerPage)) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < ceil($totalEmployees / $employeesPerPage)) ? '?page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
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
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Administration">Administration</option>
                                <option value="Service">Service</option>
                                <option value="Front Desk">Front Desk</option>
                                <option value="Finance">Finance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="join_date" class="form-label">Join Date</label>
                            <input type="date" class="form-control" id="join_date" name="join_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="salary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="salary" name="salary" step="0.01" required>
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
                        <label for="image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEmployeeBtn">Save Employee</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" id="editEmployeeId" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editName" class="form-label">Full Name</label>
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
                            <label for="editPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="editPosition" name="position" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editDepartment" class="form-label">Department</label>
                            <select class="form-select" id="editDepartment" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Administration">Administration</option>
                                <option value="Service">Service</option>
                                <option value="Front Desk">Front Desk</option>
                                <option value="Finance">Finance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editJoinDate" class="form-label">Join Date</label>
                            <input type="date" class="form-control" id="editJoinDate" name="join_date" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editSalary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="editSalary" name="salary" step="0.01" required>
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
                        <label for="editImage" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="editImage" name="image">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateEmployeeBtn">Update Employee</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">Delete Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this employee? This action cannot be undone.</p>
                <input type="hidden" id="deleteEmployeeId">
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
    
    // Handle edit employee modal
    document.querySelectorAll('[data-bs-target="#editEmployeeModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var employeeId = this.getAttribute('data-id');
            document.getElementById('editEmployeeId').value = employeeId;
            
            // In a real application, you would fetch the employee data from the server
            // For demonstration, we'll use dummy data
            var employee = <?php echo json_encode($employees[0]); ?>;
            
            document.getElementById('editName').value = employee.name;
            document.getElementById('editEmail').value = employee.email;
            document.getElementById('editPhone').value = employee.phone;
            document.getElementById('editPosition').value = employee.position;
            document.getElementById('editDepartment').value = employee.department;
            document.getElementById('editJoinDate').value = employee.join_date;
            document.getElementById('editSalary').value = employee.salary;
            document.getElementById('editStatus').value = employee.status;
        });
    });
    
    // Handle delete employee modal
    document.querySelectorAll('[data-bs-target="#deleteEmployeeModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var employeeId = this.getAttribute('data-id');
            document.getElementById('deleteEmployeeId').value = employeeId;
        });
    });
    
    // Handle save employee button
    document.getElementById('saveEmployeeBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
        modal.hide();
        
        // Show success message
        alert('Employee added successfully!');
    });
    
    // Handle update employee button
    document.getElementById('updateEmployeeBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
        modal.hide();
        
        // Show success message
        alert('Employee updated successfully!');
    });
    
    // Handle confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // In a real application, you would submit the delete request to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteEmployeeModal'));
        modal.hide();
        
        // Show success message
        alert('Employee deleted successfully!');
    });
</script>
