// Sidebar Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sidebar script loaded');
    
    // Initialize all Bootstrap collapse elements
    var collapseElementList = [].slice.call(document.querySelectorAll('.sidebar .collapse'));
    var collapseList = collapseElementList.map(function(collapseEl) {
        return new bootstrap.Collapse(collapseEl, {
            toggle: false
        });
    });
    
    // Track current page for highlighting active menu items
    const currentPage = window.location.href.split('page=')[1]?.split('&')[0] || 'dashboard';
    console.log('Current page:', currentPage);
    
    // Set active class
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(function(link) {
        const linkPage = link.getAttribute('href')?.split('page=')[1]?.split('&')[0];
        if (linkPage === currentPage) {
            link.classList.add('active');
            console.log('Set active class for', linkPage);
            
            /* Commenting out manual parent collapse expansion
            // If this is a child menu item, ensure parent is expanded
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                console.log('Found parent collapse for active item:', parentCollapse.id);
                
                // Show the parent submenu
                var collapse = bootstrap.Collapse.getInstance(parentCollapse);
                if (collapse) {
                    collapse.show();
                    const toggleBtn = document.querySelector('[data-bs-target="#' + parentCollapse.id + '"]');
                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-expanded', 'true');
                    }
                }
            }
            */
        }
    });
    
    // Add click handler to toggle buttons to rotate arrow
    document.querySelectorAll('.btn-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            console.log('Toggle button clicked');
        });
    });
});
