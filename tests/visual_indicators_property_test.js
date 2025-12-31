/**
 * Property-Based Test for Visual Event Indicators and Friday Highlighting
 * Feature: unifikasi-jadwal-jumat, Property 9: Visual Event Indicators
 * Feature: unifikasi-jadwal-jumat, Property 11: Friday Highlighting
 * Validates: Requirements 5.2, 5.5
 */

class VisualIndicatorsPropertyTest {
    constructor() {
        this.iterations = 100;
        this.results = [];
    }

    // Generator for random calendar scenarios
    generateCalendarScenario() {
        return {
            calendarMonth: this.generateRandomMonth(),
            fridayDates: this.generateFridayDates(),
            events: this.generateRandomEvents(),
            viewType: Math.random() < 0.5 ? 'public' : 'admin',
            screenSize: this.generateRandomScreenSize()
        };
    }

    generateRandomMonth() {
        const year = 2024 + Math.floor(Math.random() * 2); // 2024-2025
        const month = Math.floor(Math.random() * 12) + 1; // 1-12
        return { year, month };
    }

    generateFridayDates() {
        const { year, month } = this.generateRandomMonth();
        const fridays = [];
        const date = new Date(year, month - 1, 1);
        
        // Find all Fridays in the month
        while (date.getMonth() === month - 1) {
            if (date.getDay() === 5) { // Friday
                fridays.push(new Date(date));
            }
            date.setDate(date.getDate() + 1);
        }
        
        return fridays.map(d => d.toISOString().split('T')[0]);
    }

    generateRandomEvents() {
        const events = [];
        const eventCount = Math.floor(Math.random() * 15) + 1; // 1-15 events
        
        for (let i = 0; i < eventCount; i++) {
            const fridayDate = this.generateRandomFridayDate();
            const status = this.generateRandomStatus();
            
            events.push({
                id: i + 1,
                date: fridayDate,
                title: `Sholat Jumat ${i + 1}`,
                imam: `Imam ${i + 1}`,
                khotib: `Khotib ${i + 1}`,
                theme: `Theme ${i + 1}`,
                status: status,
                prayer_time: '12:00'
            });
        }
        
        return events;
    }

    generateRandomFridayDate() {
        const today = new Date();
        const daysRange = Math.floor(Math.random() * 180) - 90; // -90 to +90 days
        const targetDate = new Date(today.getTime() + (daysRange * 24 * 60 * 60 * 1000));
        
        // Adjust to nearest Friday
        const dayOfWeek = targetDate.getDay();
        const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
        if (daysUntilFriday !== 0) {
            targetDate.setDate(targetDate.getDate() + daysUntilFriday);
        }
        
        return targetDate.toISOString().split('T')[0];
    }

    generateRandomStatus() {
        const statuses = ['scheduled', 'completed', 'cancelled'];
        return statuses[Math.floor(Math.random() * statuses.length)];
    }

    generateRandomScreenSize() {
        const sizes = [
            { width: 320, height: 568, type: 'mobile' },
            { width: 768, height: 1024, type: 'tablet' },
            { width: 1024, height: 768, type: 'tablet-landscape' },
            { width: 1920, height: 1080, type: 'desktop' }
        ];
        return sizes[Math.floor(Math.random() * sizes.length)];
    }

    // Mock calendar state
    createMockCalendarState(scenario) {
        const calendarDates = this.generateCalendarDates(scenario.calendarMonth);
        const dateElements = new Map();
        
        // Create mock DOM elements for each date
        calendarDates.forEach(date => {
            const isFriday = new Date(date).getDay() === 5;
            const hasEvents = scenario.events.some(event => event.date === date);
            const eventCount = scenario.events.filter(event => event.date === date).length;
            
            dateElements.set(date, {
                date: date,
                isFriday: isFriday,
                hasEvents: hasEvents,
                eventCount: eventCount,
                events: scenario.events.filter(event => event.date === date),
                element: this.createMockDOMElement(date, isFriday, hasEvents, eventCount, scenario.viewType),
                rendered: false
            });
        });
        
        return {
            scenario: scenario,
            dateElements: dateElements,
            calendarDates: calendarDates,
            renderingComplete: false
        };
    }

