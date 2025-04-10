/**
 * Appointments JavaScript Module
 * Handles all appointment-related functionality
 */

class AppointmentManager {
    constructor() {
        // Initialize properties
        this.calendar = null;
        this.selectedDate = null;
        this.selectedSlot = null;
        this.selectedService = null;
        this.selectedTechnician = null;
        
        // Bind methods to this
        this.initializeCalendar = this.initializeCalendar.bind(this);
        this.loadAvailableSlots = this.loadAvailableSlots.bind(this);
        this.handleDateSelection = this.handleDateSelection.bind(this);
        this.handleSlotSelection = this.handleSlotSelection.bind(this);
        this.handleServiceSelection = this.handleServiceSelection.bind(this);
        this.handleTechnicianSelection = this.handleTechnicianSelection.bind(this);
        this.scheduleAppointment = this.scheduleAppointment.bind(this);
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => this.initialize());
    }
    
    /**
     * Initialize the appointment manager
     */
    initialize() {
        // Initialize calendar
        this.initializeCalendar();
        
        // Initialize form handlers
        this.initializeFormHandlers();
        
        // Load initial data
        this.loadServices();
        this.loadTechnicians();
        
        // Initialize tooltips and popovers
        this.initializeTooltips();
    }
    
    /**
     * Initialize the calendar
     */
    initializeCalendar() {
        const calendarEl = document.getElementById('appointment-calendar');
        if (!calendarEl) return;
        
        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6], // Monday - Saturday
                startTime: '09:00',
                endTime: '17:00'
            },
            select: this.handleDateSelection,
            eventClick: this.handleEventClick,
            events: (info, successCallback, failureCallback) => {
                this.loadAppointments(info.start, info.end, successCallback, failureCallback);
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            }
        });
        
        this.calendar.render();
    }
    
    /**
     * Initialize form handlers
     */
    initializeFormHandlers() {
        // Service selection
        const serviceSelect = document.getElementById('service-select');
        if (serviceSelect) {
            serviceSelect.addEventListener('change', this.handleServiceSelection);
        }
        
        // Technician selection
        const technicianSelect = document.getElementById('technician-select');
        if (technicianSelect) {
            technicianSelect.addEventListener('change', this.handleTechnicianSelection);
        }
        
        // Form submission
        const appointmentForm = document.getElementById('appointment-form');
        if (appointmentForm) {
            appointmentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.scheduleAppointment();
            });
        }
    }
    
    /**
     * Initialize tooltips and popovers
     */
    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
        
        const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(popover => {
            new bootstrap.Popover(popover);
        });
    }
    
    /**
     * Load available services
     */
    async loadServices() {
        try {
            const response = await fetch('../ajax/service.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get_services'
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.updateServiceSelect(data.data.services);
        } catch (error) {
            console.error('Error loading services:', error);
            showToast('Error loading services', 'error');
        }
    }
    
    /**
     * Load available technicians
     */
    async loadTechnicians() {
        try {
            const response = await fetch('../ajax/service.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=get_technicians&service_id=${this.selectedService || ''}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.updateTechnicianSelect(data.data);
        } catch (error) {
            console.error('Error loading technicians:', error);
            showToast('Error loading technicians', 'error');
        }
    }
    
    /**
     * Load appointments for calendar
     */
    async loadAppointments(start, end, successCallback, failureCallback) {
        try {
            const response = await fetch('../ajax/service.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=get_appointments&start_date=${start.toISOString()}&end_date=${end.toISOString()}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            // Convert appointments to calendar events
            const events = data.data.appointments.map(appointment => ({
                id: appointment.id,
                title: `${appointment.service.name} - ${appointment.customer.name}`,
                start: `${appointment.date}T${appointment.time}`,
                end: this.calculateEndTime(appointment.date, appointment.time, appointment.service.duration),
                className: `appointment-status-${appointment.status.toLowerCase()}`,
                extendedProps: {
                    appointment: appointment
                }
            }));
            
            successCallback(events);
        } catch (error) {
            console.error('Error loading appointments:', error);
            failureCallback(error);
            showToast('Error loading appointments', 'error');
        }
    }
    
    /**
     * Load available time slots for a date
     */
    async loadAvailableSlots() {
        if (!this.selectedDate || !this.selectedService) return;
        
        try {
            const response = await fetch('../ajax/service.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=get_available_slots&date=${this.selectedDate}&service_id=${this.selectedService}&technician_id=${this.selectedTechnician || ''}`
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.updateTimeSlots(data.data);
        } catch (error) {
            console.error('Error loading time slots:', error);
            showToast('Error loading available time slots', 'error');
        }
    }
    
    /**
     * Handle date selection from calendar
     */
    handleDateSelection(info) {
        const selectedDate = info.start;
        
        // Check if date is in the past
        if (selectedDate < new Date().setHours(0, 0, 0, 0)) {
            showToast('Cannot schedule appointments in the past', 'error');
            this.calendar.unselect();
            return;
        }
        
        // Check if date is a business day
        const day = selectedDate.getDay();
        if (day === 0) { // Sunday
            showToast('We are closed on Sundays', 'error');
            this.calendar.unselect();
            return;
        }
        
        this.selectedDate = selectedDate.toISOString().split('T')[0];
        this.updateSelectedDate();
        this.loadAvailableSlots();
    }
    
    /**
     * Handle time slot selection
     */
    handleSlotSelection(event) {
        const slot = event.target.value;
        this.selectedSlot = slot;
        this.updateSelectedSlot();
    }
    
    /**
     * Handle service selection
     */
    handleServiceSelection(event) {
        const serviceId = event.target.value;
        this.selectedService = serviceId;
        this.loadTechnicians();
        this.loadAvailableSlots();
        this.updatePrice();
    }
    
    /**
     * Handle technician selection
     */
    handleTechnicianSelection(event) {
        const technicianId = event.target.value;
        this.selectedTechnician = technicianId;
        this.loadAvailableSlots();
    }
    
    /**
     * Schedule appointment
     */
    async scheduleAppointment() {
        // Validate required fields
        if (!this.validateForm()) {
            showToast('Please fill in all required fields', 'error');
            return;
        }
        
        // Get form data
        const formData = new FormData(document.getElementById('appointment-form'));
        formData.append('action', 'schedule_appointment');
        formData.append('date', this.selectedDate);
        formData.append('time', this.selectedSlot);
        
        try {
            showLoading();
            
            const response = await fetch('../ajax/service.ajax.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            // Show success message
            showToast('Appointment scheduled successfully', 'success');
            
            // Refresh calendar
            this.calendar.refetchEvents();
            
            // Reset form
            this.resetForm();
            
            // Close modal if exists
            const modal = bootstrap.Modal.getInstance(document.getElementById('appointment-modal'));
            if (modal) {
                modal.hide();
            }
        } catch (error) {
            console.error('Error scheduling appointment:', error);
            showToast(error.message || 'Error scheduling appointment', 'error');
        } finally {
            hideLoading();
        }
    }
    
    /**
     * Update service select options
     */
    updateServiceSelect(services) {
        const select = document.getElementById('service-select');
        if (!select) return;
        
        select.innerHTML = '<option value="">Select Service</option>';
        services.forEach(service => {
            const option = document.createElement('option');
            option.value = service.id;
            option.textContent = `${service.name} ($${service.price.toFixed(2)})`;
            select.appendChild(option);
        });
    }
    
    /**
     * Update technician select options
     */
    updateTechnicianSelect(technicians) {
        const select = document.getElementById('technician-select');
        if (!select) return;
        
        select.innerHTML = '<option value="">Any Available Technician</option>';
        technicians.forEach(technician => {
            if (technician.available) {
                const option = document.createElement('option');
                option.value = technician.id;
                option.textContent = technician.name;
                select.appendChild(option);
            }
        });
    }
    
    /**
     * Update time slots
     */
    updateTimeSlots(slots) {
        const container = document.getElementById('time-slots');
        if (!container) return;
        
        container.innerHTML = '';
        slots.forEach(slot => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `btn btn-outline-primary m-1 ${slot.available ? '' : 'disabled'}`;
            button.value = slot.time;
            button.textContent = slot.time;
            button.onclick = slot.available ? this.handleSlotSelection.bind(this) : null;
            container.appendChild(button);
        });
    }
    
    /**
     * Update selected date display
     */
    updateSelectedDate() {
        const display = document.getElementById('selected-date');
        if (display) {
            display.textContent = this.selectedDate ? new Date(this.selectedDate).toLocaleDateString() : '';
        }
    }
    
    /**
     * Update selected slot display
     */
    updateSelectedSlot() {
        const display = document.getElementById('selected-slot');
        if (display) {
            display.textContent = this.selectedSlot || '';
        }
    }
    
    /**
     * Update price display
     */
    updatePrice() {
        const priceDisplay = document.getElementById('service-price');
        const serviceSelect = document.getElementById('service-select');
        if (priceDisplay && serviceSelect) {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const price = selectedOption ? selectedOption.textContent.match(/\$[\d.]+/) : null;
            priceDisplay.textContent = price ? price[0] : '';
        }
    }
    
    /**
     * Calculate end time based on start time and duration
     */
    calculateEndTime(date, time, duration) {
        const datetime = new Date(`${date}T${time}`);
        datetime.setMinutes(datetime.getMinutes() + duration);
        return datetime.toISOString();
    }
    
    /**
     * Validate appointment form
     */
    validateForm() {
        const form = document.getElementById('appointment-form');
        if (!form) return false;
        
        // Check required fields
        const required = ['customer_id', 'vehicle_id', 'service_id'];
        for (const field of required) {
            const input = form.elements[field];
            if (!input || !input.value) {
                return false;
            }
        }
        
        // Check if date and time are selected
        if (!this.selectedDate || !this.selectedSlot) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Reset appointment form
     */
    resetForm() {
        const form = document.getElementById('appointment-form');
        if (form) {
            form.reset();
        }
        
        this.selectedDate = null;
        this.selectedSlot = null;
        this.selectedService = null;
        this.selectedTechnician = null;
        
        this.updateSelectedDate();
        this.updateSelectedSlot();
        this.updatePrice();
        
        const timeSlots = document.getElementById('time-slots');
        if (timeSlots) {
            timeSlots.innerHTML = '';
        }
    }
}

// Initialize appointment manager
const appointmentManager = new AppointmentManager();
