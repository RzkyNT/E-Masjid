/**
 * ScrollEngine - Core auto scroll functionality for Al-Quran pages
 * Provides smooth scrolling with speed control and boundary detection
 */
class ScrollEngine {
    constructor(settingsManager = null) {
        this.isActive = false;
        this.speed = 'medium'; // slow, medium, fast
        this.direction = 'down'; // up, down
        this.animationId = null;
        this.lastTimestamp = 0;
        this.pausedByUser = false;
        
        // Settings manager for persistence
        this.settingsManager = settingsManager;
        
        // Speed settings in pixels per second
        this.speedSettings = {
            slow: 20,
            medium: 33,
            fast: 50
        };
        
        // Fine-grained speed control - limitless system
        this.customSpeed = null; // When using +/- controls
        this.minSpeed = 5; // Minimum speed in px/s
        this.maxSpeed = null; // No maximum limit (limitless)
        this.speedStep = 5; // Speed increment/decrement step
        this.currentSpeed = 33; // Default speed (medium)
        
        // Predefined speed levels for quick access (but not limiting)
        this.speedPresets = {
            slow: 20,
            medium: 33,
            fast: 50
        };
        
        // Bind methods to maintain context
        this.scroll = this.scroll.bind(this);
        
        // Set up user interaction detection
        this.setupInteractionDetection();
        
        // Load saved settings if settings manager is provided
        this.loadSettings();
    }
    
    /**
     * Start auto scrolling
     * Requirements: 2.1, 2.2
     */
    start() {
        if (this.isActive) {
            return; // Already active
        }
        
        this.isActive = true;
        this.pausedByUser = false;
        this.lastTimestamp = performance.now();
        this.animationId = requestAnimationFrame(this.scroll);
        
        // Dispatch custom event for UI updates
        this.dispatchScrollEvent('start');
    }
    
    /**
     * Stop auto scrolling
     * Requirements: 2.3
     */
    stop() {
        if (!this.isActive) {
            return; // Already inactive
        }
        
        this.isActive = false;
        this.pausedByUser = false;
        
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
        
        // Dispatch custom event for UI updates
        this.dispatchScrollEvent('stop');
    }
    
    /**
     * Main scroll function using requestAnimationFrame
     * Requirements: 2.1, 2.4
     */
    scroll(timestamp) {
        if (!this.isActive) {
            return;
        }
        
        // If paused by user, dispatch pause event and wait
        if (this.pausedByUser) {
            this.dispatchScrollEvent('paused', { reason: 'user_interaction' });
            return;
        }
        
        // Calculate time delta
        const deltaTime = timestamp - this.lastTimestamp;
        this.lastTimestamp = timestamp;
        
        // Calculate scroll distance based on speed and time
        const pixelsPerSecond = this.getCurrentSpeed();
        const scrollDistance = (pixelsPerSecond * deltaTime) / 1000;
        
        // Check boundaries before scrolling
        if (this.isAtBoundary()) {
            this.stop();
            return;
        }
        
        // Perform scroll
        const currentScroll = window.pageYOffset;
        const newScrollPosition = this.direction === 'down' 
            ? currentScroll + scrollDistance 
            : currentScroll - scrollDistance;
        
        window.scrollTo({
            top: newScrollPosition,
            behavior: 'auto' // Use auto for smooth manual control
        });
        
        // Continue animation
        this.animationId = requestAnimationFrame(this.scroll);
    }
    
    /**
     * Check if scroll has reached page boundaries
     * Requirements: 2.4
     */
    isAtBoundary() {
        const currentScroll = window.pageYOffset;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        
        if (this.direction === 'down') {
            // Check if at bottom
            return currentScroll >= maxScroll - 1; // -1 for floating point precision
        } else {
            // Check if at top
            return currentScroll <= 0;
        }
    }
    
    /**
     * Set scroll speed
     * Requirements: 3.2, 3.5
     */
    setSpeed(speed) {
        if (!this.speedSettings.hasOwnProperty(speed)) {
            console.warn(`Invalid speed: ${speed}. Using medium.`);
            speed = 'medium';
        }
        
        this.speed = speed;
        this.customSpeed = null; // Reset custom speed when using presets
        this.currentSpeed = this.speedSettings[speed]; // Set current speed to preset value
        
        // Save settings if manager is available
        this.saveCurrentSettings();
        
        // Dispatch event for UI updates
        this.dispatchScrollEvent('speedChange', { 
            speed, 
            customSpeed: this.customSpeed,
            currentSpeed: this.currentSpeed
        });
    }
    
