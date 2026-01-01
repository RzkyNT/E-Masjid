/**
 * ScrollStatusIndicator - Visual feedback component for auto scroll status
 * Provides active/inactive state indicators with smooth transitions
 */
class ScrollStatusIndicator {
    constructor(containerElement) {
        this.container = containerElement;
        this.isInitialized = false;
        
        // Bind methods
        this.handleScrollStart = this.handleScrollStart.bind(this);
        this.handleScrollStop = this.handleScrollStop.bind(this);
        this.handleSpeedChange = this.handleSpeedChange.bind(this);
        this.handleDirectionChange = this.handleDirectionChange.bind(this);
        
        this.init();
    }
    
    /**
     * Initialize the status indicator
     * Requirements: 2.5
     */
    init() {
        if (this.isInitialized) return;
        
        this.createStatusElements();
        this.setupEventListeners();
        this.isInitialized = true;
    }
    
    /**
     * Create status indicator elements
     */
    createStatusElements() {
        // Main status indicator
        this.statusIndicator = document.createElement('div');
        this.statusIndicator.className = 'scroll-status-indicator transition-all duration-300 ease-in-out';
        this.statusIndicator.innerHTML = `
            <div class="status-icon transition-transform duration-200">
                <svg class="w-4 h-4 play-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 5v10l8-5-8-5z"/>
                </svg>
                <svg class="w-4 h-4 pause-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 4h2v12H6V4zm6 0h2v12h-2V4z"/>
                </svg>
            </div>
            <div class="status-text text-xs font-medium ml-2">
                <span class="inactive-text">Auto Scroll</span>
                <span class="active-text hidden">Scrolling</span>
            </div>
        `;
        
        // Speed indicator
        this.speedIndicator = document.createElement('div');
        this.speedIndicator.className = 'speed-indicator text-xs text-gray-500 mt-1 transition-opacity duration-200 opacity-0';
        this.speedIndicator.innerHTML = `
            <div class="flex items-center">
                <span class="speed-label">Speed:</span>
                <span class="speed-value ml-1 font-medium">Medium</span>
            </div>
        `;
        
        // Direction indicator
        this.directionIndicator = document.createElement('div');
        this.directionIndicator.className = 'direction-indicator text-xs text-gray-500 transition-opacity duration-200 opacity-0';
        this.directionIndicator.innerHTML = `
            <div class="flex items-center">
                <svg class="w-3 h-3 direction-icon transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3l-7 7h4v7h6v-7h4l-7-7z"/>
                </svg>
                <span class="direction-label ml-1">Down</span>
            </div>
        `;
        
        // Pulse indicator for active state
        this.pulseIndicator = document.createElement('div');
        this.pulseIndicator.className = 'pulse-indicator absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full opacity-0 transition-opacity duration-200';
        this.pulseIndicator.style.animation = 'pulse 2s infinite';
        
        // Assemble the status display
        const statusContainer = document.createElement('div');
        statusContainer.className = 'relative p-2 bg-white rounded-lg shadow-sm border border-gray-200';
        statusContainer.appendChild(this.statusIndicator);
        statusContainer.appendChild(this.speedIndicator);
        statusContainer.appendChild(this.directionIndicator);
        statusContainer.appendChild(this.pulseIndicator);
        
        this.container.appendChild(statusContainer);
        
        // Add CSS animations if not already present
        this.addCustomStyles();
    }
    
