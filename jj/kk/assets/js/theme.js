/**
 * Global Theme System - Light/Dark Mode
 * This script handles theme switching functionality across the entire website
 */
document.addEventListener('DOMContentLoaded', function() {
    // Apply theme function
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.remove('light-mode');
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else if (theme === 'light') {
            document.body.classList.remove('dark-mode');
            document.body.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
        } else if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                document.body.classList.remove('light-mode');
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
                document.body.classList.add('light-mode');
            }
            localStorage.setItem('theme', 'auto');
        }
    }

    // Set initial theme based on localStorage or system preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    // Listen for theme changes from settings page
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            applyTheme(e.newValue);
        }
    });

    // Listen for system preference changes if in auto mode
    if (savedTheme === 'auto') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (localStorage.getItem('theme') === 'auto') {
                applyTheme('auto');
            }
        });
    }

    // Theme toggle functionality (if toggle exists on the current page)
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            if (currentTheme === 'light' || currentTheme === 'auto' && !window.matchMedia('(prefers-color-scheme: dark)').matches) {
                applyTheme('dark');
                showToast('Dark theme applied');
            } else {
                applyTheme('light');
                showToast('Light theme applied');
            }
        });
    }

    // Settings page specific theme controls
    const lightMode = document.getElementById('light_mode');
    const darkMode = document.getElementById('dark_mode');
    const autoMode = document.getElementById('auto_mode');
    
    if (lightMode && darkMode && autoMode) {
        lightMode.addEventListener('change', function() {
            if (this.checked) {
                applyTheme('light');
                showToast('Light theme applied');
            }
        });
        
        darkMode.addEventListener('change', function() {
            if (this.checked) {
                applyTheme('dark');
                showToast('Dark theme applied');
            }
        });
        
        autoMode.addEventListener('change', function() {
            if (this.checked) {
                applyTheme('auto');
                showToast('Auto theme mode enabled');
            }
        });
        
        // Set initial radio button state based on current theme
        if (savedTheme === 'light') {
            lightMode.checked = true;
        } else if (savedTheme === 'dark') {
            darkMode.checked = true;
        } else {
            autoMode.checked = true;
        }
    }

    // Toast notification function
    window.showToast = function(message) {
        // Create toast element if it doesn't exist
        let toast = document.getElementById('settings-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'settings-toast';
            toast.className = 'settings-toast';
            document.body.appendChild(toast);
        }
        
        // Show toast with message
        toast.textContent = message;
        toast.classList.add('show');
        
        // Hide toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    };
});
