/**
 * Main JavaScript file for Auto Care Garage Management System
 */

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Handle sidebar collapse buttons
    const collapseButtons = document.querySelectorAll('.collapse-btn');
    collapseButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the target collapse element
            const targetId = this.getAttribute('data-bs-target');
            if (!targetId) return;
            
            const collapseElement = document.querySelector(targetId);
            if (!collapseElement) return;
            
            // Toggle the collapse directly with Bootstrap
            const bsCollapse = new bootstrap.Collapse(collapseElement, {
                toggle: false
            });
            
            if (collapseElement.classList.contains('show')) {
                // If it's open, close it
                bsCollapse.hide();
                this.setAttribute('aria-expanded', 'false');
            } else {
                // If it's closed, open it
                bsCollapse.show();
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
    
    // Toggle sidebar
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
        });
        
        // Check sidebar state on load
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }
    }
    
    // User dropdown menu
    const userDropdown = document.getElementById('userDropdown');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    
    if (userDropdown && userDropdownMenu) {
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (userDropdownMenu.classList.contains('show') && 
                !userDropdownMenu.contains(e.target) && 
                !userDropdown.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Handle submenu toggle in horizontal menu
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Rotate the chevron icon
            this.classList.toggle('rotate-icon');
        });
    });
    
    // Very simple direct submenu toggle implementation
    const submenuToggles2 = document.querySelectorAll('.js-toggle-submenu');
    submenuToggles2.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get target submenu
            const targetId = this.getAttribute('data-target');
            const submenu = document.querySelector(targetId);
            
            if (!submenu) return;
            
            // Simple toggle
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                this.style.transform = 'rotate(0deg)';
            } else {
                submenu.style.display = 'block';
                this.style.transform = 'rotate(180deg)';
            }
        });
    });
    
    // Setup submenus based on active page
    const currentPage = document.location.href.split('page=')[1]?.split('&')[0] || 'dashboard';
    
    // Mark active menu items
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        // Extract page name from href
        const linkPage = link.getAttribute('href')?.split('page=')[1]?.split('&')[0];
        
        // If this is the active page
        if (linkPage === currentPage) {
            link.classList.add('active');
            
            // Find parent submenu if exists
            const parentLi = link.closest('li');
            const parentUl = parentLi?.parentElement;
            
            if (parentUl && parentUl.classList.contains('collapse')) {
                // Show the parent submenu
                parentUl.style.display = 'block';
                
                // Find and rotate the toggle icon
                const parentNavLink = parentUl.previousElementSibling;
                if (parentNavLink) {
                    const toggleIcon = parentNavLink.querySelector('.js-toggle-submenu');
                    if (toggleIcon) {
                        toggleIcon.style.transform = 'rotate(180deg)';
                    }
                }
            }
        }
    });
    
    // Initialize datepicker
    const datepickers = document.querySelectorAll('.datepicker');
    datepickers.forEach(picker => {
        new Datepicker(picker, {
            format: 'yyyy-mm-dd',
            autohide: true
        });
    });
    
    // Initialize select2
    const select2Fields = document.querySelectorAll('.select2');
    select2Fields.forEach(field => {
        new Select2(field, {
            placeholder: field.getAttribute('placeholder') || 'Select an option'
        });
    });
    
    // Handle form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Handle alerts auto-close
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
    
    // Handle confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });
    
    // Handle file inputs
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Choose file';
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    });
    
    // Handle number inputs
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('wheel', function(event) {
            event.preventDefault();
        });
    });
    
    // Handle print buttons
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Handle back buttons
    const backButtons = document.querySelectorAll('.btn-back');
    backButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            window.history.back();
        });
    });
    
    // Handle scroll to top button
    const scrollTopButton = document.querySelector('.scroll-to-top');
    if (scrollTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                scrollTopButton.style.display = 'block';
            } else {
                scrollTopButton.style.display = 'none';
            }
        });
        
        scrollTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Global Functions

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @param {string} currency - Currency code (default: USD)
 * @returns {string} Formatted amount
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Format date
 * @param {string|Date} date - Date to format
 * @param {string} format - Date format (default: yyyy-mm-dd)
 * @returns {string} Formatted date
 */
function formatDate(date, format = 'yyyy-mm-dd') {
    const d = new Date(date);
    return d.toLocaleDateString();
}

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Show toast notification
 * @param {string} message - Message to display
 * @param {string} type - Notification type (success, error, warning, info)
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || document.body;
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Make AJAX request
 * @param {string} url - Request URL
 * @param {Object} options - Request options
 * @returns {Promise} Promise object
 */
async function ajax(url, options = {}) {
    try {
        showLoading();
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message, 'error');
        throw error;
    } finally {
        hideLoading();
    }
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Validate form data
 * @param {HTMLFormElement} form - Form element to validate
 * @returns {boolean} Validation result
 */
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * Export table to CSV
 * @param {HTMLTableElement} table - Table element to export
 * @param {string} filename - Output filename
 */
function exportTableToCSV(table, filename = 'export.csv') {
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ').replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
