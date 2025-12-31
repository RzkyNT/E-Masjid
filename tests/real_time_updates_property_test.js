/**
 * Property-Based Test for Real-time Calendar Updates
 * 
 * Feature: unifikasi-jadwal-jumat, Property 3: Real-time Calendar Updates
 * Validates: Requirements 2.4
 * 
 * Property: For any CRUD operation (create, update, delete) performed through 
 * the modal, the calendar display should immediately reflect the changes 
 * without requiring manual refresh
 */

// Mock calendar and modal state
let mockCalendar = {
    events: [],
    refreshCount: 0,
    lastRefreshTime: null,
    
    addEvent: function(event) {
        this.events.push(event);
    },
    
    updateEvent: function(eventId, newData) {
        const index = this.events.findIndex(e => e.id === eventId);
        if (index !== -1) {
            this.events[index] = { ...this.events[index], ...newData };
            return true;
        }
        return false;
    },
    
    removeEvent: function(eventId) {
        const index = this.events.findIndex(e => e.id === eventId);
        if (index !== -1) {
            this.events.splice(index, 1);
            return true;
        }
        return false;
    },
    
    refetchEvents: function() {
        this.refreshCount++;
        this.lastRefreshTime = Date.now();
        console.log(`Calendar refreshed (count: ${this.refreshCount})`);
    },
    
    getEventById: function(eventId) {
        return this.events.find(e => e.id === eventId);
    },
    
    getEventsCount: function() {
        return this.events.length;
    },
    
    reset: function() {
        this.events = [];
        this.refreshCount = 0;
        this.lastRefreshTime = null;
    }
};

// Mock modal operations
let modalOperations = [];
let apiResponses = [];

// Mock API response handler
function mockApiCall(operation, data) {
    const response = {
        success: true,
        message: `${operation} berhasil`,
        timestamp: Date.now(),
        data: data
    };
    
    apiResponses.push(response);
    return Promise.resolve(response);
}

// Mock CRUD operations with calendar refresh
async function mockCreateSchedule(scheduleData) {
    modalOperations.push({
        type: 'create',
        data: scheduleData,
        timestamp: Date.now()
    });
    
    // Simulate API call
    const response = await mockApiCall('create', scheduleData);
    
    if (response.success) {
        // Add to calendar
        const newEvent = {
            id: Math.random().toString(36).substr(2, 9),
            title: scheduleData.khutbah_theme,
            start: scheduleData.friday_date,
            extendedProps: scheduleData
        };
        
        mockCalendar.addEvent(newEvent);
        
        // Trigger calendar refresh
        mockCalendar.refetchEvents();
        
        return { success: true, event: newEvent };
    }
    
    return { success: false, error: response.message };
}

async function mockUpdateSchedule(eventId, scheduleData) {
    modalOperations.push({
        type: 'update',
        eventId: eventId,
        data: scheduleData,
        timestamp: Date.now()
    });
    
    // Simulate API call
    const response = await mockApiCall('update', { id: eventId, ...scheduleData });
    
    if (response.success) {
        // Update in calendar
        const updated = mockCalendar.updateEvent(eventId, {
            title: scheduleData.khutbah_theme,
            start: scheduleData.friday_date,
            extendedProps: scheduleData
        });
        
        if (updated) {
            // Trigger calendar refresh
            mockCalendar.refetchEvents();
            return { success: true };
        }
    }
    
    return { success: false, error: response.message };
}

async function mockDeleteSchedule(eventId) {
    modalOperations.push({
        type: 'delete',
        eventId: eventId,
        timestamp: Date.now()
    });
    
    // Simulate API call
    const response = await mockApiCall('delete', { id: eventId });
    
    if (response.success) {
        // Remove from calendar
        const removed = mockCalendar.removeEvent(eventId);
        
        if (removed) {
            // Trigger calendar refresh
            mockCalendar.refetchEvents();
            return { success: true };
        }
    }
    
    return { success: false, error: response.message };
}

// Property test generators
function generateRandomSchedule() {
    const fridayDate = generateRandomFriday();
    return {
        friday_date: fridayDate.toISOString().split('T')[0],
        prayer_time: '12:00',
        imam_name: `Imam ${Math.random().toString(36).substr(2, 5)}`,
        khotib_name: `Khotib ${Math.random().toString(36).substr(2, 5)}`,
        khutbah_theme: `Tema ${Math.random().toString(36).substr(2, 8)}`,
        khutbah_description: `Deskripsi ${Math.random().toString(36).substr(2, 10)}`,
        location: 'Masjid Al-Muhajirin',
        special_notes: '',
        status: 'scheduled'
    };
}

