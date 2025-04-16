/**
 * New Sidebar Menu Implementation
 * This is a simpler, more direct implementation that replaces the conflicting
 * implementations in sidebar.js and sidebar-fix.js
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('New sidebar script loaded');
    
    // Get the current page from URL
    const currentPageParam = window.location.search.match(/page=([^&]*)/);
    const currentPage = currentPageParam ? currentPageParam[1] : 'dashboard';
    
    // Close all submenus by default
    document.querySelectorAll('.collapse').forEach(submenu => {
        submenu.style.display = 'none';
    });
    
    // Highlight the active link and open its parent submenu if needed
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        // Extract page name from href
        let linkPage = '';
        const hrefAttr = link.getAttribute('href');
        
        if (hrefAttr) {
            const pageMatch = hrefAttr.match(/page=([^&]*)/);
            if (pageMatch) linkPage = pageMatch[1];
        }
        
        // If this is the active page
        if (linkPage === currentPage) {
            link.classList.add('active');
            
            // If this is a submenu item, open its parent
            if (link.classList.contains('submenu-item')) {
                // Find parent collapse
                const parentSubmenu = link.closest('.collapse');
                if (parentSubmenu) {
                    // Show this submenu
                    parentSubmenu.style.display = 'block';
                    
                    // Update the toggle button state
                    const toggleButton = document.querySelector(`[data-bs-target="#${parentSubmenu.id}"]`);
                    if (toggleButton) {
                        toggleButton.setAttribute('aria-expanded', 'true');
                        const icon = toggleButton.querySelector('i.fa-chevron-down');
                        if (icon) icon.style.transform = 'rotate(180deg)';
                    }
                }
            }
        }
    });
    
    // Add click handlers to all collapse buttons
    document.querySelectorAll('.btn-collapse').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find target submenu
            const targetId = this.getAttribute('data-bs-target');
            const submenu = document.querySelector(targetId);
            
            if (!submenu) return;
            
            // Toggle submenu visibility
            const isVisible = submenu.style.display === 'block';
            
            // First close all other submenus
            document.querySelectorAll('.collapse').forEach(menu => {
                // Skip the target submenu
                if (menu.id === submenu.id.replace('#', '')) return;
                
                menu.style.display = 'none';
                
                // Reset all other toggle buttons
                const otherButton = document.querySelector(`[data-bs-target="#${menu.id}"]`);
                if (otherButton) {
                    otherButton.setAttribute('aria-expanded', 'false');
                    const icon = otherButton.querySelector('i.fa-chevron-down');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle the target submenu
            if (isVisible) {
                submenu.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                const icon = this.querySelector('i.fa-chevron-down');
                if (icon) icon.style.transform = 'rotate(0deg)';
            } else {
                submenu.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
                const icon = this.querySelector('i.fa-chevron-down');
                if (icon) icon.style.transform = 'rotate(180deg)';
            }
        });
    });
}); 