<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to book an appointment.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can book appointments.', 'error');
}

$error = '';
$success = '';

// Get customer vehicles
$customer_id = $_SESSION['user_id'];
$sql = "SELECT * FROM vehicles WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$vehicles_result = $stmt->get_result();

// Get services
$sql = "SELECT * FROM services WHERE status = 'active'";
$services_result = $conn->query($sql);

// Process appointment form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = sanitize_input($_POST['vehicle_id']);
    $service_id = sanitize_input($_POST['service_id']);
    $date = sanitize_input($_POST['date']);
    $time = sanitize_input($_POST['time']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validate input
    if (empty($vehicle_id) || empty($service_id) || empty($date) || empty($time)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Get service duration
        $sql = "SELECT duration FROM services WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();
        $duration = $service['duration'];
        
        // Calculate end time
        $start_time = $time;
        $end_time = date('H:i:s', strtotime($start_time) + $duration * 60);
        
        // Check if time slot is available
        $available_times = get_available_times($date, $service_id);
        if (!in_array($time, $available_times)) {
            $error = 'The selected time slot is not available. Please choose another time.';
        } else {
            // Insert appointment
            $sql = "INSERT INTO appointments (customer_id, vehicle_id, service_id, date, start_time, end_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissss", $customer_id, $vehicle_id, $service_id, $date, $start_time, $end_time, $notes);
            
            if ($stmt->execute()) {
                $success = 'Appointment booked successfully!';
                // Redirect to dashboard
                redirect('dashboard.php', 'Appointment booked successfully!', 'success');
            } else {
                $error = 'Failed to book appointment. Please try again.';
            }
        }
    }
}

// Pre-select service if provided in URL
$selected_service = isset($_GET['service']) ? intval($_GET['service']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Book an Appointment</h1>
            <p>Schedule your vehicle service with our expert mechanics</p>
        </div>
    </section>
    
    <section class="appointment-section">
        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($vehicles_result->num_rows == 0): ?>
                <div class="alert alert-info">
                    <p>You don't have any vehicles registered. Please add a vehicle first.</p>
                    <a href="add-vehicle.php" class="btn btn-primary mt-2">Add Vehicle</a>
                </div>
            <?php else: ?>
                <div class="appointment-container">
                    <div class="appointment-form-container">
                        <h2>Schedule Your Service</h2>
                        <form action="appointment.php" method="post" id="appointment-form" class="appointment-form">
                            <div class="form-group">
                                <label for="vehicle_id">Select Vehicle</label>
                                <select id="vehicle_id" name="vehicle_id" required>
                                    <option value="">-- Select Vehicle --</option>
                                    <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                                        <option value="<?php echo $vehicle['id']; ?>">
                                            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="service_id">Select Service</label>
                                <select id="service_id" name="service_id" required>
                                    <option value="">-- Select Service --</option>
                                    <?php while ($service = $services_result->fetch_assoc()): ?>
                                        <option value="<?php echo $service['id']; ?>" <?php echo ($selected_service == $service['id']) ? 'selected' : ''; ?> data-duration="<?php echo $service['duration']; ?>" data-price="<?php echo $service['price']; ?>">
                                            <?php echo htmlspecialchars($service['name'] . ' - ' . format_currency($service['price'])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Select Date</label>
                                <input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Select Time</label>
                                <select id="time" name="time" required disabled>
                                    <option value="">-- Select Date First --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="notes">Additional Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Please provide any additional information about your service needs"></textarea>
                            </div>
                            <div class="appointment-summary">
                                <h3>Appointment Summary</h3>
                                <div class="summary-item">
                                    <span>Service:</span>
                                    <span id="summary-service">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Duration:</span>
                                    <span id="summary-duration">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Price:</span>
                                    <span id="summary-price">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Date & Time:</span>
                                    <span id="summary-datetime">-</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                    <div class="appointment-info">
                        <div class="info-card">
                            <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                            <ul>
                                <li>Please arrive 10 minutes before your scheduled appointment.</li>
                                <li>Cancellations should be made at least 24 hours in advance.</li>
                                <li>For emergency services, please call us directly.</li>
                                <li>All prices are estimates and may vary based on your vehicle's condition.</li>
                            </ul>
                        </div>
                        <div class="info-card">
                            <h3><i class="fas fa-clock"></i> Business Hours</h3>
                            <?php
                            // Get business hours
                            $business_hours = json_decode($settings['business_hours'] ?? '{}', true);
                            if (!empty($business_hours)) {
                                echo '<ul class="hours-list">';
                                $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
                                         'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                                
                                foreach ($days as $day_key => $day_name) {
                                    $hours = isset($business_hours[$day_key]) ? $business_hours[$day_key] : 'closed';
                                    echo '<li><span>' . $day_name . ':</span> <span>' . ucfirst($hours) . '</span></li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const timeSelect = document.getElementById('time');
            const serviceSelect = document.getElementById('service_id');
            
            // Summary elements
            const summaryService = document.getElementById('summary-service');
            const summaryDuration = document.getElementById('summary-duration');
            const summaryPrice = document.getElementById('summary-price');
            const summaryDateTime = document.getElementById('summary-datetime');
            
            // Update available times when date or service changes
            function updateAvailableTimes() {
                const date = dateInput.value;
                const serviceId = serviceSelect.value;
                
                if (date && serviceId) {
                    timeSelect.disabled = true;
                    timeSelect.innerHTML = '<option value="">Loading available times...</option>';
                    
                    // Fetch available times via AJAX
                    fetch(`get-available-times.php?date=${date}&service_id=${serviceId}`)
                        .then(response => response.json())
                        .then(data => {
                            timeSelect.innerHTML = '';
                            
                            if (data.length === 0) {
                                timeSelect.innerHTML = '<option value="">No available times</option>';
                            } else {
                                timeSelect.innerHTML = '<option value="">-- Select Time --</option>';
                                data.forEach(time => {
                                    const option = document.createElement('option');
                                    option.value = time;
                                    option.textContent = time;
                                    timeSelect.appendChild(option);
                                });
                            }
                            
                            timeSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error fetching available times:', error);
                            timeSelect.innerHTML = '<option value="">Error loading times</option>';
                            timeSelect.disabled = false;
                        });
                }
            }
            
            // Update summary when selections change
            function updateSummary() {
                const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
                const date = dateInput.value;
                const time = timeSelect.value;
                
                if (selectedService && selectedService.value) {
                    const serviceName = selectedService.textContent.split(' - ')[0];
                    const duration = selectedService.dataset.duration;
                    const price = selectedService.dataset.price;
                    
                    summaryService.textContent = serviceName;
                    summaryDuration.textContent = `${duration} minutes`;
                    summaryPrice.textContent = `$${parseFloat(price).toFixed(2)}`;
                } else {
                    summaryService.textContent = '-';
                    summaryDuration.textContent = '-';
                    summaryPrice.textContent = '-';
                }
                
                if (date && time) {
                    const formattedDate = new Date(date).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    summaryDateTime.textContent = `${formattedDate} at ${time}`;
                } else {
                    summaryDateTime.textContent = '-';
                }
            }
            
            // Event listeners
            dateInput.addEventListener('change', function() {
                updateAvailableTimes();
                updateSummary();
            });
            
            serviceSelect.addEventListener('change', function() {
                updateAvailableTimes();
                updateSummary();
            });
            
            timeSelect.addEventListener('change', updateSummary);
        });
    </script>
</body>
</html>