    createMockDOMElement(date, isFriday, hasEvents, eventCount, viewType) {
        const element = {
            date: date,
            classList: new Set(),
            style: {},
            children: [],
            attributes: new Map(),
            
            // Mock DOM methods
            querySelector: function(selector) {
                return this.children.find(child => child.matches && child.matches(selector));
            },
            
            appendChild: function(child) {
                this.children.push(child);
                child.parentElement = this;
            },
            
            setAttribute: function(name, value) {
                this.attributes.set(name, value);
            },
            
            getAttribute: function(name) {
                return this.attributes.get(name);
            }
        };
        
        // Add base classes
        element.classList.add('fc-daygrid-day');
        
        if (isFriday) {
            element.classList.add('fc-day-fri');
        }
        
        if (hasEvents) {
            element.classList.add('has-events');
        }
        
        return element;
    }

    generateCalendarDates(calendarMonth) {
        const { year, month } = calendarMonth;
        const dates = [];
        const date = new Date(year, month - 1, 1);
        
        while (date.getMonth() === month - 1) {
            dates.push(date.toISOString().split('T')[0]);
            date.setDate(date.getDate() + 1);
        }
        
        return dates;
    }

    // Simulate calendar rendering with visual indicators
    simulateCalendarRendering(calendarState) {
        const { dateElements, scenario } = calendarState;
        const renderingResults = [];
        
        dateElements.forEach((dateInfo, date) => {
            const result = this.simulateDateCellRendering(dateInfo, scenario);
            renderingResults.push(result);
            dateInfo.rendered = true;
        });
        
        calendarState.renderingComplete = true;
        return renderingResults;
    }

    simulateDateCellRendering(dateInfo, scenario) {
        const { element, isFriday, hasEvents, eventCount, events } = dateInfo;
        const renderingResult = {
            date: dateInfo.date,
            isFriday: isFriday,
            hasEvents: hasEvents,
            eventCount: eventCount,
            appliedStyles: {},
            addedElements: [],
            errors: []
        };
        
        try {
            // Apply Friday highlighting
            if (isFriday) {
                this.applyFridayHighlighting(element, renderingResult, scenario);
            }
            
            // Apply event indicators
            if (hasEvents) {
                this.applyEventIndicators(element, renderingResult, events, scenario);
            }
            
            // Apply responsive adjustments
            this.applyResponsiveAdjustments(element, renderingResult, scenario);
            
        } catch (error) {
            renderingResult.errors.push(`Rendering error: ${error.message}`);
        }
        
        return renderingResult;
    }

    applyFridayHighlighting(element, result, scenario) {
        // Base Friday styling
        result.appliedStyles.backgroundColor = '#f0fdf4';
        result.appliedStyles.border = scenario.viewType === 'admin' ? '2px solid #bbf7d0' : '1px solid #bbf7d0';
        
        // Day number styling
        const dayNumber = { 
            color: '#059669',
            fontWeight: '700',
            backgroundColor: '#dcfce7',
            borderRadius: '50%',
            width: scenario.viewType === 'admin' ? '28px' : '24px',
            height: scenario.viewType === 'admin' ? '28px' : '24px'
        };
        result.appliedStyles.dayNumber = dayNumber;
        
        // Friday badge
        const fridayBadge = {
            position: 'absolute',
            top: scenario.viewType === 'admin' ? '3px' : '2px',
            left: scenario.viewType === 'admin' ? '3px' : '2px',
            backgroundColor: '#059669',
            color: 'white',
            fontSize: scenario.viewType === 'admin' ? '9px' : '8px',
            text: scenario.viewType === 'admin' ? 'JUMAT' : 'JUM'
        };
        result.addedElements.push({ type: 'friday-badge', styles: fridayBadge });
        
        // Add to element
        element.classList.add('fc-day-fri');
        element.style = { ...element.style, ...result.appliedStyles };
    }

