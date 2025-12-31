/**
 * Property-Based Test for Calendar Navigation
 * Feature: unifikasi-jadwal-jumat, Property 10: Calendar Navigation
 * Validates: Requirements 5.3
 */

class CalendarNavigationPropertyTest {
    constructor() {
        this.iterations = 100;
        this.results = [];
    }

    // Generator for random navigation scenarios
    generateNavigationScenario() {
        return {
            initialDate: this.generateRandomDate(),
            navigationSequence: this.generateNavigationSequence(),
            viewType: Math.random() < 0.5 ? 'public' : 'admin',
            userInteractions: this.generateUserInteractions(),
            eventData: this.generateEventData()
        };
    }

    generateRandomDate() {
        const currentYear = new Date().getFullYear();
        const year = currentYear + Math.floor(Math.random() * 3) - 1; // -1 to +2 years
        const month = Math.floor(Math.random() * 12); // 0-11
        const day = Math.floor(Math.random() * 28) + 1; // 1-28 (safe for all months)
        
        return new Date(year, month, day);
    }

    generateNavigationSequence() {
        const sequenceLength = Math.floor(Math.random() * 10) + 1; // 1-10 navigation actions
        const sequence = [];
        const actions = ['prev', 'next', 'prevYear', 'nextYear', 'today', 'gotoDate', 'changeView'];
        
        for (let i = 0; i < sequenceLength; i++) {
            const action = actions[Math.floor(Math.random() * actions.length)];
            const navigationAction = {
                type: action,
                timestamp: Date.now() + (i * 100), // Simulate timing
                expectedBehavior: this.getExpectedBehavior(action)
            };
            
            // Add specific parameters for certain actions
            if (action === 'gotoDate') {
                navigationAction.targetDate = this.generateRandomDate();
            } else if (action === 'changeView') {
                navigationAction.targetView = Math.random() < 0.5 ? 'dayGridMonth' : 'listMonth';
            }
            
            sequence.push(navigationAction);
        }
        
        return sequence;
    }

    getExpectedBehavior(action) {
        const behaviors = {
            'prev': 'Navigate to previous month, load events for new range',
            'next': 'Navigate to next month, load events for new range',
            'prevYear': 'Navigate to previous year, validate year limits',
            'nextYear': 'Navigate to next year, validate year limits',
            'today': 'Navigate to current month, highlight today if visible',
            'gotoDate': 'Navigate to specific date, update calendar view',
            'changeView': 'Change calendar view type, maintain current date'
        };
        return behaviors[action] || 'Unknown behavior';
    }

    generateUserInteractions() {
        const interactions = [];
        const interactionCount = Math.floor(Math.random() * 5) + 1; // 1-5 interactions
        const interactionTypes = ['click', 'keyboard', 'touch', 'scroll'];
        
        for (let i = 0; i < interactionCount; i++) {
            interactions.push({
                type: interactionTypes[Math.floor(Math.random() * interactionTypes.length)],
                target: this.generateInteractionTarget(),
                timing: Math.floor(Math.random() * 1000), // Random timing
                modifiers: this.generateKeyboardModifiers()
            });
        }
        
        return interactions;
    }

    generateInteractionTarget() {
        const targets = [
            'prev-button', 'next-button', 'today-button', 'prevYear-button', 
            'nextYear-button', 'currentMonth-button', 'calendar-cell', 'view-button'
        ];
        return targets[Math.floor(Math.random() * targets.length)];
    }

    generateKeyboardModifiers() {
        return {
            ctrl: Math.random() < 0.3,
            shift: Math.random() < 0.2,
            alt: Math.random() < 0.1
        };
    }

    generateEventData() {
        const events = [];
        const eventCount = Math.floor(Math.random() * 20) + 1; // 1-20 events
        
        for (let i = 0; i < eventCount; i++) {
            events.push({
                id: i + 1,
                date: this.generateRandomFridayDate(),
                title: `Event ${i + 1}`,
                status: ['scheduled', 'completed', 'cancelled'][Math.floor(Math.random() * 3)]
            });
        }
        
        return events;
    }

