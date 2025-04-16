<?php
// Vehicles page
// Include required files
session_start();
//require_once '../config/config.php';
//require_once '../includes/functions.php';
//require_once '../includes/auth.php';

require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';



// Get vehicle type filter if set
$vehicleType = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$customer = isset($_GET['customer']) ? $_GET['customer'] : '';

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$vehicles = [
    [
        'id' => 1,
        'model' => 'BMW 535',
        'type' => 'Sedan Car',
        'price' => 25000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'Active'
    ],
    [
        'id' => 2,
        'model' => 'Benz M4',
        'type' => 'Sedan Car',
        'price' => 22000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'Active'
    ],
    [
        'id' => 3,
        'model' => 'Audi Q3',
        'type' => 'Compact Car',
        'price' => 30000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Peter Parker',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'In Service'
    ],
    [
        'id' => 4,
        'model' => 'BMW 525',
        'type' => 'Sedan Car',
        'price' => 26000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'Active'
    ],
    [
        'id' => 5,
        'model' => 'BMW R5',
        'type' => 'Sedan Car',
        'price' => 32000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'Inactive'
    ],
    [
        'id' => 6,
        'model' => 'Ford Mustang',
        'type' => 'Racing Car',
        'price' => 45000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'Active'
    ],
    [
        'id' => 7,
        'model' => 'Audi R8',
        'type' => 'Racing Car',
        'price' => 25000.00,
        'manufacturing_date' => '2021-07-28',
        'engine_no' => '1HGBH41JXMN010197',
        'number_plate' => 'US13RC4402',
        'customer' => 'Bendial Joseph',
        'image' => 'assets/images/default-vehicle.png',
        'status' => 'In Service'
    ]
];

// Filter vehicles by type if filter is set
if (!empty($vehicleType) || !empty($search) || !empty($customer)) {
    $filteredVehicles = [];
    foreach ($vehicles as $vehicle) {
        $match = true;
        
        if (!empty($vehicleType) && stripos($vehicle['type'], $vehicleType) === false) {
            $match = false;
        }
        
        if (!empty($search)) {
            $searchStr = strtolower($search);
            $vehicleStr = strtolower($vehicle['model'] . ' ' . $vehicle['type'] . ' ' . $vehicle['number_plate'] . ' ' . $vehicle['engine_no']);
            if (strpos($vehicleStr, $searchStr) === false) {
                $match = false;
            }
        }
        
        if (!empty($customer) && stripos($vehicle['customer'], $customer) === false) {
            $match = false;
        }
        
        if ($match) {
            $filteredVehicles[] = $vehicle;
        }
    }
    $vehicles = $filteredVehicles;
}

// Get unique vehicle types and customers for filters
$vehicleTypes = array_unique(array_column($vehicles, 'type'));
$customers = array_unique(array_column($vehicles, 'customer'));

// Pagination
$totalVehicles = count($vehicles);
$vehiclesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $vehiclesPerPage;
$paginatedVehicles = array_slice($vehicles, $startIndex, $vehiclesPerPage);

// Calculate vehicle statistics
$activeVehicles = count(array_filter($vehicles, function($v) { return $v['status'] === 'Active'; }));
$inServiceVehicles = count(array_filter($vehicles, function($v) { return $v['status'] === 'In Service'; }));
$inactiveVehicles = count(array_filter($vehicles, function($v) { return $v['status'] === 'Inactive'; }));
?>

<!-- Page header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-dark fw-bold"><i class="fas fa-car me-2 text-primary"></i>Vehicle Management</h2>
    <a href="index.php?page=add-vehicle" class="btn btn-primary d-flex align-items-center">
        <i class="fas fa-plus me-2"></i> Add New Vehicle
    </a>
</div>