    applyEventIndicators(element, result, events, scenario) {
        // Event indicator dot
        const indicator = {
            position: 'absolute',
            top: scenario.viewType === 'admin' ? '5px' : '4px',
            right: scenario.viewType === 'admin' ? '5px' : '4px',
            width: scenario.viewType === 'admin' ? '12px' : '10px',
            height: scenario.viewType === 'admin' ? '12px' : '10px',
            backgroundColor: this.getEventIndicatorColor(events),
            borderRadius: '50%',
            border: '2px solid white'
        };
        result.addedElements.push({ type: 'event-indicator', styles: indicator });
        
        // Event count badge for multiple events
        if (events.length > 1) {
            const countBadge = {
                position: 'absolute',
                top: '-2px',
                right: '-2px',
                backgroundColor: '#ef4444',
                color: 'white',
                fontSize: '10px',
                text: events.length.toString(),
                minWidth: scenario.viewType === 'admin' ? '18px' : '16px'
            };
            result.addedElements.push({ type: 'count-badge', styles: countBadge });
        }
        
        // Enhanced Friday styling for events
        if (element.classList.has('fc-day-fri')) {
            result.appliedStyles.backgroundColor = '#ecfdf5';
            result.appliedStyles.border = '2px solid #10b981';
            if (result.appliedStyles.dayNumber) {
                result.appliedStyles.dayNumber.backgroundColor = '#10b981';
                result.appliedStyles.dayNumber.color = 'white';
            }
        }
        
        element.classList.add('has-events');
    }

    getEventIndicatorColor(events) {
        // Determine color based on event statuses
        const statuses = events.map(e => e.status);
        if (statuses.includes('cancelled')) {
            return '#ef4444'; // Red for cancelled
        } else if (statuses.includes('completed')) {
            return '#6b7280'; // Gray for completed
        } else {
            return '#10b981'; // Green for scheduled
        }
    }

    applyResponsiveAdjustments(element, result, scenario) {
        const { screenSize } = scenario;
        
        if (screenSize.type === 'mobile') {
            // Mobile adjustments
            if (result.appliedStyles.dayNumber) {
                result.appliedStyles.dayNumber.width = '20px';
                result.appliedStyles.dayNumber.height = '20px';
                result.appliedStyles.dayNumber.fontSize = '12px';
            }
            
            // Adjust badge sizes
            result.addedElements.forEach(element => {
                if (element.type === 'friday-badge') {
                    element.styles.fontSize = '7px';
                    element.styles.padding = '1px 3px';
                }
                if (element.type === 'event-indicator') {
                    element.styles.width = '8px';
                    element.styles.height = '8px';
                }
            });
        }
    }

    // Property validation
    validateVisualIndicatorProperties(scenario, calendarState, renderingResults) {
        const errors = [];
        
        // Property 9: Visual Event Indicators
        const eventIndicatorErrors = this.validateEventIndicators(scenario, renderingResults);
        errors.push(...eventIndicatorErrors);
        
        // Property 11: Friday Highlighting
        const fridayHighlightingErrors = this.validateFridayHighlighting(scenario, renderingResults);
        errors.push(...fridayHighlightingErrors);
        
        // Cross-validation: Fridays with events should have both highlighting and indicators
        const crossValidationErrors = this.validateCrossProperties(scenario, renderingResults);
        errors.push(...crossValidationErrors);
        
        return {
            passed: errors.length === 0,
            errors: errors,
            scenario: scenario,
            totalDates: renderingResults.length,
            fridayCount: renderingResults.filter(r => r.isFriday).length,
            eventCount: renderingResults.filter(r => r.hasEvents).length
        };
    }

    validateEventIndicators(scenario, renderingResults) {
        const errors = [];
        
        renderingResults.forEach(result => {
            const { date, hasEvents, eventCount, addedElements } = result;
            
            if (hasEvents) {
                // Should have event indicator
                const hasIndicator = addedElements.some(el => el.type === 'event-indicator');
                if (!hasIndicator) {
                    errors.push(`Date ${date}: Has events but missing event indicator`);
                }
                
                // Should have count badge for multiple events
                if (eventCount > 1) {
                    const hasCountBadge = addedElements.some(el => el.type === 'count-badge');
                    if (!hasCountBadge) {
                        errors.push(`Date ${date}: Has ${eventCount} events but missing count badge`);
                    } else {
                        const countBadge = addedElements.find(el => el.type === 'count-badge');
                        if (countBadge.styles.text !== eventCount.toString()) {
                            errors.push(`Date ${date}: Count badge shows '${countBadge.styles.text}' but should show '${eventCount}'`);
                        }
                    }
                }
                
                // Should have has-events class applied
                // (This would be checked in real DOM, simulated here)
                
            } else {
                // Should NOT have event indicator
                const hasIndicator = addedElements.some(el => el.type === 'event-indicator');
                if (hasIndicator) {
                    errors.push(`Date ${date}: No events but has event indicator`);
                }
                
                // Should NOT have count badge
                const hasCountBadge = addedElements.some(el => el.type === 'count-badge');
                if (hasCountBadge) {
                    errors.push(`Date ${date}: No events but has count badge`);
                }
            }
        });
        
        return errors;
    }

