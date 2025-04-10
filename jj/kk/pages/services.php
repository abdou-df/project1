<?php
// Services page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$services = [
    [
        'id' => 1,
        'name' => 'Oil Change',
        'description' => 'Regular oil change service including oil filter replacement',
        'duration' => '1 hour',
        'price' => 120.00,
        'category' => 'Maintenance',
        'status' => 'active',
        'image' => 'assets/images/services/oil-change.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Brake Service',
        'description' => 'Complete brake inspection and pad replacement',
        'duration' => '2 hours',
        'price' => 350.00,
        'category' => 'Repair',
        'status' => 'active',
        'image' => 'assets/images/services/brake-service.jpg'
    ],
    [
        'id' => 3,
        'name' => 'Tire Rotation',
        'description' => 'Tire rotation and wheel balancing',
        'duration' => '1 hour',
        'price' => 80.00,
        'category' => 'Maintenance',
        'status' => 'active',
        'image' => 'assets/images/services/tire-rotation.jpg'
    ],
    [
        'id' => 4,
        'name' => 'Engine Tune-up',
        'description' => 'Complete engine performance optimization',
        'duration' => '3 hours',
        'price' => 250.00,
        'category' => 'Performance',
        'status' => 'active',
        'image' => 'assets/images/services/engine-tuneup.jpg'
    ],
    [
        'id' => 5,
        'name' => 'AC Service',
        'description' => 'Air conditioning system check and recharge',
        'duration' => '2 hours',
        'price' => 180.00,
        'category' => 'Comfort',
        'status' => 'active',
        'image' => 'assets/images/services/ac-service.jpg'
    ]
];

// Pagination
$totalServices = count($services);
$servicesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $servicesPerPage;
$paginatedServices = array_slice($services, $startIndex, $servicesPerPage);
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Services <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="fas fa-plus"></i></button></h2>
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
            Showing 1 - <?php echo min($servicesPerPage, $totalServices); ?> of <?php echo $totalServices; ?>
        </div>
    </div>
</div>

<!-- Search and filter bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search services..." aria-label="Search">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Repair">Repair</option>
                    <option value="Performance">Performance</option>
                    <option value="Comfort">Comfort</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" aria-label="Sort by">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="price_asc">Price (Low-High)</option>
                    <option value="price_desc">Price (High-Low)</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Services grid -->
<div class="row">
    <?php foreach ($paginatedServices as $service): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <img src="<?php echo $service['image']; ?>" class="card-img-top" alt="<?php echo $service['name']; ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0"><?php echo $service['name']; ?></h5>
                    <span class="badge bg-primary">$<?php echo number_format($service['price'], 2); ?></span>
                </div>
                <p class="card-text text-muted small mb-2"><?php echo $service['description']; ?></p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-light text-dark"><i class="far fa-clock me-1"></i> <?php echo $service['duration']; ?></span>
                    <span class="badge bg-secondary"><?php echo $service['category']; ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal" data-id="<?php echo $service['id']; ?>">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal" data-id="<?php echo $service['id']; ?>">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalServices > $servicesPerPage): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= ceil($totalServices / $servicesPerPage); $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($currentPage >= ceil($totalServices / $servicesPerPage)) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < ceil($totalServices / $servicesPerPage)) ? '?page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addServiceForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Repair">Repair</option>
                                <option value="Performance">Performance</option>
                                <option value="Comfort">Comfort</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="duration" class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g. 1 hour" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Service Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveServiceBtn">Save Service</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm">
                    <input type="hidden" id="editServiceId" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-select" id="editCategory" name="category" required>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Repair">Repair</option>
                                <option value="Performance">Performance</option>
                                <option value="Comfort">Comfort</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editPrice" class="form-label">Price ($)</label>
                            <input type="number" class="form-control" id="editPrice" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editDuration" class="form-label">Duration</label>
                            <input type="text" class="form-control" id="editDuration" name="duration" placeholder="e.g. 1 hour" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editImage" class="form-label">Service Image</label>
                        <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateServiceBtn">Update Service</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Service Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServiceModalLabel">Delete Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this service? This action cannot be undone.</p>
                <input type="hidden" id="deleteServiceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle edit service modal
    document.querySelectorAll('[data-bs-target="#editServiceModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var serviceId = this.getAttribute('data-id');
            document.getElementById('editServiceId').value = serviceId;
            
            // In a real application, you would fetch the service data from the server
            // For demonstration, we'll use dummy data
            var service = <?php echo json_encode($services[0]); ?>;
            
            document.getElementById('editName').value = service.name;
            document.getElementById('editCategory').value = service.category;
            document.getElementById('editPrice').value = service.price;
            document.getElementById('editDuration').value = service.duration;
            document.getElementById('editDescription').value = service.description;
        });
    });
    
    // Handle delete service modal
    document.querySelectorAll('[data-bs-target="#deleteServiceModal"]').forEach(function(element) {
        element.addEventListener('click', function() {
            var serviceId = this.getAttribute('data-id');
            document.getElementById('deleteServiceId').value = serviceId;
        });
    });
    
    // Handle save service button
    document.getElementById('saveServiceBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('addServiceModal'));
        modal.hide();
        
        // Show success message
        alert('Service added successfully!');
    });
    
    // Handle update service button
    document.getElementById('updateServiceBtn').addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('editServiceModal'));
        modal.hide();
        
        // Show success message
        alert('Service updated successfully!');
    });
    
    // Handle confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // In a real application, you would submit the delete request to the server
        // For demonstration, we'll just close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteServiceModal'));
        modal.hide();
        
        // Show success message
        alert('Service deleted successfully!');
    });
</script>