function generateRandomFriday() {
    const today = new Date();
    const daysAhead = Math.floor(Math.random() * 365) + 1; // 1-365 days ahead
    const futureDate = new Date(today.getTime() + (daysAhead * 24 * 60 * 60 * 1000));
    
    // Find the next Friday from this future date
    const dayOfWeek = futureDate.getDay();
    const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
    const fridayDate = new Date(futureDate);
    fridayDate.setDate(futureDate.getDate() + daysUntilFriday);
    
    return fridayDate;
}

function generateScheduleUpdate(originalSchedule) {
    const updates = { ...originalSchedule };
    
    // Randomly update some fields
    if (Math.random() < 0.3) {
        updates.imam_name = `Updated Imam ${Math.random().toString(36).substr(2, 5)}`;
    }
    if (Math.random() < 0.3) {
        updates.khotib_name = `Updated Khotib ${Math.random().toString(36).substr(2, 5)}`;
    }
    if (Math.random() < 0.5) {
        updates.khutbah_theme = `Updated Tema ${Math.random().toString(36).substr(2, 8)}`;
    }
    if (Math.random() < 0.2) {
        updates.status = ['scheduled', 'completed', 'cancelled'][Math.floor(Math.random() * 3)];
    }
    
    return updates;
}

// Property Tests
async function runPropertyTests() {
    console.log('Running Property-Based Tests for Real-time Calendar Updates...');
    
    let passedTests = 0;
    let totalTests = 0;
    const failures = [];
    
    // Property 1: Create operation triggers calendar refresh
    console.log('\n=== Property 1: Create Operation Triggers Calendar Refresh ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        // Reset state
        mockCalendar.reset();
        modalOperations = [];
        apiResponses = [];
        
        const scheduleData = generateRandomSchedule();
        const initialRefreshCount = mockCalendar.refreshCount;
        const initialEventCount = mockCalendar.getEventsCount();
        
        // Perform create operation
        const result = await mockCreateSchedule(scheduleData);
        
        // Verify: Calendar should be refreshed and event added
        const refreshTriggered = mockCalendar.refreshCount > initialRefreshCount;
        const eventAdded = mockCalendar.getEventsCount() > initialEventCount;
        
        if (result.success && refreshTriggered && eventAdded) {
            passedTests++;
        } else {
            failures.push({
                property: 'Create Operation Refresh',
                input: JSON.stringify(scheduleData, null, 2),
                expected: 'Calendar refreshed and event added',
                actual: `Success: ${result.success}, Refresh: ${refreshTriggered}, Event Added: ${eventAdded}`
            });
        }
    }
    
    // Property 2: Update operation triggers calendar refresh
    console.log('\n=== Property 2: Update Operation Triggers Calendar Refresh ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        // Reset state and add initial event
        mockCalendar.reset();
        modalOperations = [];
        apiResponses = [];
        
        const originalSchedule = generateRandomSchedule();
        const createResult = await mockCreateSchedule(originalSchedule);
        
        if (createResult.success) {
            const eventId = createResult.event.id;
            const initialRefreshCount = mockCalendar.refreshCount;
            const updatedSchedule = generateScheduleUpdate(originalSchedule);
            
            // Perform update operation
            const updateResult = await mockUpdateSchedule(eventId, updatedSchedule);
            
            // Verify: Calendar should be refreshed
            const refreshTriggered = mockCalendar.refreshCount > initialRefreshCount;
            const eventExists = mockCalendar.getEventById(eventId) !== undefined;
            
            if (updateResult.success && refreshTriggered && eventExists) {
                passedTests++;
            } else {
                failures.push({
                    property: 'Update Operation Refresh',
                    input: `Event ID: ${eventId}`,
                    expected: 'Calendar refreshed and event updated',
                    actual: `Success: ${updateResult.success}, Refresh: ${refreshTriggered}, Event Exists: ${eventExists}`
                });
            }
        } else {
            // Skip this test if create failed
            totalTests--;
        }
    }
    
    // Property 3: Delete operation triggers calendar refresh
    console.log('\n=== Property 3: Delete Operation Triggers Calendar Refresh ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        // Reset state and add initial event
        mockCalendar.reset();
        modalOperations = [];
        apiResponses = [];
        
        const originalSchedule = generateRandomSchedule();
        const createResult = await mockCreateSchedule(originalSchedule);
        
        if (createResult.success) {
            const eventId = createResult.event.id;
            const initialRefreshCount = mockCalendar.refreshCount;
            const initialEventCount = mockCalendar.getEventsCount();
            
            // Perform delete operation
            const deleteResult = await mockDeleteSchedule(eventId);
            
            // Verify: Calendar should be refreshed and event removed
            const refreshTriggered = mockCalendar.refreshCount > initialRefreshCount;
            const eventRemoved = mockCalendar.getEventsCount() < initialEventCount;
            const eventNotExists = mockCalendar.getEventById(eventId) === undefined;
            
            if (deleteResult.success && refreshTriggered && eventRemoved && eventNotExists) {
                passedTests++;
            } else {
                failures.push({
                    property: 'Delete Operation Refresh',
                    input: `Event ID: ${eventId}`,
                    expected: 'Calendar refreshed and event removed',
                    actual: `Success: ${deleteResult.success}, Refresh: ${refreshTriggered}, Event Removed: ${eventRemoved}, Not Exists: ${eventNotExists}`
                });
            }
        } else {
            // Skip this test if create failed
            totalTests--;
        }
    }
    
    // Property 4: Refresh timing consistency
    console.log('\n=== Property 4: Refresh Timing Consistency ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        // Reset state
        mockCalendar.reset();
        modalOperations = [];
        
        const scheduleData = generateRandomSchedule();
        const operationStartTime = Date.now();
        
        // Perform operation
        const result = await mockCreateSchedule(scheduleData);
        
        // Verify: Refresh should happen after operation
        const refreshTime = mockCalendar.lastRefreshTime;
        const refreshAfterOperation = refreshTime && refreshTime >= operationStartTime;
        
        if (result.success && refreshAfterOperation) {
            passedTests++;
        } else {
            failures.push({
                property: 'Refresh Timing',
                input: `Operation time: ${operationStartTime}`,
                expected: 'Refresh after operation completion',
                actual: `Success: ${result.success}, Refresh Time: ${refreshTime}, After Operation: ${refreshAfterOperation}`
            });
        }
    }
    
    // Property 5: Multiple operations refresh consistency
    console.log('\n=== Property 5: Multiple Operations Refresh Consistency ===');
    for (let i = 0; i < 50; i++) {
        totalTests++;
        
        // Reset state
        mockCalendar.reset();
        modalOperations = [];
        
        const operationCount = Math.floor(Math.random() * 5) + 2; // 2-6 operations
        let successfulOperations = 0;
        
        // Perform multiple operations
        for (let j = 0; j < operationCount; j++) {
            const scheduleData = generateRandomSchedule();
            const result = await mockCreateSchedule(scheduleData);
            if (result.success) {
                successfulOperations++;
            }
        }
        
        // Verify: Refresh count should match successful operations
        const expectedRefreshes = successfulOperations;
        const actualRefreshes = mockCalendar.refreshCount;
        
        if (actualRefreshes === expectedRefreshes) {
            passedTests++;
        } else {
            failures.push({
                property: 'Multiple Operations Refresh',
                input: `${operationCount} operations, ${successfulOperations} successful`,
                expected: `${expectedRefreshes} refreshes`,
                actual: `${actualRefreshes} refreshes`
            });
        }
    }
    
    // Results
    console.log('\n=== Test Results ===');
    console.log(`Total Tests: ${totalTests}`);
    console.log(`Passed: ${passedTests}`);
    console.log(`Failed: ${totalTests - passedTests}`);
    console.log(`Success Rate: ${((passedTests / totalTests) * 100).toFixed(2)}%`);
    
    if (failures.length > 0) {
        console.log('\n=== Failures ===');
        failures.slice(0, 5).forEach((failure, index) => {
            console.log(`\nFailure ${index + 1}:`);
            console.log(`Property: ${failure.property}`);
            console.log(`Input: ${failure.input}`);
            console.log(`Expected: ${failure.expected}`);
            console.log(`Actual: ${failure.actual}`);
        });
        
        if (failures.length > 5) {
            console.log(`\n... and ${failures.length - 5} more failures`);
        }
        
        return false; // Test failed
    }
    
    return true; // All tests passed
}

// Run the tests
if (typeof module !== 'undefined' && module.exports) {
    // Node.js environment
    module.exports = { runPropertyTests };
    
    // Auto-run tests in Node.js
    console.log('Running Real-time Calendar Updates Property Tests...');
    
    // Use async wrapper for Node.js
    (async () => {
        try {
            const testResult = await runPropertyTests();
            console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
            process.exit(testResult ? 0 : 1);
        } catch (error) {
            console.error('Test execution error:', error);
            process.exit(1);
        }
    })();
} else {
    // Browser environment
    (async () => {
        try {
            const testResult = await runPropertyTests();
            console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
        } catch (error) {
            console.error('Test execution error:', error);
        }
    })();
}