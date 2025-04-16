// Vehicles JavaScript

// DOM References
const overlay = document.getElementById('overlay');
const deleteModal = document.getElementById('deleteModal');
const closeButtons = document.querySelectorAll('.close-modal, .cancel-btn');
const deleteButtons = document.querySelectorAll('.delete-btn');
const gridViewBtn = document.getElementById('gridViewBtn');
const listViewBtn = document.getElementById('listViewBtn');
const gridView = document.getElementById('gridView');
const listView = document.getElementById('listView');
const toggleFiltersBtn = document.getElementById('toggleFilters');
const filterBody = document.getElementById('filterBody');
const resetFiltersBtn = document.getElementById('resetFilters');
const selectAllCheckbox = document.getElementById('selectAll');
const exportBtn = document.getElementById('exportBtn');
const printBtn = document.getElementById('printBtn');

// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Popular Makes Chart
    const makesChartCtx = document.getElementById('makesChart').getContext('2d');
    
    // Fetch data from PHP (in a real app, you would use AJAX)
    // For demo, we'll use hardcoded data
    const popularMakes = [
        { make: 'Toyota', count: 12 },
        { make: 'Honda', count: 8 },
        { make: 'Ford', count: 7 },
        { make: 'BMW', count: 5 },
        { make: 'Audi', count: 4 }
    ];
    
    const makesChart = new Chart(makesChartCtx, {
        type: 'bar',
        data: {
            labels: popularMakes.map(item => item.make),
            datasets: [{
                label: 'Number of Vehicles',
                data: popularMakes.map(item => item.count),
                backgroundColor: [
                    'rgba(52, 152, 219, 0.7)',
                    'rgba(46, 204, 113, 0.7)',
                    'rgba(155, 89, 182, 0.7)',
                    'rgba(52, 73, 94, 0.7)',
                    'rgba(26, 188, 156, 0.7)'
                ],
                borderColor: [
                    'rgba(52, 152, 219, 1)',
                    'rgba(46, 204, 113, 1)',
                    'rgba(155, 89, 182, 1)',
                    'rgba(52, 73, 94, 1)',
                    'rgba(26, 188, 156, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});

// Modal Functions
function openModal(modal) {
    overlay.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const activeModals = document.querySelectorAll('.modal.active');
    activeModals.forEach(modal => {
        modal.classList.remove('active');
    });
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Event Listeners
closeButtons.forEach(button => {
    button.addEventListener('click', closeModal);
});

overlay.addEventListener('click', closeModal);

// Handle delete vehicle buttons
deleteButtons.forEach(button => {
    button.addEventListener('click', () => {
        const vehicleId = button.getAttribute('data-id');
        document.getElementById('deleteVehicleId').value = vehicleId;
        openModal(deleteModal);
    });
});

// Toggle view (grid/list)
gridViewBtn.addEventListener('click', () => {
    gridView.style.display = 'grid';
    listView.style.display = 'none';
    gridViewBtn.classList.add('active');
    listViewBtn.classList.remove('active');
});

listViewBtn.addEventListener('click', () => {
    gridView.style.display = 'none';
    listView.style.display = 'block';
    gridViewBtn.classList.remove('active');
    listViewBtn.classList.add('active');
});

// Toggle filters
toggleFiltersBtn.addEventListener('click', () => {
    const isVisible = filterBody.style.display !== 'none';
    filterBody.style.display = isVisible ? 'none' : 'block';
    toggleFiltersBtn.innerHTML = isVisible ? 
        '<i class="fas fa-chevron-down"></i>' : 
        '<i class="fas fa-chevron-up"></i>';
});

// Reset filters
resetFiltersBtn.addEventListener('click', () => {
    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'search') {
            input.value = '';
        } else if (input.type === 'select-one') {
            input.selectedIndex = 0;
        }
    });
    
    form.submit();
});

// Handle select all checkbox
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody .checkbox-wrapper input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

// Export functionality
exportBtn.addEventListener('click', () => {
    alert('Export functionality would be implemented here');
});

// Print functionality
printBtn.addEventListener('click', () => {
    window.print();
});

// View details functionality
document.querySelectorAll('.view-btn').forEach(button => {
    button.addEventListener('click', () => {
        const vehicleId = button.getAttribute('data-id');
        window.location.href = `index.php?page=vehicle-details&id=${vehicleId}`;
    });
});

// Edit vehicle functionality
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const vehicleId = button.getAttribute('data-id');
        window.location.href = `index.php?page=edit-vehicle&id=${vehicleId}`;
    });
});

// Schedule service functionality
document.querySelectorAll('.service-btn').forEach(button => {
    button.addEventListener('click', () => {
        const vehicleId = button.getAttribute('data-id');
        window.location.href = `index.php?page=create-appointment&vehicle_id=${vehicleId}`;
    });
});