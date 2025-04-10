<?php
// Employee details page

// Get employee ID from URL
$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// In a real application, this would be fetched from the database
// For demonstration, we'll use dummy data
$employee = [
    'id' => 1,
    'name' => 'John Smith',
    'email' => 'john.smith@garage.com',
    'phone' => '+1 (123) 456-7890',
    'position' => 'Manager',
    'department' => 'Administration',
    'join_date' => '2020-01-15',
    'salary' => 5000.00,
    'status' => 'active',
    'image' => 'assets/images/default-user.png',
    'address' => '123 Main St, New York, NY 10001',
    'emergency_contact' => 'Jane Smith (+1 987-654-3210)',
    'skills' => ['Management', 'Customer Service', 'Inventory Control', 'Team Leadership'],
    'education' => [
        [
            'degree' => 'Bachelor of Business Administration',
            'institution' => 'New York University',
            'year' => '2015'
        ]
    ],
    'experience' => [
        [
            'position' => 'Assistant Manager',
            'company' => 'Auto Repair Shop',
            'from' => '2015',
            'to' => '2020',
            'description' => 'Managed daily operations and supervised staff.'
        ]
    ],
    'attendance' => [
        ['date' => '2023-03-01', 'status' => 'present', 'check_in' => '08:00', 'check_out' => '17:00'],
        ['date' => '2023-03-02', 'status' => 'present', 'check_in' => '08:15', 'check_out' => '17:30'],
        ['date' => '2023-03-03', 'status' => 'present', 'check_in' => '08:05', 'check_out' => '17:15'],
        ['date' => '2023-03-06', 'status' => 'present', 'check_in' => '08:10', 'check_out' => '17:00'],
        ['date' => '2023-03-07', 'status' => 'absent', 'check_in' => '', 'check_out' => ''],
        ['date' => '2023-03-08', 'status' => 'present', 'check_in' => '08:00', 'check_out' => '17:00'],
        ['date' => '2023-03-09', 'status' => 'present', 'check_in' => '08:30', 'check_out' => '17:45']
    ]
];

// If employee not found, redirect to employees page
if ($employeeId === 0) {
    header("Location: index.php?page=employees");
    exit();
}
?>

<!-- Back button -->
<div class="mb-3">
    <a href="index.php?page=employees" class="btn btn-link ps-0"><i class="fas fa-arrow-left me-2"></i> Back to Employees</a>
</div>

<!-- Employee actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0"><?php echo $employee['name']; ?></h2>
    <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#editEmployeeModal"><i class="fas fa-edit me-2"></i> Edit</button>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i> Send Email</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i> Print Details</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Employee profile -->