<!-- Vehicle stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Total Vehicles</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo $totalVehicles; ?></h4>
                    </div>
                    <div class="bg-primary-light rounded-circle p-3">
                        <i class="fas fa-car text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Active Vehicles</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo $activeVehicles; ?></h4>
                    </div>
                    <div class="bg-success-light rounded-circle p-3">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">In Service</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo $inServiceVehicles; ?></h4>
                    </div>
                    <div class="bg-warning-light rounded-circle p-3">
                        <i class="fas fa-tools text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">Inactive Vehicles</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo $inactiveVehicles; ?></h4>
                    </div>
                    <div class="bg-danger-light rounded-circle p-3">
                        <i class="fas fa-car-crash text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search vehicles..." value="<?php echo $search; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach($vehicleTypes as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($vehicleType === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="customer" class="form-select">
                        <option value="">All Customers</option>
                        <?php foreach($customers as $cust): ?>
                            <option value="<?php echo $cust; ?>" <?php echo ($customer === $cust) ? 'selected' : ''; ?>><?php echo $cust; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Vehicles table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-car-alt me-2"></i>Vehicle List</h5>
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
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr class="bg-light">
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll"></label>
                            </div>
                        </th>
                        <th>Vehicle</th>
                        <th>Price</th>
                        <th>Registration</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th width="120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedVehicles as $vehicle): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $vehicle['id']; ?>">
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2 bg-light rounded">
                                    <img src="<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model']; ?>" class="rounded-circle" width="40" height="40">
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $vehicle['model']; ?></h6>
                                    <small class="text-muted"><?php echo $vehicle['type']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium">$<?php echo number_format($vehicle['price'], 2); ?></span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Number Plate:</small>
                                <span class="badge bg-light text-dark"><?php echo $vehicle['number_plate']; ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2 bg-primary-light rounded-circle">
                                    <span class="avatar-text text-primary"><?php echo substr($vehicle['customer'], 0, 1); ?></span>
                                </div>
                                <div><?php echo $vehicle['customer']; ?></div>
                            </div>
                        </td>
                        <td>
                            <?php
                            switch ($vehicle['status']) {
                                case 'Active':
                                    echo '<span class="badge bg-success">Active</span>';
                                    break;
                                case 'In Service':
                                    echo '<span class="badge bg-warning">In Service</span>';
                                    break;
                                case 'Inactive':
                                    echo '<span class="badge bg-danger">Inactive</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Unknown</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0" type="button" id="vehicleActions<?php echo $vehicle['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="vehicleActions<?php echo $vehicle['id']; ?>">
                                    <li><a class="dropdown-item" href="index.php?page=vehicle-details&id=<?php echo $vehicle['id']; ?>"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="index.php?page=edit-vehicle&id=<?php echo $vehicle['id']; ?>"><i class="fas fa-edit me-2 text-info"></i>Edit</a></li>
                                    <li><a class="dropdown-item" href="index.php?page=create-appointment&vehicle_id=<?php echo $vehicle['id']; ?>"><i class="fas fa-calendar-plus me-2 text-success"></i>Schedule Service</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="deleteVehicle(<?php echo $vehicle['id']; ?>); return false;"><i class="fas fa-trash me-2 text-danger"></i>Delete</a></li>
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
<?php if ($totalVehicles > $vehiclesPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) . ((!empty($vehicleType)) ? '&type=' . $vehicleType : '') . ((!empty($search)) ? '&search=' . $search : '') . ((!empty($customer)) ? '&customer=' . $customer : '') : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php 
        $totalPages = ceil($totalVehicles / $vehiclesPerPage);
        for ($i = 1; $i <= min(5, $totalPages); $i++): 
        ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?><?php echo (!empty($vehicleType) ? '&type=' . $vehicleType : '') . (!empty($search) ? '&search=' . $search : '') . (!empty($customer) ? '&customer=' . $customer : ''); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < $totalPages) ? '?page=' . ($currentPage + 1) . ((!empty($vehicleType)) ? '&type=' . $vehicleType : '') . ((!empty($search)) ? '&search=' . $search : '') . ((!empty($customer)) ? '&customer=' . $customer : '') : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
// Handle select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('tbody .form-check-input');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});

// Delete vehicle function
function deleteVehicle(vehicleId) {
    if (confirm('Are you sure you want to delete this vehicle?')) {
        // In a real application, you would submit a form or make an AJAX request
        alert('Vehicle deleted successfully!');
        // Reload the page or update the UI
        window.location.reload();
    }
}
</script>
