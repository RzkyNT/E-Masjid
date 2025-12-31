/**
 * Friday Schedule Modal Behavior
 * 
 * Extends the base modal functionality with Friday schedule specific features
 * including CRUD operations, form handling, and calendar integration.
 */

// Friday Schedule Modal State
window.FridayScheduleModal = {
    currentMode: 'view',
    currentEventData: null,
    isLoading: false,
    
    // Initialize Friday schedule modal functionality
    init: function() {
        // Listen for modal events
        document.addEventListener('modal:open', this.handleModalOpen.bind(this));
        document.addEventListener('modal:close', this.handleModalClose.bind(this));
        
        // Initialize autocomplete for names
        this.initAutocomplete();
        
        console.log('Friday Schedule Modal initialized');
    },
    
    // Handle modal open events
    handleModalOpen: function(event) {
        const modalId = event.detail.modalId;
        
        if (modalId.includes('fridaySchedule') || modalId.includes('schedule')) {
            this.setupModalForSchedule(modalId);
        }
    },
    
    // Handle modal close events
    handleModalClose: function(event) {
        const modalId = event.detail.modalId;
        
        if (modalId.includes('fridaySchedule') || modalId.includes('schedule')) {
            this.cleanupModalForSchedule(modalId);
        }
    },
    
    // Setup modal for Friday schedule operations
    setupModalForSchedule: function(modalId) {
        const modeInput = document.getElementById(`${modalId}-mode`);
        if (modeInput) {
            this.currentMode = modeInput.value || 'view';
        }
        
        // Setup date picker to only allow Fridays
        this.setupFridayDatePicker(modalId);
        
        // Setup form event listeners
        this.setupFormEventListeners(modalId);
        
        // Focus first input if in add/edit mode
        if (this.currentMode !== 'view') {
            setTimeout(() => {
                const firstInput = document.querySelector(`#${modalId} input:not([type="hidden"])`);
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        }
    },
    
    // Cleanup modal after closing
    cleanupModalForSchedule: function(modalId) {
        this.currentMode = 'view';
        this.currentEventData = null;
        this.isLoading = false;
        
        // Clear any timers or event listeners
        clearModalErrors(modalId);
    },
    
    // Setup Friday date picker
    setupFridayDatePicker: function(modalId) {
        const dateInput = document.getElementById(`${modalId}-friday_date`);
        if (!dateInput) return;
        
        // Set minimum date to next Friday
        const today = new Date();
        const nextFriday = this.getNextFriday(today);
        dateInput.min = nextFriday.toISOString().split('T')[0];
        
        // Add event listener to validate Friday selection
        dateInput.addEventListener('change', (e) => {
            const selectedDate = new Date(e.target.value);
            const dayOfWeek = selectedDate.getDay();
            
            if (dayOfWeek !== 5) { // 5 = Friday
                showFieldError(modalId, 'friday_date', 'Silakan pilih hari Jumat');
                e.target.value = '';
            } else {
                // Clear error if valid Friday is selected
                const errorElement = document.getElementById(`${modalId}-friday_date-error`);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
                const fieldElement = document.getElementById(`${modalId}-friday_date`);
                if (fieldElement) {
                    fieldElement.classList.remove('border-red-500');
                }
            }
        });
        
        // If no date is set and we're in add mode, suggest next Friday
        if (!dateInput.value && this.currentMode === 'add') {
            dateInput.value = nextFriday.toISOString().split('T')[0];
        }
    },
    
    // Get next Friday date
    getNextFriday: function(date = new Date()) {
        const daysUntilFriday = (5 - date.getDay() + 7) % 7;
        const nextFriday = new Date(date);
        nextFriday.setDate(date.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
        return nextFriday;
    },
    
    // Setup form event listeners
    setupFormEventListeners: function(modalId) {
        const form = document.getElementById(`${modalId}-form`);
        if (!form) return;
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(modalId, input);
            });
            
            input.addEventListener('input', () => {
                // Clear error on input
                const fieldName = input.name;
                const errorElement = document.getElementById(`${modalId}-${fieldName}-error`);
                if (errorElement && !errorElement.classList.contains('hidden')) {
                    errorElement.classList.add('hidden');
                    input.classList.remove('border-red-500');
                }
            });
        });
        
        // Form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleFormSubmit(modalId);
        });
    },
    
    // Validate individual field
    validateField: function(modalId, field) {
        const fieldName = field.name;
        const value = field.value.trim();
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            showFieldError(modalId, fieldName, 'Field ini wajib diisi');
            return false;
        }
        
        // Specific field validations
        switch (fieldName) {
            case 'friday_date':
                if (value) {
                    const selectedDate = new Date(value);
                    const dayOfWeek = selectedDate.getDay();
                    
                    if (dayOfWeek !== 5) {
                        showFieldError(modalId, fieldName, 'Tanggal harus hari Jumat');
                        return false;
                    }
                    
                    // Check if date is in the past (only for add mode)
                    if (this.currentMode === 'add') {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        selectedDate.setHours(0, 0, 0, 0);
                        
                        if (selectedDate < today) {
                            showFieldError(modalId, fieldName, 'Tanggal tidak boleh di masa lalu');
                            return false;
                        }
                    }
                }
                break;
                
            case 'prayer_time':
                if (value) {
                    const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                    if (!timePattern.test(value)) {
                        showFieldError(modalId, fieldName, 'Format waktu tidak valid (HH:MM)');
                        return false;
                    }
                }
                break;
                
            case 'imam_name':
            case 'khotib_name':
                if (value && value.length < 2) {
                    showFieldError(modalId, fieldName, 'Nama minimal 2 karakter');
                    return false;
                }
                break;
                
            case 'khutbah_theme':
                if (value && value.length < 5) {
                    showFieldError(modalId, fieldName, 'Tema khutbah minimal 5 karakter');
                    return false;
                }
                break;
        }
        
        return true;
    },
    
    // Handle form submission
    handleFormSubmit: function(modalId) {
        if (validateModalForm(modalId)) {
            saveFridaySchedule(modalId);
        }
    },
    
    // Initialize autocomplete functionality
    initAutocomplete: function() {
        // This would typically connect to a backend API
        // For now, we'll use local storage for suggestions
        this.loadSuggestions();
    },
    
    // Load suggestions from local storage
    loadSuggestions: function() {
        const imamSuggestions = JSON.parse(localStorage.getItem('imam_suggestions') || '[]');
        const khotibSuggestions = JSON.parse(localStorage.getItem('khotib_suggestions') || '[]');
        const themeSuggestions = JSON.parse(localStorage.getItem('theme_suggestions') || '[]');
        
        this.suggestions = {
            imam_name: imamSuggestions,
            khotib_name: khotibSuggestions,
            khutbah_theme: themeSuggestions
        };
    },
    
    // Save suggestions to local storage
    saveSuggestion: function(fieldName, value) {
        if (!value || value.length < 2) return;
        
        const storageKey = `${fieldName.replace('_name', '')}_suggestions`;
        const suggestions = JSON.parse(localStorage.getItem(storageKey) || '[]');
        
        if (!suggestions.includes(value)) {
            suggestions.push(value);
            // Keep only last 10 suggestions
            if (suggestions.length > 10) {
                suggestions.shift();
            }
            localStorage.setItem(storageKey, JSON.stringify(suggestions));
        }
    },
    
    // Setup autocomplete for input field
    setupAutocomplete: function(inputElement, suggestions) {
        if (!inputElement || !suggestions.length) return;
        
        const datalistId = `${inputElement.id}_datalist`;
        let datalist = document.getElementById(datalistId);
        
        if (!datalist) {
            datalist = document.createElement('datalist');
            datalist.id = datalistId;
            inputElement.parentNode.appendChild(datalist);
            inputElement.setAttribute('list', datalistId);
        }
        
        // Update datalist options
        datalist.innerHTML = '';
        suggestions.forEach(suggestion => {
            const option = document.createElement('option');
            option.value = suggestion;
            datalist.appendChild(option);
        });
    }
};