    generateRandomFridayDate() {
        const baseDate = this.generateRandomDate();
        const dayOfWeek = baseDate.getDay();
        const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
        
        if (daysUntilFriday !== 0) {
            baseDate.setDate(baseDate.getDate() + daysUntilFriday);
        }
        
        return baseDate.toISOString().split('T')[0];
    }

    // Mock calendar state management
    createMockCalendarState(scenario) {
        return {
            currentDate: new Date(scenario.initialDate),
            currentView: 'dayGridMonth',
            events: scenario.eventData,
            navigationHistory: [],
            loadingStates: [],
            stateChanges: [],
            errors: [],
            
            // Mock calendar methods
            gotoDate: function(date) {
                const oldDate = new Date(this.currentDate);
                this.currentDate = new Date(date);
                this.navigationHistory.push({
                    action: 'gotoDate',
                    from: oldDate,
                    to: new Date(date),
                    timestamp: Date.now()
                });
                return this.validateDateNavigation(oldDate, new Date(date));
            },
            
            prev: function() {
                const oldDate = new Date(this.currentDate);
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.navigationHistory.push({
                    action: 'prev',
                    from: oldDate,
                    to: new Date(this.currentDate),
                    timestamp: Date.now()
                });
                return this.validateMonthNavigation(oldDate, new Date(this.currentDate));
            },
            
            next: function() {
                const oldDate = new Date(this.currentDate);
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.navigationHistory.push({
                    action: 'next',
                    from: oldDate,
                    to: new Date(this.currentDate),
                    timestamp: Date.now()
                });
                return this.validateMonthNavigation(oldDate, new Date(this.currentDate));
            },
            
            changeView: function(viewType) {
                const oldView = this.currentView;
                this.currentView = viewType;
                this.navigationHistory.push({
                    action: 'changeView',
                    from: oldView,
                    to: viewType,
                    timestamp: Date.now()
                });
                return this.validateViewChange(oldView, viewType);
            },
            
            validateDateNavigation: function(fromDate, toDate) {
                const errors = [];
                
                // Validate date change
                if (fromDate.getTime() === toDate.getTime()) {
                    errors.push('Date navigation did not change the current date');
                }
                
                // Validate reasonable date range
                const yearDiff = Math.abs(toDate.getFullYear() - fromDate.getFullYear());
                if (yearDiff > 10) {
                    errors.push(`Navigation jumped too far: ${yearDiff} years`);
                }
                
                return { success: errors.length === 0, errors };
            },
            
            validateMonthNavigation: function(fromDate, toDate) {
                const errors = [];
                
                // Validate month change
                const monthDiff = (toDate.getFullYear() - fromDate.getFullYear()) * 12 + 
                                 (toDate.getMonth() - fromDate.getMonth());
                
                if (Math.abs(monthDiff) !== 1) {
                    errors.push(`Month navigation should change by 1 month, but changed by ${monthDiff}`);
                }
                
                return { success: errors.length === 0, errors };
            },
            
            validateViewChange: function(fromView, toView) {
                const errors = [];
                const validViews = ['dayGridMonth', 'listMonth'];
                
                if (!validViews.includes(toView)) {
                    errors.push(`Invalid view type: ${toView}`);
                }
                
                if (fromView === toView) {
                    errors.push('View change did not actually change the view');
                }
                
                return { success: errors.length === 0, errors };
            }
        };
    }

    // Simulate navigation execution
    async simulateNavigation(calendarState, scenario) {
        const navigationResults = [];
        
        for (const navAction of scenario.navigationSequence) {
            const result = await this.executeNavigationAction(calendarState, navAction);
            navigationResults.push(result);
            
            // Simulate loading delay
            await new Promise(resolve => setTimeout(resolve, Math.random() * 50));
        }
        
        return navigationResults;
    }

