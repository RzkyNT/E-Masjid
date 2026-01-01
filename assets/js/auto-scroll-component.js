/**
 * AutoScrollComponent - Complete auto scroll functionality with settings persistence
 * Integrates ScrollEngine and SettingsManager for a complete solution
 * Requirements: 7.3, 7.4, 8.4, 8.5
 */
class AutoScrollComponent {
    constructor(containerId = 'auto-scroll-floating') {
        this.containerId = containerId;
        this.container = null;
        this.settingsManager = null;
        this.scrollEngine = null;
        this.isInitialized = false;
        
        // UI elements
        this.elements = {
            mainButton: null,
            settingsButton: null,
            controls: null,
            statusElement: null,
            iconElement: null,
            speedButtons: null,
            directionButtons: null,
            resetButton: null,
            speedIndicator: null,
            speedProgress: null,
            speedIncreaseBtn: null,
            speedDecreaseBtn: null
        };
        
        // State
        this.controlsVisible = false;
        this.displayOptionsVisible = false;
        this.isScrolling = false;
        
        // Bind methods
        this.handleMainButtonClick = this.handleMainButtonClick.bind(this);
        this.handleSettingsButtonClick = this.handleSettingsButtonClick.bind(this);
        this.handleSpeedButtonClick = this.handleSpeedButtonClick.bind(this);
        this.handleDirectionButtonClick = this.handleDirectionButtonClick.bind(this);
        this.handleResetButtonClick = this.handleResetButtonClick.bind(this);
        this.handleKeyboardShortcuts = this.handleKeyboardShortcuts.bind(this);
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            this.initialize();
        }
    }
    
    /**
     * Initialize the component
     * Requirements: 7.3, 8.4
     */
    initialize() {
        try {
            // Find container element
            this.container = document.getElementById(this.containerId);
            if (!this.container) {
                console.error(`Auto scroll container not found: ${this.containerId}`);
                return;
            }
            
            // Initialize settings manager
            this.settingsManager = new SettingsManager();
            
            // Initialize scroll engine with settings manager
            this.scrollEngine = new ScrollEngine(this.settingsManager);
            
            // Get UI elements
            this.getUIElements();
            
            // Set up event listeners
            this.setupEventListeners();
            
            // Restore settings and update UI
            this.restoreSettings();
            
            this.isInitialized = true;
            
            // Dispatch initialization event
            this.dispatchEvent('initialized', { component: this });
            
        } catch (error) {
            console.error('Failed to initialize AutoScrollComponent:', error);
        }
    }
    
    /**
     * Get references to UI elements
     */
    getUIElements() {
        this.elements.mainButton = document.getElementById('auto-scroll-main-btn');
        this.elements.iconElement = document.getElementById('auto-scroll-icon');
        this.elements.speedIncreaseFloating = document.getElementById('speed-increase-floating');
        this.elements.speedDecreaseFloating = document.getElementById('speed-decrease-floating');
        this.elements.displayOptionsBtn = document.getElementById('display-options-btn');
        this.elements.displayOptionsPanel = document.getElementById('display-options-panel');
        this.elements.speedIndicatorFloating = document.getElementById('speed-indicator-floating');
        this.elements.speedText = document.getElementById('speed-text');
        
        // Display options checkboxes
        this.elements.showTransliteration = document.getElementById('show-transliteration');
        this.elements.showTranslation = document.getElementById('show-translation');
        this.elements.showTafsir = document.getElementById('show-tafsir');
        this.elements.resetDisplayOptions = document.getElementById('reset-display-options');
        
        // Legacy elements (may not exist in simplified design)
        this.elements.settingsButton = document.getElementById('auto-scroll-settings-btn');
        this.elements.controls = document.getElementById('auto-scroll-controls');
        this.elements.statusElement = document.getElementById('auto-scroll-status');
        this.elements.resetButton = document.getElementById('auto-scroll-reset');
        this.elements.speedIndicator = document.getElementById('speed-indicator');
        this.elements.speedProgress = document.getElementById('speed-progress');
        this.elements.speedIncreaseBtn = document.getElementById('speed-increase-btn');
        this.elements.speedDecreaseBtn = document.getElementById('speed-decrease-btn');
        this.elements.speedValue = document.getElementById('speed-value');
        this.elements.speedControlButtons = document.getElementById('speed-control-buttons');
        
        // Get button collections (may be empty in simplified design)
        this.elements.speedButtons = document.querySelectorAll('.speed-btn');
        this.elements.directionButtons = document.querySelectorAll('.direction-btn');
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Main button
        if (this.elements.mainButton) {
            this.elements.mainButton.addEventListener('click', this.handleMainButtonClick);
        }
        
        // Floating speed controls
        if (this.elements.speedIncreaseFloating) {
            this.elements.speedIncreaseFloating.addEventListener('click', (e) => {
                e.stopPropagation();
                this.scrollEngine.increaseSpeed();
                this.updateUI();
                this.showSpeedFeedback('+');
            });
        }
        
        if (this.elements.speedDecreaseFloating) {
            this.elements.speedDecreaseFloating.addEventListener('click', (e) => {
                e.stopPropagation();
                this.scrollEngine.decreaseSpeed();
                this.updateUI();
                this.showSpeedFeedback('-');
            });
        }
        
        // Display options button
        if (this.elements.displayOptionsBtn) {
            this.elements.displayOptionsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDisplayOptions();
            });
        }
        
        // Display options checkboxes
        if (this.elements.showTransliteration) {
            this.elements.showTransliteration.addEventListener('change', () => {
                this.toggleContentVisibility('transliteration-content', this.elements.showTransliteration.checked);
                this.saveDisplaySettings();
            });
        }
        
        if (this.elements.showTranslation) {
            this.elements.showTranslation.addEventListener('change', () => {
                this.toggleContentVisibility('translation-content', this.elements.showTranslation.checked);
                this.saveDisplaySettings();
            });
        }
        
        if (this.elements.showTafsir) {
            this.elements.showTafsir.addEventListener('change', () => {
                this.toggleContentVisibility('tafsir-content', this.elements.showTafsir.checked);
                this.saveDisplaySettings();
            });
        }
        
        // Reset display options
        if (this.elements.resetDisplayOptions) {
            this.elements.resetDisplayOptions.addEventListener('click', (e) => {
                e.stopPropagation();
                this.resetDisplaySettings();
            });
        }
        
        // Legacy elements (if they exist)
        if (this.elements.settingsButton) {
            this.elements.settingsButton.addEventListener('click', this.handleSettingsButtonClick);
        }
        
        if (this.elements.speedButtons.length > 0) {
            this.elements.speedButtons.forEach(button => {
                button.addEventListener('click', this.handleSpeedButtonClick);
            });
        }
        
        if (this.elements.directionButtons.length > 0) {
            this.elements.directionButtons.forEach(button => {
                button.addEventListener('click', this.handleDirectionButtonClick);
            });
        }
        
        if (this.elements.speedIncreaseBtn) {
            this.elements.speedIncreaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.scrollEngine.increaseSpeed();
                this.updateUI();
            });
        }
        
        if (this.elements.speedDecreaseBtn) {
            this.elements.speedDecreaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.scrollEngine.decreaseSpeed();
                this.updateUI();
            });
        }
        
        if (this.elements.resetButton) {
            this.elements.resetButton.addEventListener('click', this.handleResetButtonClick);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts);
        
        // Outside click to close display options
        document.addEventListener('click', this.handleOutsideClick);
        
        // Prevent display options panel from closing when clicking inside
        if (this.elements.displayOptionsPanel) {
            this.elements.displayOptionsPanel.addEventListener('click', (e) => e.stopPropagation());
        }
        
        // Prevent controls from closing when clicking inside (legacy)
        if (this.elements.controls) {
            this.elements.controls.addEventListener('click', (e) => e.stopPropagation());
        }
        
        // ScrollEngine events
        document.addEventListener('scrollEngine:start', () => {
            this.isScrolling = true;
            this.updateUI();
        });
        
        document.addEventListener('scrollEngine:stop', () => {
            this.isScrolling = false;
            this.updateUI();
        });
        
        document.addEventListener('scrollEngine:speedChange', () => {
            this.updateUI();
        });
        
        document.addEventListener('scrollEngine:directionChange', () => {
            this.updateUI();
        });
        
        document.addEventListener('scrollEngine:settingsReset', () => {
            this.updateUI();
        });
        
        // New pause/resume events for user interaction handling
        document.addEventListener('scrollEngine:pausedByUser', (e) => {
            this.handlePauseByUser(e.detail);
        });
        
        document.addEventListener('scrollEngine:resumedFromPause', (e) => {
            this.handleResumeFromPause(e.detail);
        });
        
        document.addEventListener('scrollEngine:paused', (e) => {
            this.handleScrollPaused(e.detail);
        });
        
        // Temporary disable events for feature compatibility
        document.addEventListener('scrollEngine:temporaryDisabled', (e) => {
            this.handleTemporaryDisabled(e.detail);
        });
        
        document.addEventListener('scrollEngine:temporaryEnabled', (e) => {
            this.handleTemporaryEnabled(e.detail);
        });
    }
    
    /**
     * Restore settings from storage
     * Requirements: 7.3
     */
    restoreSettings() {
        try {
            const settings = this.settingsManager.loadSettings();
            
            // Apply settings to scroll engine
            this.scrollEngine.applySettings(settings);
            
            // Load display settings
            this.loadDisplaySettings();
            
            // Update UI to reflect loaded settings
            this.updateUI();
            
            // Dispatch event
            this.dispatchEvent('settingsRestored', { settings });
            
        } catch (error) {
            console.error('Failed to restore settings:', error);
        }
    }
    
    /**
     * Handle main button click
     */
    handleMainButtonClick(e) {
        e.stopPropagation();
        
        if (this.isScrolling) {
            this.scrollEngine.stop();
        } else {
            this.scrollEngine.start();
        }
    }
    
    /**
     * Handle settings button click
     */
    handleSettingsButtonClick(e) {
        e.stopPropagation();
        this.toggleControls();
    }
    
    /**
     * Handle speed button click
     */
    handleSpeedButtonClick(e) {
        e.stopPropagation();
        const speed = e.target.dataset.speed;
        if (speed) {
            this.scrollEngine.setSpeed(speed);
            this.updateUI();
        }
    }
    
    /**
     * Handle direction button click
     */
    handleDirectionButtonClick(e) {
        e.stopPropagation();
        const direction = e.target.dataset.direction;
        if (direction) {
            this.scrollEngine.setDirection(direction);
            this.updateUI();
        }
    }
    
    /**
     * Handle reset button click
     * Requirements: 7.4
     */
    handleResetButtonClick(e) {
        e.stopPropagation();
        
        // Stop scrolling if active
        if (this.isScrolling) {
            this.scrollEngine.stop();
        }
        
        // Reset to defaults
        this.scrollEngine.resetToDefaults();
        
        // Update UI
        this.updateUI();
        
        // Dispatch event
        this.dispatchEvent('settingsReset');
    }
    
    /**
     * Handle keyboard shortcuts
     */
    handleKeyboardShortcuts(e) {
        if (e.key === 'Escape') {
            if (this.displayOptionsVisible) {
                this.hideDisplayOptions();
            } else if (this.controlsVisible) {
                this.hideControls();
            }
        } else if (e.key === ' ' || e.code === 'Space') {
            e.preventDefault();
            if (this.isScrolling) {
                this.scrollEngine.stop();
            } else {
                this.scrollEngine.start();
            }
        } else if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            this.scrollEngine.increaseSpeed();
            this.updateUI();
            this.showSpeedFeedback('+');
        } else if (e.key === '-') {
            e.preventDefault();
            this.scrollEngine.decreaseSpeed();
            this.updateUI();
            this.showSpeedFeedback('-');
        }
    }
    
    /**
     * Handle outside click
     */
    handleOutsideClick(e) {
        if (!this.container.contains(e.target)) {
            if (this.controlsVisible) {
                this.hideControls();
            }
            if (this.displayOptionsVisible) {
                this.hideDisplayOptions();
            }
        }
    }
    
    /**
     * Toggle display options visibility
     */
    toggleDisplayOptions() {
        if (this.displayOptionsVisible) {
            this.hideDisplayOptions();
        } else {
            this.showDisplayOptions();
        }
    }
    
    /**
     * Show display options panel
     */
    showDisplayOptions() {
        if (this.elements.displayOptionsPanel) {
            this.elements.displayOptionsPanel.classList.add('show');
        }
        this.displayOptionsVisible = true;
    }
    
    /**
     * Hide display options panel
     */
    hideDisplayOptions() {
        if (this.elements.displayOptionsPanel) {
            this.elements.displayOptionsPanel.classList.remove('show');
        }
        this.displayOptionsVisible = false;
    }
    
    /**
     * Toggle content visibility
     */
    toggleContentVisibility(className, show) {
        const elements = document.querySelectorAll('.' + className);
        elements.forEach(element => {
            if (show) {
                element.classList.remove('hidden', 'hiding');
            } else {
                element.classList.add('hiding');
                setTimeout(() => {
                    element.classList.add('hidden');
                    element.classList.remove('hiding');
                }, 300);
            }
        });
    }
    
    /**
     * Save display settings to localStorage
     */
    saveDisplaySettings() {
        const settings = {
            showTransliteration: this.elements.showTransliteration ? this.elements.showTransliteration.checked : true,
            showTranslation: this.elements.showTranslation ? this.elements.showTranslation.checked : true,
            showTafsir: this.elements.showTafsir ? this.elements.showTafsir.checked : true
        };
        localStorage.setItem('alquran_display_settings', JSON.stringify(settings));
    }
    
    /**
     * Load display settings from localStorage
     */
    loadDisplaySettings() {
        try {
            const saved = localStorage.getItem('alquran_display_settings');
            if (saved) {
                const settings = JSON.parse(saved);
                
                if (this.elements.showTransliteration) {
                    this.elements.showTransliteration.checked = settings.showTransliteration !== false;
                    this.toggleContentVisibility('transliteration-content', this.elements.showTransliteration.checked);
                }
                
                if (this.elements.showTranslation) {
                    this.elements.showTranslation.checked = settings.showTranslation !== false;
                    this.toggleContentVisibility('translation-content', this.elements.showTranslation.checked);
                }
                
                if (this.elements.showTafsir) {
                    this.elements.showTafsir.checked = settings.showTafsir !== false;
                    this.toggleContentVisibility('tafsir-content', this.elements.showTafsir.checked);
                }
            }
        } catch (error) {
            console.error('Failed to load display settings:', error);
        }
    }
    
    /**
     * Reset display settings to defaults
     */
    resetDisplaySettings() {
        if (this.elements.showTransliteration) {
            this.elements.showTransliteration.checked = true;
            this.toggleContentVisibility('transliteration-content', true);
        }
        
        if (this.elements.showTranslation) {
            this.elements.showTranslation.checked = true;
            this.toggleContentVisibility('translation-content', true);
        }
        
        if (this.elements.showTafsir) {
            this.elements.showTafsir.checked = true;
            this.toggleContentVisibility('tafsir-content', true);
        }
        
        localStorage.removeItem('alquran_display_settings');
    }
    
    /**
     * Toggle controls visibility
     */
    toggleControls() {
        if (this.controlsVisible) {
            this.hideControls();
        } else {
            this.showControls();
        }
    }
    
    /**
     * Show controls
     */
    showControls() {
        if (this.elements.controls) {
            this.elements.controls.classList.add('show');
        }
        if (this.elements.settingsButton) {
            this.elements.settingsButton.classList.add('show');
        }
        if (this.elements.speedControlButtons) {
            this.elements.speedControlButtons.classList.add('show');
        }
        this.controlsVisible = true;
    }
    
    /**
     * Hide controls
     */
    hideControls() {
        if (this.elements.controls) {
            this.elements.controls.classList.remove('show');
        }
        if (this.elements.settingsButton) {
            this.elements.settingsButton.classList.remove('show');
        }
        if (this.elements.speedControlButtons) {
            this.elements.speedControlButtons.classList.remove('show');
        }
        this.controlsVisible = false;
    }
    
    /**
     * Show speed feedback
     */
    showSpeedFeedback(action) {
        // Update speed indicator floating element
        if (this.elements.speedIndicatorFloating && this.elements.speedText) {
            const currentSpeed = this.scrollEngine.getCurrentSpeed();
            
            let speedLabel = '';
            if (currentSpeed <= 22) {
                speedLabel = 'Lambat';
            } else if (currentSpeed <= 41) {
                speedLabel = 'Sedang';
            } else {
                speedLabel = 'Cepat';
            }
            
            this.elements.speedText.textContent = speedLabel;
            this.elements.speedIndicatorFloating.classList.add('show');
            
            // Hide after delay
            setTimeout(() => {
                this.elements.speedIndicatorFloating.classList.remove('show');
            }, 2000);
        }
        
        // Show temporary feedback popup
        const feedbackElement = document.createElement('div');
        feedbackElement.className = 'fixed bottom-20 right-20 bg-gray-800 text-white px-3 py-2 rounded-lg text-sm font-medium z-50 transition-all duration-300';
        feedbackElement.style.opacity = '0';
        feedbackElement.style.transform = 'translateY(10px)';
        
        const currentSpeed = this.scrollEngine.getCurrentSpeed();
        feedbackElement.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas fa-${action === '+' ? 'plus' : 'minus'} text-xs"></i>
                <span>${action === '+' ? 'Dipercepat' : 'Diperlambat'}</span>
                <span class="text-gray-300">(${currentSpeed}px/s)</span>
            </div>
        `;
        
        document.body.appendChild(feedbackElement);
        
        // Animate in
        setTimeout(() => {
            feedbackElement.style.opacity = '1';
            feedbackElement.style.transform = 'translateY(0)';
        }, 10);
        
        // Remove after delay
        setTimeout(() => {
            feedbackElement.style.opacity = '0';
            feedbackElement.style.transform = 'translateY(10px)';
            setTimeout(() => {
                if (feedbackElement.parentNode) {
                    feedbackElement.parentNode.removeChild(feedbackElement);
                }
            }, 300);
        }, 1500);
    }
    
    /**
     * Update UI to reflect current state
     */
    updateUI() {
        this.updateMainButton();
        
        // Update legacy elements if they exist
        if (this.elements.statusElement) {
            this.updateStatusText();
        }
        if (this.elements.speedButtons.length > 0) {
            this.updateSpeedButtons();
        }
        if (this.elements.directionButtons.length > 0) {
            this.updateDirectionButtons();
        }
        if (this.elements.speedIndicator && this.elements.speedProgress) {
            this.updateSpeedIndicator();
        }
    }
    
    /**
     * Update main button state
     */
    updateMainButton() {
        if (!this.elements.mainButton || !this.elements.iconElement) return;
        
        if (this.isScrolling) {
            this.elements.mainButton.classList.add('active');
            this.elements.iconElement.className = 'fas fa-pause text-lg group-hover:scale-110 transition-transform duration-200';
        } else {
            this.elements.mainButton.classList.remove('active');
            this.elements.iconElement.className = 'fas fa-play text-lg group-hover:scale-110 transition-transform duration-200';
        }
    }
    
    /**
     * Handle pause by user interaction
     */
    handlePauseByUser(detail) {
        // Update status to show paused state
        if (this.elements.statusElement) {
            this.elements.statusElement.textContent = 'Dijeda';
            this.elements.statusElement.className = 'text-xs font-medium text-yellow-600';
        }
        
        // Add visual indicator to main button
        if (this.elements.mainButton) {
            this.elements.mainButton.classList.add('paused');
        }
        
        // Dispatch event for external listeners
        this.dispatchEvent('pausedByUser', { source: detail.source });
    }
    
    /**
     * Handle resume from pause
     */
    handleResumeFromPause(detail) {
        // Update status back to active
        if (this.elements.statusElement) {
            this.elements.statusElement.textContent = 'Aktif';
            this.elements.statusElement.className = 'text-xs font-medium text-green-600';
        }
        
        // Remove paused visual indicator
        if (this.elements.mainButton) {
            this.elements.mainButton.classList.remove('paused');
        }
        
        // Dispatch event for external listeners
        this.dispatchEvent('resumedFromPause', { source: detail.source });
    }
    
    /**
     * Handle scroll paused event
     */
    handleScrollPaused(detail) {
        // This is called during the scroll loop when paused
        // We can use this for more granular feedback if needed
        if (detail.reason === 'user_interaction') {
            // Could add subtle animation or indicator here
        }
    }
    
    /**
     * Handle temporary disable for feature compatibility
     */
    handleTemporaryDisabled(detail) {
        // Update UI to show temporary disabled state
        if (this.elements.statusElement) {
            this.elements.statusElement.textContent = 'Sementara Nonaktif';
            this.elements.statusElement.className = 'text-xs font-medium text-orange-600';
        }
        
        // Add visual indicator
        if (this.elements.mainButton) {
            this.elements.mainButton.classList.add('temporarily-disabled');
        }
        
        // Dispatch event
        this.dispatchEvent('temporaryDisabled', { reason: detail.reason, duration: detail.duration });
    }
    
    /**
     * Handle temporary enable after feature usage
     */
    handleTemporaryEnabled(detail) {
        // Remove temporary disabled indicator
        if (this.elements.mainButton) {
            this.elements.mainButton.classList.remove('temporarily-disabled');
        }
        
        // Update UI back to normal
        this.updateUI();
        
        // Dispatch event
        this.dispatchEvent('temporaryEnabled', { reason: detail.reason });
    }
    
    /**
     * Update status text
     */
    updateStatusText() {
        if (!this.elements.statusElement) return;
        
        const state = this.scrollEngine.getState();
        let status, className;
        
        if (state.isActive && state.pausedByUser) {
            status = 'Dijeda';
            className = 'text-xs font-medium text-yellow-600';
        } else if (state.isActive) {
            status = 'Aktif';
            className = 'text-xs font-medium text-green-600';
        } else {
            status = 'Tidak Aktif';
            className = 'text-xs font-medium text-gray-500';
        }
        
        this.elements.statusElement.textContent = status;
        this.elements.statusElement.className = className;
    }
    
    /**
     * Update speed buttons
     */
    updateSpeedButtons() {
        const currentSpeed = this.scrollEngine.speed;
        
        this.elements.speedButtons.forEach(button => {
            if (button.dataset.speed === currentSpeed) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
    
    /**
     * Update direction buttons
     */
    updateDirectionButtons() {
        const currentDirection = this.scrollEngine.direction;
        
        this.elements.directionButtons.forEach(button => {
            if (button.dataset.direction === currentDirection) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
    
    /**
     * Update speed indicator
     */
    updateSpeedIndicator() {
        if (!this.elements.speedIndicator || !this.elements.speedProgress) return;
        
        const state = this.scrollEngine.getState();
        const currentSpeed = state.currentSpeed;
        const speedIndex = state.speedIndex;
        const maxIndex = this.scrollEngine.speedLevels.length - 1;
        
        // Update text indicator
        let speedText = '';
        if (currentSpeed <= 22) {
            speedText = 'Lambat';
        } else if (currentSpeed <= 41) {
            speedText = 'Sedang';
        } else {
            speedText = 'Cepat';
        }
        
        this.elements.speedIndicator.textContent = speedText;
        
        // Update speed value display
        if (this.elements.speedValue) {
            this.elements.speedValue.textContent = `(${currentSpeed}px/s)`;
        }
        
        // Update progress bar
        const progressPercent = (speedIndex / maxIndex) * 100;
        this.elements.speedProgress.style.width = `${progressPercent}%`;
        
        // Update progress bar color based on speed
        if (currentSpeed <= 22) {
            this.elements.speedProgress.className = 'bg-blue-500 h-2 rounded-full transition-all duration-300';
        } else if (currentSpeed <= 41) {
            this.elements.speedProgress.className = 'bg-green-500 h-2 rounded-full transition-all duration-300';
        } else {
            this.elements.speedProgress.className = 'bg-red-500 h-2 rounded-full transition-all duration-300';
        }
    }
    
    /**
     * Get current settings
     */
    getCurrentSettings() {
        return this.scrollEngine ? this.scrollEngine.getCurrentSettings() : null;
    }
    
    /**
     * Reset to defaults
     * Requirements: 7.4
     */
    resetToDefaults() {
        this.handleResetButtonClick({ stopPropagation: () => {} });
    }
    
    /**
     * Dispatch custom events
     */
    dispatchEvent(type, data = {}) {
        const event = new CustomEvent(`autoScroll:${type}`, {
            detail: { ...data, component: this }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Destroy the component
     */
    destroy() {
        if (this.scrollEngine) {
            this.scrollEngine.destroy();
        }
        
        // Remove event listeners
        document.removeEventListener('keydown', this.handleKeyboardShortcuts);
        document.removeEventListener('click', this.handleOutsideClick);
        
        this.isInitialized = false;
    }
    
    /**
     * Get component info for debugging
     */
    getInfo() {
        return {
            isInitialized: this.isInitialized,
            isScrolling: this.isScrolling,
            controlsVisible: this.controlsVisible,
            currentSettings: this.getCurrentSettings(),
            storageInfo: this.settingsManager ? this.settingsManager.getStorageInfo() : null
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoScrollComponent;
}