    /**
     * Increase scroll speed (limitless)
     * Requirements: 3.1, 3.4
     */
    increaseSpeed() {
        // Get current effective speed
        const currentEffectiveSpeed = this.getCurrentSpeed();
        
        // Calculate new speed with step increment
        const newSpeed = currentEffectiveSpeed + this.speedStep;
        
        // Set the new custom speed (no upper limit)
        this.customSpeed = newSpeed;
        this.currentSpeed = newSpeed;
        
        // Update speed preset category
        this.updateSpeedPreset();
        
        // Save settings if manager is available
        this.saveCurrentSettings();
        
        // Dispatch event for UI updates
        this.dispatchScrollEvent('speedChange', { 
            speed: this.speed, 
            customSpeed: this.customSpeed,
            currentSpeed: this.currentSpeed
        });
    }
    
    /**
     * Decrease scroll speed (with minimum limit)
     * Requirements: 3.1, 3.4
     */
    decreaseSpeed() {
        // Get current effective speed
        const currentEffectiveSpeed = this.getCurrentSpeed();
        
        // Calculate new speed with step decrement
        const newSpeed = Math.max(this.minSpeed, currentEffectiveSpeed - this.speedStep);
        
        // Set the new custom speed (with minimum limit)
        this.customSpeed = newSpeed;
        this.currentSpeed = newSpeed;
        
        // Update speed preset category
        this.updateSpeedPreset();
        
        // Save settings if manager is available
        this.saveCurrentSettings();
        
        // Dispatch event for UI updates
        this.dispatchScrollEvent('speedChange', { 
            speed: this.speed, 
            customSpeed: this.customSpeed,
            currentSpeed: this.currentSpeed
        });
    }
    
    /**
     * Get current effective speed
     */
    getCurrentSpeed() {
        // Return custom speed if set, otherwise return preset speed
        return this.customSpeed || this.speedSettings[this.speed] || this.currentSpeed;
    }
    
    /**
     * Update speed preset based on current custom speed
     */
    updateSpeedPreset() {
        const currentSpeed = this.getCurrentSpeed();
        
        // Determine which preset category is closest
        if (currentSpeed <= 25) {
            this.speed = 'slow';
        } else if (currentSpeed <= 40) {
            this.speed = 'medium';
        } else {
            this.speed = 'fast';
        }
    }
    
    /**
     * Set scroll direction
     * Requirements: 4.1, 4.2
     */
    setDirection(direction) {
        if (direction !== 'up' && direction !== 'down') {
            console.warn(`Invalid direction: ${direction}. Using down.`);
            direction = 'down';
        }
        
        this.direction = direction;
        
        // Save settings if manager is available
        this.saveCurrentSettings();
        
        // Dispatch event for UI updates
        this.dispatchScrollEvent('directionChange', { direction });
    }
    
    /**
     * Get current scroll state
     */
    getState() {
        return {
            isActive: this.isActive,
            speed: this.speed,
            customSpeed: this.customSpeed,
            currentSpeed: this.getCurrentSpeed(),
            minSpeed: this.minSpeed,
            maxSpeed: this.maxSpeed, // null for limitless
            speedStep: this.speedStep,
            direction: this.direction,
            pausedByUser: this.pausedByUser,
            currentPosition: window.pageYOffset,
            maxScroll: document.documentElement.scrollHeight - window.innerHeight
        };
    }
    
