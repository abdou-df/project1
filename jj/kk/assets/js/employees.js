// Modern Employees Management Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const overlay = document.getElementById('overlay');
    const addEmployeeModal = document.getElementById('addEmployeeModal');
    const editEmployeeModal = document.getElementById('editEmployeeModal');
    const deleteEmployeeModal = document.getElementById('deleteEmployeeModal');
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const closeAddModal = document.getElementById('closeAddModal');
    const cancelAddBtn = document.getElementById('cancelAddBtn');
    const saveEmployeeBtn = document.getElementById('saveEmployeeBtn');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const updateEmployeeBtn = document.getElementById('updateEmployeeBtn');
    const closeDeleteModal = document.getElementById('closeDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const viewToggleBtns = document.querySelectorAll('.view-btn');
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const selectAll = document.getElementById('selectAll');
    const fileInputs = document.querySelectorAll('input[type="file"]');
    const editBtns = document.querySelectorAll('.edit-btn');
    const deleteBtns = document.querySelectorAll('.delete-btn');
    const paginationBtns = document.querySelectorAll('.pagination-btn');

    // View Toggle
    viewToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Remove active class from all buttons
            viewToggleBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show the selected view
            if (view === 'card') {
                cardView.style.display = 'grid';
                tableView.style.display = 'none';
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
            }
        });
    });

    // File Input Handling
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            const fileNameElement = this.parentElement.querySelector('.file-name');
            if (fileNameElement) {
                fileNameElement.textContent = fileName;
            }
        });
    });

    // Modal Functions
    function openModal(modal) {
        overlay.style.display = 'block';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        overlay.style.display = 'none';
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Add Employee Modal
    addEmployeeBtn.addEventListener('click', function() {
        openModal(addEmployeeModal);
    });

    closeAddModal.addEventListener('click', function() {
        closeModal(addEmployeeModal);
    });

    cancelAddBtn.addEventListener('click', function() {
        closeModal(addEmployeeModal);
    });

    saveEmployeeBtn.addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal and show a notification
        closeModal(addEmployeeModal);
        showNotification('Employee added successfully!', 'success');
    });

    // Edit Employee Modal
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-id');
            document.getElementById('editEmployeeId').value = employeeId;
            
            // In a real application, you would fetch the employee data from the server
            // For demonstration, we'll use dummy data
            // This would be replaced with an AJAX call to get the employee data
            
            // Simulate fetching employee data
            const dummyEmployee = {
                id: employeeId,
                name: 'John Smith',
                email: 'john.smith@garage.com',
                phone: '+1 (123) 456-7890',
                position: 'Manager',
                department: 'Administration',
                join_date: '2020-01-15',
                salary: 5000.00,
                status: 'active'
            };
            
            // Populate the form
            document.getElementById('editName').value = dummyEmployee.name;
            document.getElementById('editEmail').value = dummyEmployee.email;
            document.getElementById('editPhone').value = dummyEmployee.phone;
            document.getElementById('editPosition').value = dummyEmployee.position;
            document.getElementById('editDepartment').value = dummyEmployee.department;
            document.getElementById('editJoinDate').value = dummyEmployee.join_date;
            document.getElementById('editSalary').value = dummyEmployee.salary;
            document.getElementById('editStatus').value = dummyEmployee.status;
            
            openModal(editEmployeeModal);
        });
    });

    closeEditModal.addEventListener('click', function() {
        closeModal(editEmployeeModal);
    });

    cancelEditBtn.addEventListener('click', function() {
        closeModal(editEmployeeModal);
    });

    updateEmployeeBtn.addEventListener('click', function() {
        // In a real application, you would submit the form data to the server
        // For demonstration, we'll just close the modal and show a notification
        closeModal(editEmployeeModal);
        showNotification('Employee updated successfully!', 'success');
    });

    // Delete Employee Modal
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-id');
            document.getElementById('deleteEmployeeId').value = employeeId;
            openModal(deleteEmployeeModal);
        });
    });

    closeDeleteModal.addEventListener('click', function() {
        closeModal(deleteEmployeeModal);
    });

    cancelDeleteBtn.addEventListener('click', function() {
        closeModal(deleteEmployeeModal);
    });

    confirmDeleteBtn.addEventListener('click', function() {
        // In a real application, you would submit the delete request to the server
        // For demonstration, we'll just close the modal and show a notification
        closeModal(deleteEmployeeModal);
        showNotification('Employee deleted successfully!', 'warning');
    });

    // Close modals when clicking on overlay
    overlay.addEventListener('click', function() {
        closeModal(addEmployeeModal);
        closeModal(editEmployeeModal);
        closeModal(deleteEmployeeModal);
    });

    // Select All Checkbox
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Pagination
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            
            const page = this.getAttribute('data-page');
            if (page) {
                // In a real application, you would navigate to the page
                // For demonstration, we'll just show a notification
                showNotification(`Navigating to page ${page}`, 'info');
            }
        });
    });

    // Notification System
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(notification);
        
        // Add styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            .notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 1rem;
                border-radius: var(--radius);
                background-color: white;
                box-shadow: var(--shadow-lg);
                z-index: 1100;
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                max-width: 450px;
                animation: slideIn 0.3s ease, fadeOut 0.5s ease 4.5s forwards;
                border-left: 4px solid var(--primary-color);
            }
            
            .notification.success {
                border-left-color: var(--success-color);
            }
            
            .notification.warning {
                border-left-color: var(--warning-color);
            }
            
            .notification.error {
                border-left-color: var(--danger-color);
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .notification-content i {
                font-size: 1.25rem;
            }
            
            .notification.info i {
                color: var(--primary-color);
            }
            
            .notification.success i {
                color: var(--success-color);
            }
            
            .notification.warning i {
                color: var(--warning-color);
            }
            
            .notification.error i {
                color: var(--danger-color);
            }
            
            .notification-close {
                background: transparent;
                border: none;
                color: var(--gray-500);
                cursor: pointer;
                font-size: 0.875rem;
            }
            
            .notification-close:hover {
                color: var(--gray-900);
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                    visibility: hidden;
                }
            }
        `;
        
        document.head.appendChild(style);
        
        // Close notification on click
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function() {
            notification.remove();
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    function getNotificationIcon(type) {
        switch (type) {
            case 'success':
                return 'fa-check-circle';
            case 'warning':
                return 'fa-exclamation-triangle';
            case 'error':
                return 'fa-times-circle';
            default:
                return 'fa-info-circle';
        }
    }
});