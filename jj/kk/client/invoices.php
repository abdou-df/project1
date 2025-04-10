<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', 'Please login to view your invoices.', 'info');
}

// Check if user is a customer
if (!is_customer()) {
    redirect('index.php', 'Only customers can access this page.', 'error');
}

$customer_id = $_SESSION['user_id'];

// Get invoices
$sql = "SELECT i.*, v.make, v.model, v.year, v.license_plate 
        FROM invoices i 
        JOIN vehicles v ON i.vehicle_id = v.id 
        WHERE i.customer_id = ? 
        ORDER BY i.issue_date DESC";
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
    <title>My Invoices - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>My Invoices</h1>
            <p>View and manage your invoices</p>
        </div>
    </section>
    
    <section class="invoices-section">
        <div class="container">
            <?php echo display_message(); ?>
            
            <?php if ($invoices_result->num_rows > 0): ?>
                <div class="invoices-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Service</th>
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
                                    <td>
                                        <?php
                                        // Get service name if appointment_id exists
                                        if (!empty($invoice['appointment_id'])) {
                                            $sql = "SELECT s.name FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $invoice['appointment_id']);
                                            $stmt->execute();
                                            $service_result = $stmt->get_result();
                                            if ($service_result->num_rows > 0) {
                                                $service = $service_result->fetch_assoc();
                                                echo htmlspecialchars($service['name']);
                                            } else {
                                                echo "Service";
                                            }
                                        } else {
                                            echo "Service";
                                        }
                                        ?>
                                    </td>
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
                    <p>You don't have any invoices yet.</p>
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