/**
 * Friday Schedule CRUD Operations
 */

// Save Friday schedule (create or update)
async function saveFridaySchedule(modalId) {
    if (FridayScheduleModal.isLoading) return;
    
    try {
        FridayScheduleModal.isLoading = true;
        showModalLoading(modalId, true);
        clearModalErrors(modalId);
        
        const formData = getModalFormData(modalId);
        const mode = formData.mode || 'add';
        
        // Save suggestions for autocomplete
        FridayScheduleModal.saveSuggestion('imam_name', formData.imam_name);
        FridayScheduleModal.saveSuggestion('khotib_name', formData.khotib_name);
        FridayScheduleModal.saveSuggestion('khutbah_theme', formData.khutbah_theme);
        
        // Prepare API request
        const apiUrl = '../../api/friday_schedule_crud.php';
        const requestData = {
            action: mode === 'add' ? 'create' : 'update',
            ...formData
        };
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Success - close modal and refresh calendar
            closeModal(modalId);
            
            // Trigger calendar refresh if available
            if (typeof refreshCalendar === 'function') {
                refreshCalendar();
                console.log('Calendar refreshed after save operation');
            }
            
            // Show success message
            showNotification('Jadwal Jumat berhasil disimpan', 'success');
            
            // Update any list views if visible
            if (currentView === 'list') {
                setTimeout(() => {
                    loadListView();
                }, 500);
            }
            
        } else {
            // Handle validation errors
            if (result.errors) {
                Object.keys(result.errors).forEach(fieldName => {
                    showFieldError(modalId, fieldName, result.errors[fieldName]);
                });
            } else {
                showModalError(modalId, result.message || 'Terjadi kesalahan saat menyimpan data');
            }
        }
        
    } catch (error) {
        console.error('Error saving Friday schedule:', error);
        showModalError(modalId, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        
    } finally {
        FridayScheduleModal.isLoading = false;
        showModalLoading(modalId, false);
    }
}

