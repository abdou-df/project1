<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get all active services
$sql = "SELECT * FROM services WHERE status = 'active' ORDER BY name";
$services_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Auto Care Garage</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-header">
        <div class="container">
            <h1>Our Services</h1>
            <p>Professional auto care solutions for your vehicle</p>
        </div>
    </section>
    
    <section class="services-section">
        <div class="container">
            <div class="services-intro">
                <div class="section-title">
                    <h2>What We Offer</h2>
                    <p>Comprehensive auto care services for all makes and models</p>
                </div>
                <p class="intro-text">At Auto Care Garage, we provide a wide range of automotive services to keep your vehicle running smoothly. Our certified technicians use state-of-the-art equipment and quality parts to ensure the best results for your car.</p>
            </div>
            
            <div class="services-list">
                <?php if ($services_result->num_rows > 0): ?>
                    <?php while ($service = $services_result->fetch_assoc()): ?>
                        <div class="service-item" id="service-<?php echo $service['id']; ?>">
                            <div class="service-icon">
                                <?php
                                // Assign icons based on service name
                                $icon = 'fa-wrench';
                                if (stripos($service['name'], 'oil') !== false) {
                                    $icon = 'fa-oil-can';
                                } elseif (stripos($service['name'], 'brake') !== false) {
                                    $icon = 'fa-brake-warning';
                                } elseif (stripos($service['name'], 'tire') !== false) {
                                    $icon = 'fa-tire';
                                } elseif (stripos($service['name'], 'engine') !== false) {
                                    $icon = 'fa-engine';
                                } elseif (stripos($service['name'], 'ac') !== false) {
                                    $icon = 'fa-snowflake';
                                } elseif (stripos($service['name'], 'battery') !== false) {
                                    $icon = 'fa-car-battery';
                                } elseif (stripos($service['name'], 'wheel') !== false) {
                                    $icon = 'fa-dharmachakra';
                                } elseif (stripos($service['name'], 'transmission') !== false) {
                                    $icon = 'fa-gears';
                                }
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="service-content">
                                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <div class="service-meta">
                                    <div class="service-price"><?php echo format_currency($service['price']); ?></div>
                                    <div class="service-duration"><i class="fas fa-clock"></i> <?php echo $service['duration']; ?> minutes</div>
                                </div>
                                <a href="appointment.php?service=<?php echo $service['id']; ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>No Services Available</h3>
                        <p>We're currently updating our service offerings. Please check back soon.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to get your car serviced?</h2>
                <p>Book an appointment today and experience our quality service</p>
                <a href="appointment.php" class="btn btn-light">Book Appointment</a>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

