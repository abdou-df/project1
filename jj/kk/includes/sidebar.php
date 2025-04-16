<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="assets/images/logo.png" alt="Garage Master">
            <div>
                <div class="brand-text">GARAGE</div>
                <div class="brand-subtext">MASTER</div>
            </div>
        </div>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav-item">
            <li>
                <a href="index.php?page=dashboard" class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=inventory" class="nav-link <?php echo ($page == 'inventory') ? 'active' : ''; ?>">
                    <div>
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span class="nav-text">Inventory</span>
                    </div>
                </a>
            </li>
            
            <li>
                <a href="#" class="nav-link d-flex align-items-center justify-content-between <?php echo (in_array($page, ['users', 'employees', 'customers'])) ? 'active' : ''; ?>" 
                   onclick="window.location.href='index.php?page=users'; return false;">
                    <div>
                        <i class="fa-solid fa-users"></i>
                        <span class="nav-text">Users</span>
                    </div>
                    <button class="btn-collapse ms-2" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#usersSubmenu" 
                            aria-expanded="<?php echo (in_array($page, ['users', 'employees', 'customers'])) ? 'true' : 'false'; ?>">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </a>
                <div class="collapse <?php echo (in_array($page, ['users', 'employees', 'customers'])) ? 'show' : ''; ?>" id="usersSubmenu">
                    <ul class="nav-submenu">
                        <li>
                            <a class="submenu-item" href="index.php?page=users"><i class="fa-solid fa-circle fa-xs me-2"></i><span>All Users</span></a>
                        </li>
                        <li>
                            <a class="submenu-item" href="index.php?page=employees"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Employees</span></a>
                        </li>
                        <li>
                            <a class="submenu-item" href="index.php?page=customers"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Customers</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li>
                <a href="index.php?page=vehicles" class="nav-link <?php echo (in_array($page, ['vehicles', 'vehicle-details'])) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-car"></i>
                    <span class="nav-text">Vehicles</span>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=services" class="nav-link <?php echo ($page == 'services') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-wrench"></i>
                    <span class="nav-text">Services</span>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=quotation" class="nav-link <?php echo ($page == 'quotation') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span class="nav-text">Quotation</span>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=invoices" class="nav-link <?php echo ($page == 'invoices') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="nav-text">Invoices</span>
                </a>
            </li>
            
            <li>
                <a href="#" class="nav-link d-flex align-items-center justify-content-between <?php echo (in_array($page, ['job-card'])) ? 'active' : ''; ?>" 
                   onclick="window.location.href='index.php?page=job-card'; return false;">
                    <div>
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span class="nav-text">Job Card</span>
                    </div>
                </a>
                <div class="collapse <?php echo (in_array($page, ['job-card'])) ? 'show' : ''; ?>" id="jobCardSubmenu">
             
            </li>
            
            <li>
                <a href="#" class="nav-link d-flex align-items-center justify-content-between <?php echo (in_array($page, ['accounts'])) ? 'active' : ''; ?>" 
                   onclick="window.location.href='index.php?page=accounts'; return false;">
                    <div>
                        <i class="fa-solid fa-calculator"></i>
                        <span class="nav-text">Accounts & Tax</span>
                    </div>
                    <button class="btn-collapse ms-2" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#accountsSubmenu" 
                            aria-expanded="<?php echo (in_array($page, ['accounts'])) ? 'true' : 'false'; ?>">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </a>
                <div class="collapse <?php echo (in_array($page, ['accounts'])) ? 'show' : ''; ?>" id="accountsSubmenu">
                    <ul class="nav-submenu">
                        <li>
                            <a class="submenu-item" href="index.php?page=accounts&view=income"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Income</span></a>
                        </li>
                        <li>
                            <a class="submenu-item" href="index.php?page=accounts&view=expense"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Expense</span></a>
                        </li>
                        <li>
                            <a class="submenu-item" href="index.php?page=accounts&view=tax"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Tax Rates</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li>
                <a href="#" class="nav-link d-flex align-items-center justify-content-between <?php echo (in_array($page, ['sales'])) ? 'active' : ''; ?>" 
                   onclick="window.location.href='index.php?page=sales'; return false;">
                    <div>
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="nav-text">Sales</span>
                    </div>
                    <button class="btn-collapse ms-2" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#salesSubmenu" 
                            aria-expanded="<?php echo (in_array($page, ['sales'])) ? 'true' : 'false'; ?>">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </a>
                <div class="collapse <?php echo (in_array($page, ['sales'])) ? 'show' : ''; ?>" id="salesSubmenu">
                    <ul class="nav-submenu">
                        <li>
                            <a class="submenu-item" href="index.php?page=sales&type=vehicle"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Vehicle Sales</span></a>
                        </li>
                        <li>
                            <a class="submenu-item" href="index.php?page=sales&type=part"><i class="fa-solid fa-circle fa-xs me-2"></i><span>Part Sales</span></a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li>
                <a href="index.php?page=compliances" class="nav-link <?php echo ($page == 'compliances') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span class="nav-text">Compliances</span>
                </a>
            </li>
            
           
            
            <li>
                <a href="index.php?page=email-templates" class="nav-link <?php echo ($page == 'email-templates') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="nav-text">Email Templates</span>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=custom-fields" class="nav-link <?php echo ($page == 'custom-fields') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-sliders"></i>
                    <span class="nav-text">Custom Fields</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer mt-auto p-3">
        <a href="logout.php" class="btn btn-light btn-sm w-100">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</div>
