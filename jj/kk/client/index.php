<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Care Garage - Professional Car Repair Services</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Professional Auto Repair Services</h1>
                <p>Trust your vehicle with our expert mechanics</p>
                <div class="hero-buttons">
                    <a href="services.php" class="btn btn-primary">Our Services</a>
                    <a href="appointment.php" class="btn btn-secondary">Book Appointment</a>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Us</h2>
                <p>We provide quality service for your vehicle</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Expert Mechanics</h3>
                    <p>Our certified technicians have years of experience in all types of vehicle repair.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Quick Service</h3>
                    <p>We value your time and strive to provide efficient and timely service.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>Fair Pricing</h3>
                    <p>Transparent pricing with no hidden fees or unexpected charges.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Quality Guarantee</h3>
                    <p>All our repairs come with a satisfaction guarantee for your peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="services-preview">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Comprehensive auto care solutions</p>
            </div>
            <div class="services-grid">
                <?php
                // Fetch services from the database
                $sql = "SELECT * FROM services WHERE status = 'active' LIMIT 6";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="service-card">';
                        echo '<div class="service-icon">';
                        
                        // Assign icons based on service name
                        $icon = 'fa-wrench';
                        if (stripos($row['name'], 'oil') !== false) {
                            $icon = 'fa-oil-can';
                        } elseif (stripos($row['name'], 'brake') !== false) {
                            $icon = 'fa-brake-warning';
                        } elseif (stripos($row['name'], 'tire') !== false) {
                            $icon = 'fa-tire';
                        } elseif (stripos($row['name'], 'engine') !== false) {
                            $icon = 'fa-engine';
                        } elseif (stripos($row['name'], 'ac') !== false) {
                            $icon = 'fa-snowflake';
                        } elseif (stripos($row['name'], 'battery') !== false) {
                            $icon = 'fa-car-battery';
                        } elseif (stripos($row['name'], 'wheel') !== false) {
                            $icon = 'fa-dharmachakra';
                        } elseif (stripos($row['name'], 'transmission') !== false) {
                            $icon = 'fa-gears';
                        }
                        
                        echo '<i class="fas ' . $icon . '"></i>';
                        echo '</div>';
                        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                        echo '<div class="service-price">$' . htmlspecialchars($row['price']) . '</div>';
                        echo '<a href="appointment.php?service=' . $row['id'] . '" class="btn btn-sm">Book Now</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No services available at the moment.</p>';
                }
                ?>
            </div>
            <div class="view-all">
                <a href="services.php" class="btn btn-outline">View All Services</a>
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

    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Customers Say</h2>
                <p>Trusted by car owners across the city</p>
            </div>
            <div class="testimonials-slider">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"The team at Auto Care Garage did an amazing job with my car. Fast service and reasonable prices!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="images/customer1.jpg" alt="Customer">
                        </div>
                        <div class="author-info">
                            <h4>John Smith</h4>
                            <p>Satisfied Customer</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"I've been bringing my vehicles here for years. They're always honest about what needs to be fixed and what can wait."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="images/customer2.jpg" alt="Customer">
                        </div>
                        <div class="author-info">
                            <h4>Sarah Williams</h4>
                            <p>Loyal Customer</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"The online booking system is so convenient! I was able to schedule my appointment and get my car fixed the same day."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="images/customer3.jpg" alt="Customer">
                        </div>
                        <div class="author-info">
                            <h4>Michael Brown</h4>
                            <p>New Customer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>

