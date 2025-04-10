<?php
// Appointments page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$appointments = [
    [
        'id' => 1,
        'customer_name' => 'John Smith',
        'vehicle' => 'Toyota Camry (2019)',
        'service_type' => 'Oil Change',
        'date' => '2023-03-25',
        'time' => '09:00 AM',
        'status' => 'Confirmed',
        'mechanic' => 'Mike Johnson',
        'notes' => 'Customer requested synthetic oil'
    ],
    [
        'id' => 2,
        'customer_name' => 'Sarah Williams',
        'vehicle' => 'Honda Accord (2020)',
        'service_type' => 'Brake Inspection',
        'date' => '2023-03-25',
        'time' => '10:30 AM',
        'status' => 'Confirmed',
        'mechanic' => 'David Wilson',
        'notes' => ''
    ],
    [
        'id' => 3,
        'customer_name' => 'Michael Brown',
        'vehicle' => 'Ford F-150 (2018)',
        'service_type' => 'Tire Rotation',
        'date' => '2023-03-25',
        'time' => '01:00 PM',
        'status' => 'Pending',
        'mechanic' => 'Robert Brown',
        'notes' => 'Customer will be 15 minutes late'
    ],
    [
        'id' => 4,
        'customer_name' => 'Emily Davis',
        'vehicle' => 'Nissan Altima (2021)',
        'service_type' => 'Full Inspection',
        'date' => '2023-03-26',
        'time' => '11:00 AM',
        'status' => 'Confirmed',
        'mechanic' => 'James Davis',
        'notes' => ''
    ],
    [
        'id' => 5,
        'customer_name' => 'Robert Johnson',
        'vehicle' => 'Chevrolet Malibu (2017)',
        'service_type' => 'Engine Diagnostics',
        'date' => '2023-03-26',
        'time' => '02:30 PM',
        'status' => 'Confirmed',
        'mechanic' => 'William Miller',
        'notes' => 'Check engine light is on'
    ],
    [
        'id' => 6,
        'customer_name' => 'Jennifer Wilson',
        'vehicle' => 'Hyundai Sonata (2020)',
        'service_type' => 'Oil Change',
        'date' => '2023-03-27',
        'time' => '09:30 AM',
        'status' => 'Pending',
        'mechanic' => 'Mike Johnson',
        'notes' => ''
    ],
    [
        'id' => 7,
        'customer_name' => 'David Miller',
        'vehicle' => 'Kia Optima (2019)',
        'service_type' => 'Brake Replacement',
        'date' => '2023-03-27',
        'time' => '11:30 AM',
        'status' => 'Confirmed',
        'mechanic' => 'David Wilson',
        'notes' => 'Front brakes only'
    ],
    [
        'id' => 8,
        'customer_name' => 'Lisa Taylor',
        'vehicle' => 'Mazda CX-5 (2022)',
        'service_type' => 'Scheduled Maintenance',
        'date' => '2023-03-28',
        'time' => '10:00 AM',
        'status' => 'Confirmed',
        'mechanic' => 'Robert Brown',
        'notes' => '30,000 mile service'
    ]
];

// Pagination
$totalItems = count($appointments);
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $itemsPerPage;
$paginatedItems = array_slice($appointments, $startIndex, $itemsPerPage);

// Get unique mechanics and service types for filters
$mechanics = array_unique(array_column($appointments, 'mechanic'));
$serviceTypes = array_unique(array_column($appointments, 'service_type'));
?>

<!-- Page header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-dark fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Appointments</h2>
    <a href="index.php?page=create-appointment" class="btn btn-primary d-flex align-items-center">
        <i class="fas fa-plus me-2"></i> New Appointment
    </a>
</div>