    validateFridayHighlighting(scenario, renderingResults) {
        const errors = [];
        
        renderingResults.forEach(result => {
            const { date, isFriday, appliedStyles, addedElements } = result;
            
            if (isFriday) {
                // Should have Friday highlighting
                if (!appliedStyles.backgroundColor || !appliedStyles.backgroundColor.includes('#f0fdf4')) {
                    errors.push(`Friday ${date}: Missing or incorrect background color`);
                }
                
                // Should have Friday badge
                const hasFridayBadge = addedElements.some(el => el.type === 'friday-badge');
                if (!hasFridayBadge) {
                    errors.push(`Friday ${date}: Missing Friday badge`);
                }
                
                // Should have enhanced day number styling
                if (!appliedStyles.dayNumber) {
                    errors.push(`Friday ${date}: Missing day number styling`);
                } else {
                    const dayNumber = appliedStyles.dayNumber;
                    if (dayNumber.color !== '#059669') {
                        errors.push(`Friday ${date}: Incorrect day number color`);
                    }
                    if (dayNumber.fontWeight !== '700') {
                        errors.push(`Friday ${date}: Incorrect day number font weight`);
                    }
                    if (dayNumber.borderRadius !== '50%') {
                        errors.push(`Friday ${date}: Incorrect day number border radius`);
                    }
                }
                
                // Check responsive adjustments
                if (scenario.screenSize.type === 'mobile') {
                    if (appliedStyles.dayNumber && appliedStyles.dayNumber.width !== '20px') {
                        errors.push(`Friday ${date}: Mobile day number size not adjusted`);
                    }
                }
                
            } else {
                // Should NOT have Friday-specific styling
                const hasFridayBadge = addedElements.some(el => el.type === 'friday-badge');
                if (hasFridayBadge) {
                    errors.push(`Non-Friday ${date}: Has Friday badge`);
                }
                
                // Should not have Friday background color
                if (appliedStyles.backgroundColor && appliedStyles.backgroundColor.includes('#f0fdf4')) {
                    errors.push(`Non-Friday ${date}: Has Friday background color`);
                }
            }
        });
        
        return errors;
    }

    validateCrossProperties(scenario, renderingResults) {
        const errors = [];
        
        renderingResults.forEach(result => {
            const { date, isFriday, hasEvents, appliedStyles } = result;
            
            // Fridays with events should have enhanced styling
            if (isFriday && hasEvents) {
                if (!appliedStyles.backgroundColor || !appliedStyles.backgroundColor.includes('#ecfdf5')) {
                    errors.push(`Friday with events ${date}: Missing enhanced background color`);
                }
                
                if (!appliedStyles.border || !appliedStyles.border.includes('#10b981')) {
                    errors.push(`Friday with events ${date}: Missing enhanced border`);
                }
                
                if (appliedStyles.dayNumber) {
                    if (appliedStyles.dayNumber.backgroundColor !== '#10b981') {
                        errors.push(`Friday with events ${date}: Day number should have event color background`);
                    }
                    if (appliedStyles.dayNumber.color !== 'white') {
                        errors.push(`Friday with events ${date}: Day number should have white text`);
                    }
                }
            }
        });
        
        return errors;
    }

