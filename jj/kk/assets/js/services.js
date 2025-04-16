// Services JavaScript

// DOM References
const addServiceBtn = document.getElementById('addServiceBtn');
const overlay = document.getElementById('overlay');
const allModals = document.querySelectorAll('.modal');
const closeButtons = document.querySelectorAll('.close-modal, .cancel-btn');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const sortBy = document.getElementById('sortBy');
const recordsPerPage = document.getElementById('recordsPerPage');
const serviceCards = document.querySelectorAll('.service-card');
const editButtons = document.querySelectorAll('.edit-btn');
const deleteButtons = document.querySelectorAll('.delete-btn');

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    overlay.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    allModals.forEach(modal => {
        modal.classList.remove('active');
    });
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Event Listeners
addServiceBtn.addEventListener('click', () => {
    openModal('addServiceModal');
});

closeButtons.forEach(button => {
    button.addEventListener('click', closeModal);
});

overlay.addEventListener('click', closeModal);

// Handle edit service buttons
editButtons.forEach(button => {
    button.addEventListener('click', () => {
        const serviceId = button.getAttribute('data-id');
        document.getElementById('editServiceId').value = serviceId;
        
        // In a real app, you would fetch the service data based on the ID
        // For demo purposes, we'll extract data from the service card
        
        // Find the parent service card
        const serviceCard = button.closest('.service-card');
        
        // Extract data from the service card
        const name = serviceCard.querySelector('h3').textContent;
        const category = serviceCard.getAttribute('data-category');
        const priceElement = serviceCard.querySelector('.service-price span');
        const price = priceElement.classList.contains('free-badge') ? 0 : 
                     parseFloat(priceElement.textContent.replace('$', ''));
        const durationText = serviceCard.querySelector('.service-duration').textContent.trim();
        const duration = parseInt(durationText.match(/\d+/)[0]);
        const description = serviceCard.querySelector('p').textContent;
        
        // Populate the edit form
        document.getElementById('editName').value = name;
        document.getElementById('editCategory').value = category;
        document.getElementById('editPrice').value = price;
        document.getElementById('editDuration').value = duration;
        document.getElementById('editDescription').value = description;
        document.getElementById('editStatus').value = 'active'; // Default to active
        
        openModal('editServiceModal');
    });
});

// Handle delete service buttons
deleteButtons.forEach(button => {
    button.addEventListener('click', () => {
        const serviceId = button.getAttribute('data-id');
        document.getElementById('deleteServiceId').value = serviceId;
        openModal('deleteServiceModal');
    });
});

// Handle form submissions with AJAX
document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    // In a real app, you would use AJAX to submit the form
    // For demo purposes, we'll just let the form submit normally
});

document.getElementById('editServiceForm').addEventListener('submit', function(e) {
    // In a real app, you would use AJAX to submit the form
    // For demo purposes, we'll just let the form submit normally
});

document.getElementById('deleteServiceForm').addEventListener('submit', function(e) {
    // In a real app, you would use AJAX to submit the form
    // For demo purposes, we'll just let the form submit normally
});

// Auto-submit form when changing filters
categoryFilter.addEventListener('change', () => {
    document.getElementById('searchForm').submit();
});

sortBy.addEventListener('change', () => {
    document.getElementById('searchForm').submit();
});

recordsPerPage.addEventListener('change', () => {
    document.getElementById('searchForm').submit();
});