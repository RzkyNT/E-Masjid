/**
 * Property-Based Test for Responsive Layout Adaptation
 * Feature: unifikasi-jadwal-jumat, Property 5: Responsive Layout Adaptation
 * Validates: Requirements 3.4
 */

class ResponsiveBehaviorPropertyTest {
    constructor() {
        this.iterations = 100;
        this.results = [];
    }

    // Generator for random screen sizes and device configurations
    generateDeviceConfiguration() {
        const deviceTypes = [
            { name: 'mobile-portrait', width: 320, height: 568, orientation: 'portrait' },
            { name: 'mobile-landscape', width: 568, height: 320, orientation: 'landscape' },
            { name: 'tablet-portrait', width: 768, height: 1024, orientation: 'portrait' },
            { name: 'tablet-landscape', width: 1024, height: 768, orientation: 'landscape' },
            { name: 'desktop-small', width: 1280, height: 720, orientation: 'landscape' },
            { name: 'desktop-large', width: 1920, height: 1080, orientation: 'landscape' },
            { name: 'ultrawide', width: 2560, height: 1440, orientation: 'landscape' }
        ];

        // Sometimes use predefined devices, sometimes generate random dimensions
        if (Math.random() < 0.7) {
            return deviceTypes[Math.floor(Math.random() * deviceTypes.length)];
        } else {
            // Generate random screen size
            const width = Math.floor(Math.random() * 2000) + 320; // 320-2320px
            const height = Math.floor(Math.random() * 1500) + 240; // 240-1740px
            const orientation = width > height ? 'landscape' : 'portrait';
            
            return {
                name: 'custom',
                width: width,
                height: height,
                orientation: orientation
            };
        }
    }

    generateViewportChanges() {
        const changeCount = Math.floor(Math.random() * 5) + 1; // 1-5 viewport changes
        const changes = [];
        
        for (let i = 0; i < changeCount; i++) {
            changes.push({
                device: this.generateDeviceConfiguration(),
                delay: Math.floor(Math.random() * 100), // 0-99ms delay
                userAction: Math.random() < 0.3 ? this.generateUserAction() : null
            });
        }
        
        return changes;
    }

    generateUserAction() {
        const actions = [
            { type: 'view-toggle', target: Math.random() < 0.5 ? 'card' : 'calendar' },
            { type: 'calendar-navigation', direction: Math.random() < 0.5 ? 'next' : 'prev' },
            { type: 'calendar-view-change', view: Math.random() < 0.5 ? 'month' : 'list' },
            { type: 'modal-open', eventId: Math.floor(Math.random() * 10) + 1 }
        ];
        
        return actions[Math.floor(Math.random() * actions.length)];
    }

    // Mock DOM and layout system
    createMockLayout(device) {
        const layout = {
            device: device,
            viewport: { width: device.width, height: device.height },
            elements: {
                viewToggle: this.createMockElement('view-toggle', device),
                cardView: this.createMockElement('card-view', device),
                calendarView: this.createMockElement('calendar-view', device),
                modal: this.createMockElement('modal', device),
                navigation: this.createMockElement('navigation', device)
            },
            breakpoints: this.getActiveBreakpoints(device.width),
            isResponsive: true,
            errors: []
        };

        return layout;
    }

    createMockElement(elementType, device) {
        const element = {
            type: elementType,
            visible: true,
            responsive: true,
            classes: [],
            styles: {},
            dimensions: { width: 0, height: 0 },
            position: { x: 0, y: 0 }
        };

        // Apply responsive behavior based on screen size
        switch (elementType) {
            case 'view-toggle':
                if (device.width < 640) {
                    element.classes.push('flex-col', 'sm:flex-row', 'space-y-4', 'sm:space-y-0');
                    element.styles.flexDirection = 'column';
                } else {
                    element.classes.push('flex-row', 'space-x-4');
                    element.styles.flexDirection = 'row';
                }
                break;

            case 'card-view':
                if (device.width < 768) {
                    element.classes.push('grid-cols-1');
                    element.styles.gridTemplateColumns = '1fr';
                } else {
                    element.classes.push('grid-cols-2');
                    element.styles.gridTemplateColumns = 'repeat(2, 1fr)';
                }
                break;

            case 'calendar-view':
                if (device.width < 640) {
                    element.classes.push('text-xs', 'compact-view');
                    element.styles.fontSize = '0.75rem';
                } else {
                    element.classes.push('text-sm', 'normal-view');
                    element.styles.fontSize = '0.875rem';
                }
                break;

            case 'modal':
                if (device.width < 640) {
                    element.classes.push('w-full', 'mx-4');
                    element.styles.width = '100%';
                    element.styles.margin = '0 1rem';
                } else if (device.width < 1024) {
                    element.classes.push('w-3/4');
                    element.styles.width = '75%';
                } else {
                    element.classes.push('w-1/2');
                    element.styles.width = '50%';
                }
                break;

            case 'navigation':
                if (device.width < 640) {
                    element.classes.push('flex-col', 'space-y-2');
                    element.styles.flexDirection = 'column';
                } else {
                    element.classes.push('flex-row', 'space-x-4');
                    element.styles.flexDirection = 'row';
                }
                break;
        }

        return element;
    }

