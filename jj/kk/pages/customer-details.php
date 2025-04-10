<?php
// Customer details page

// Get customer ID from URL
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// In a real application, this would be fetched from the database
// For demonstration, we'll use dummy data
$customer = [
    'id' => 1,
    'name' => 'Bendial Joseph',
    'email' => 'bendial.joseph@gmail.com',
    'phone' => '+1 (123) 456-7890',
    'address' => '123 Main St, New York, NY 10001',
    'status' => 'active',
    'image' => 'assets/images/default-user.png',
    'created_at' => '2023-01-15',
    'vehicles' => [
        [
            'id' => 1,
            'model' => 'BMW 535',
            'type' => 'Sedan Car',
            'number_plate' => 'GJ-13-BC-7174',
            'status' => 'active'
        ],
        [
            'id' => 2,
            'model' => 'Benz M4',
            'type' => 'Sedan Car',
            'number_plate' => 'GJ-14-BC-8174',
            'status' => 'maintenance'
        ],
        [
            'id' => 3,
            'model' => 'Audi Q3',
            'type' => 'Compact Car',
            'number_plate' => 'GJ-15-BC-9174',
            'status' => 'active'
        ]
    ],
    'service_history' => [
        [
            'id' => 1,
            'vehicle' => 'BMW 535',
            'service_type' => 'Oil Change',
            'date' => '2023-03-15',
            'cost' => 120.00,
            'status' => 'completed'
        ],
        [
            'id' => 2,
            'vehicle' => 'Benz M4',
            'service_type' => 'Brake Inspection',
            'date' => '2023-02-20',
            'cost' => 350.00,
            'status' => 'in_progress'
        ],
        [
            'id' => 3,
            'vehicle' => 'Audi Q3',
            'service_type' => 'Tire Rotation',
            'date' => '2023-01-10',
            'cost' => 80.00,
            'status' => 'completed'
        ]
    ],
    'billing_history' => [
        [
            'id' => 1,
            'invoice_no' => 'INV-2023-001',
            'date' => '2023-03-15',
            'amount' => 120.00,
            'status' => 'paid'
        ],
        [
            'id' => 2,
            'invoice_no' => 'INV-2023-002',
            'date' => '2023-02-20',
            'amount' => 350.00,
            'status' => 'pending'
        ],
        [
            'id' => 3,
            'invoice_no' => 'INV-2023-003',
            'date' => '2023-01-10',
            'amount' => 80.00,
            'status' => 'paid'
        ]
    ]
];

// If customer not found, redirect to customers page
if ($customerId === 0) {
    header("Location: index.php?page=customers");
    exit();
}
?>

<!-- Back button -->
<div class="mb-3">
    <a href="index.php?page=customers" class="btn btn-link ps-0"><i class="fas fa-arrow-left me-2"></i> Back to Customers</a>
</div>

<!-- Customer header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                <img src="<?php echo $customer['image']; ?>" alt="<?php echo $customer['name']; ?>" class="rounded-circle mb-3" width="120" height="120">
                <?php if ($customer['status'] === 'active'): ?>
                <span class="badge bg-success d-block">Active</span>
                <?php else: ?>
                <span class="badge bg-danger d-block">Inactive</span>
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <h3 class="mb-3"><?php echo $customer['name']; ?></h3>
                <p class="mb-2"><i class="fas fa-envelope me-2"></i> <?php echo $customer['email']; ?></p>
                <p class="mb-2"><i class="fas fa-phone me-2"></i> <?php echo $customer['phone']; ?></p>
                <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $customer['address']; ?></p>
            </div>
            <div class="col-md-5">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1"><?php echo count($customer['vehicles']); ?></h4>
                            <small class="text-muted">Vehicles</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1"><?php echo count($customer['service_history']); ?></h4>
                            <small class="text-muted">Services</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h4 class="mb-1"><?php echo count($customer['billing_history']); ?></h4>
                            <small class="text-muted">Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer tabs -->
<ul class="nav nav-tabs mb-4" id="customerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-controls="vehicles" aria-selected="true">VEHICLES</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="false">SERVICE HISTORY</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab" aria-controls="billing" aria-selected="false">BILLING HISTORY</button>
    </li>
</ul>

<!-- Tab content -->
<div class="tab-content" id="customerTabsContent">
    <!-- Vehicles Tab -->
    <div class="tab-pane fade show active" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>MODEL</th>
                                <th>TYPE</th>
                                <th>NUMBER PLATE</th>
                                <th>STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer['vehicles'] as $vehicle): ?>
                            <tr>
                                <td><?php echo $vehicle['model']; ?></td>
                                <td><?php echo $vehicle['type']; ?></td>
                                <td><?php echo $vehicle['number_plate']; ?></td>
                                <td>
                                    <?php if ($vehicle['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php elseif ($vehicle['status'] === 'maintenance'): ?>
                                    <span class="badge bg-warning">Maintenance</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?page=vehicle-details&id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Service History Tab -->
    <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>VEHICLE</th>
                                <th>SERVICE TYPE</th>
                                <th>DATE</th>
                                <th>COST</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer['service_history'] as $service): ?>
                            <tr>
                                <td><?php echo $service['vehicle']; ?></td>
                                <td><?php echo $service['service_type']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($service['date'])); ?></td>
                                <td>$<?php echo number_format($service['cost'], 2); ?></td>
                                <td>
                                    <?php if ($service['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                    <?php elseif ($service['status'] === 'in_progress'): ?>
                                    <span class="badge bg-warning">In Progress</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Billing History Tab -->
    <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>INVOICE NO</th>
                                <th>DATE</th>
                                <th>AMOUNT</th>
                                <th>STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer['billing_history'] as $bill): ?>
                            <tr>
                                <td><?php echo $bill['invoice_no']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($bill['date'])); ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td>
                                    <?php if ($bill['status'] === 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
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
