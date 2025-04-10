<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-car"></i>
                    <span>Auto Care Garage</span>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="header-buttons">
                <?php if (is_logged_in()): ?>
                    <?php if (is_staff()): ?>
                        <a href="admin/index.php" class="btn btn-sm">Dashboard</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-sm">My Account</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm">Login</a>
                    <a href="register.php" class="btn btn-sm btn-outline">Register</a>
                <?php endif; ?>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
</header>
<div class="mobile-menu">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if (is_logged_in()): ?>
            <?php if (is_staff()): ?>
                <li><a href="admin/index.php">Dashboard</a></li>
            <?php else: ?>
                <li><a href="dashboard.php">My Account</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</div>