    /**
     * Add custom CSS styles for animations
     */
    addCustomStyles() {
        if (document.getElementById('scroll-status-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'scroll-status-styles';
        style.textContent = `
            @keyframes pulse {
                0%, 100% { opacity: 0.7; transform: scale(1); }
                50% { opacity: 1; transform: scale(1.1); }
            }
            
            .scroll-status-indicator.active {
                color: #059669; /* green-600 */
            }
            
            .scroll-status-indicator.paused {
                color: #d97706; /* amber-600 */
            }
            
            .direction-icon.up {
                transform: rotate(180deg);
            }
            
            .speed-indicator.visible,
            .direction-indicator.visible {
                opacity: 1;
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Setup event listeners for scroll engine events
     */
    setupEventListeners() {
        document.addEventListener('scrollEngine:start', this.handleScrollStart);
        document.addEventListener('scrollEngine:stop', this.handleScrollStop);
        document.addEventListener('scrollEngine:speedChange', this.handleSpeedChange);
        document.addEventListener('scrollEngine:directionChange', this.handleDirectionChange);
    }
    
    /**
     * Handle scroll start event
     * Requirements: 2.5
     */
    handleScrollStart(event) {
        const { state } = event.detail;
        
        // Update main status
        this.statusIndicator.classList.add('active');
        this.statusIndicator.classList.remove('paused');
        
        // Toggle icons
        const playIcon = this.statusIndicator.querySelector('.play-icon');
        const pauseIcon = this.statusIndicator.querySelector('.pause-icon');
        const inactiveText = this.statusIndicator.querySelector('.inactive-text');
        const activeText = this.statusIndicator.querySelector('.active-text');
        
        playIcon.classList.add('hidden');
        pauseIcon.classList.remove('hidden');
        inactiveText.classList.add('hidden');
        activeText.classList.remove('hidden');
        
        // Show additional indicators
        this.speedIndicator.classList.add('visible');
        this.directionIndicator.classList.add('visible');
        this.pulseIndicator.style.opacity = '1';
        
        // Update speed and direction displays
        this.updateSpeedDisplay(state.speed);
        this.updateDirectionDisplay(state.direction);
    }
    
    /**
     * Handle scroll stop event
     * Requirements: 2.5
     */
    handleScrollStop(event) {
        // Update main status
        this.statusIndicator.classList.remove('active', 'paused');
        
        // Toggle icons
        const playIcon = this.statusIndicator.querySelector('.play-icon');
        const pauseIcon = this.statusIndicator.querySelector('.pause-icon');
        const inactiveText = this.statusIndicator.querySelector('.inactive-text');
        const activeText = this.statusIndicator.querySelector('.active-text');
        
        pauseIcon.classList.add('hidden');
        playIcon.classList.remove('hidden');
        activeText.classList.add('hidden');
        inactiveText.classList.remove('hidden');
        
        // Hide additional indicators
        this.speedIndicator.classList.remove('visible');
        this.directionIndicator.classList.remove('visible');
        this.pulseIndicator.style.opacity = '0';
    }
    
    /**
     * Handle speed change event
     */
    handleSpeedChange(event) {
        const { speed } = event.detail;
        this.updateSpeedDisplay(speed);
    }
    
    /**
     * Handle direction change event
     */
    handleDirectionChange(event) {
        const { direction } = event.detail;
        this.updateDirectionDisplay(direction);
    }
    
    /**
     * Update speed display
     */
    updateSpeedDisplay(speed) {
        const speedValue = this.speedIndicator.querySelector('.speed-value');
        speedValue.textContent = speed.charAt(0).toUpperCase() + speed.slice(1);
    }
    
    /**
     * Update direction display
     */
    updateDirectionDisplay(direction) {
        const directionIcon = this.directionIndicator.querySelector('.direction-icon');
        const directionLabel = this.directionIndicator.querySelector('.direction-label');
        
        directionLabel.textContent = direction.charAt(0).toUpperCase() + direction.slice(1);
        
        if (direction === 'up') {
            directionIcon.classList.add('up');
        } else {
            directionIcon.classList.remove('up');
        }
    }
    
    /**
     * Show paused state (for user interaction)
     */
    showPausedState() {
        this.statusIndicator.classList.add('paused');
        this.statusIndicator.classList.remove('active');
        
        const activeText = this.statusIndicator.querySelector('.active-text');
        activeText.textContent = 'Paused';
    }
    
    /**
     * Hide paused state
     */
    hidePausedState() {
        this.statusIndicator.classList.remove('paused');
        this.statusIndicator.classList.add('active');
        
        const activeText = this.statusIndicator.querySelector('.active-text');
        activeText.textContent = 'Scrolling';
    }
    
    /**
     * Cleanup method
     */
    destroy() {
        document.removeEventListener('scrollEngine:start', this.handleScrollStart);
        document.removeEventListener('scrollEngine:stop', this.handleScrollStop);
        document.removeEventListener('scrollEngine:speedChange', this.handleSpeedChange);
        document.removeEventListener('scrollEngine:directionChange', this.handleDirectionChange);
        
        if (this.container && this.container.firstChild) {
            this.container.removeChild(this.container.firstChild);
        }
        
        this.isInitialized = false;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScrollStatusIndicator;
}