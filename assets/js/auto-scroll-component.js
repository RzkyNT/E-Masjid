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
        this.elements.settingsButton = document.getElementById('auto-scroll-settings-btn');
        this.elements.controls = document.getElementById('auto-scroll-controls');
        this.elements.statusElement = document.getElementById('auto-scroll-status');
        this.elements.iconElement = document.getElementById('auto-scroll-icon');
        this.elements.resetButton = document.getElementById('auto-scroll-reset');
        this.elements.speedIndicator = document.getElementById('speed-indicator');
        this.elements.speedProgress = document.getElementById('speed-progress');
        this.elements.speedIncreaseBtn = document.getElementById('speed-increase');
        this.elements.speedDecreaseBtn = document.getElementById('speed-decrease');
        
        // Get button collections
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
        
        // Settings button
        if (this.elements.settingsButton) {
            this.elements.settingsButton.addEventListener('click', this.handleSettingsButtonClick);
        }
        
        // Speed buttons
        this.elements.speedButtons.forEach(button => {
            button.addEventListener('click', this.handleSpeedButtonClick);
        });
        
        // Direction buttons
        this.elements.directionButtons.forEach(button => {
            button.addEventListener('click', this.handleDirectionButtonClick);
        });
        
        // Fine speed controls
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
        
        // Reset button
        if (this.elements.resetButton) {
            this.elements.resetButton.addEventListener('click', this.handleResetButtonClick);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts);
        
        // Outside click to close controls
        document.addEventListener('click', this.handleOutsideClick);
        
        // Prevent controls from closing when clicking inside
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
        if (e.key === 'Escape' && this.controlsVisible) {
            this.hideControls();
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
        } else if (e.key === '-') {
            e.preventDefault();
            this.scrollEngine.decreaseSpeed();
            this.updateUI();
        }
    }
    
    /**
     * Handle outside click
     */
    handleOutsideClick(e) {
        if (!this.container.contains(e.target) && this.controlsVisible) {
            this.hideControls();
        }
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
        this.controlsVisible = false;
    }
    
    /**
     * Update UI to reflect current state
     */
    updateUI() {
        this.updateMainButton();
        this.updateStatusText();
        this.updateSpeedButtons();
        this.updateDirectionButtons();
        this.updateSpeedIndicator();
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
        
        // Add custom speed info if using fine control
        if (state.customSpeed) {
            speedText += ` (${currentSpeed}px/s)`;
        }
        
        this.elements.speedIndicator.textContent = speedText;
        
        // Update progress bar
        const progressPercent = (speedIndex / maxIndex) * 100;
        this.elements.speedProgress.style.width = `${progressPercent}%`;
        
        // Update progress bar color based on speed
        if (currentSpeed <= 22) {
            this.elements.speedProgress.className = 'bg-blue-500 h-1.5 rounded-full transition-all duration-300';
        } else if (currentSpeed <= 41) {
            this.elements.speedProgress.className = 'bg-green-500 h-1.5 rounded-full transition-all duration-300';
        } else {
            this.elements.speedProgress.className = 'bg-red-500 h-1.5 rounded-full transition-all duration-300';
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