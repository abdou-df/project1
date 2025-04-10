<?php
// Create Appointment page

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$customers = [
    ['id' => 1, 'name' => 'John Smith', 'phone' => '(555) 123-4567'],
    ['id' => 2, 'name' => 'Sarah Williams', 'phone' => '(555) 234-5678'],
    ['id' => 3, 'name' => 'Michael Brown', 'phone' => '(555) 345-6789'],
    ['id' => 4, 'name' => 'Emily Davis', 'phone' => '(555) 456-7890'],
    ['id' => 5, 'name' => 'Robert Johnson', 'phone' => '(555) 567-8901']
];

$vehicles = [
    ['id' => 1, 'customer_id' => 1, 'make' => 'Toyota', 'model' => 'Camry', 'year' => '2019', 'license_plate' => 'ABC-1234'],
    ['id' => 2, 'customer_id' => 2, 'make' => 'Honda', 'model' => 'Accord', 'year' => '2020', 'license_plate' => 'DEF-5678'],
    ['id' => 3, 'customer_id' => 3, 'make' => 'Ford', 'model' => 'F-150', 'year' => '2018', 'license_plate' => 'GHI-9012'],
    ['id' => 4, 'customer_id' => 4, 'make' => 'Nissan', 'model' => 'Altima', 'year' => '2021', 'license_plate' => 'JKL-3456'],
    ['id' => 5, 'customer_id' => 5, 'make' => 'Chevrolet', 'model' => 'Malibu', 'year' => '2017', 'license_plate' => 'MNO-7890']
];

$services = [
    ['id' => 1, 'name' => 'Oil Change', 'duration' => 30, 'price' => 49.99],
    ['id' => 2, 'name' => 'Brake Inspection', 'duration' => 45, 'price' => 79.99],
    ['id' => 3, 'name' => 'Tire Rotation', 'duration' => 30, 'price' => 39.99],
    ['id' => 4, 'name' => 'Full Inspection', 'duration' => 60, 'price' => 129.99],
    ['id' => 5, 'name' => 'Engine Diagnostics', 'duration' => 45, 'price' => 89.99],
    ['id' => 6, 'name' => 'Brake Replacement', 'duration' => 90, 'price' => 249.99],
    ['id' => 7, 'name' => 'Scheduled Maintenance', 'duration' => 120, 'price' => 199.99]
];

$mechanics = [
    ['id' => 1, 'name' => 'Mike Johnson'],
    ['id' => 2, 'name' => 'David Wilson'],
    ['id' => 3, 'name' => 'Robert Brown'],
    ['id' => 4, 'name' => 'James Davis'],
    ['id' => 5, 'name' => 'William Miller']
];

// Check if we're editing an existing appointment
$isEditing = isset($_GET['id']);
$appointmentId = $isEditing ? (int)$_GET['id'] : null;

// In a real application, you would fetch the appointment data from the database
// For demonstration, we'll use dummy data for editing
$editAppointment = null;
if ($isEditing) {
    $editAppointment = [
        'id' => 1,
        'customer_id' => 1,
        'vehicle_id' => 1,
        'service_id' => 1,
        'date' => '2023-03-25',
        'time' => '09:00',
        'mechanic_id' => 1,
        'notes' => 'Customer requested synthetic oil',
        'status' => 'Confirmed'
    ];
}
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3"><?php echo $isEditing ? 'Edit' : 'Create'; ?> Appointment</h2>
    <a href="index.php?page=appointments" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to Appointments
    </a>
</div>