<div class="row">
    <!-- Employee info card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <img src="<?php echo $employee['image']; ?>" alt="<?php echo $employee['name']; ?>" class="rounded-circle mb-3" width="120" height="120">
                <h4 class="mb-1"><?php echo $employee['name']; ?></h4>
                <p class="text-muted mb-3"><?php echo $employee['position']; ?></p>
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-primary me-2"><?php echo $employee['department']; ?></span>
                    <?php if ($employee['status'] === 'active'): ?>
                    <span class="badge bg-success">Active</span>
                    <?php else: ?>
                    <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center">
                    <a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-envelope"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-phone"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-comment"></i></a>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <h5 class="mb-0"><?php echo rand(1, 5); ?></h5>
                        <small class="text-muted">Years</small>
                    </div>
                    <div class="col-4 border-end">
                        <h5 class="mb-0"><?php echo rand(10, 50); ?></h5>
                        <small class="text-muted">Tasks</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0"><?php echo rand(90, 100); ?>%</h5>
                        <small class="text-muted">Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Employee details -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="employeeDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">Personal</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab" aria-controls="skills" aria-selected="false">Skills & Education</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="false">Attendance</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="employeeDetailsTabContent">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5 class="mb-3">Contact Information</h5>
                                <p class="mb-2"><strong><i class="fas fa-envelope me-2"></i> Email:</strong> <?php echo $employee['email']; ?></p>
                                <p class="mb-2"><strong><i class="fas fa-phone me-2"></i> Phone:</strong> <?php echo $employee['phone']; ?></p>
                                <p class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i> Address:</strong> <?php echo $employee['address']; ?></p>
                                <p class="mb-2"><strong><i class="fas fa-exclamation-circle me-2"></i> Emergency Contact:</strong> <?php echo $employee['emergency_contact']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Employment Information</h5>
                                <p class="mb-2"><strong><i class="fas fa-user-tie me-2"></i> Position:</strong> <?php echo $employee['position']; ?></p>
                                <p class="mb-2"><strong><i class="fas fa-building me-2"></i> Department:</strong> <?php echo $employee['department']; ?></p>
                                <p class="mb-2"><strong><i class="fas fa-calendar-alt me-2"></i> Join Date:</strong> <?php echo date('F d, Y', strtotime($employee['join_date'])); ?></p>
                                <p class="mb-2"><strong><i class="fas fa-money-bill-wave me-2"></i> Salary:</strong> $<?php echo number_format($employee['salary'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Skills & Education Tab -->
                    <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="skills-tab">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h5 class="mb-3">Skills</h5>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($employee['skills'] as $skill): ?>
                                    <span class="badge bg-primary me-2 mb-2 p-2"><?php echo $skill; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h5 class="mb-3">Education</h5>
                                <?php foreach ($employee['education'] as $education): ?>
                                <div class="card mb-2">
                                    <div class="card-body p-3">
                                        <h6 class="mb-1"><?php echo $education['degree']; ?></h6>
                                        <p class="mb-1 text-muted"><?php echo $education['institution']; ?></p>
                                        <small class="text-muted">Graduated: <?php echo $education['year']; ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Work Experience</h5>
                        <div class="timeline">
                            <?php foreach ($employee['experience'] as $experience): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo $experience['position']; ?> at <?php echo $experience['company']; ?></h6>
                                    <p class="mb-1 text-muted"><?php echo $experience['from']; ?> - <?php echo $experience['to']; ?></p>
                                    <p class="mb-0"><?php echo $experience['description']; ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Attendance Tab -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Attendance History</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-calendar-alt me-1"></i> View Calendar</button>
                                <button class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i> Export</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Working Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employee['attendance'] as $attendance): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($attendance['date'])); ?></td>
                                        <td>
                                            <?php if ($attendance['status'] === 'present'): ?>
                                            <span class="badge bg-success">Present</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $attendance['check_in'] ? $attendance['check_in'] : '-'; ?></td>
                                        <td><?php echo $attendance['check_out'] ? $attendance['check_out'] : '-'; ?></td>
                                        <td>
                                            <?php 
                                            if ($attendance['check_in'] && $attendance['check_out']) {
                                                $checkIn = strtotime($attendance['check_in']);
                                                $checkOut = strtotime($attendance['check_out']);
                                                $hours = round(($checkOut - $checkIn) / 3600, 2);
                                                echo $hours . ' hrs';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent activities and tasks -->
<div class="row">
    <!-- Recent activities -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activities</h5>
                <a href="#" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Completed oil change service</h6>
                            <small class="text-muted">3 days ago</small>
                        </div>
                        <p class="mb-1">Performed oil change on customer vehicle #VIN-12345.</p>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Updated inventory</h6>
                            <small class="text-muted">5 days ago</small>
                        </div>
                        <p class="mb-1">Added 20 new oil filters to inventory.</p>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Processed customer payment</h6>
                            <small class="text-muted">1 week ago</small>
                        </div>
                        <p class="mb-1">Processed payment for invoice #INV-2023-001.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assigned tasks -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assigned Tasks</h5>
                <a href="#" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Complete monthly inventory check</h6>
                            <small class="text-danger">Due tomorrow</small>
                        </div>
                        <p class="mb-1">Verify all items in the inventory and update quantities.</p>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Train new mechanic</h6>
                            <small class="text-warning">Due in 3 days</small>
                        </div>
                        <p class="mb-1">Provide training on basic diagnostic procedures.</p>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 30%;" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Submit monthly report</h6>
                            <small class="text-muted">Due in 1 week</small>
                        </div>
                        <p class="mb-1">Prepare and submit the monthly performance report.</p>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
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
                    <input type="hidden" id="editEmployeeId" name="id" value="<?php echo $employee['id']; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editName" name="name" value="<?php echo $employee['name']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo $employee['email']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone" value="<?php echo $employee['phone']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="editPosition" name="position" value="<?php echo $employee['position']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editDepartment" class="form-label">Department</label>
                            <select class="form-select" id="editDepartment" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Administration" <?php echo ($employee['department'] === 'Administration') ? 'selected' : ''; ?>>Administration</option>
                                <option value="Service" <?php echo ($employee['department'] === 'Service') ? 'selected' : ''; ?>>Service</option>
                                <option value="Front Desk" <?php echo ($employee['department'] === 'Front Desk') ? 'selected' : ''; ?>>Front Desk</option>
                                <option value="Finance" <?php echo ($employee['department'] === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editJoinDate" class="form-label">Join Date</label>
                            <input type="date" class="form-control" id="editJoinDate" name="join_date" value="<?php echo $employee['join_date']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editSalary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="editSalary" name="salary" value="<?php echo $employee['salary']; ?>" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="active" <?php echo ($employee['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($employee['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="2"><?php echo $employee['address']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editEmergencyContact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" id="editEmergencyContact" name="emergency_contact" value="<?php echo $employee['emergency_contact']; ?>">
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
                <p>Are you sure you want to delete <strong><?php echo $employee['name']; ?></strong>? This action cannot be undone.</p>
                <input type="hidden" id="deleteEmployeeId" value="<?php echo $employee['id']; ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styling */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 15px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 40px;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: 7px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    padding: 15px;
    border-radius: 5px;
    background: #f8f9fa;
    border-left: 3px solid #007bff;
}
</style>

<script>
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
        // For demonstration, we'll just redirect to the employees page
        window.location.href = 'index.php?page=employees';
    });
</script>