    async executeNavigationAction(calendarState, navAction) {
        const startTime = Date.now();
        const beforeState = {
            date: new Date(calendarState.currentDate),
            view: calendarState.currentView,
            eventsLoaded: calendarState.events.length
        };
        
        let actionResult;
        
        try {
            switch (navAction.type) {
                case 'prev':
                    actionResult = calendarState.prev();
                    break;
                case 'next':
                    actionResult = calendarState.next();
                    break;
                case 'prevYear':
                    actionResult = this.simulateYearNavigation(calendarState, -1);
                    break;
                case 'nextYear':
                    actionResult = this.simulateYearNavigation(calendarState, 1);
                    break;
                case 'today':
                    actionResult = calendarState.gotoDate(new Date());
                    break;
                case 'gotoDate':
                    actionResult = calendarState.gotoDate(navAction.targetDate);
                    break;
                case 'changeView':
                    actionResult = calendarState.changeView(navAction.targetView);
                    break;
                default:
                    actionResult = { success: false, errors: [`Unknown action: ${navAction.type}`] };
            }
        } catch (error) {
            actionResult = { success: false, errors: [`Action execution error: ${error.message}`] };
        }
        
        const afterState = {
            date: new Date(calendarState.currentDate),
            view: calendarState.currentView,
            eventsLoaded: calendarState.events.length
        };
        
        const executionTime = Date.now() - startTime;
        
        return {
            action: navAction,
            beforeState,
            afterState,
            actionResult,
            executionTime,
            success: actionResult.success
        };
    }

    simulateYearNavigation(calendarState, direction) {
        const currentYear = calendarState.currentDate.getFullYear();
        const targetYear = currentYear + direction;
        const currentYearBase = new Date().getFullYear();
        
        // Validate year limits (same as implementation)
        const isPublic = Math.random() < 0.5; // Simulate view type
        const maxPastYears = isPublic ? 2 : 5;
        const maxFutureYears = isPublic ? 5 : 10;
        
        if (targetYear < currentYearBase - maxPastYears) {
            return { 
                success: false, 
                errors: [`Cannot navigate more than ${maxPastYears} years back`] 
            };
        }
        
        if (targetYear > currentYearBase + maxFutureYears) {
            return { 
                success: false, 
                errors: [`Cannot navigate more than ${maxFutureYears} years forward`] 
            };
        }
        
        // Perform year navigation
        const oldDate = new Date(calendarState.currentDate);
        calendarState.currentDate.setFullYear(targetYear);
        
        calendarState.navigationHistory.push({
            action: direction > 0 ? 'nextYear' : 'prevYear',
            from: oldDate,
            to: new Date(calendarState.currentDate),
            timestamp: Date.now()
        });
        
        return { success: true, errors: [] };
    }

    // Property validation
    validateNavigationProperties(scenario, calendarState, navigationResults) {
        const errors = [];
        
        // Property 10: Calendar Navigation
        const navigationErrors = this.validateCalendarNavigation(scenario, navigationResults);
        errors.push(...navigationErrors);
        
        // Additional navigation consistency checks
        const consistencyErrors = this.validateNavigationConsistency(calendarState, navigationResults);
        errors.push(...consistencyErrors);
        
        // State preservation checks
        const stateErrors = this.validateStatePreservation(scenario, calendarState, navigationResults);
        errors.push(...stateErrors);
        
        return {
            passed: errors.length === 0,
            errors: errors,
            scenario: scenario,
            totalNavigations: navigationResults.length,
            successfulNavigations: navigationResults.filter(r => r.success).length,
            failedNavigations: navigationResults.filter(r => !r.success).length
        };
    }