<!-- Appointment form -->
<div class="card">
    <div class="card-body">
        <form id="appointmentForm">
            <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?php echo $editAppointment['id']; ?>">
            <?php endif; ?>
            
            <!-- Customer and Vehicle Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="customer" class="form-label">Select Customer</label>
                                <select class="form-select" id="customer" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>" <?php echo ($isEditing && $editAppointment['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                        <?php echo $customer['name']; ?> - <?php echo $customer['phone']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Customer</option>
                                </select>
                            </div>
                            
                            <div id="newCustomerFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="customerName" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customerName" name="customer_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customerPhone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="customerPhone" name="customer_phone">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customerEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="customerEmail" name="customer_email">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="vehicle" class="form-label">Select Vehicle</label>
                                <select class="form-select" id="vehicle" name="vehicle_id" required>
                                    <option value="">Select Vehicle</option>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>" data-customer="<?php echo $vehicle['customer_id']; ?>" <?php echo ($isEditing && $editAppointment['vehicle_id'] == $vehicle['id']) ? 'selected' : ''; ?>>
                                        <?php echo $vehicle['make']; ?> <?php echo $vehicle['model']; ?> (<?php echo $vehicle['year']; ?>) - <?php echo $vehicle['license_plate']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Vehicle</option>
                                </select>
                            </div>
                            
                            <div id="newVehicleFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vehicleMake" class="form-label">Make</label>
                                        <input type="text" class="form-control" id="vehicleMake" name="vehicle_make">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="vehicleModel" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="vehicleModel" name="vehicle_model">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="vehicleYear" class="form-label">Year</label>
                                        <input type="number" class="form-control" id="vehicleYear" name="vehicle_year" min="1900" max="2099">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="vehiclePlate" class="form-label">License Plate</label>
                                        <input type="text" class="form-control" id="vehiclePlate" name="vehicle_plate">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="vehicleVin" class="form-label">VIN</label>
                                        <input type="text" class="form-control" id="vehicleVin" name="vehicle_vin">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Service Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="service" class="form-label">Select Service</label>
                                <select class="form-select" id="service" name="service_id" required>
                                    <option value="">Select Service</option>
                                    <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" data-duration="<?php echo $service['duration']; ?>" data-price="<?php echo $service['price']; ?>" <?php echo ($isEditing && $editAppointment['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                        <?php echo $service['name']; ?> - $<?php echo number_format($service['price'], 2); ?> (<?php echo $service['duration']; ?> min)
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="custom">+ Custom Service</option>
                                </select>
                            </div>
                            
                            <div id="customServiceFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="serviceName" class="form-label">Service Description</label>
                                    <input type="text" class="form-control" id="serviceName" name="service_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="serviceDuration" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="serviceDuration" name="service_duration" min="15" step="15" value="30">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="servicePrice" class="form-label">Estimated Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="servicePrice" name="service_price" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="appointmentDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="appointmentDate" name="date" required value="<?php echo $isEditing ? $editAppointment['date'] : date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="appointmentTime" class="form-label">Time</label>
                                    <input type="time" class="form-control" id="appointmentTime" name="time" required value="<?php echo $isEditing ? $editAppointment['time'] : '09:00'; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mechanic" class="form-label">Assigned Mechanic</label>
                                <select class="form-select" id="mechanic" name="mechanic_id" required>
                                    <option value="">Select Mechanic</option>
                                    <?php foreach ($mechanics as $mechanic): ?>
                                    <option value="<?php echo $mechanic['id']; ?>" <?php echo ($isEditing && $editAppointment['mechanic_id'] == $mechanic['id']) ? 'selected' : ''; ?>>
                                        <?php echo $mechanic['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if ($isEditing): ?>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="confirmed" <?php echo ($editAppointment['status'] == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="pending" <?php echo ($editAppointment['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo ($editAppointment['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo ($editAppointment['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $isEditing ? $editAppointment['notes'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendReminder" name="send_reminder" checked>
                        <label class="form-check-label" for="sendReminder">
                            Send appointment reminder to customer
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Submit buttons -->
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php?page=appointments'">Cancel</button>
                <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Update' : 'Create'; ?> Appointment</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle customer selection
    document.getElementById('customer').addEventListener('change', function() {
        var newCustomerFields = document.getElementById('newCustomerFields');
        var vehicleSelect = document.getElementById('vehicle');
        
        if (this.value === 'new') {
            newCustomerFields.style.display = 'block';
            
            // Hide all vehicle options except 'Add New Vehicle'
            Array.from(vehicleSelect.options).forEach(function(option) {
                if (option.value !== 'new' && option.value !== '') {
                    option.style.display = 'none';
                }
            });
            
            vehicleSelect.value = 'new';
            document.getElementById('newVehicleFields').style.display = 'block';
        } else {
            newCustomerFields.style.display = 'none';
            
            // Show only vehicles belonging to selected customer
            var customerId = this.value;
            Array.from(vehicleSelect.options).forEach(function(option) {
                if (option.value !== 'new' && option.value !== '') {
                    if (option.getAttribute('data-customer') === customerId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                } else {
                    option.style.display = '';
                }
            });
            
            vehicleSelect.value = '';
            document.getElementById('newVehicleFields').style.display = 'none';
        }
    });
    
    // Handle vehicle selection
    document.getElementById('vehicle').addEventListener('change', function() {
        var newVehicleFields = document.getElementById('newVehicleFields');
        
        if (this.value === 'new') {
            newVehicleFields.style.display = 'block';
        } else {
            newVehicleFields.style.display = 'none';
        }
    });
    
    // Handle service selection
    document.getElementById('service').addEventListener('change', function() {
        var customServiceFields = document.getElementById('customServiceFields');
        
        if (this.value === 'custom') {
            customServiceFields.style.display = 'block';
        } else {
            customServiceFields.style.display = 'none';
            
            // Update duration if a predefined service is selected
            if (this.value !== '') {
                var selectedOption = this.options[this.selectedIndex];
                var duration = selectedOption.getAttribute('data-duration');
                var price = selectedOption.getAttribute('data-price');
                
                // You could use this to calculate end time or display additional info
                console.log('Selected service duration: ' + duration + ' minutes');
                console.log('Selected service price: $' + price);
            }
        }
    });
    
    // Handle form submission
    document.getElementById('appointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just redirect to the appointments page
        alert('Appointment <?php echo $isEditing ? 'updated' : 'created'; ?> successfully!');
        window.location.href = 'index.php?page=appointments';
    });
</script>