    /**
     * Setup detection for manual user interactions
     * Requirements: 6.3, 6.4
     */
    setupInteractionDetection() {
        let interactionTimeout;
        let lastScrollPosition = window.pageYOffset;
        let scrollCheckInterval;
        let touchStartY = null;
        let isManualScrolling = false;
        
        const pauseForInteraction = (source = 'unknown') => {
            if (!this.isActive) return;
            
            this.pausedByUser = true;
            isManualScrolling = true;
            
            // Dispatch event for UI feedback
            this.dispatchScrollEvent('pausedByUser', { source, pausedByUser: true });
            
            // Clear existing timeout
            if (interactionTimeout) {
                clearTimeout(interactionTimeout);
            }
            
            // Resume after 2 seconds of no interaction
            interactionTimeout = setTimeout(() => {
                this.pausedByUser = false;
                isManualScrolling = false;
                
                // Dispatch event for UI feedback
                this.dispatchScrollEvent('resumedFromPause', { source: 'timeout', pausedByUser: false });
                
                if (this.isActive) {
                    this.lastTimestamp = performance.now();
                    this.animationId = requestAnimationFrame(this.scroll);
                }
            }, 2000);
        };
        
        const resumeFromInteraction = () => {
            if (interactionTimeout) {
                clearTimeout(interactionTimeout);
            }
            
            if (this.pausedByUser && this.isActive) {
                this.pausedByUser = false;
                isManualScrolling = false;
                
                // Dispatch event for UI feedback
                this.dispatchScrollEvent('resumedFromPause', { source: 'manual', pausedByUser: false });
                
                this.lastTimestamp = performance.now();
                this.animationId = requestAnimationFrame(this.scroll);
            }
        };
        
        // Mouse wheel detection
        window.addEventListener('wheel', (e) => {
            pauseForInteraction('wheel');
        }, { passive: true });
        
        // Touch events for mobile
        window.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                touchStartY = e.touches[0].clientY;
            }
        }, { passive: true });
        
        window.addEventListener('touchmove', (e) => {
            if (touchStartY !== null && e.touches.length === 1) {
                const touchCurrentY = e.touches[0].clientY;
                const touchDelta = Math.abs(touchCurrentY - touchStartY);
                
                // If significant touch movement, pause auto scroll
                if (touchDelta > 10) {
                    pauseForInteraction('touch');
                    touchStartY = null;
                }
            }
        }, { passive: true });
        
        window.addEventListener('touchend', () => {
            touchStartY = null;
        }, { passive: true });
        
        // Keyboard navigation detection
        window.addEventListener('keydown', (e) => {
            // Don't pause on auto-scroll shortcuts
            const autoScrollKeys = ['Space', 'Equal', 'Plus', 'Minus'];
            if (autoScrollKeys.includes(e.code) || 
                (e.key === ' ' && !e.ctrlKey && !e.metaKey) ||
                (e.key === '+' || e.key === '=' || e.key === '-')) {
                return;
            }
            
            // Don't pause on font size shortcuts (Ctrl/Cmd + +/-)
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=' || e.key === '-' || e.key === '0')) {
                return;
            }
            
            // Pause on navigation keys
            const navigationKeys = ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'];
            if (navigationKeys.includes(e.code)) {
                pauseForInteraction('keyboard');
            }
        });
        
        // Detect manual scrollbar usage by monitoring scroll position changes
        // that don't match our auto-scroll pattern
        scrollCheckInterval = setInterval(() => {
            if (!this.isActive || this.pausedByUser) {
                lastScrollPosition = window.pageYOffset;
                return;
            }
            
            const currentPosition = window.pageYOffset;
            const positionDelta = Math.abs(currentPosition - lastScrollPosition);
            
            // If scroll position changed significantly and we're not in the middle of our scroll animation
            if (positionDelta > 5 && !isManualScrolling) {
                // Check if this change matches our expected auto-scroll pattern
                const expectedDirection = this.direction === 'down' ? 1 : -1;
                const actualDirection = currentPosition > lastScrollPosition ? 1 : -1;
                
                // If direction doesn't match or change is too large, it's likely manual
                if (expectedDirection !== actualDirection || positionDelta > 50) {
                    pauseForInteraction('scrollbar');
                }
            }
            
            lastScrollPosition = currentPosition;
        }, 100);
        
        // Click detection on page content (but not on auto-scroll controls)
        document.addEventListener('click', (e) => {
            // Use the shouldPauseForElement method to determine if we should pause
            if (this.shouldPauseForElement(e.target)) {
                pauseForInteraction('click');
            }
        });
        
        // Store cleanup function for later use
        this.cleanupInteractionDetection = () => {
            if (interactionTimeout) {
                clearTimeout(interactionTimeout);
            }
            if (scrollCheckInterval) {
                clearInterval(scrollCheckInterval);
            }
        };
    }
    
    /**
     * Dispatch custom events for UI components
     */
    dispatchScrollEvent(type, data = {}) {
        const event = new CustomEvent(`scrollEngine:${type}`, {
            detail: { ...data, state: this.getState() }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Cleanup method
     */
    destroy() {
        this.stop();
        
        // Clean up interaction detection
        if (this.cleanupInteractionDetection) {
            this.cleanupInteractionDetection();
        }
    }
    
    /**
     * Load settings from SettingsManager
     * Requirements: 7.3
     */
    loadSettings() {
        if (!this.settingsManager) {
            return;
        }
        
        try {
            const settings = this.settingsManager.loadSettings();
            
            // Apply loaded settings
            this.speed = settings.speed;
            this.direction = settings.direction;
            this.customSpeed = settings.customSpeed;
            this.currentSpeedIndex = settings.speedIndex;
            
            // Dispatch events to update UI
            this.dispatchScrollEvent('settingsLoaded', { settings });
            
        } catch (error) {
            console.error('Failed to load settings:', error);
        }
    }
    
    /**
     * Save current settings to SettingsManager
     * Requirements: 7.1, 7.2
     */
    saveCurrentSettings() {
        if (!this.settingsManager) {
            return;
        }
        
        try {
            const currentSettings = {
                speed: this.speed,
                direction: this.direction,
                customSpeed: this.customSpeed,
                speedIndex: this.currentSpeedIndex,
                autoStart: false // We don't auto-start for safety
            };
            
            this.settingsManager.saveSettings(currentSettings);
        } catch (error) {
            console.error('Failed to save settings:', error);
        }
    }
    
    /**
     * Reset settings to defaults
     * Requirements: 7.4
     */
    resetToDefaults() {
        if (!this.settingsManager) {
            // Reset to hardcoded defaults if no settings manager
            this.speed = 'medium';
            this.direction = 'down';
            this.customSpeed = null;
            this.currentSpeed = 33; // Default medium speed
        } else {
            try {
                const defaultSettings = this.settingsManager.resetSettings();
                
                // Apply default settings
                this.speed = defaultSettings.speed || 'medium';
                this.direction = defaultSettings.direction || 'down';
                this.customSpeed = defaultSettings.customSpeed || null;
                this.currentSpeed = defaultSettings.currentSpeed || 33;
                
                // Dispatch event to update UI
                this.dispatchScrollEvent('settingsReset', { settings: defaultSettings });
                
            } catch (error) {
                console.error('Failed to reset settings:', error);
            }
        }
    }
    
    /**
     * Temporarily disable auto-scroll for specific feature usage
     * Requirements: 8.1, 8.2, 8.3
     */
    temporaryDisable(reason, duration = 1000) {
        if (!this.isActive) return;
        
        const wasActive = this.isActive;
        this.stop();
        
        // Dispatch event
        this.dispatchScrollEvent('temporaryDisabled', { reason, duration });
        
        // Re-enable after specified duration
        setTimeout(() => {
            if (wasActive) {
                this.start();
                this.dispatchScrollEvent('temporaryEnabled', { reason });
            }
        }, duration);
    }
    
    /**
     * Check if auto-scroll should be paused for specific elements
     * Requirements: 8.1, 8.2, 8.3
     */
    shouldPauseForElement(element) {
        if (!element) return false;
        
        // Check if element is a font control
        const isFontControl = element.closest('[onclick*="changeFontSize"]') ||
                             element.closest('[onclick*="resetFontSize"]') ||
                             element.getAttribute('onclick')?.includes('changeFontSize') ||
                             element.getAttribute('onclick')?.includes('resetFontSize');
        
        // Check if element is a copy/share button
        const isCopyShareButton = element.closest('[onclick*="copyAyat"]') ||
                                 element.closest('[onclick*="shareAyat"]') ||
                                 element.getAttribute('onclick')?.includes('copyAyat') ||
                                 element.getAttribute('onclick')?.includes('shareAyat');
        
        // Check if element is navigation
        const isNavigation = element.closest('select') ||
                            element.closest('input') ||
                            element.closest('form') ||
                            element.closest('nav') ||
                            element.closest('.navigation') ||
                            element.closest('[role="navigation"]');
        
        // Check if element is auto-scroll control
        const isAutoScrollControl = element.closest('#auto-scroll-floating');
        
        return !(isFontControl || isCopyShareButton || isNavigation || isAutoScrollControl);
    }
    
    /**
     * Get current settings object
     */
    getCurrentSettings() {
        return {
            speed: this.speed,
            direction: this.direction,
            customSpeed: this.customSpeed,
            currentSpeed: this.currentSpeed,
            minSpeed: this.minSpeed,
            maxSpeed: this.maxSpeed,
            speedStep: this.speedStep,
            autoStart: false,
            lastUsed: Date.now()
        };
    }
    
    /**
     * Apply settings from external source
     * Requirements: 7.3
     */
    applySettings(settings) {
        if (!settings || typeof settings !== 'object') {
            return;
        }
        
        // Validate and apply settings
        if (settings.speed && ['slow', 'medium', 'fast'].includes(settings.speed)) {
            this.speed = settings.speed;
        }
        
        if (settings.direction && ['up', 'down'].includes(settings.direction)) {
            this.direction = settings.direction;
        }
        
        if (typeof settings.customSpeed === 'number' && settings.customSpeed >= this.minSpeed) {
            this.customSpeed = settings.customSpeed;
            this.currentSpeed = settings.customSpeed;
        } else if (settings.customSpeed === null) {
            this.customSpeed = null;
        }
        
        if (typeof settings.currentSpeed === 'number' && settings.currentSpeed >= this.minSpeed) {
            this.currentSpeed = settings.currentSpeed;
        }
        
        if (typeof settings.speedStep === 'number' && settings.speedStep > 0) {
            this.speedStep = settings.speedStep;
        }
        
        // Save the applied settings
        this.saveCurrentSettings();
        
        // Dispatch event to update UI
        this.dispatchScrollEvent('settingsApplied', { settings: this.getCurrentSettings() });
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScrollEngine;
}