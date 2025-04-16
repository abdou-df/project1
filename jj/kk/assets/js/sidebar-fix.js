// Direct Sidebar Menu Implementation
document.addEventListener('DOMContentLoaded', function() {
    /* Commenting out manual collapse button click handler
    // Find all collapse buttons
    const collapseButtons = document.querySelectorAll('.btn-collapse');
    
    // Add click handler to each button
    collapseButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find the target submenu
            const targetId = this.getAttribute('data-bs-target');
            const submenu = document.querySelector(targetId);
            
            if (!submenu) return;
            
            // Toggle the submenu visibility
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                this.querySelector('.fas').style.transform = 'rotate(0deg)';
            } else {
                submenu.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
                this.querySelector('.fas').style.transform = 'rotate(180deg)';
            }
        });
    });
    */
    
    /* // Commenting out auto-open logic
    // Auto-open submenus based on current page
    const currentPage = window.location.href.split('page=')[1]?.split('&')[0] || 'dashboard';
    
    // Set initial state for submenus based on active page
    document.querySelectorAll('.collapse').forEach(submenu => {
        // By default, hide all submenus
        submenu.style.display = 'none';
        
        // Check if any child links in this submenu match the current page
        const submenuLinks = submenu.querySelectorAll('a');
        let isActive = false;
        
        submenuLinks.forEach(link => {
            const linkPage = link.getAttribute('href')?.split('page=')[1]?.split('&')[0];
            if (linkPage === currentPage) {
                isActive = true;
                link.classList.add('active');
            }
        });
        
        // Also check if the parent link is active
        const parentLink = submenu.previousElementSibling;
        if (parentLink) {
            const parentLinkPage = parentLink.getAttribute('onclick')?.split("'index.php?page=")[1]?.split("'")[0];
            if (parentLinkPage === currentPage) {
                isActive = true;
                parentLink.classList.add('active');
            }
        }
        
        // If active, show this submenu
        if (isActive) {
            submenu.style.display = 'block';
            
            // Update the toggle button state
            const toggleButton = document.querySelector(`[data-bs-target="#${submenu.id}"]`);
            if (toggleButton) {
                toggleButton.setAttribute('aria-expanded', 'true');
                toggleButton.querySelector('.fas').style.transform = 'rotate(180deg)';
            }
        }
    });
    */
});