    validateCalendarNavigation(scenario, navigationResults) {
        const errors = [];
        
        navigationResults.forEach((result, index) => {
            const { action, beforeState, afterState, actionResult } = result;
            
            // Validate that navigation actions produce expected state changes
            switch (action.type) {
                case 'prev':
                case 'next':
                    if (actionResult.success) {
                        const expectedMonthDiff = action.type === 'next' ? 1 : -1;
                        const actualMonthDiff = (afterState.date.getFullYear() - beforeState.date.getFullYear()) * 12 + 
                                              (afterState.date.getMonth() - beforeState.date.getMonth());
                        
                        if (actualMonthDiff !== expectedMonthDiff) {
                            errors.push(`Navigation ${index + 1} (${action.type}): Expected month change of ${expectedMonthDiff}, got ${actualMonthDiff}`);
                        }
                    }
                    break;
                    
                case 'gotoDate':
                    if (actionResult.success && action.targetDate) {
                        const targetDate = new Date(action.targetDate);
                        const navigatedDate = afterState.date;
                        
                        if (targetDate.getFullYear() !== navigatedDate.getFullYear() || 
                            targetDate.getMonth() !== navigatedDate.getMonth()) {
                            errors.push(`Navigation ${index + 1} (gotoDate): Did not navigate to correct month`);
                        }
                    }
                    break;
                    
                case 'changeView':
                    if (actionResult.success) {
                        if (afterState.view !== action.targetView) {
                            errors.push(`Navigation ${index + 1} (changeView): View not changed to ${action.targetView}`);
                        }
                        
                        // Date should remain the same during view changes
                        if (beforeState.date.getTime() !== afterState.date.getTime()) {
                            errors.push(`Navigation ${index + 1} (changeView): Date changed during view change`);
                        }
                    }
                    break;
                    
                case 'today':
                    if (actionResult.success) {
                        const today = new Date();
                        const navigatedDate = afterState.date;
                        
                        if (today.getFullYear() !== navigatedDate.getFullYear() || 
                            today.getMonth() !== navigatedDate.getMonth()) {
                            errors.push(`Navigation ${index + 1} (today): Did not navigate to current month`);
                        }
                    }
                    break;
            }
            
            // Validate that events are preserved during navigation
            if (beforeState.eventsLoaded !== afterState.eventsLoaded) {
                errors.push(`Navigation ${index + 1}: Event count changed from ${beforeState.eventsLoaded} to ${afterState.eventsLoaded}`);
            }
        });
        
        return errors;
    }

    validateNavigationConsistency(calendarState, navigationResults) {
        const errors = [];
        
        // Check navigation history consistency
        const history = calendarState.navigationHistory;
        
        if (history.length !== navigationResults.length) {
            errors.push(`Navigation history length mismatch: expected ${navigationResults.length}, got ${history.length}`);
        }
        
        // Validate navigation sequence makes sense
        for (let i = 1; i < history.length; i++) {
            const prev = history[i - 1];
            const current = history[i];
            
            // Check that navigation is sequential
            if (prev.to.getTime() !== current.from.getTime()) {
                errors.push(`Navigation sequence break at step ${i}: previous 'to' date doesn't match current 'from' date`);
            }
            
            // Check timing consistency
            if (current.timestamp <= prev.timestamp) {
                errors.push(`Navigation timing inconsistency at step ${i}: timestamps not in order`);
            }
        }
        
        return errors;
    }

    validateStatePreservation(scenario, calendarState, navigationResults) {
        const errors = [];
        
        // Validate that view type is preserved unless explicitly changed
        const viewChanges = navigationResults.filter(r => r.action.type === 'changeView');
        const nonViewNavigations = navigationResults.filter(r => r.action.type !== 'changeView');
        
        nonViewNavigations.forEach((result, index) => {
            if (result.beforeState.view !== result.afterState.view) {
                errors.push(`Non-view navigation ${index + 1} changed view from ${result.beforeState.view} to ${result.afterState.view}`);
            }
        });
        
        // Validate that successful navigations don't produce errors
        const successfulNavigations = navigationResults.filter(r => r.success);
        successfulNavigations.forEach((result, index) => {
            if (result.actionResult.errors && result.actionResult.errors.length > 0) {
                errors.push(`Successful navigation ${index + 1} has errors: ${result.actionResult.errors.join(', ')}`);
            }
        });
        
        return errors;
    }