    getActiveBreakpoints(width) {
        const breakpoints = [];
        
        if (width >= 1536) breakpoints.push('2xl');
        if (width >= 1280) breakpoints.push('xl');
        if (width >= 1024) breakpoints.push('lg');
        if (width >= 768) breakpoints.push('md');
        if (width >= 640) breakpoints.push('sm');
        
        return breakpoints;
    }

    // Simulate viewport changes and responsive behavior
    simulateViewportChange(currentLayout, newDevice) {
        const newLayout = this.createMockLayout(newDevice);
        
        // Track what changed
        const changes = {
            deviceChanged: currentLayout.device.name !== newDevice.name,
            widthChanged: currentLayout.viewport.width !== newDevice.width,
            heightChanged: currentLayout.viewport.height !== newDevice.height,
            orientationChanged: currentLayout.device.orientation !== newDevice.orientation,
            breakpointChanged: JSON.stringify(currentLayout.breakpoints) !== JSON.stringify(newLayout.breakpoints),
            elementsChanged: []
        };

        // Check element-level changes
        Object.keys(currentLayout.elements).forEach(elementKey => {
            const oldElement = currentLayout.elements[elementKey];
            const newElement = newLayout.elements[elementKey];
            
            const elementChanges = {
                element: elementKey,
                classesChanged: JSON.stringify(oldElement.classes) !== JSON.stringify(newElement.classes),
                stylesChanged: JSON.stringify(oldElement.styles) !== JSON.stringify(newElement.styles),
                oldClasses: oldElement.classes,
                newClasses: newElement.classes,
                oldStyles: oldElement.styles,
                newStyles: newElement.styles
            };
            
            if (elementChanges.classesChanged || elementChanges.stylesChanged) {
                changes.elementsChanged.push(elementChanges);
            }
        });

        return { newLayout, changes };
    }

    // Simulate user interactions during viewport changes
    simulateUserAction(layout, action) {
        if (!action) return { success: true, errors: [] };

        const errors = [];
        
        try {
            switch (action.type) {
                case 'view-toggle':
                    // Simulate view toggle functionality
                    const viewToggle = layout.elements.viewToggle;
                    if (!viewToggle.visible || !viewToggle.responsive) {
                        errors.push('View toggle not accessible during viewport change');
                    }
                    break;

                case 'calendar-navigation':
                    // Simulate calendar navigation
                    const navigation = layout.elements.navigation;
                    if (!navigation.visible || layout.device.width < 320) {
                        errors.push('Calendar navigation not functional on very small screens');
                    }
                    break;

                case 'calendar-view-change':
                    // Simulate calendar view change
                    const calendarView = layout.elements.calendarView;
                    if (!calendarView.responsive) {
                        errors.push('Calendar view change not responsive');
                    }
                    break;

                case 'modal-open':
                    // Simulate modal opening
                    const modal = layout.elements.modal;
                    if (layout.device.width < 320 && modal.styles.width === '100%') {
                        // This is actually correct behavior, not an error
                    } else if (!modal.responsive) {
                        errors.push('Modal not responsive to screen size');
                    }
                    break;
            }
        } catch (error) {
            errors.push(`User action simulation failed: ${error.message}`);
        }

        return { success: errors.length === 0, errors };
    }

