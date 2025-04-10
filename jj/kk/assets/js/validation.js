/**
 * Garage Management System - Form Validation
 * 
 * This file contains validation functions for all forms in the system.
 * It uses jQuery Validation Plugin for client-side validation.
 */

// Make sure jQuery and jQuery Validation Plugin are loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is required for validation.js');
}

// Validation configuration
const GarageValidation = (function() {
    // Default validation settings
    const defaultSettings = {
        errorElement: 'span',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).addClass('is-valid').removeClass('is-invalid');
        },
        errorPlacement: function(error, element) {
            if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else if (element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                error.appendTo(element.parent().parent());
            } else {
                error.insertAfter(element);
            }
        }
    };

    // Common validation rules
    const commonRules = {
        required: {
            required: true,
            messages: {
                required: "هذا الحقل مطلوب"
            }
        },
        email: {
            email: true,
            messages: {
                email: "يرجى إدخال عنوان بريد إلكتروني صالح"
            }
        },
        phone: {
            pattern: /^[0-9]{10,15}$/,
            messages: {
                pattern: "يرجى إدخال رقم هاتف صالح"
            }
        },
        password: {
            minlength: 8,
            messages: {
                minlength: "كلمة المرور يجب أن تكون على الأقل 8 أحرف"
            }
        },
        confirmPassword: {
            equalTo: "#password",
            messages: {
                equalTo: "كلمات المرور غير متطابقة"
            }
        },
        number: {
            number: true,
            messages: {
                number: "يرجى إدخال رقم صالح"
            }
        },
        positiveNumber: {
            min: 0,
            messages: {
                min: "يجب أن يكون الرقم موجبًا"
            }
        },
        date: {
            date: true,
            messages: {
                date: "يرجى إدخال تاريخ صالح"
            }
        }
    };

    // Initialize validation for a form
    const initForm = function(formSelector, customRules = {}) {
        const $form = $(formSelector);
        if ($form.length === 0) {
            return false;
        }

        // Merge default settings with custom rules
        const settings = $.extend(true, {}, defaultSettings, { rules: customRules });
        
        // Initialize jQuery Validation
        $form.validate(settings);
        
        return $form;
    };

    // Login form validation
    const initLoginForm = function() {
        return initForm('#login-form', {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true
            }
        });
    };

    // Registration form validation
    const initRegistrationForm = function() {
        return initForm('#registration-form', {
            name: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                pattern: /^[0-9]{10,15}$/
            },
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            },
            terms: {
                required: true
            }
        });
    };

    // User form validation
    const initUserForm = function() {
        return initForm('#user-form', {
            name: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                pattern: /^[0-9]{10,15}$/
            },
            role: {
                required: true
            }
        });
    };

    // Customer form validation
    const initCustomerForm = function() {
        return initForm('#customer-form', {
            name: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                pattern: /^[0-9]{10,15}$/
            },
            address: {
                required: true
            }
        });
    };

    // Vehicle form validation
    const initVehicleForm = function() {
        return initForm('#vehicle-form', {
            make: {
                required: true
            },
            model: {
                required: true
            },
            year: {
                required: true,
                digits: true,
                min: 1900,
                max: new Date().getFullYear() + 1
            },
            license_plate: {
                required: true
            },
            customer_id: {
                required: true
            }
        });
    };

    // Service form validation
    const initServiceForm = function() {
        return initForm('#service-form', {
            name: {
                required: true
            },
            description: {
                required: true
            },
            price: {
                required: true,
                number: true,
                min: 0
            },
            duration: {
                required: true,
                digits: true,
                min: 1
            }
        });
    };

    // Appointment form validation
    const initAppointmentForm = function() {
        return initForm('#appointment-form', {
            customer_id: {
                required: true
            },
            vehicle_id: {
                required: true
            },
            service_id: {
                required: true
            },
            appointment_date: {
                required: true,
                date: true
            },
            appointment_time: {
                required: true
            },
            technician_id: {
                required: true
            }
        });
    };

    // Invoice form validation
    const initInvoiceForm = function() {
        return initForm('#invoice-form', {
            customer_id: {
                required: true
            },
            vehicle_id: {
                required: true
            },
            invoice_date: {
                required: true,
                date: true
            },
            due_date: {
                required: true,
                date: true
            },
            payment_method: {
                required: true
            }
        });
    };

    // Part form validation
    const initPartForm = function() {
        return initForm('#part-form', {
            name: {
                required: true
            },
            part_number: {
                required: true
            },
            category: {
                required: true
            },
            price: {
                required: true,
                number: true,
                min: 0
            },
            cost: {
                required: true,
                number: true,
                min: 0
            },
            quantity: {
                required: true,
                digits: true,
                min: 0
            }
        });
    };

    // Change password form validation
    const initChangePasswordForm = function() {
        return initForm('#change-password-form', {
            current_password: {
                required: true
            },
            new_password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                equalTo: "#new_password"
            }
        });
    };

    // Settings form validation
    const initSettingsForm = function() {
        return initForm('#settings-form', {
            company_name: {
                required: true
            },
            company_email: {
                required: true,
                email: true
            },
            company_phone: {
                required: true,
                pattern: /^[0-9]{10,15}$/
            },
            company_address: {
                required: true
            }
        });
    };

    // Custom validation methods
    const addCustomMethods = function() {
        // Add custom validation methods here if needed
        $.validator.addMethod("timeFormat", function(value, element) {
            return this.optional(element) || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value);
        }, "يرجى إدخال وقت صالح بتنسيق HH:MM");
        
        $.validator.addMethod("futureDate", function(value, element) {
            const today = new Date().setHours(0, 0, 0, 0);
            const inputDate = new Date(value).setHours(0, 0, 0, 0);
            return this.optional(element) || inputDate >= today;
        }, "يجب أن يكون التاريخ في المستقبل");
        
        $.validator.addMethod("pastDate", function(value, element) {
            const today = new Date().setHours(0, 0, 0, 0);
            const inputDate = new Date(value).setHours(0, 0, 0, 0);
            return this.optional(element) || inputDate <= today;
        }, "يجب أن يكون التاريخ في الماضي");
    };

    // Initialize all forms
    const initAllForms = function() {
        // Add custom validation methods
        addCustomMethods();
        
        // Initialize all forms
        initLoginForm();
        initRegistrationForm();
        initUserForm();
        initCustomerForm();
        initVehicleForm();
        initServiceForm();
        initAppointmentForm();
        initInvoiceForm();
        initPartForm();
        initChangePasswordForm();
        initSettingsForm();
    };

    // Public API
    return {
        init: initAllForms,
        initLoginForm: initLoginForm,
        initRegistrationForm: initRegistrationForm,
        initUserForm: initUserForm,
        initCustomerForm: initCustomerForm,
        initVehicleForm: initVehicleForm,
        initServiceForm: initServiceForm,
        initAppointmentForm: initAppointmentForm,
        initInvoiceForm: initInvoiceForm,
        initPartForm: initPartForm,
        initChangePasswordForm: initChangePasswordForm,
        initSettingsForm: initSettingsForm,
        initForm: initForm
    };
})();

// Initialize validation when document is ready
$(document).ready(function() {
    // Initialize all forms validation
    GarageValidation.init();
    
    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        $(this).valid();
    });
    
    // Prevent form submission if validation fails
    $('form').on('submit', function(e) {
        const form = $(this);
        if (!form.valid()) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.find('.is-invalid:first');
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });
});
