/**
 * User Statistics Dashboard - Dynamic Data Fetching
 * This script handles dynamic updating of user statistics without page refresh
 */

// Function to fetch updated statistics from the server
function fetchUserStats() {
  // Show loading indicators
  document.querySelectorAll('.counter').forEach(counter => {
    counter.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  });
  
  // Fetch updated stats using AJAX
  fetch('ajax/get-user-stats.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update statistics with animation
      updateStatWithAnimation('total_users', data.total_users);
      updateStatWithAnimation('total_active_users', data.total_active_users);
      updateStatWithAnimation('total_inactive_users', data.total_inactive_users);
      updateStatWithAnimation('total_admin_users', data.total_admin_users);
      updateStatWithAnimation('total_employee_users', data.total_employee_users);
      updateStatWithAnimation('total_customer_users', data.total_customer_users);
    })
    .catch(error => {
      console.error('Error fetching user statistics:', error);
      // Restore previous values or show error
      document.querySelectorAll('.counter').forEach(counter => {
        counter.innerHTML = counter.getAttribute('data-original') || '0';
      });
    });
}

// Function to animate counting up to a number
function updateStatWithAnimation(id, targetValue) {
  const counterElement = document.getElementById(id);
  if (!counterElement) return;
  
  // Save original value for potential error recovery
  if (!counterElement.getAttribute('data-original')) {
    counterElement.setAttribute('data-original', counterElement.innerText);
  }
  
  // Get current displayed value
  const startValue = parseInt(counterElement.innerText) || 0;
  const targetNumber = parseInt(targetValue);
  
  // Calculate animation parameters
  const duration = 1500; // milliseconds
  const steps = 60;
  const stepValue = (targetNumber - startValue) / steps;
  const stepTime = duration / steps;
  
  let currentValue = startValue;
  let currentStep = 0;
  
  // Clear any existing animation
  if (counterElement._animationTimer) {
    clearInterval(counterElement._animationTimer);
  }
  
  // Start animation
  counterElement._animationTimer = setInterval(() => {
    currentStep++;
    currentValue += stepValue;
    
    if (currentStep >= steps) {
      clearInterval(counterElement._animationTimer);
      counterElement.textContent = targetNumber;
    } else {
      counterElement.textContent = Math.round(currentValue);
    }
  }, stepTime);
}

// Function to refresh stats on demand
function refreshStats() {
  fetchUserStats();
  
  // Show refresh animation
  const refreshBtn = document.getElementById('refresh-stats-btn');
  if (refreshBtn) {
    refreshBtn.classList.add('rotating');
    setTimeout(() => {
      refreshBtn.classList.remove('rotating');
    }, 1000);
  }
}

// Set up auto-refresh timer (every 5 minutes)
let autoRefreshTimer;
function startAutoRefresh() {
  stopAutoRefresh(); // Clear any existing timer
  autoRefreshTimer = setInterval(fetchUserStats, 5 * 60 * 1000);
}

function stopAutoRefresh() {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer);
  }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  // Store initial values as data attributes
  document.querySelectorAll('.counter').forEach(counter => {
    counter.setAttribute('data-original', counter.innerText);
  });
  
  // Set up refresh button if it exists
  const refreshBtn = document.getElementById('refresh-stats-btn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', refreshStats);
  }
  
  // Start auto-refresh
  startAutoRefresh();
  
  // Stop auto-refresh when page is not visible to save resources
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      stopAutoRefresh();
    } else {
      startAutoRefresh();
    }
  });
});
