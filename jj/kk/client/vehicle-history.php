<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to view vehicle history.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can access this page.', 'error');
}

// Check if vehicle ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('vehicles.php', 'Invalid vehicle ID.', 'error');
}

$vehicle_id = intval($_GET['id']);
$customer_id = $_SESSION['user_id'];

// Check if the vehicle belongs to the customer
$sql = "SELECT * FROM vehicles WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $vehicle_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('vehicles.php', 'Vehicle not found.', 'error');
}

$vehicle = $result->fetch_assoc();

// Get service history
$sql = "SELECT a.*, s.name as service_name, s.price as service_price 
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        WHERE a.vehicle_id = ? 
        ORDER BY a.date DESC, a.start_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$appointments_result = $stmt->get_result();

// Get invoices
$sql = "SELECT i.* 
        FROM invoices i 
        WHERE i.vehicle_id = ? 
        ORDER BY i.issue_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$invoices_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle History - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Vehicle Service History</h1>
            <p>View service history for your vehicle</p>
        </div>
    </section>
    
    <section class="vehicle-history-section">
        <div class="container">
            <?php echo display_message(); ?>
            
            <div class="vehicle-info-card">
                <div class="vehicle-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="vehicle-details">
                    <h2><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></h2>
                    <div class="vehicle-meta">
                        <span class="license-plate"><?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                        <?php if (!empty($vehicle['vin'])): ?>
                            <span class="vin">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="vehicle-specs">
                        <?php if (!empty($vehicle['mileage'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span><?php echo number_format($vehicle['mileage']); ?> miles</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($vehicle['color'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-palette"></i>
                                <span><?php echo htmlspecialchars($vehicle['color']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($vehicle['transmission'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-cog"></i>
                                <span><?php echo ucfirst($vehicle['transmission']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($vehicle['fuel_type'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-gas-pump"></i>
                                <span><?php echo ucfirst($vehicle['fuel_type']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="vehicle-actions">
                    <a href="edit-vehicle.php?id=<?php echo $vehicle_id; ?>" class="btn btn-sm">Edit Vehicle</a>
                    <a href="vehicles.php" class="btn btn-sm btn-outline">Back to Vehicles</a>
                </div>
            </div>
            
            <div class="history-tabs">
                <div class="tab-header">
                    <button class="tab-btn active" data-tab="services">Service History</button>
                    <button class="tab-btn" data-tab="invoices">Invoices</button>
                </div>
                
                <div class="tab-content active" id="services-tab">
                    <h3>Service History</h3>
                    
                    <?php if ($appointments_result->num_rows > 0): ?>
                        <div class="service-history-list">
                            <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                <div class="service-history-item">
                                    <div class="service-date">
                                        <div class="date-day"><?php echo date('d', strtotime($appointment['date'])); ?></div>
                                        <div class="date-month"><?php echo date('M', strtotime($appointment['date'])); ?></div>
                                        <div class="date-year"><?php echo date('Y', strtotime($appointment['date'])); ?></div>
                                    </div>
                                    <div class="service-details">
                                        <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
                                        <div class="service-meta">
                                            <span class="service-time">
                                                <i class="fas fa-clock"></i> 
                                                <?php echo date('h:i A', strtotime($appointment['start_time'])); ?>
                                            </span>
                                            <span class="service-price">
                                                <i class="fas fa-dollar-sign"></i> 
                                                <?php echo format_currency($appointment['service_price']); ?>
                                            </span>
                                            <span class="service-status status-<?php echo strtolower($appointment['status']); ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($appointment['notes'])): ?>
                                            <div class="service-notes">
                                                <strong>Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="service-actions">
                                        <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm">Details</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3>No Service History</h3>
                            <p>This vehicle doesn't have any service history yet.</p>
                            <a href="appointment.php" class="btn btn-primary">Book a Service</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="invoices-tab">
                    <h3>Invoices</h3>
                    
                    <?php if ($invoices_result->num_rows > 0): ?>
                        <div class="invoices-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
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
                                            <td><?php echo format_currency($invoice['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($invoice['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view-invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm">View</a>
                                                <?php if (in_array($invoice['status'], ['unpaid', 'partially_paid', 'overdue'])): ?>
                                                    <a href="pay-invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary">Pay</a>
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
                            <p>This vehicle doesn't have any invoices yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and content
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>

