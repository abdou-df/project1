// Modal handling
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Handle select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('tbody .checkbox-column input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// File input handling
document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('change', function() {
        const fileName = this.value.split('\\').pop();
        if (fileName) {
            this.closest('.file-input-container').querySelector('.file-name').textContent = fileName;
        } else {
            this.closest('.file-input-container').querySelector('.file-name').textContent = 'No file chosen';
        }
    });
});

// Open modals via buttons
function openEditModal(customerId) {
    document.getElementById('editCustomerId').value = customerId;
    
    // In a real application, you would fetch the customer data from the server
    // For demonstration, we'll use dummy data that matches the PHP array
    const customerData = {
        name: 'Bendial Joseph',
        email: 'bendial.joseph@gmail.com',
        phone: '+1 (123) 456-7890',
        address: '123 Main St, New York, NY 10001',
        status: 'active'
    };
    
    document.getElementById('editName').value = customerData.name;
    document.getElementById('editEmail').value = customerData.email;
    document.getElementById('editPhone').value = customerData.phone;
    document.getElementById('editAddress').value = customerData.address;
    document.getElementById('editStatus').value = customerData.status;
    
    openModal('editCustomerModal');
}

function openDeleteModal(customerId) {
    document.getElementById('deleteCustomerId').value = customerId;
    openModal('deleteCustomerModal');
}

// Add click event listeners to action buttons
document.querySelectorAll('.btn-add').forEach(btn => {
    btn.addEventListener('click', function() {
        openModal('addCustomerModal');
    });
});

// Form submissions
document.getElementById('saveCustomerBtn').addEventListener('click', function() {
    // In a real application, you would submit the form data to the server
    // For demonstration, we'll just close the modal and show an alert
    alert('Customer added successfully!');
    closeModal('addCustomerModal');
    
    // Reset form
    document.getElementById('addCustomerForm').reset();
});

document.getElementById('updateCustomerBtn').addEventListener('click', function() {
    // In a real application, you would submit the form data to the server
    // For demonstration, we'll just close the modal and show an alert
    alert('Customer updated successfully!');
    closeModal('editCustomerModal');
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    // In a real application, you would submit the delete request to the server
    // For demonstration, we'll just close the modal and show an alert
    alert('Customer deleted successfully!');
    closeModal('deleteCustomerModal');
});

// Close modals when clicking on backdrop
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', function() {
        this.parentElement.classList.remove('active');
        document.body.style.overflow = 'auto';
    });
});

// Prevent closing when clicking on modal content
document.querySelectorAll('.modal-content').forEach(content => {
    content.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

// Close modals with escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = 'auto';
    }
});

// Initialize any UI components that need it on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add responsive handling for sidebar
    const toggleSidebarBtn = document.createElement('button');
    toggleSidebarBtn.className = 'toggle-sidebar-btn';
    toggleSidebarBtn.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.top-header').prepend(toggleSidebarBtn);
    
    toggleSidebarBtn.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        document.querySelector('.main-content').classList.toggle('expanded');
    });
    
    // For mobile view, initially collapse sidebar
    if (window.innerWidth < 768) {
        document.querySelector('.sidebar').classList.add('collapsed');
        document.querySelector('.main-content').classList.add('expanded');
    }
});