document.addEventListener('DOMContentLoaded', function() {
    // View Toggle
    const viewButtons = document.querySelectorAll('.view-btn');
    const viewContainers = document.querySelectorAll('.view-container');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected view
            viewContainers.forEach(container => {
                container.classList.remove('active');
                if (container.id === view + 'View') {
                    container.classList.add('active');
                }
            });
            
            // Update URL parameter
            const url = new URL(window.location.href);
            url.searchParams.set('view', view);
            window.history.replaceState({}, '', url);
        });
    });
    
    // Handle Select All Checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Handle Row Checkboxes
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            
            // If any checkbox is unchecked, uncheck "select all"
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            
            // If all checkboxes are checked, check "select all"
            if (selectAllCheckbox) {
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    });
    
    // Update Selected Count
    function updateSelectedCount() {
        const selectedCount = document.getElementById('selectedCount');
        const bulkStatusBtn = document.getElementById('bulkStatusBtn');
        const bulkAssignBtn = document.getElementById('bulkAssignBtn');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkExportBtn = document.getElementById('bulkExportBtn');
        
        if (selectedCount) {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const count = checked.length;
            selectedCount.textContent = count;
            
            // Enable/disable bulk action buttons
            if (bulkStatusBtn) bulkStatusBtn.disabled = count === 0;
            if (bulkAssignBtn) bulkAssignBtn.disabled = count === 0;
            if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
            if (bulkExportBtn) bulkExportBtn.disabled = count === 0;
        }
    }
    
    // Sortable Columns
    const sortableColumns = document.querySelectorAll('.sortable');
    sortableColumns.forEach(column => {
        column.addEventListener('click', function() {
            const sort = this.dataset.sort;
            const currentSort = document.querySelector('input[name="sort"]').value;
            let newSort = sort + ' ASC';
            
            // Toggle sort direction
            if (currentSort === sort + ' ASC') {
                newSort = sort + ' DESC';
                this.classList.remove('asc');
                this.classList.add('desc');
            } else if (currentSort === sort + ' DESC') {
                newSort = sort + ' ASC';
                this.classList.remove('desc');
                this.classList.add('asc');
            } else {
                // Remove classes from other columns
                sortableColumns.forEach(col => {
                    col.classList.remove('asc', 'desc');
                });
                this.classList.add('asc');
            }
            
            // Update sort input and submit form
            document.querySelector('input[name="sort"]').value = newSort;
            document.getElementById('filterForm').submit();
        });
    });
    
    // Pagination Links
    const pageLinks = document.querySelectorAll('.page-link[data-page]');
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            
            if (!this.classList.contains('disabled')) {
                document.querySelector('input[name="page"]').value = page;
                document.getElementById('filterForm').submit();
            }
        });
    });
    
    // Per Page Selector
    const perPageSelectors = document.querySelectorAll('#perPage, #perPageCards');
    perPageSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            document.querySelector('input[name="per_page"]').value = this.value;
            document.querySelector('input[name="page"]').value = 1; // Reset to first page
            document.getElementById('filterForm').submit();
        });
    });
    
    // Dropdown Toggles
    document.addEventListener('click', function(e) {
        const toggle = e.target.closest('.dropdown-toggle');
        
        if (toggle) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = toggle.nextElementSibling;
            const isOpen = menu.classList.contains('show');
            
            // Close all open menus
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                openMenu.classList.remove('show');
            });
            
            // Toggle the clicked menu
            if (!isOpen) {
                menu.classList.add('show');
            }
        } else if (!e.target.closest('.dropdown-menu')) {
            // Close all menus when clicking outside
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    
    // Modal Functions
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal(modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Close Modal Buttons
    const closeModalButtons = document.querySelectorAll('.close-modal');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Close Modal on Outside Click
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
    // Change Status Button
    const changeStatusButtons = document.querySelectorAll('.change-status');
    changeStatusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const jobCardId = this.dataset.id;
            document.getElementById('statusJobCardId').value = jobCardId;
            
            // Get current status and set it in the dropdown
            const statusCell = this.closest('tr')?.querySelector('.status-badge') || 
                               this.closest('.job-card')?.querySelector('.status-badge') ||
                               this.closest('.kanban-card')?.closest('.kanban-column');
                               
            let currentStatus = '';
            
            if (statusCell) {
                if (statusCell.classList.contains('badge-primary')) {
                    currentStatus = 'scheduled';
                } else if (statusCell.classList.contains('badge-info')) {
                    currentStatus = 'confirmed';
                } else if (statusCell.classList.contains('badge-warning')) {
                    currentStatus = 'in_progress';
                } else if (statusCell.classList.contains('badge-success')) {
                    currentStatus = 'completed';
                } else if (statusCell.classList.contains('badge-danger')) {
                    currentStatus = 'cancelled';
                } else if (statusCell.dataset && statusCell.dataset.status) {
                    currentStatus = statusCell.dataset.status;
                }
            }
            
            if (currentStatus) {
                document.getElementById('newStatus').value = currentStatus;
            }
            
            openModal('changeStatusModal');
        });
    });
    
    // Save Status Button
    const saveStatusBtn = document.getElementById('saveStatusBtn');
    if (saveStatusBtn) {
        saveStatusBtn.addEventListener('click', function() {
            const form = document.getElementById('changeStatusForm');
            const jobCardId = document.getElementById('statusJobCardId').value;
            const newStatus = document.getElementById('newStatus').value;
            const note = document.getElementById('statusNote').value;
            
            // AJAX request to update status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/update_job_card_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            // Close modal
                            closeModal(document.getElementById('changeStatusModal'));
                            
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                }
            };
            
            xhr.send(`job_card_id=${jobCardId}&status=${newStatus}&note=${encodeURIComponent(note)}`);
        });
    }
    
    // Delete Job Card Button
    const deleteButtons = document.querySelectorAll('.delete-job-card');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const jobCardId = this.dataset.id;
            document.getElementById('deleteJobCardId').value = jobCardId;
            
            openModal('deleteModal');
        });
    });
    
    // Confirm Delete Button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const jobCardId = document.getElementById('deleteJobCardId').value;
            
            // AJAX request to delete job card
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/delete_job_card.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            // Close modal
                            closeModal(document.getElementById('deleteModal'));
                            
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                }
            };
            
            xhr.send(`job_card_id=${jobCardId}`);
        });
    }
    
    // Bulk Status Change Button
    const bulkStatusBtn = document.getElementById('bulkStatusBtn');
    if (bulkStatusBtn) {
        bulkStatusBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            
            if (checked.length > 0) {
                const jobCardIds = Array.from(checked).map(checkbox => checkbox.dataset.id).join(',');
                document.getElementById('bulkJobCardIds').value = jobCardIds;
                
                openModal('bulkStatusModal');
            }
        });
    }
    
    // Save Bulk Status Button
    const saveBulkStatusBtn = document.getElementById('saveBulkStatusBtn');
    if (saveBulkStatusBtn) {
        saveBulkStatusBtn.addEventListener('click', function() {
            const jobCardIds = document.getElementById('bulkJobCardIds').value;
            const newStatus = document.getElementById('bulkNewStatus').value;
            const note = document.getElementById('bulkStatusNote').value;
            
            // AJAX request to update status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/update_bulk_job_card_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            // Close modal
                            closeModal(document.getElementById('bulkStatusModal'));
                            
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                }
            };
            
            xhr.send(`job_card_ids=${jobCardIds}&status=${newStatus}&note=${encodeURIComponent(note)}`);
        });
    }
    
    // Bulk Assign Mechanic Button
    const bulkAssignBtn = document.getElementById('bulkAssignBtn');
    if (bulkAssignBtn) {
        bulkAssignBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            
            if (checked.length > 0) {
                const jobCardIds = Array.from(checked).map(checkbox => checkbox.dataset.id).join(',');
                document.getElementById('bulkAssignJobCardIds').value = jobCardIds;
                
                openModal('bulkAssignModal');
            }
        });
    }
    
    // Save Bulk Assign Button
    const saveBulkAssignBtn = document.getElementById('saveBulkAssignBtn');
    if (saveBulkAssignBtn) {
        saveBulkAssignBtn.addEventListener('click', function() {
            const jobCardIds = document.getElementById('bulkAssignJobCardIds').value;
            const mechanicId = document.getElementById('bulkMechanicId').value;
            
            if (!mechanicId) {
                alert('Please select a mechanic');
                return;
            }
            
            // AJAX request to assign mechanic
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/assign_bulk_mechanic.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            // Close modal
                            closeModal(document.getElementById('bulkAssignModal'));
                            
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                }
            };
            
            xhr.send(`job_card_ids=${jobCardIds}&mechanic_id=${mechanicId}`);
        });
    }
    
    // Bulk Delete Button
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            
            if (checked.length > 0) {
                const jobCardIds = Array.from(checked).map(checkbox => checkbox.dataset.id).join(',');
                document.getElementById('bulkDeleteJobCardIds').value = jobCardIds;
                
                openModal('bulkDeleteModal');
            }
        });
    }
    
    // Confirm Bulk Delete Button
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
    if (confirmBulkDeleteBtn) {
        confirmBulkDeleteBtn.addEventListener('click', function() {
            const jobCardIds = document.getElementById('bulkDeleteJobCardIds').value;
            
            // AJAX request to delete job cards
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/delete_bulk_job_cards.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            // Close modal
                            closeModal(document.getElementById('bulkDeleteModal'));
                            
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                }
            };
            
            xhr.send(`job_card_ids=${jobCardIds}`);
        });
    }
    
    // Print Job Card
    const printButtons = document.querySelectorAll('.print-job-card');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const jobCardId = this.dataset.id;
            window.open(`print_job_card.php?id=${jobCardId}`, '_blank');
        });
    });
    
    // Bulk Export Button
    const bulkExportBtn = document.getElementById('bulkExportBtn');
    if (bulkExportBtn) {
        bulkExportBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            
            if (checked.length > 0) {
                const jobCardIds = Array.from(checked).map(checkbox => checkbox.dataset.id).join(',');
                window.open(`export_job_cards.php?ids=${jobCardIds}&format=pdf`, '_blank');
            }
        });
    }
    
    // Create Job Card Button
    const createJobCardBtn = document.getElementById('createJobCardBtn');
    if (createJobCardBtn) {
        createJobCardBtn.addEventListener('click', function() {
            window.location.href = 'create_job_card.php';
        });
    }
    
    // Edit Job Card Button
    const editButtons = document.querySelectorAll('.edit-job-card');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const jobCardId = this.dataset.id;
            window.location.href = `edit_job_card.php?id=${jobCardId}`;
        });
    });
    
    // Initialize Kanban Drag and Drop (if needed)
    if (document.getElementById('kanbanView')) {
        initKanbanDragDrop();
    }
    
    // Kanban Drag and Drop
    function initKanbanDragDrop() {
        const kanbanCards = document.querySelectorAll('.kanban-card');
        const kanbanColumns = document.querySelectorAll('.kanban-column');
        
        kanbanCards.forEach(card => {
            card.setAttribute('draggable', true);
            
            card.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', card.dataset.id);
                card.classList.add('dragging');
            });
            
            card.addEventListener('dragend', function() {
                card.classList.remove('dragging');
            });
        });
        
        kanbanColumns.forEach(column => {
            column.addEventListener('dragover', function(e) {
                e.preventDefault();
                column.classList.add('drag-over');
            });
            
            column.addEventListener('dragleave', function() {
                column.classList.remove('drag-over');
            });
            
            column.addEventListener('drop', function(e) {
                e.preventDefault();
                column.classList.remove('drag-over');
                
                const jobCardId = e.dataTransfer.getData('text/plain');
                const newStatus = column.dataset.status;
                
                // AJAX request to update status
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'ajax/update_job_card_status.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            
                            if (response.success) {
                                // Move card to new column
                                const card = document.querySelector(`.kanban-card[data-id="${jobCardId}"]`);
                                const oldColumn = card.closest('.column-body');
                                const newColumn = column.querySelector('.column-body');
                                
                                newColumn.appendChild(card);
                                
                                // Update counters
                                updateKanbanCounters();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        } catch (e) {
                            alert('Error processing response');
                        }
                    }
                };
                
                xhr.send(`job_card_id=${jobCardId}&status=${newStatus}`);
            });
        });
    }
    
    // Update Kanban Counters
    function updateKanbanCounters() {
        const columns = document.querySelectorAll('.kanban-column');
        
        columns.forEach(column => {
            const cards = column.querySelectorAll('.kanban-card').length;
            const counter = column.querySelector('.counter');
            
            if (counter) {
                counter.textContent = cards;
            }
        });
    }
});