// Delete Friday schedule
async function deleteFridaySchedule(modalId) {
    if (FridayScheduleModal.isLoading) return;
    
    const formData = getModalFormData(modalId);
    const scheduleId = formData.id;
    
    if (!scheduleId) {
        showModalError(modalId, 'ID jadwal tidak ditemukan');
        return;
    }
    
    // Confirm deletion
    if (!confirm('Apakah Anda yakin ingin menghapus jadwal Jumat ini?')) {
        return;
    }
    
    try {
        FridayScheduleModal.isLoading = true;
        showModalLoading(modalId, true);
        clearModalErrors(modalId);
        
        const apiUrl = '../../api/friday_schedule_crud.php';
        const requestData = {
            action: 'delete',
            id: scheduleId
        };
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Success - close modal and refresh calendar
            closeModal(modalId);
            
            // Trigger calendar refresh if available
            if (typeof refreshCalendar === 'function') {
                refreshCalendar();
                console.log('Calendar refreshed after delete operation');
            }
            
            // Show success message
            showNotification('Jadwal Jumat berhasil dihapus', 'success');
            
            // Update any list views if visible
            if (typeof currentView !== 'undefined' && currentView === 'list') {
                setTimeout(() => {
                    if (typeof loadListView === 'function') {
                        loadListView();
                    }
                }, 500);
            }
            
        } else {
            showModalError(modalId, result.message || 'Terjadi kesalahan saat menghapus data');
        }
        
    } catch (error) {
        console.error('Error deleting Friday schedule:', error);
        showModalError(modalId, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        
    } finally {
        FridayScheduleModal.isLoading = false;
        showModalLoading(modalId, false);
    }
}

// Open Friday schedule modal for adding new schedule
function openAddScheduleModal(selectedDate = null) {
    const modalId = 'addFridayScheduleModal';
    
    // Reset form
    resetModalForm(modalId);
    
    // Set selected date if provided
    if (selectedDate) {
        const dateInput = document.getElementById(`${modalId}-friday_date`);
        if (dateInput) {
            dateInput.value = selectedDate;
        }
    }
    
    // Update modal title
    updateModalTitle(modalId, 'Tambah Agenda Jumat');
    
    // Open modal
    openModal(modalId);
}

