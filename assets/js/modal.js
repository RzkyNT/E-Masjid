/**
 * Reusable Modal Component JavaScript
 * 
 * Provides functionality for opening, closing, and managing modal state
 * with support for animations, keyboard navigation, and form handling.
 */

// Global modal state management
window.ModalManager = {
    openModals: new Set(),
    
    // Initialize modal event listeners
    init: function() {
        // ESC key handler for all modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ModalManager.closeTopModal();
            }
        });
        
        // Click outside handler for all modals
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                const modal = e.target;
                const backdropClose = modal.getAttribute('data-backdrop-close') === 'true';
                if (backdropClose) {
                    const modalId = modal.id;
                    closeModal(modalId);
                }
            }
        });
        
        // Prevent body scroll when modal is open
        this.updateBodyScroll();
    },
    
    // Add modal to open set
    addModal: function(modalId) {
        this.openModals.add(modalId);
        this.updateBodyScroll();
    },
    
    // Remove modal from open set
    removeModal: function(modalId) {
        this.openModals.delete(modalId);
        this.updateBodyScroll();
    },
    
    // Close the topmost modal
    closeTopModal: function() {
        if (this.openModals.size > 0) {
            const modalIds = Array.from(this.openModals);
            const topModalId = modalIds[modalIds.length - 1];
            closeModal(topModalId);
        }
    },
    
    // Update body scroll based on open modals
    updateBodyScroll: function() {
        if (this.openModals.size > 0) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
};

/**
 * Open a modal with animation
 * @param {string} modalId - The ID of the modal to open
 * @param {Object} options - Additional options for opening the modal
 */
function openModal(modalId, options = {}) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID '${modalId}' not found`);
        return;
    }
    
    // Default options
    const defaults = {
        animation: true,
        focus: true,
        onOpen: null
    };
    
    options = Object.assign(defaults, options);
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Add animation class if enabled
    if (options.animation) {
        const container = modal.querySelector('.modal-container') || modal.firstElementChild;
        if (container) {
            container.classList.add('modal-enter');
            setTimeout(() => {
                container.classList.remove('modal-enter');
            }, 300);
        }
    }
    
    // Focus management
    if (options.focus) {
        // Focus first focusable element in modal
        setTimeout(() => {
            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }
        }, 100);
    }
    
    // Add to modal manager
    ModalManager.addModal(modalId);
    
    // Call onOpen callback
    if (typeof options.onOpen === 'function') {
        options.onOpen(modalId);
    }
    
    // Trigger custom event
    modal.dispatchEvent(new CustomEvent('modal:open', { detail: { modalId, options } }));
}

/**
 * Close a modal with animation
 * @param {string} modalId - The ID of the modal to close
 * @param {Object} options - Additional options for closing the modal
 */
function closeModal(modalId, options = {}) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID '${modalId}' not found`);
        return;
    }
    
    // Default options
    const defaults = {
        animation: true,
        onClose: null
    };
    
    options = Object.assign(defaults, options);
    
    // Add animation class if enabled
    if (options.animation) {
        const container = modal.querySelector('.modal-container') || modal.firstElementChild;
        if (container) {
            container.classList.add('modal-exit');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                container.classList.remove('modal-exit');
                
                // Remove from modal manager
                ModalManager.removeModal(modalId);
                
                // Call onClose callback
                if (typeof options.onClose === 'function') {
                    options.onClose(modalId);
                }
                
                // Trigger custom event
                modal.dispatchEvent(new CustomEvent('modal:close', { detail: { modalId, options } }));
            }, 300);
        }
    } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        
        // Remove from modal manager
        ModalManager.removeModal(modalId);
        
        // Call onClose callback
        if (typeof options.onClose === 'function') {
            options.onClose(modalId);
        }
        
        // Trigger custom event
        modal.dispatchEvent(new CustomEvent('modal:close', { detail: { modalId, options } }));
    }
    
    // Clear any form errors
    clearModalErrors(modalId);
}

