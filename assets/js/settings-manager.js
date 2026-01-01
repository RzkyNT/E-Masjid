/**
 * SettingsManager - Handles persistence of auto scroll settings
 * Provides localStorage save/load functionality with fallback support
 * Requirements: 7.1, 7.2, 7.5
 */
class SettingsManager {
    constructor() {
        this.storageKey = 'autoScrollSettings';
        this.fallbackStorage = new Map(); // In-memory fallback
        this.isLocalStorageAvailable = this.checkLocalStorageAvailability();
        
        // Default settings
        this.defaultSettings = {
            speed: 'medium',
            direction: 'down',
            autoStart: false,
            customSpeed: null,
            speedIndex: 5, // Default to medium (33 px/s)
            lastUsed: Date.now()
        };
    }
    
    /**
     * Check if localStorage is available
     * Requirements: 7.5
     */
    checkLocalStorageAvailability() {
        try {
            const testKey = '__localStorage_test__';
            localStorage.setItem(testKey, 'test');
            localStorage.removeItem(testKey);
            return true;
        } catch (e) {
            console.warn('localStorage not available, using fallback storage');
            return false;
        }
    }
    
    /**
     * Save settings to storage
     * Requirements: 7.1, 7.2
     */
    saveSettings(settings) {
        try {
            // Validate settings object
            const validatedSettings = this.validateSettings(settings);
            
            // Add timestamp
            validatedSettings.lastUsed = Date.now();
            
            if (this.isLocalStorageAvailable) {
                localStorage.setItem(this.storageKey, JSON.stringify(validatedSettings));
            } else {
                // Use fallback storage
                this.fallbackStorage.set(this.storageKey, validatedSettings);
            }
            
            return true;
        } catch (error) {
            console.error('Failed to save settings:', error);
            return false;
        }
    }
    
    /**
     * Load settings from storage
     * Requirements: 7.1, 7.2, 7.3
     */
    loadSettings() {
        try {
            let savedSettings = null;
            
            if (this.isLocalStorageAvailable) {
                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    savedSettings = JSON.parse(stored);
                }
            } else {
                // Use fallback storage
                savedSettings = this.fallbackStorage.get(this.storageKey);
            }
            
            if (savedSettings) {
                // Validate and merge with defaults
                return this.validateSettings(savedSettings);
            }
            
            // Return default settings if nothing saved
            return { ...this.defaultSettings };
            
        } catch (error) {
            console.error('Failed to load settings:', error);
            // Return defaults on error
            return { ...this.defaultSettings };
        }
    }
    
    /**
     * Reset settings to defaults
     * Requirements: 7.4
     */
    resetSettings() {
        try {
            const defaultSettings = { ...this.defaultSettings };
            defaultSettings.lastUsed = Date.now();
            
            if (this.isLocalStorageAvailable) {
                localStorage.setItem(this.storageKey, JSON.stringify(defaultSettings));
            } else {
                this.fallbackStorage.set(this.storageKey, defaultSettings);
            }
            
            return defaultSettings;
        } catch (error) {
            console.error('Failed to reset settings:', error);
            return { ...this.defaultSettings };
        }
    }
    
    /**
     * Clear all settings
     * Requirements: 7.4
     */
    clearSettings() {
        try {
            if (this.isLocalStorageAvailable) {
                localStorage.removeItem(this.storageKey);
            } else {
                this.fallbackStorage.delete(this.storageKey);
            }
            return true;
        } catch (error) {
            console.error('Failed to clear settings:', error);
            return false;
        }
    }
    
    /**
     * Validate settings object and merge with defaults
     * Ensures all required properties exist with valid values
     */
    validateSettings(settings) {
        const validated = { ...this.defaultSettings };
        
        if (settings && typeof settings === 'object') {
            // Validate speed
            if (settings.speed && ['slow', 'medium', 'fast'].includes(settings.speed)) {
                validated.speed = settings.speed;
            }
            
            // Validate direction
            if (settings.direction && ['up', 'down'].includes(settings.direction)) {
                validated.direction = settings.direction;
            }
            
            // Validate autoStart
            if (typeof settings.autoStart === 'boolean') {
                validated.autoStart = settings.autoStart;
            }
            
            // Validate customSpeed
            if (typeof settings.customSpeed === 'number' && settings.customSpeed > 0) {
                validated.customSpeed = settings.customSpeed;
            } else if (settings.customSpeed === null) {
                validated.customSpeed = null;
            }
            
            // Validate speedIndex
            if (typeof settings.speedIndex === 'number' && 
                settings.speedIndex >= 0 && 
                settings.speedIndex <= 10) {
                validated.speedIndex = settings.speedIndex;
            }
            
            // Validate lastUsed
            if (typeof settings.lastUsed === 'number' && settings.lastUsed > 0) {
                validated.lastUsed = settings.lastUsed;
            }
        }
        
        return validated;
    }
    
    /**
     * Get current settings without loading from storage
     * Useful for getting defaults
     */
    getDefaultSettings() {
        return { ...this.defaultSettings };
    }
    
    /**
     * Check if settings exist in storage
     */
    hasSettings() {
        try {
            if (this.isLocalStorageAvailable) {
                return localStorage.getItem(this.storageKey) !== null;
            } else {
                return this.fallbackStorage.has(this.storageKey);
            }
        } catch (error) {
            return false;
        }
    }
    
    /**
     * Get storage info for debugging
     */
    getStorageInfo() {
        return {
            isLocalStorageAvailable: this.isLocalStorageAvailable,
            storageKey: this.storageKey,
            hasSettings: this.hasSettings(),
            fallbackStorageSize: this.fallbackStorage.size
        };
    }
    
    /**
     * Update specific setting without loading/saving entire object
     * Useful for frequent updates like speed changes
     */
    updateSetting(key, value) {
        try {
            const currentSettings = this.loadSettings();
            currentSettings[key] = value;
            currentSettings.lastUsed = Date.now();
            
            return this.saveSettings(currentSettings);
        } catch (error) {
            console.error('Failed to update setting:', error);
            return false;
        }
    }
    
    /**
     * Batch update multiple settings
     */
    updateSettings(updates) {
        try {
            const currentSettings = this.loadSettings();
            const updatedSettings = { ...currentSettings, ...updates };
            updatedSettings.lastUsed = Date.now();
            
            return this.saveSettings(updatedSettings);
        } catch (error) {
            console.error('Failed to update settings:', error);
            return false;
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SettingsManager;
}