// Open Friday schedule modal for viewing schedule
function openViewScheduleModal(eventData) {
    const modalId = 'viewFridayScheduleModal';
    
    // Populate form with event data
    populateModalForm(modalId, eventData);
    
    // Update modal title
    updateModalTitle(modalId, 'Detail Agenda Jumat');
    
    // Open modal
    openModal(modalId);
}

// Open Friday schedule modal for editing schedule
function openEditScheduleModal(eventData) {
    const modalId = 'editFridayScheduleModal';
    
    // Populate form with event data
    populateModalForm(modalId, eventData);
    
    // Update modal title
    updateModalTitle(modalId, 'Edit Agenda Jumat');
    
    // Store current event data
    FridayScheduleModal.currentEventData = eventData;
    
    // Open modal
    openModal(modalId);
}

// Validate modal form
function validateModalForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    if (!form) return false;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!FridayScheduleModal.validateField(modalId, input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Get modal form data
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

// Populate modal form with data
function populateModalForm(modalId, data) {
    if (!data) return;
    
    Object.keys(data).forEach(key => {
        const input = document.getElementById(`${modalId}-${key}`);
        if (input) {
            input.value = data[key] || '';
        }
    });
}

// Reset modal form
function resetModalForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    if (form) {
        form.reset();
        
        // Clear all error messages
        const errorElements = form.querySelectorAll('[id$="-error"]');
        errorElements.forEach(el => {
            el.classList.add('hidden');
        });
        
        // Remove error styling from inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }
}

// Update modal title
function updateModalTitle(modalId, title) {
    const titleElement = document.getElementById(`${modalId}-title`);
    if (titleElement) {
        titleElement.textContent = title;
    }
}

// Show field error
function showFieldError(modalId, fieldName, message) {
    const errorElement = document.getElementById(`${modalId}-${fieldName}-error`);
    const fieldElement = document.getElementById(`${modalId}-${fieldName}`);
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
    
    if (fieldElement) {
        fieldElement.classList.add('border-red-500');
    }
}

// Show modal error
function showModalError(modalId, message) {
    const errorElement = document.getElementById(`${modalId}-error`);
    const errorMessageElement = document.getElementById(`${modalId}-error-message`);
    
    if (errorElement && errorMessageElement) {
        errorMessageElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
}

// Clear modal errors
function clearModalErrors(modalId) {
    const errorElement = document.getElementById(`${modalId}-error`);
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
    
    // Clear field errors
    const form = document.getElementById(`${modalId}-form`);
    if (form) {
        const errorElements = form.querySelectorAll('[id$="-error"]');
        errorElements.forEach(el => {
            el.classList.add('hidden');
        });
        
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }
}

// Show modal loading
function showModalLoading(modalId, show) {
    const loadingElement = document.getElementById(`${modalId}-loading`);
    if (loadingElement) {
        if (show) {
            loadingElement.classList.remove('hidden');
        } else {
            loadingElement.classList.add('hidden');
        }
    }
}

// Show notification message
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    // Set notification style based on type
    switch (type) {
        case 'success':
            notification.classList.add('bg-green-500', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-red-500', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-500', 'text-white');
            break;
        default:
            notification.classList.add('bg-blue-500', 'text-white');
            break;
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Initialize Friday Schedule Modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    FridayScheduleModal.init();
});

// Export functions for global use
window.saveFridaySchedule = saveFridaySchedule;
window.deleteFridaySchedule = deleteFridaySchedule;
window.openAddScheduleModal = openAddScheduleModal;
window.openViewScheduleModal = openViewScheduleModal;
window.openEditScheduleModal = openEditScheduleModal;
window.showNotification = showNotification;
window.validateModalForm = validateModalForm;
window.getModalFormData = getModalFormData;
window.populateModalForm = populateModalForm;
window.resetModalForm = resetModalForm;
window.updateModalTitle = updateModalTitle;
window.showFieldError = showFieldError;
window.showModalError = showModalError;
window.clearModalErrors = clearModalErrors;
window.showModalLoading = showModalLoading;