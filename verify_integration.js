/**
 * Integration Verification Script
 * Tests auto scroll integration with existing Al-Quran features
 * Requirements: 8.1, 8.2, 8.3
 */

// Test results storage
const testResults = {
    fontSizeCompatibility: false,
    copyShareCompatibility: false,
    noInterference: false,
    settingsPersistence: false
};

/**
 * Verify font size control compatibility
 * Requirements: 8.1
 */
function verifyFontSizeCompatibility() {
    console.log('Testing font size control compatibility...');
    
    try {
        // Check if font size functions exist
        const fontSizeFunctionsExist = 
            typeof changeFontSize === 'function' &&
            typeof resetFontSize === 'function' &&
            typeof applyFontSize === 'function';
        
        if (!fontSizeFunctionsExist) {
            console.error('Font size functions not found');
            return false;
        }
        
        // Check if font size controls exist in DOM
        const fontControls = document.querySelectorAll('[onclick*="changeFontSize"], [onclick*="resetFontSize"]');
        if (fontControls.length === 0) {
            console.error('Font size control buttons not found');
            return false;
        }
        
        // Check if auto scroll component recognizes font controls
        if (typeof window.autoScrollComponent !== 'undefined' && 
            window.autoScrollComponent.scrollEngine &&
            typeof window.autoScrollComponent.scrollEngine.shouldPauseForElement === 'function') {
            
            const fontButton = fontControls[0];
            const shouldPause = window.autoScrollComponent.scrollEngine.shouldPauseForElement(fontButton);
            
            if (shouldPause) {
                console.error('Auto scroll should NOT pause for font controls');
                return false;
            }
        }
        
        console.log('✓ Font size compatibility verified');
        return true;
        
    } catch (error) {
        console.error('Font size compatibility test failed:', error);
        return false;
    }
}

/**
 * Verify copy/share functionality compatibility
 * Requirements: 8.2
 */
function verifyCopyShareCompatibility() {
    console.log('Testing copy/share functionality compatibility...');
    
    try {
        // Check if copy/share functions exist
        const copyShareFunctionsExist = 
            typeof copyAyat === 'function' &&
            typeof shareAyat === 'function' &&
            typeof showButtonFeedback === 'function';
        
        if (!copyShareFunctionsExist) {
            console.error('Copy/share functions not found');
            return false;
        }
        
        // Check if copy/share buttons exist in DOM
        const copyButtons = document.querySelectorAll('[onclick*="copyAyat"]');
        const shareButtons = document.querySelectorAll('[onclick*="shareAyat"]');
        
        if (copyButtons.length === 0 || shareButtons.length === 0) {
            console.error('Copy/share buttons not found');
            return false;
        }
        
        // Check if auto scroll component recognizes copy/share controls
        if (typeof window.autoScrollComponent !== 'undefined' && 
            window.autoScrollComponent.scrollEngine &&
            typeof window.autoScrollComponent.scrollEngine.shouldPauseForElement === 'function') {
            
            const copyButton = copyButtons[0];
            const shouldPause = window.autoScrollComponent.scrollEngine.shouldPauseForElement(copyButton);
            
            if (shouldPause) {
                console.error('Auto scroll should NOT pause for copy/share controls');
                return false;
            }
        }
        
        console.log('✓ Copy/share compatibility verified');
        return true;
        
    } catch (error) {
        console.error('Copy/share compatibility test failed:', error);
        return false;
    }
}

/**
 * Verify no interference with existing functionality
 * Requirements: 8.3
 */
function verifyNoInterference() {
    console.log('Testing for interference with existing functionality...');
    
    try {
        // Check if auto scroll floating button exists
        const autoScrollButton = document.getElementById('auto-scroll-floating');
        if (!autoScrollButton) {
            console.error('Auto scroll floating button not found');
            return false;
        }
        
        // Check if auto scroll button doesn't interfere with page layout
        const computedStyle = window.getComputedStyle(autoScrollButton);
        if (computedStyle.position !== 'fixed') {
            console.error('Auto scroll button should be fixed positioned');
            return false;
        }
        
        // Check z-index to ensure it doesn't interfere
        const zIndex = parseInt(computedStyle.zIndex);
        if (zIndex < 50) {
            console.error('Auto scroll button z-index too low, may be hidden');
            return false;
        }
        
        // Check if existing page elements are still accessible
        const ayatContainers = document.querySelectorAll('.ayat-container');
        if (ayatContainers.length === 0) {
            console.error('Ayat containers not found');
            return false;
        }
        
        // Verify ayat containers are not obscured
        const firstAyat = ayatContainers[0];
        const ayatRect = firstAyat.getBoundingClientRect();
        const buttonRect = autoScrollButton.getBoundingClientRect();
        
        // Check if button overlaps with content (should not)
        const overlaps = !(buttonRect.right < ayatRect.left || 
                          buttonRect.left > ayatRect.right || 
                          buttonRect.bottom < ayatRect.top || 
                          buttonRect.top > ayatRect.bottom);
        
        if (overlaps) {
            console.warn('Auto scroll button may overlap with content');
        }
        
        console.log('✓ No interference verified');
        return true;
        
    } catch (error) {
        console.error('No interference test failed:', error);
        return false;
    }
}