    // Run a single property test iteration
    async runSingleTest(iteration) {
        try {
            // Generate test scenario
            const scenario = this.generateNavigationScenario();
            
            // Create mock calendar state
            const calendarState = this.createMockCalendarState(scenario);
            
            // Execute navigation sequence
            const navigationResults = await this.simulateNavigation(calendarState, scenario);
            
            // Validate properties
            const validation = this.validateNavigationProperties(scenario, calendarState, navigationResults);
            
            return {
                iteration: iteration,
                passed: validation.passed,
                errors: validation.errors,
                scenario: scenario,
                totalNavigations: validation.totalNavigations,
                successfulNavigations: validation.successfulNavigations,
                failedNavigations: validation.failedNavigations,
                executionTime: Date.now()
            };
            
        } catch (error) {
            return {
                iteration: iteration,
                passed: false,
                errors: [`Test execution error: ${error.message}`],
                scenario: null,
                totalNavigations: 0,
                successfulNavigations: 0,
                failedNavigations: 0,
                executionTime: Date.now()
            };
        }
    }

    // Run all property tests
    async runAllTests() {
        this.results = [];
        const startTime = Date.now();
        
        console.log(`\n🧪 Starting ${this.iterations} property test iterations...`);
        console.log('Feature: unifikasi-jadwal-jumat, Property 10: Calendar Navigation');
        console.log('Validates: Requirements 5.3\n');
        
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
        console.log('📊 CALENDAR NAVIGATION PROPERTY TEST RESULTS');
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
        console.log(`Total Navigations Tested: ${stats.totalNavigations}`);
        console.log(`Successful Navigations: ${stats.successfulNavigations}`);
        console.log(`Failed Navigations: ${stats.failedNavigations}`);
        console.log(`Average Navigations per Test: ${stats.avgNavigations}`);
        console.log(`Navigation Success Rate: ${stats.navigationSuccessRate}%`);
        console.log(`View Types Tested: ${stats.viewTypes.join(', ')}`);
        console.log(`Navigation Actions Tested: ${stats.actionTypes.join(', ')}`);
        console.log(`Coverage: ${stats.coverage}%`);
        
        console.log('\n' + '='.repeat(70));
        
        return failed === 0;
    }

    calculateStatistics() {
        const totalNavigations = this.results.reduce((sum, r) => sum + r.totalNavigations, 0);
        const successfulNavigations = this.results.reduce((sum, r) => sum + r.successfulNavigations, 0);
        const failedNavigations = this.results.reduce((sum, r) => sum + r.failedNavigations, 0);
        
        const avgNavigations = (totalNavigations / this.iterations).toFixed(2);
        const navigationSuccessRate = totalNavigations > 0 ? 
            ((successfulNavigations / totalNavigations) * 100).toFixed(2) : 0;
        
        // Collect unique view types and action types tested
        const viewTypes = new Set();
        const actionTypes = new Set();
        
        this.results.forEach(r => {
            if (r.scenario) {
                viewTypes.add(r.scenario.viewType);
                r.scenario.navigationSequence.forEach(nav => {
                    actionTypes.add(nav.type);
                });
            }
        });
        
        // Coverage estimation
        const uniqueScenarios = new Set(this.results.map(r => 
            r.scenario ? `${r.scenario.viewType}-${r.totalNavigations}` : ''
        )).size;
        const coverage = Math.min(100, (uniqueScenarios / this.iterations * 100)).toFixed(1);
        
        return {
            totalNavigations,
            successfulNavigations,
            failedNavigations,
            avgNavigations,
            navigationSuccessRate,
            viewTypes: Array.from(viewTypes),
            actionTypes: Array.from(actionTypes),
            coverage
        };
    }
}

// Run the tests
async function main() {
    const test = new CalendarNavigationPropertyTest();
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

module.exports = CalendarNavigationPropertyTest;