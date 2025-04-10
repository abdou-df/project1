<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to access your dashboard.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('admin/index.php');
}

$customer_id = $_SESSION['user_id'];

// Get customer information
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();

// Get customer vehicles
$sql = "SELECT * FROM vehicles WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$vehicles_result = $stmt->get_result();

// Get upcoming appointments
$sql = "SELECT a.*, s.name as service_name, s.price as service_price, 
               v.make, v.model, v.year, v.license_plate 
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        JOIN vehicles v ON a.vehicle_id = v.id 
        WHERE a.customer_id = ? AND a.date >= CURDATE() AND a.status != 'cancelled' 
        ORDER BY a.date, a.start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$upcoming_appointments_result = $stmt->get_result();

// Get past appointments
$sql = "SELECT a.*, s.name as service_name, s.price as service_price, 
               v.make, v.model, v.year, v.license_plate 
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        JOIN vehicles v ON a.vehicle_id = v.id 
        WHERE a.customer_id = ? AND (a.date < CURDATE() OR a.status = 'completed') 
        ORDER BY a.date DESC, a.start_time DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$past_appointments_result = $stmt->get_result();

// Get invoices
$sql = "SELECT i.*, v.make, v.model, v.year, v.license_plate 
        FROM invoices i 
        JOIN vehicles v ON i.vehicle_id = v.id 
        WHERE i.customer_id = ? 
        ORDER BY i.issue_date DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$invoices_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="dashboard-header">
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars($customer['first_name']); ?>!</h1>
            <p>Manage your appointments, vehicles, and account information</p>
        </div>
    </section>
    
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h3>
                            <p><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                    </div>
                    <nav class="dashboard-nav">
                        <ul>
                            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="appointment.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                            <li><a href="vehicles.php"><i class="fas fa-car"></i> My Vehicles</a></li>
                            <li><a href="invoices.php"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
                            <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <?php echo display_message(); ?>
                    
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $vehicles_result->num_rows; ?></h3>
                                <p>Vehicles</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $upcoming_appointments_result->num_rows; ?></h3>
                                <p>Upcoming Appointments</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php 
                                    $sql = "SELECT COUNT(*) as count FROM appointments WHERE customer_id = ? AND (date < CURDATE() OR status = 'completed')";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $customer_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                ?></h3>
                                <p>Past Services</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php 
                                    $sql = "SELECT COUNT(*) as count FROM invoices WHERE customer_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $customer_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                ?></h3>
                                <p>Invoices</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-actions">
                        <a href="appointment.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="action-info">
                                <h3>Book Appointment</h3>
                                <p>Schedule a new service for your vehicle</p>
                            </div>
                        </a>
                        <a href="add-vehicle.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-car-side"></i>
                            </div>
                            <div class="action-info">
                                <h3>Add Vehicle</h3>
                                <p>Register a new vehicle to your account</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="dashboard-section-title">
                        <h2>Upcoming Appointments</h2>
                        <a href="appointments.php" class="btn btn-sm">View All</a>
                    </div>
                    
                    <?php if ($upcoming_appointments_result->num_rows > 0): ?>
                        <div class="appointments-list">
                            <?php while ($appointment = $upcoming_appointments_result->fetch_assoc()): ?>
                                <div class="appointment-card">
                                    <div class="appointment-date">
                                        <div class="date-day"><?php echo date('d', strtotime($appointment['date'])); ?></div>
                                        <div class="date-month"><?php echo date('M', strtotime($appointment['date'])); ?></div>
                                    </div>
                                    <div class="appointment-details">
                                        <h3><?php echo htmlspecialchars($appointment['service_name']); ?></h3>
                                        <p class="vehicle-info">
                                            <i class="fas fa-car"></i> 
                                            <?php echo htmlspecialchars($appointment['year'] . ' ' . $appointment['make'] . ' ' . $appointment['model']); ?>
                                        </p>
                                        <p class="time-info">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('h:i A', strtotime($appointment['start_time'])); ?> - 
                                            <?php echo date('h:i A', strtotime($appointment['end_time'])); ?>
                                        </p>
                                        <div class="appointment-status status-<?php echo strtolower($appointment['status']); ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm">Details</a>
                                        <?php if ($appointment['status'] == 'scheduled'): ?>
                                            <a href="cancel-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <h3>No Upcoming Appointments</h3>
                            <p>You don't have any upcoming appointments scheduled.</p>
                            <a href="appointment.php" class="btn btn-primary">Book an Appointment</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="dashboard-section-title">
                        <h2>My Vehicles</h2>
                        <a href="vehicles.php" class="btn btn-sm">View All</a>
                    </div>
                    
                    <?php if ($vehicles_result->num_rows > 0): ?>
                        <div class="vehicles-grid">
                            <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                                <div class="vehicle-card">
                                    <div class="vehicle-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="vehicle-info">
                                        <h3><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                                        <p class="license-plate"><?php echo htmlspecialchars($vehicle['license_plate']); ?></p>
                                        <div class="vehicle-details">  ?></p>
                                        <div class="vehicle-details">
                                            <p><i class="fas fa-tachometer-alt"></i> <?php echo number_format($vehicle['mileage']); ?> miles</p>
                                            <p><i class="fas fa-palette"></i> <?php echo htmlspecialchars($vehicle['color']); ?></p>
                                            <p><i class="fas fa-cog"></i> <?php echo ucfirst($vehicle['transmission']); ?></p>
                                        </div>
                                        <div class="vehicle-actions">
                                            <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm">Edit</a>
                                            <a href="vehicle-history.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-outline">Service History</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <div class="vehicle-card add-vehicle-card">
                                <a href="add-vehicle.php">
                                    <div class="add-icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <h3>Add New Vehicle</h3>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-car-side"></i>
                            </div>
                            <h3>No Vehicles</h3>
                            <p>You haven't added any vehicles to your account yet.</p>
                            <a href="add-vehicle.php" class="btn btn-primary">Add a Vehicle</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="dashboard-section-title">
                        <h2>Recent Invoices</h2>
                        <a href="invoices.php" class="btn btn-sm">View All</a>
                    </div>
                    
                    <?php if ($invoices_result->num_rows > 0): ?>
                        <div class="invoices-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($invoice = $invoices_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                            <td><?php echo format_date($invoice['issue_date']); ?></td>
                                            <td><?php echo htmlspecialchars($invoice['year'] . ' ' . $invoice['make'] . ' ' . $invoice['model']); ?></td>
                                            <td><?php echo format_currency($invoice['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($invoice['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view-invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm">View</a>
                                                <?php if ($invoice['status'] != 'paid'): ?>
                                                    <a href="pay-invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline">Pay</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h3>No Invoices</h3>
                            <p>You don't have any invoices yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