<!-- Appointment stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Today's Appointments</h6>
                        <h4 class="mb-0 fw-bold">5</h4>
                    </div>
                    <div class="bg-primary-light rounded-circle p-3">
                        <i class="fas fa-calendar-day text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Upcoming</h6>
                        <h4 class="mb-0 fw-bold">12</h4>
                    </div>
                    <div class="bg-success-light rounded-circle p-3">
                        <i class="fas fa-calendar-alt text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Pending</h6>
                        <h4 class="mb-0 fw-bold">3</h4>
                    </div>
                    <div class="bg-warning-light rounded-circle p-3">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Completed</h6>
                        <h4 class="mb-0 fw-bold">28</h4>
                    </div>
                    <div class="bg-info-light rounded-circle p-3">
                        <i class="fas fa-check-circle text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar view toggle -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group" role="group" aria-label="View toggle">
        <button type="button" class="btn btn-primary active"><i class="fas fa-list me-2"></i> List View</button>
        <button type="button" class="btn btn-outline-primary"><i class="fas fa-calendar-alt me-2"></i> Calendar View</button>
    </div>
    <div class="d-flex">
        <div class="dropdown me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="recordsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                10
            </button>
            <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="recordsDropdown">
                <li><a class="dropdown-item" href="#">10</a></li>
                <li><a class="dropdown-item" href="#">25</a></li>
                <li><a class="dropdown-item" href="#">50</a></li>
                <li><a class="dropdown-item" href="#">100</a></li>
            </ul>
        </div>
        <div class="text-muted mt-2">
            Showing 1 - <?php echo min($itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?>
        </div>
    </div>
</div>

<!-- Search and filter -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Search appointments...">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select">
                    <option value="">All Mechanics</option>
                    <?php foreach ($mechanics as $mechanic): ?>
                    <option value="<?php echo $mechanic; ?>"><?php echo $mechanic; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select">
                    <option value="">All Services</option>
                    <?php foreach ($serviceTypes as $service): ?>
                    <option value="<?php echo $service; ?>"><?php echo $service; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select">
                    <option value="">All Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="authenticated">Authenticated</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" placeholder="Date">
            </div>
        </div>
    </div>
</div>

<!-- Appointments Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-calendar-check me-2"></i>Appointment List</h5>
        <div>
            <button class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-file-export me-1"></i> Export
            </button>
            <button class="btn btn-sm btn-outline-primary">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll"></label>
                            </div>
                        </th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Mechanic</th>
                        <th>Status</th>
                        <th width="120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedItems as $appointment): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2 bg-primary-light rounded-circle">
                                    <span class="avatar-text text-primary"><?php echo substr($appointment['customer_name'], 0, 1); ?></span>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $appointment['customer_name']; ?></h6>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $appointment['vehicle']; ?></td>
                        <td><?php echo $appointment['service_type']; ?></td>
                        <td>
                            <div>
                                <div class="fw-medium"><?php echo date('d M Y', strtotime($appointment['date'])); ?></div>
                                <small class="text-muted"><?php echo $appointment['time']; ?></small>
                            </div>
                        </td>
                        <td><?php echo $appointment['mechanic']; ?></td>
                        <td>
                            <?php if ($appointment['status'] == 'Confirmed'): ?>
                                <span class="badge bg-success">Confirmed</span>
                            <?php elseif ($appointment['status'] == 'Pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($appointment['status'] == 'Authenticated'): ?>
                                <span class="badge bg-primary">Authenticated</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $appointment['status']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2 text-primary"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2 text-info"></i>Edit</a></li>
                                    <?php if ($appointment['status'] == 'Confirmed' || $appointment['status'] == 'Pending'): ?>
                                    <li><a class="dropdown-item authenticate-appointment" href="#" data-id="<?php echo $appointment['id']; ?>" data-bs-toggle="modal" data-bs-target="#authenticateModal"><i class="fas fa-check-circle me-2 text-success"></i>Authenticate</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2 text-danger"></i>Delete</a></li>
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
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= min(5, ceil($totalItems / $itemsPerPage)); $i++): ?>
        <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>">
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

<!-- Authenticate Appointment Modal -->
<div class="modal fade" id="authenticateModal" tabindex="-1" aria-labelledby="authenticateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authenticateModalLabel">Authenticate Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to authenticate this appointment? This will confirm that you as a worker accept the appointment time and service details.</p>
                <form id="authenticateForm">
                    <input type="hidden" id="appointmentId" name="appointment_id" value="">
                    <div class="mb-3">
                        <label for="workerNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="workerNotes" name="worker_notes" rows="3" placeholder="Add any notes or special instructions for this appointment"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAuthenticate">Authenticate</button>
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

    // Handle appointment authentication
    document.querySelectorAll('.authenticate-appointment').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            var appointmentId = this.getAttribute('data-id');
            document.getElementById('appointmentId').value = appointmentId;
        });
    });

    // Handle authentication confirmation
    document.getElementById('confirmAuthenticate').addEventListener('click', function() {
        var appointmentId = document.getElementById('appointmentId').value;
        var workerNotes = document.getElementById('workerNotes').value;
        
        // In a real application, this would be an AJAX call to the server
        // For demonstration purposes, we'll simulate a successful response
        
        // Simulate AJAX request
        setTimeout(function() {
            // Close the modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('authenticateModal'));
            modal.hide();
            
            // Show success message
            alert('Appointment authenticated successfully!');
            
            // Reload the page to reflect changes
            window.location.reload();
        }, 1000);
    });
</script>
