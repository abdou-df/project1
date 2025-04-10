document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.settings-sidebar');
    const content = document.querySelector('.settings-content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const settingsTabs = document.getElementById('settingsTabs');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // 1. Sidebar Toggle for Mobile
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar if clicking outside on mobile
    document.addEventListener('click', (event) => {
        if (sidebar && sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    // 2. Theme Selection (Light/Dark/Auto) & Accent Color
    const themeRadios = document.querySelectorAll('input[name="theme_mode"]');
    const colorOptions = document.querySelectorAll('.color-option');

    function applyTheme(theme) {
        if (theme === 'dark' || (theme === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark'); // Ensure body class is also set
        } else {
            document.documentElement.classList.remove('dark');
            document.body.classList.remove('dark'); // Ensure body class is also removed
        }
        localStorage.setItem('theme', theme); // Save preference
    }

    function applyAccentColor(color) {
        document.documentElement.style.setProperty('--primary-color', color);
        // Optional: Adjust related colors like a darker shade
        // document.documentElement.style.setProperty('--primary-dark', adjustColor(color, -20)); 
        localStorage.setItem('accent-color', color); // Save preference

        // Update active state on color options
        colorOptions.forEach(opt => {
            opt.classList.toggle('active', opt.getAttribute('data-color') === color);
        });
    }

    // Helper to adjust color brightness (optional)
    // function adjustColor(color, amount) { ... }

    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            applyTheme(this.value);
        });
    });

    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            applyAccentColor(color);
            // Optional: Update a hidden input if saving server-side
            // const accentInput = document.getElementById('accent_color_input');
            // if(accentInput) accentInput.value = color; 
        });
    });

    // Initial theme and color application (already handled by inline script in <head>)
    // const savedTheme = localStorage.getItem('theme') || 'light'; // Get preference or default
    // const savedAccentColor = localStorage.getItem('accent-color') || '#4361ee'; 
    // applyTheme(savedTheme);
    // applyAccentColor(savedAccentColor);
    // Ensure UI controls match saved state (handled by PHP outputting JSON)

    // 3. File Upload Preview
    const fileInputs = document.querySelectorAll('.file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(event) {
            const previewContainer = this.closest('.file-upload').querySelector('.file-preview');
            const file = event.target.files[0];
            if (file && previewContainer) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview"><span>${file.name}</span>`;
                }
                reader.readAsDataURL(file);
            }
        });
    });

    // 4. Password Visibility Toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // 5. Update URL on Tab Click (Simulating Page Change)
    if (settingsTabs) {
        const tabButtons = settingsTabs.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(event) { // Use Bootstrap's event
                const section = event.target.getAttribute('data-section');
                if (section) {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('section', section);
                    // Update URL without reloading the page
                    history.pushState({path: currentUrl.href}, '', currentUrl.href);
                }
            });
        });
    }

    // 6. Handle Form Submissions (Example: Prevent default if using AJAX)
    // const settingsForms = document.querySelectorAll('.settings-content form');
    // settingsForms.forEach(form => {
    //     form.addEventListener('submit', function(event) {
    //         // If using AJAX for form submission, prevent default
    //         // event.preventDefault(); 
    //         // console.log('Form submitted:', this.id);
    //         // Add AJAX submission logic here
    //     });
    // });

    console.log('Settings JS Initialized');
}); 