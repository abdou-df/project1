<?php
// Vehicle details page

// Get vehicle ID from URL
$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// In a real application, this would be fetched from the database
// For demonstration, we'll use dummy data
$vehicle = [
    'id' => 1,
    'model' => 'BMW 535',
    'type' => 'Car',
    'registration_date' => '01 January, 2018',
    'odometer' => '24,000 km',
    'number_plate' => 'GJ-13-BC-7174',
    'vehicle_name' => 'V1',
    'manufacturing_date' => '01 January, 2018',
    'chassis_no' => 'HGFH457B7845',
    'gears' => '5',
    'gear_box' => 'H17B',
    'gear_box_no' => '45',
    'brand' => 'BMW',
    'engine' => '11',
    'engine_no' => '45',
    'engine_size' => '1600 CC',
    'model_year' => '2018',
    'fuel_type' => 'Petrol',
    'color' => 'White',
    'images' => [
        'assets/images/default-vehicle.png',
        'assets/images/default-vehicle.png',
        'assets/images/default-vehicle.png',
        'assets/images/default-vehicle.png'
    ],
    'maintenance_history' => [
        [
            'date' => '2023-01-15',
            'service' => 'Oil Change',
            'description' => 'Regular oil change and filter replacement',
            'cost' => 120.00,
            'technician' => 'John Smith'
        ],
        [
            'date' => '2022-10-05',
            'service' => 'Brake Inspection',
            'description' => 'Checked brake pads and replaced rear pads',
            'cost' => 350.00,
            'technician' => 'Mike Johnson'
        ],
        [
            'date' => '2022-06-20',
            'service' => 'Tire Rotation',
            'description' => 'Rotated tires and balanced wheels',
            'cost' => 80.00,
            'technician' => 'John Smith'
        ]
    ],
    'mot_details' => [
        [
            'date' => '2023-02-10',
            'result' => 'Pass',
            'expiry_date' => '2024-02-10',
            'certificate_no' => 'MOT123456',
            'inspector' => 'David Wilson'
        ],
        [
            'date' => '2022-02-15',
            'result' => 'Pass',
            'expiry_date' => '2023-02-15',
            'certificate_no' => 'MOT789012',
            'inspector' => 'Robert Brown'
        ]
    ]
];

// If vehicle not found, redirect to vehicles page
if ($vehicleId === 0) {
    header("Location: index.php?page=vehicles");
    exit();
}
?>

<!-- Back button -->
<div class="mb-3">
    <a href="index.php?page=vehicles" class="btn btn-link ps-0"><i class="fas fa-arrow-left me-2"></i> BMW 535</a>
</div>

<!-- Vehicle header -->
<div class="card mb-4">
    <div class="card-body bg-dark text-white">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <img src="<?php echo $vehicle['images'][0]; ?>" alt="<?php echo $vehicle['model']; ?>" class="rounded me-3" width="100" height="100">
                    <div>
                        <h2 class="mb-1"><?php echo $vehicle['model']; ?> <a href="#" class="text-warning"><i class="fas fa-edit"></i></a></h2>
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-3"><i class="fas fa-car me-1"></i> <?php echo $vehicle['type']; ?></span>
                            <span><i class="fas fa-calendar-alt me-1"></i> <?php echo $vehicle['registration_date']; ?></span>
                        </div>
                        <div>
                            <span class="d-inline-block"><i class="fas fa-tachometer-alt me-1"></i> <?php echo $vehicle['odometer']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- This would be a chart in a real application -->
                <div class="d-flex justify-content-end h-100">
                    <div class="d-flex align-items-end">
                        <div class="bg-white" style="width: 10px; height: 100px; margin-right: 5px;"></div>
                        <div class="bg-primary" style="width: 10px; height: 80px; margin-right: 5px;"></div>
                        <div class="bg-warning" style="width: 10px; height: 120px; margin-right: 5px;"></div>
                        <div class="bg-danger" style="width: 10px; height: 60px; margin-right: 5px;"></div>
                        <div class="bg-success" style="width: 10px; height: 90px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle tabs -->
<ul class="nav nav-tabs mb-4" id="vehicleTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">BASIC DETAIL</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="false">DESCRIPTION</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab" aria-controls="maintenance" aria-selected="false">MAINTENANCE HISTORY</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="mot-tab" data-bs-toggle="tab" data-bs-target="#mot" type="button" role="tab" aria-controls="mot" aria-selected="false">MOT TEST DETAILS</button>
    </li>
</ul>

<!-- Tab content -->
<div class="tab-content" id="vehicleTabsContent">
    <!-- Basic Details Tab -->
    <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <strong>Number Plate</strong>
                            </div>
                            <div class="col-md-7">
                                <?php echo $vehicle['number_plate']; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <strong>Vehicle Name</strong>
                            </div>
                            <div class="col-md-7">
                                <?php echo $vehicle['vehicle_name']; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <strong>Date of Manufacturing</strong>
                            </div>
                            <div class="col-md-7">
                                <?php echo $vehicle['manufacturing_date']; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <strong>Vehicle Type</strong>
                            </div>
                            <div class="col-md-7">
                                <?php echo $vehicle['type']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8 mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body p-0">
                                <img src="<?php echo $vehicle['images'][0]; ?>" class="img-fluid w-100" alt="Vehicle Image">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body p-0">
                                <img src="<?php echo $vehicle['images'][1]; ?>" class="img-fluid w-100" alt="Vehicle Image">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body p-0">
                                <img src="<?php echo $vehicle['images'][2]; ?>" class="img-fluid w-100" alt="Vehicle Image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">More Info.</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Chassis No:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['chassis_no']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>No of Gears:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['gears']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Gear Box:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['gear_box']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Gear Box No:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['gear_box_no']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Vehicle Brand:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['brand']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Engine:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['engine']; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Fuel Type:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['fuel_type']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Odometer Reading:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['odometer']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Engine No:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['engine_no']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Engine Size:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['engine_size']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Model Year:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['model_year']; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Color:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $vehicle['color']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Description Tab -->
    <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Vehicle Description</h5>
                <p>This BMW 535 is in excellent condition with regular maintenance and service history. The vehicle features a powerful engine, comfortable interior, and advanced safety features.</p>
                <p>The car has been well-maintained by its previous owner and has a complete service history. It comes with all original documentation and has passed all required inspections.</p>
            </div>
        </div>
    </div>
    
    <!-- Maintenance History Tab -->
    <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Maintenance History</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Cost</th>
                                <th>Technician</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicle['maintenance_history'] as $maintenance): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($maintenance['date'])); ?></td>
                                <td><?php echo $maintenance['service']; ?></td>
                                <td><?php echo $maintenance['description']; ?></td>
                                <td>$<?php echo number_format($maintenance['cost'], 2); ?></td>
                                <td><?php echo $maintenance['technician']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MOT Test Details Tab -->
    <div class="tab-pane fade" id="mot" role="tabpanel" aria-labelledby="mot-tab">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">MOT Test Details</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Test Date</th>
                                <th>Result</th>
                                <th>Expiry Date</th>
                                <th>Certificate No</th>
                                <th>Inspector</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicle['mot_details'] as $mot): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($mot['date'])); ?></td>
                                <td>
                                    <?php if ($mot['result'] === 'Pass'): ?>
                                    <span class="badge bg-success">Pass</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d-m-Y', strtotime($mot['expiry_date'])); ?></td>
                                <td><?php echo $mot['certificate_no']; ?></td>
                                <td><?php echo $mot['inspector']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