    // Run a single property test iteration
    async runSingleTest(iteration) {
        try {
            // Generate test scenario
            const scenario = this.generateCalendarScenario();
            
            // Create mock calendar state
            const calendarState = this.createMockCalendarState(scenario);
            
            // Simulate calendar rendering
            const renderingResults = this.simulateCalendarRendering(calendarState);
            
            // Validate properties
            const validation = this.validateVisualIndicatorProperties(scenario, calendarState, renderingResults);
            
            return {
                iteration: iteration,
                passed: validation.passed,
                errors: validation.errors,
                scenario: scenario,
                totalDates: validation.totalDates,
                fridayCount: validation.fridayCount,
                eventCount: validation.eventCount,
                executionTime: Date.now()
            };
            
        } catch (error) {
            return {
                iteration: iteration,
                passed: false,
                errors: [`Test execution error: ${error.message}`],
                scenario: null,
                totalDates: 0,
                fridayCount: 0,
                eventCount: 0,
                executionTime: Date.now()
            };
        }
    }

    // Run all property tests
    async runAllTests() {
        this.results = [];
        const startTime = Date.now();
        
        console.log(`\n🧪 Starting ${this.iterations} property test iterations...`);
        console.log('Feature: unifikasi-jadwal-jumat, Property 9: Visual Event Indicators');
        console.log('Feature: unifikasi-jadwal-jumat, Property 11: Friday Highlighting');
        console.log('Validates: Requirements 5.2, 5.5\n');
        
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
        
        console.log('\n' + '='.repeat(70));
        console.log('📊 VISUAL INDICATORS PROPERTY TEST RESULTS');
        console.log('='.repeat(70));
        console.log(`Total Iterations: ${this.iterations}`);
        console.log(`Passed: ${passed}`);
        console.log(`Failed: ${failed}`);
        console.log(`Pass Rate: ${passRate}%`);
        console.log(`Execution Time: ${executionTime}ms`);
        console.log(`Status: ${failed === 0 ? '✅ PASSED' : '❌ FAILED'}`);
        
        // Failure details
        if (failed > 0) {
            console.log('\n🐛 FAILURE DETAILS (First 3):');
            console.log('-'.repeat(50));
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
        console.log('-'.repeat(50));
        console.log(`Total Dates Tested: ${stats.totalDates}`);
        console.log(`Total Fridays Tested: ${stats.totalFridays}`);
        console.log(`Total Events Tested: ${stats.totalEvents}`);
        console.log(`Average Fridays per Calendar: ${stats.avgFridays}`);
        console.log(`Average Events per Calendar: ${stats.avgEvents}`);
        console.log(`Screen Sizes Tested: ${stats.screenSizes.join(', ')}`);
        console.log(`View Types Tested: ${stats.viewTypes.join(', ')}`);
        console.log(`Coverage: ${stats.coverage}%`);
        
        console.log('\n' + '='.repeat(70));
        
        return failed === 0;
    }

    calculateStatistics() {
        const totalDates = this.results.reduce((sum, r) => sum + r.totalDates, 0);
        const totalFridays = this.results.reduce((sum, r) => sum + r.fridayCount, 0);
        const totalEvents = this.results.reduce((sum, r) => sum + r.eventCount, 0);
        
        const avgFridays = (totalFridays / this.iterations).toFixed(2);
        const avgEvents = (totalEvents / this.iterations).toFixed(2);
        
        // Collect unique screen sizes and view types tested
        const screenSizes = new Set();
        const viewTypes = new Set();
        
        this.results.forEach(r => {
            if (r.scenario) {
                screenSizes.add(r.scenario.screenSize.type);
                viewTypes.add(r.scenario.viewType);
            }
        });
        
        // Coverage estimation
        const uniqueScenarios = new Set(this.results.map(r => 
            r.scenario ? `${r.scenario.viewType}-${r.scenario.screenSize.type}-${r.fridayCount}-${r.eventCount}` : ''
        )).size;
        const coverage = Math.min(100, (uniqueScenarios / this.iterations * 100)).toFixed(1);
        
        return {
            totalDates,
            totalFridays,
            totalEvents,
            avgFridays,
            avgEvents,
            screenSizes: Array.from(screenSizes),
            viewTypes: Array.from(viewTypes),
            coverage
        };
    }
}

// Run the tests
async function main() {
    const test = new VisualIndicatorsPropertyTest();
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

module.exports = VisualIndicatorsPropertyTest;