/**
 * Toggle modal open/close state
 * @param {string} modalId - The ID of the modal to toggle
 */
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID '${modalId}' not found`);
        return;
    }
    
    if (modal.classList.contains('hidden')) {
        openModal(modalId);
    } else {
        closeModal(modalId);
    }
}

/**
 * Update modal title
 * @param {string} modalId - The ID of the modal
 * @param {string} title - New title text
 */
function updateModalTitle(modalId, title) {
    const titleElement = document.getElementById(`${modalId}-title`);
    if (titleElement) {
        titleElement.textContent = title;
    }
}

/**
 * Update modal content
 * @param {string} modalId - The ID of the modal
 * @param {string} content - New HTML content
 */
function updateModalContent(modalId, content) {
    const bodyElement = document.getElementById(`${modalId}-body`);
    if (bodyElement) {
        bodyElement.innerHTML = content;
    }
}

/**
 * Update modal footer
 * @param {string} modalId - The ID of the modal
 * @param {string} footer - New HTML footer content
 */
function updateModalFooter(modalId, footer) {
    const footerElement = document.getElementById(`${modalId}-footer`);
    if (footerElement) {
        footerElement.innerHTML = footer;
    }
}

/**
 * Show loading state in modal
 * @param {string} modalId - The ID of the modal
 * @param {boolean} show - Whether to show or hide loading
 */
function showModalLoading(modalId, show = true) {
    const loadingElement = document.getElementById(`${modalId}-loading`);
    const formElement = document.getElementById(`${modalId}-form`);
    
    if (loadingElement) {
        if (show) {
            loadingElement.classList.remove('hidden');
            if (formElement) {
                formElement.style.opacity = '0.5';
                formElement.style.pointerEvents = 'none';
            }
        } else {
            loadingElement.classList.add('hidden');
            if (formElement) {
                formElement.style.opacity = '';
                formElement.style.pointerEvents = '';
            }
        }
    }
}

/**
 * Show error message in modal
 * @param {string} modalId - The ID of the modal
 * @param {string} message - Error message to display
 */
function showModalError(modalId, message) {
    const errorElement = document.getElementById(`${modalId}-error`);
    const errorMessageElement = document.getElementById(`${modalId}-error-message`);
    
    if (errorElement && errorMessageElement) {
        errorMessageElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
}

/**
 * Clear all error messages in modal
 * @param {string} modalId - The ID of the modal
 */
function clearModalErrors(modalId) {
    // Clear general error
    const errorElement = document.getElementById(`${modalId}-error`);
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
    
    // Clear field-specific errors
    const modal = document.getElementById(modalId);
    if (modal) {
        const errorElements = modal.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
        
        // Remove error styling from inputs
        const inputs = modal.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }
}

/**
 * Show field-specific error
 * @param {string} modalId - The ID of the modal
 * @param {string} fieldName - Name of the field
 * @param {string} message - Error message
 */
function showFieldError(modalId, fieldName, message) {
    const fieldElement = document.getElementById(`${modalId}-${fieldName}`);
    const errorElement = document.getElementById(`${modalId}-${fieldName}-error`);
    
    if (fieldElement) {
        fieldElement.classList.add('border-red-500');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
}

/**
 * Validate modal form
 * @param {string} modalId - The ID of the modal
 * @returns {boolean} - Whether form is valid
 */
function validateModalForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    if (!form) return true;
    
    let isValid = true;
    clearModalErrors(modalId);
    
    // Get all required fields
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        const fieldName = field.name;
        
        if (!value) {
            showFieldError(modalId, fieldName, 'Field ini wajib diisi');
            isValid = false;
        }
    });
    
    // Validate date field (must be Friday)
    const dateField = document.getElementById(`${modalId}-friday_date`);
    if (dateField && dateField.value) {
        const selectedDate = new Date(dateField.value);
        const dayOfWeek = selectedDate.getDay();
        
        if (dayOfWeek !== 5) { // 5 = Friday
            showFieldError(modalId, 'friday_date', 'Tanggal harus hari Jumat');
            isValid = false;
        }
    }
    
    // Validate time field
    const timeField = document.getElementById(`${modalId}-prayer_time`);
    if (timeField && timeField.value) {
        const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        if (!timePattern.test(timeField.value)) {
            showFieldError(modalId, 'prayer_time', 'Format waktu tidak valid');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Get form data from modal
 * @param {string} modalId - The ID of the modal
 * @returns {Object} - Form data as object
 */
function getModalFormData(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    if (!form) return {};
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

/**
 * Populate modal form with data
 * @param {string} modalId - The ID of the modal
 * @param {Object} data - Data to populate form with
 */
function populateModalForm(modalId, data) {
    const form = document.getElementById(`${modalId}-form`);
    if (!form || !data) return;
    
    Object.keys(data).forEach(key => {
        const field = document.getElementById(`${modalId}-${key}`);
        if (field) {
            field.value = data[key] || '';
        }
    });
}

/**
 * Reset modal form
 * @param {string} modalId - The ID of the modal
 */
function resetModalForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    if (form) {
        form.reset();
        clearModalErrors(modalId);
    }
}

// Initialize modal manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    ModalManager.init();
});

// Export functions for use in other scripts
window.openModal = openModal;
window.closeModal = closeModal;
window.toggleModal = toggleModal;
window.updateModalTitle = updateModalTitle;
window.updateModalContent = updateModalContent;
window.updateModalFooter = updateModalFooter;
window.showModalLoading = showModalLoading;
window.showModalError = showModalError;
window.clearModalErrors = clearModalErrors;
window.showFieldError = showFieldError;
window.validateModalForm = validateModalForm;
window.getModalFormData = getModalFormData;
window.populateModalForm = populateModalForm;
window.resetModalForm = resetModalForm;