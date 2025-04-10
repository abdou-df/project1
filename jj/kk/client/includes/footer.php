<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <div class="footer-logo">
                    <i class="fas fa-car"></i>
                    <span>Auto Care Garage</span>
                </div>
                <p>Professional auto repair services you can trust.</p>
                <div class="footer-contact">
                    <div><i class="fas fa-map-marker-alt"></i> <?php echo $settings['garage_address'] ?? '123 Repair Street, Fixitville, CA 12345'; ?></div>
                    <div><i class="fas fa-phone"></i> <?php echo $settings['garage_phone'] ?? '555-123-4567'; ?></div>
                    <div><i class="fas fa-envelope"></i> <?php echo $settings['garage_email'] ?? 'info@autocare.com'; ?></div>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="appointment.php">Book Appointment</a></li>
                </ul>
            </div>
            <div class="footer-services">
                <h3>Our Services</h3>
                <ul>
                    <?php
                    // Fetch services from the database
                    $sql = "SELECT id, name FROM services WHERE status = 'active' LIMIT 5";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<li><a href="services.php#service-' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            <div class="footer-hours">
                <h3>Business Hours</h3>
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
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Auto Care Garage. All rights reserved.
            </div>
        </div>
    </div>
</footer>