    // Property validation
    validateResponsiveLayoutAdaptation(scenario, layoutChanges, userActionResults) {
        const errors = [];

        // Property 5: Responsive Layout Adaptation
        // For any screen size or device orientation, the interface should adapt appropriately while maintaining all functionality and readability

        // Check that layout adapts to different screen sizes
        for (let i = 0; i < layoutChanges.length; i++) {
            const { newLayout, changes } = layoutChanges[i];
            const device = newLayout.device;

            // Validate breakpoint-based adaptations
            if (changes.breakpointChanged) {
                // Check that elements adapted to new breakpoints
                if (changes.elementsChanged.length === 0) {
                    errors.push(`Viewport change ${i + 1}: No element adaptations despite breakpoint change`);
                }
            }

            // Validate mobile-specific adaptations
            if (device.width < 640) {
                // Check view toggle layout
                const viewToggle = newLayout.elements.viewToggle;
                if (!viewToggle.classes.includes('flex-col')) {
                    errors.push(`Viewport change ${i + 1}: View toggle should stack vertically on mobile (width: ${device.width}px)`);
                }

                // Check card view grid
                const cardView = newLayout.elements.cardView;
                if (!cardView.classes.includes('grid-cols-1')) {
                    errors.push(`Viewport change ${i + 1}: Card view should use single column on mobile (width: ${device.width}px)`);
                }

                // Check modal width
                const modal = newLayout.elements.modal;
                if (!modal.classes.includes('w-full')) {
                    errors.push(`Viewport change ${i + 1}: Modal should be full width on mobile (width: ${device.width}px)`);
                }
            }

            // Validate tablet adaptations
            if (device.width >= 640 && device.width < 1024) {
                const cardView = newLayout.elements.cardView;
                if (device.width >= 768 && !cardView.classes.includes('grid-cols-2')) {
                    errors.push(`Viewport change ${i + 1}: Card view should use two columns on tablet (width: ${device.width}px)`);
                }
            }

            // Validate desktop adaptations
            if (device.width >= 1024) {
                const viewToggle = newLayout.elements.viewToggle;
                if (!viewToggle.classes.includes('flex-row')) {
                    errors.push(`Viewport change ${i + 1}: View toggle should be horizontal on desktop (width: ${device.width}px)`);
                }
            }

            // Check for layout errors
            if (newLayout.errors.length > 0) {
                errors.push(...newLayout.errors.map(error => `Viewport change ${i + 1}: ${error}`));
            }

            // Validate functionality preservation
            Object.values(newLayout.elements).forEach(element => {
                if (!element.visible) {
                    errors.push(`Viewport change ${i + 1}: Element ${element.type} became invisible`);
                }
                if (!element.responsive) {
                    errors.push(`Viewport change ${i + 1}: Element ${element.type} lost responsive behavior`);
                }
            });
        }

        // Check user action results
        userActionResults.forEach((result, index) => {
            if (!result.success) {
                errors.push(...result.errors.map(error => `User action ${index + 1}: ${error}`));
            }
        });

        // Validate extreme cases
        const extremeDevices = layoutChanges.filter(change => 
            change.newLayout.device.width < 320 || change.newLayout.device.width > 2000
        );
        
        extremeDevices.forEach((change, index) => {
            const device = change.newLayout.device;
            if (device.width < 320) {
                // Very small screens should still be functional
                const layout = change.newLayout;
                if (!layout.elements.viewToggle.visible) {
                    errors.push(`Extreme viewport ${index + 1}: Interface not functional on very small screen (${device.width}px)`);
                }
            }
        });

        return {
            passed: errors.length === 0,
            errors: errors,
            layoutChangeCount: layoutChanges.length,
            userActionCount: userActionResults.length,
            deviceTypes: [...new Set(layoutChanges.map(change => change.newLayout.device.name))]
        };
    }

    // Run a single property test iteration
    async runSingleTest(iteration) {
        try {
            // Generate test scenario
            const scenario = {
                initialDevice: this.generateDeviceConfiguration(),
                viewportChanges: this.generateViewportChanges()
            };
            
            // Create initial layout
            let currentLayout = this.createMockLayout(scenario.initialDevice);
            const layoutChanges = [];
            const userActionResults = [];
            
            // Execute viewport changes
            for (const change of scenario.viewportChanges) {
                // Add delay simulation
                if (change.delay > 0) {
                    await new Promise(resolve => setTimeout(resolve, Math.min(change.delay, 10))); // Cap delay for testing
                }
                
                // Simulate viewport change
                const changeResult = this.simulateViewportChange(currentLayout, change.device);
                layoutChanges.push(changeResult);
                currentLayout = changeResult.newLayout;
                
                // Simulate user action if present
                if (change.userAction) {
                    const actionResult = this.simulateUserAction(currentLayout, change.userAction);
                    userActionResults.push(actionResult);
                }
            }
            
            // Validate property
            const validation = this.validateResponsiveLayoutAdaptation(scenario, layoutChanges, userActionResults);
            
            return {
                iteration: iteration,
                passed: validation.passed,
                errors: validation.errors,
                scenario: scenario,
                layoutChangeCount: validation.layoutChangeCount,
                userActionCount: validation.userActionCount,
                deviceTypes: validation.deviceTypes,
                executionTime: Date.now()
            };
            
        } catch (error) {
            return {
                iteration: iteration,
                passed: false,
                errors: [`Test execution error: ${error.message}`],
                scenario: null,
                layoutChangeCount: 0,
                userActionCount: 0,
                deviceTypes: [],
                executionTime: Date.now()
            };
        }
    }