/**
 * Verify settings persistence works correctly
 * Requirements: 7.1, 7.2, 7.3
 */
function verifySettingsPersistence() {
    console.log('Testing settings persistence...');
    
    try {
        // Check if auto scroll component exists
        if (typeof window.autoScrollComponent === 'undefined') {
            console.error('Auto scroll component not found');
            return false;
        }
        
        const component = window.autoScrollComponent;
        
        // Check if settings manager exists
        if (!component.settingsManager) {
            console.error('Settings manager not found');
            return false;
        }
        
        // Test settings save/load
        const testSettings = {
            speed: 'fast',
            direction: 'up',
            customSpeed: 45,
            speedIndex: 8
        };
        
        // Save test settings
        const saveResult = component.settingsManager.saveSettings(testSettings);
        if (!saveResult) {
            console.error('Failed to save settings');
            return false;
        }
        
        // Load settings back
        const loadedSettings = component.settingsManager.loadSettings();
        if (!loadedSettings) {
            console.error('Failed to load settings');
            return false;
        }
        
        // Verify settings match
        if (loadedSettings.speed !== testSettings.speed ||
            loadedSettings.direction !== testSettings.direction ||
            loadedSettings.customSpeed !== testSettings.customSpeed ||
            loadedSettings.speedIndex !== testSettings.speedIndex) {
            console.error('Loaded settings do not match saved settings');
            return false;
        }
        
        // Reset to defaults
        const defaultSettings = component.settingsManager.resetSettings();
        if (!defaultSettings || defaultSettings.speed !== 'medium') {
            console.error('Failed to reset to default settings');
            return false;
        }
        
        console.log('✓ Settings persistence verified');
        return true;
        
    } catch (error) {
        console.error('Settings persistence test failed:', error);
        return false;
    }
}

/**
 * Run all integration verification tests
 */
function runIntegrationVerification() {
    console.log('Starting Auto Scroll Integration Verification...');
    console.log('================================================');
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runTests);
    } else {
        runTests();
    }
    
    function runTests() {
        // Wait a bit for auto scroll component to initialize
        setTimeout(() => {
            testResults.fontSizeCompatibility = verifyFontSizeCompatibility();
            testResults.copyShareCompatibility = verifyCopyShareCompatibility();
            testResults.noInterference = verifyNoInterference();
            testResults.settingsPersistence = verifySettingsPersistence();
            
            // Print results
            console.log('\n================================================');
            console.log('Integration Verification Results:');
            console.log('================================================');
            
            const results = [
                ['Font Size Compatibility', testResults.fontSizeCompatibility],
                ['Copy/Share Compatibility', testResults.copyShareCompatibility],
                ['No Interference', testResults.noInterference],
                ['Settings Persistence', testResults.settingsPersistence]
            ];
            
            let passCount = 0;
            results.forEach(([testName, passed]) => {
                const status = passed ? '✓ PASS' : '✗ FAIL';
                const color = passed ? '\x1b[32m' : '\x1b[31m';
                console.log(`${color}${status}\x1b[0m ${testName}`);
                if (passed) passCount++;
            });
            
            console.log('================================================');
            console.log(`Overall Result: ${passCount}/${results.length} tests passed`);
            
            if (passCount === results.length) {
                console.log('\x1b[32m✓ All integration tests PASSED!\x1b[0m');
                console.log('Auto scroll component is properly integrated with existing features.');
            } else {
                console.log('\x1b[31m✗ Some integration tests FAILED!\x1b[0m');
                console.log('Please review the failed tests and fix integration issues.');
            }
            
            return passCount === results.length;
        }, 1000);
    }
}

// Export for use in other contexts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        runIntegrationVerification,
        verifyFontSizeCompatibility,
        verifyCopyShareCompatibility,
        verifyNoInterference,
        verifySettingsPersistence,
        testResults
    };
}

// Auto-run if in browser context
if (typeof window !== 'undefined') {
    runIntegrationVerification();
}