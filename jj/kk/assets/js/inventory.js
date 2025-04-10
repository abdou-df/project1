/**
 * Inventory Management JavaScript Module
 * Handles all inventory-related functionality
 */

class InventoryManager {
    constructor() {
        // Initialize properties
        this.dataTable = null;
        this.selectedPart = null;
        this.chartInstances = {};
        
        // Bind methods to this
        this.initializeDataTable = this.initializeDataTable.bind(this);
        this.loadParts = this.loadParts.bind(this);
        this.handlePartSelection = this.handlePartSelection.bind(this);
        this.handleQuantityAdjustment = this.handleQuantityAdjustment.bind(this);
        this.updateCharts = this.updateCharts.bind(this);
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => this.initialize());
    }
    
    /**
     * Initialize the inventory manager
     */
    initialize() {
        // Initialize DataTable
        this.initializeDataTable();
        
        // Initialize form handlers
        this.initializeFormHandlers();
        
        // Initialize charts
        this.initializeCharts();
        
        // Load initial data
        this.loadParts();
        this.loadInventoryValue();
        this.loadLowStock();
        
        // Initialize tooltips and popovers
        this.initializeTooltips();
        
        // Set up search and filter handlers
        this.setupSearchAndFilters();
    }
    
    /**
     * Initialize DataTable for parts list
     */
    initializeDataTable() {
        const table = document.getElementById('parts-table');
        if (!table) return;
        
        this.dataTable = new DataTable(table, {
            serverSide: true,
            processing: true,
            ajax: {
                url: '../ajax/inventory.ajax.php',
                type: 'POST',
                data: function(d) {
                    return {
                        action: 'get_parts',
                        page: Math.ceil(d.start / d.length) + 1,
                        limit: d.length,
                        search: d.search.value,
                        category: document.getElementById('category-filter').value,
                        status: document.getElementById('status-filter').value
                    };
                }
            },
            columns: [
                { data: 'part_number' },
                { data: 'name' },
                { data: 'category' },
                { 
                    data: 'quantity',
                    render: function(data, type, row) {
                        const colorClass = data <= row.min_quantity ? 'text-danger' : 
                                         data <= row.min_quantity * 1.5 ? 'text-warning' : 'text-success';
                        return `<span class="${colorClass}">${data}</span>`;
                    }
                },
                { 
                    data: 'price',
                    render: function(data) {
                        return formatCurrency(data);
                    }
                },
                { 
                    data: 'status',
                    render: function(data) {
                        return `<span class="badge bg-${data === PART_STATUS_ACTIVE ? 'success' : 'danger'}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-primary btn-edit" data-id="${data.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-info btn-history" data-id="${data.id}">
                                    <i class="fas fa-history"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-delete" data-id="${data.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            responsive: true
        });
        
        // Handle row selection
        table.addEventListener('click', (e) => {
            const target = e.target.closest('button');
            if (!target) return;
            
            const id = target.dataset.id;
            if (target.classList.contains('btn-edit')) {
                this.editPart(id);
            } else if (target.classList.contains('btn-history')) {
                this.viewHistory(id);
            } else if (target.classList.contains('btn-delete')) {
                this.deletePart(id);
            }
        });
    }
    
    /**
     * Initialize form handlers
     */
    initializeFormHandlers() {
        // Add part form
        const addPartForm = document.getElementById('add-part-form');
        if (addPartForm) {
            addPartForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addPart();
            });
        }
        
        // Edit part form
        const editPartForm = document.getElementById('edit-part-form');
        if (editPartForm) {
            editPartForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updatePart();
            });
        }
        
        // Quantity adjustment form
        const adjustQuantityForm = document.getElementById('adjust-quantity-form');
        if (adjustQuantityForm) {
            adjustQuantityForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.adjustQuantity();
            });
        }
    }
    
    /**
     * Initialize charts
     */
    initializeCharts() {
        // Stock value chart
        const stockValueCtx = document.getElementById('stock-value-chart');
        if (stockValueCtx) {
            this.chartInstances.stockValue = new Chart(stockValueCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Stock Value by Category',
                        data: [],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Stock quantity chart
        const stockQuantityCtx = document.getElementById('stock-quantity-chart');
        if (stockQuantityCtx) {
            this.chartInstances.stockQuantity = new Chart(stockQuantityCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                            'rgba(255, 159, 64, 0.5)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
    }
    
    /**
     * Load parts data
     */
    async loadParts() {
        if (this.dataTable) {
            this.dataTable.ajax.reload();
        }
    }
    
    /**
     * Load inventory value data
     */
    async loadInventoryValue() {
        try {
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get_inventory_value'
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.updateInventoryValueDisplay(data.data);
            this.updateCharts(data.data);
        } catch (error) {
            console.error('Error loading inventory value:', error);
            showToast('Error loading inventory value', 'error');
        }
    }
    
    /**
     * Load low stock items
     */
    async loadLowStock() {
        try {
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get_low_stock'
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.updateLowStockDisplay(data.data);
        } catch (error) {
            console.error('Error loading low stock items:', error);
            showToast('Error loading low stock items', 'error');
        }
    }
    
    /**
     * Add new part
     */
    async addPart() {
        const form = document.getElementById('add-part-form');
        if (!form) return;
        
        if (!this.validateForm(form)) {
            showToast('Please fill in all required fields', 'error');
            return;
        }
        
        try {
            showLoading();
            
            const formData = new FormData(form);
            formData.append('action', 'add_part');
            
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            showToast('Part added successfully', 'success');
            this.loadParts();
            this.loadInventoryValue();
            
            // Close modal if exists
            const modal = bootstrap.Modal.getInstance(document.getElementById('add-part-modal'));
            if (modal) {
                modal.hide();
            }
            
            // Reset form
            form.reset();
        } catch (error) {
            console.error('Error adding part:', error);
            showToast(error.message || 'Error adding part', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * Edit part
     */
    async editPart(id) {
        try {
            showLoading();
            
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=get_part&part_id=${id}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.selectedPart = data.data;
            this.populateEditForm(data.data);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('edit-part-modal'));
            modal.show();
        } catch (error) {
            console.error('Error loading part details:', error);
            showToast('Error loading part details', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * Update part
     */
    async updatePart() {
        const form = document.getElementById('edit-part-form');
        if (!form || !this.selectedPart) return;
        
        if (!this.validateForm(form)) {
            showToast('Please fill in all required fields', 'error');
            return;
        }
        
        try {
            showLoading();
            
            const formData = new FormData(form);
            formData.append('action', 'update_part');
            formData.append('part_id', this.selectedPart.id);
            
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            showToast('Part updated successfully', 'success');
            this.loadParts();
            this.loadInventoryValue();
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('edit-part-modal'));
            if (modal) {
                modal.hide();
            }
        } catch (error) {
            console.error('Error updating part:', error);
            showToast(error.message || 'Error updating part', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * Delete part
     */
    async deletePart(id) {
        if (!confirm('Are you sure you want to delete this part?')) {
            return;
        }
        
        try {
            showLoading();
            
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=delete_part&part_id=${id}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            showToast('Part deleted successfully', 'success');
            this.loadParts();
            this.loadInventoryValue();
        } catch (error) {
            console.error('Error deleting part:', error);
            showToast(error.message || 'Error deleting part', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * View part history
     */
    async viewHistory(id) {
        try {
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=get_part&part_id=${id}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.displayHistory(data.data);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('history-modal'));
            modal.show();
        } catch (error) {
            console.error('Error loading part history:', error);
            showToast('Error loading part history', 'error');
        }
    }
    
    /**
     * Adjust quantity
     */
    async adjustQuantity() {
        const form = document.getElementById('adjust-quantity-form');
        if (!form || !this.selectedPart) return;
        
        if (!this.validateForm(form)) {
            showToast('Please fill in all required fields', 'error');
            return;
        }
        
        try {
            showLoading();
            
            const formData = new FormData(form);
            formData.append('action', 'adjust_quantity');
            formData.append('part_id', this.selectedPart.id);
            
            const response = await fetch('../ajax/inventory.ajax.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            showToast('Quantity adjusted successfully', 'success');
            this.loadParts();
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('adjust-quantity-modal'));
            if (modal) {
                modal.hide();
            }
            
            // Reset form
            form.reset();
        } catch (error) {
            console.error('Error adjusting quantity:', error);
            showToast(error.message || 'Error adjusting quantity', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * Update inventory value display
     */
    updateInventoryValueDisplay(data) {
        const totalCostEl = document.getElementById('total-cost');
        if (totalCostEl) {
            totalCostEl.textContent = formatCurrency(data.total_cost);
        }
        
        const totalRetailEl = document.getElementById('total-retail');
        if (totalRetailEl) {
            totalRetailEl.textContent = formatCurrency(data.total_retail);
        }
        
        const potentialProfitEl = document.getElementById('potential-profit');
        if (potentialProfitEl) {
            potentialProfitEl.textContent = formatCurrency(data.potential_profit);
        }
    }
    
    /**
     * Update low stock display
     */
    updateLowStockDisplay(data) {
        const container = document.getElementById('low-stock-list');
        if (!container) return;
        
        container.innerHTML = '';
        data.forEach(item => {
            container.innerHTML += `
                <div class="alert alert-warning">
                    <h6 class="alert-heading">${item.name}</h6>
                    <p class="mb-0">
                        Part #: ${item.part_number}<br>
                        Current Stock: ${item.quantity} (Min: ${item.min_quantity})<br>
                        Last Ordered: ${item.last_ordered}
                    </p>
                </div>
            `;
        });
    }
    
    /**
     * Update charts
     */
    updateCharts(data) {
        // Update stock value chart
        if (this.chartInstances.stockValue) {
            const categories = Object.keys(data.by_category);
            const values = categories.map(cat => data.by_category[cat].retail);
            
            this.chartInstances.stockValue.data.labels = categories;
            this.chartInstances.stockValue.data.datasets[0].data = values;
            this.chartInstances.stockValue.update();
        }
        
        // Update stock quantity chart
        if (this.chartInstances.stockQuantity) {
            const categories = Object.keys(data.by_category);
            const quantities = categories.map(cat => data.by_category[cat].quantity);
            
            this.chartInstances.stockQuantity.data.labels = categories;
            this.chartInstances.stockQuantity.data.datasets[0].data = quantities;
            this.chartInstances.stockQuantity.update();
        }
    }
    
    /**
     * Populate edit form
     */
    populateEditForm(part) {
        const form = document.getElementById('edit-part-form');
        if (!form) return;
        
        // Set form values
        form.elements['name'].value = part.name;
        form.elements['part_number'].value = part.part_number;
        form.elements['category'].value = part.category;
        form.elements['description'].value = part.description;
        form.elements['specifications'].value = part.specifications;
        form.elements['price'].value = part.price;
        form.elements['cost'].value = part.cost;
        form.elements['min_quantity'].value = part.min_quantity;
        form.elements['max_quantity'].value = part.max_quantity;
        form.elements['location'].value = part.location;
        form.elements['supplier_id'].value = part.supplier.id;
        form.elements['warranty'].value = part.warranty;
        form.elements['notes'].value = part.notes;
        form.elements['status'].value = part.status;
    }
    
    /**
     * Display part history
     */
    displayHistory(part) {
        const container = document.getElementById('history-content');
        if (!container) return;
        
        container.innerHTML = `
            <h5>${part.name} (${part.part_number})</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${part.history.map(item => `
                            <tr>
                                <td>${item.date}</td>
                                <td>${item.type}</td>
                                <td>${item.quantity}</td>
                                <td>${item.reference}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    /**
     * Set up search and filter handlers
     */
    setupSearchAndFilters() {
        // Category filter
        const categoryFilter = document.getElementById('category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.loadParts());
        }
        
        // Status filter
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.loadParts());
        }
        
        // Search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => this.loadParts(), 500));
        }
    }
    
    /**
     * Validate form
     */
    validateForm(form) {
        const required = form.querySelectorAll('[required]');
        for (const field of required) {
            if (!field.value.trim()) {
                return false;
            }
        }
        return true;
    }
}

// Initialize inventory manager
const inventoryManager = new InventoryManager();
