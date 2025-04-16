document.addEventListener('DOMContentLoaded', function() {
    // Handle per page dropdown
    const perPageSelect = document.getElementById('per-page-select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const form = document.getElementById('filterForm');
            const perPageInput = form.querySelector('input[name="per_page"]');
            perPageInput.value = this.value;
            form.submit();
        });
    }

    // Handle select all checkbox
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

    // Handle individual row checkboxes
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

    // Update selected count and enable/disable bulk action buttons
    function updateSelectedCount() {
        const selectedCountElement = document.getElementById('selectedCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkExportBtn = document.getElementById('bulkExportBtn');
        
        if (selectedCountElement) {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const count = checked.length;
            selectedCountElement.textContent = count;
            
            if (bulkDeleteBtn) {
                bulkDeleteBtn.disabled = count === 0;
            }
            
            if (bulkExportBtn) {
                bulkExportBtn.disabled = count === 0;
            }
        }
    }

    // Toggle dropdown menu
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            const isOpen = menu.classList.contains('show');
            
            // Close all open menus
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                openMenu.classList.remove('show');
            });
            
            // Toggle the clicked menu
            if (!isOpen) {
                menu.classList.add('show');
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Modal handlers
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = {
        'createInvoiceModal': document.getElementById('createInvoiceBtn'),
        'deleteInvoiceModal': document.querySelectorAll('.delete-invoice')
    };
    
    // Open modal functions
    if (modalTriggers.createInvoiceModal) {
        modalTriggers.createInvoiceModal.addEventListener('click', function() {
            openModal('createInvoiceModal');
        });
    }
    
    modalTriggers.deleteInvoiceModal.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const invoiceId = this.dataset.id;
            document.getElementById('deleteInvoiceId').value = invoiceId;
            openModal('deleteInvoiceModal');
        });
    });
    
    // Close modal with buttons
    const closeModalButtons = document.querySelectorAll('.close-modal, #cancelInvoiceBtn, #cancelDeleteBtn');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
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

    // Invoice item calculations
    const invoiceItemsTable = document.getElementById('invoiceItemsTable');
    if (invoiceItemsTable) {
        // Add item button
        const addItemBtn = document.getElementById('addItemBtn');
        if (addItemBtn) {
            addItemBtn.addEventListener('click', function() {
                const tbody = invoiceItemsTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.classList.add('item-row');
                
                newRow.innerHTML = `
                    <td>
                        <input type="text" name="items[${rowCount}][name]" placeholder="Item name" required>
                    </td>
                    <td>
                        <input type="text" name="items[${rowCount}][description]" placeholder="Description">
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][quantity]" class="item-quantity" value="1" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][price]" class="item-price" value="0.00" step="0.01" required>
                    </td>
                    <td>
                        <span class="item-total">$0.00</span>
                        <input type="hidden" name="items[${rowCount}][total]" value="0.00">
                    </td>
                    <td>
                        <button type="button" class="btn-icon remove-item">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(newRow);
                addItemEventListeners(newRow);
                updateInvoiceTotals();
            });
        }
        
        // Add event listeners to existing rows
        const existingRows = invoiceItemsTable.querySelectorAll('tbody tr');
        existingRows.forEach(row => {
            addItemEventListeners(row);
        });
        
        // Remove item button event delegation
        invoiceItemsTable.addEventListener('click', function(e) {
            const target = e.target.closest('.remove-item');
            if (target) {
                const row = target.closest('tr');
                if (invoiceItemsTable.querySelectorAll('tbody tr').length > 1) {
                    row.remove();
                    updateInvoiceTotals();
                } else {
                    // Don't remove the last row, just clear it
                    const inputs = row.querySelectorAll('input:not([type="hidden"])');
                    inputs.forEach(input => {
                        if (input.type === 'number') {
                            input.value = input.classList.contains('item-quantity') ? 1 : 0;
                        } else {
                            input.value = '';
                        }
                    });
                    row.querySelector('.item-total').textContent = '$0.00';
                    row.querySelector('input[type="hidden"]').value = '0.00';
                    updateInvoiceTotals();
                }
            }
        });
        
        // Update totals when form loads
        updateInvoiceTotals();
    }
    
    // Add event listeners to item row
    function addItemEventListeners(row) {
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        
        if (quantityInput && priceInput) {
            // Update totals when quantity or price changes
            [quantityInput, priceInput].forEach(input => {
                input.addEventListener('input', function() {
                    updateRowTotal(row);
                    updateInvoiceTotals();
                });
            });
        }
    }
    
    // Update a single row's total
    function updateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const total = quantity * price;
        
        row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
        row.querySelector('input[type="hidden"]').value = total.toFixed(2);
    }
    
    // Update all invoice totals
    function updateInvoiceTotals() {
        let subtotal = 0;
        
        // Calculate subtotal
        document.querySelectorAll('#invoiceItemsTable tbody tr').forEach(row => {
            const total = parseFloat(row.querySelector('input[type="hidden"]').value) || 0;
            subtotal += total;
        });
        
        // Calculate tax and total
        const taxRate = 0.10; // 10%
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        // Update display
        if (document.getElementById('subtotal')) {
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '$' + tax.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
            
            // Update hidden inputs for form submission
            document.getElementById('subtotal_input').value = subtotal.toFixed(2);
            document.getElementById('tax_input').value = tax.toFixed(2);
            document.getElementById('total_input').value = total.toFixed(2);
        }
    }
    
    // Handle save invoice button
    const saveInvoiceBtn = document.getElementById('saveInvoiceBtn');
    if (saveInvoiceBtn) {
        saveInvoiceBtn.addEventListener('click', function() {
            const form = document.getElementById('createInvoiceForm');
            if (form && form.checkValidity()) {
                form.submit();
            } else {
                // Trigger HTML5 validation
                form.reportValidity();
            }
        });
    }
    
    // Handle delete confirmation
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const invoiceId = document.getElementById('deleteInvoiceId').value;
            // Submit to delete endpoint
            window.location.href = `delete-invoice.php?id=${invoiceId}`;
        });
    }
});