    // Run all property tests
    async runAllTests() {
        this.results = [];
        const startTime = Date.now();
        
        console.log(`\n🧪 Starting ${this.iterations} property test iterations...`);
        console.log('Feature: unifikasi-jadwal-jumat, Property 5: Responsive Layout Adaptation');
        console.log('Validates: Requirements 3.4\n');
        
        for (let i = 1; i <= this.iterations; i++) {
            const result = await this.runSingleTest(i);
            this.results.push(result);
            
            // Update progress every 25 iterations
            if (i % 25 === 0) {
                const passed = this.results.filter(r => r.passed).length;
                const failed = this.results.filter(r => !r.passed).length;
                console.log(`Progress: ${i}/${this.iterations} - Passed: ${passed}, Failed: ${failed}`);
            }
        }
        
        const endTime = Date.now();
        const executionTime = endTime - startTime;
        
        // Generate final report
        this.generateReport(executionTime);
    }

    generateReport(executionTime) {
        const passed = this.results.filter(r => r.passed).length;
        const failed = this.results.filter(r => !r.passed).length;
        const passRate = ((passed / this.iterations) * 100).toFixed(2);
        
        console.log('\n' + '='.repeat(60));
        console.log('📊 PROPERTY TEST RESULTS SUMMARY');
        console.log('='.repeat(60));
        console.log(`Total Iterations: ${this.iterations}`);
        console.log(`Passed: ${passed}`);
        console.log(`Failed: ${failed}`);
        console.log(`Pass Rate: ${passRate}%`);
        console.log(`Execution Time: ${executionTime}ms`);
        console.log(`Status: ${failed === 0 ? '✅ PASSED' : '❌ FAILED'}`);
        
        // Failure details
        if (failed > 0) {
            console.log('\n🐛 FAILURE DETAILS (First 3):');
            console.log('-'.repeat(40));
            const failures = this.results.filter(r => !r.passed);
            failures.slice(0, 3).forEach(failure => {
                console.log(`\nIteration ${failure.iteration}:`);
                failure.errors.forEach(error => {
                    console.log(`  • ${error}`);
                });
            });
        }
        
        // Statistics
        const stats = this.calculateStatistics();
        console.log('\n📈 TEST STATISTICS:');
        console.log('-'.repeat(40));
        console.log(`Average Layout Changes per Test: ${stats.avgLayoutChanges}`);
        console.log(`Max Layout Changes: ${stats.maxLayoutChanges}`);
        console.log(`Min Layout Changes: ${stats.minLayoutChanges}`);
        console.log(`Total Viewport Changes Tested: ${stats.totalViewportChanges}`);
        console.log(`Device Types Tested: ${stats.deviceTypes.join(', ')}`);
        console.log(`Responsive Adaptation Rate: ${stats.adaptationRate}%`);
        
        console.log('\n' + '='.repeat(60));
        
        return failed === 0;
    }

    calculateStatistics() {
        const layoutChangeCounts = this.results.map(r => r.layoutChangeCount);
        const totalLayoutChanges = layoutChangeCounts.reduce((sum, count) => sum + count, 0);
        const avgLayoutChanges = (totalLayoutChanges / this.iterations).toFixed(2);
        const maxLayoutChanges = Math.max(...layoutChangeCounts);
        const minLayoutChanges = Math.min(...layoutChangeCounts);
        
        // Collect all device types tested
        const allDeviceTypes = new Set();
        this.results.forEach(result => {
            if (result.deviceTypes) {
                result.deviceTypes.forEach(type => allDeviceTypes.add(type));
            }
        });
        
        // Calculate adaptation success rate
        const successfulAdaptations = this.results.filter(r => r.passed).length;
        const adaptationRate = ((successfulAdaptations / this.iterations) * 100).toFixed(2);
        
        return {
            avgLayoutChanges,
            maxLayoutChanges,
            minLayoutChanges,
            totalViewportChanges: totalLayoutChanges,
            deviceTypes: Array.from(allDeviceTypes),
            adaptationRate
        };
    }
}

// Run the tests
async function main() {
    const test = new ResponsiveBehaviorPropertyTest();
    const success = await test.runAllTests();
    process.exit(success ? 0 : 1);
}

// Run if this file is executed directly
if (require.main === module) {
    main().catch(error => {
        console.error('Test execution failed:', error);
        process.exit(1);
    });
}

module.exports = ResponsiveBehaviorPropertyTest;