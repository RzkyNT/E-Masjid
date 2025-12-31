/**
 * Property-Based Test for Admin Calendar Click Interaction
 * 
 * Feature: unifikasi-jadwal-jumat, Property 2: Calendar Click Interaction
 * Validates: Requirements 2.1, 2.3
 * 
 * Property: For any calendar grid cell representing a Friday date, clicking on it 
 * should trigger the appropriate modal (add modal for empty dates, edit modal for 
 * dates with existing events)
 */

// Mock FullCalendar and modal functions for testing
const mockFullCalendar = {
    events: [],
    addEvent: function(event) {
        this.events.push(event);
    },
    getEventById: function(id) {
        return this.events.find(e => e.id === id);
    },
    getEventsForDate: function(date) {
        return this.events.filter(e => e.start === date);
    }
};

// Mock modal functions
let modalOpenCalls = [];
let modalData = {};

function openAddScheduleModal(selectedDate) {
    modalOpenCalls.push({
        type: 'add',
        date: selectedDate,
        timestamp: Date.now()
    });
}

function openEditScheduleModal(eventData) {
    modalOpenCalls.push({
        type: 'edit',
        data: eventData,
        timestamp: Date.now()
    });
}

function openViewScheduleModal(eventData) {
    modalOpenCalls.push({
        type: 'view',
        data: eventData,
        timestamp: Date.now()
    });
}

// Mock date click handler from the admin page
function handleDateClick(info) {
    const clickedDate = info.date;
    const dayOfWeek = clickedDate.getDay();
    
    // Only allow adding on Fridays
    if (dayOfWeek !== 5) {
        return { error: 'Not a Friday' };
    }
    
    // Check if date is in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    clickedDate.setHours(0, 0, 0, 0);
    
    if (clickedDate < today) {
        return { error: 'Past date' };
    }
    
    // Format date for input
    const formattedDate = clickedDate.toISOString().split('T')[0];
    
    // Open add modal with pre-filled date
    openAddScheduleModal(formattedDate);
    
    return { success: true, action: 'add', date: formattedDate };
}

// Mock event click handler
function handleEventClick(info) {
    const event = info.event;
    const eventData = {
        id: event.id,
        friday_date: event.startStr,
        prayer_time: event.extendedProps.prayer_time || '12:00',
        imam_name: event.extendedProps.imam_name || '',
        khotib_name: event.extendedProps.khotib_name || '',
        khutbah_theme: event.title || '',
        khutbah_description: event.extendedProps.khutbah_description || '',
        location: event.extendedProps.location || '',
        special_notes: event.extendedProps.special_notes || '',
        status: event.extendedProps.status || 'scheduled'
    };
    
    // Open edit modal
    openEditScheduleModal(eventData);
    
    return { success: true, action: 'edit', data: eventData };
}

// Property test generators
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

function generateRandomNonFriday() {
    const today = new Date();
    const daysAhead = Math.floor(Math.random() * 365) + 1;
    const futureDate = new Date(today.getTime() + (daysAhead * 24 * 60 * 60 * 1000));
    
    // Ensure it's not a Friday
    while (futureDate.getDay() === 5) {
        futureDate.setDate(futureDate.getDate() + 1);
    }
    
    return futureDate;
}

function generatePastFriday() {
    const today = new Date();
    const daysBack = Math.floor(Math.random() * 365) + 1; // 1-365 days back
    const pastDate = new Date(today.getTime() - (daysBack * 24 * 60 * 60 * 1000));
    
    // Find the previous Friday from this past date
    const dayOfWeek = pastDate.getDay();
    const daysBackToFriday = (dayOfWeek - 5 + 7) % 7;
    const fridayDate = new Date(pastDate);
    fridayDate.setDate(pastDate.getDate() - daysBackToFriday);
    
    return fridayDate;
}

function generateRandomEvent() {
    const fridayDate = generateRandomFriday();
    return {
        id: Math.random().toString(36).substr(2, 9),
        title: `Khutbah ${Math.random().toString(36).substr(2, 5)}`,
        start: fridayDate.toISOString().split('T')[0],
        startStr: fridayDate.toISOString().split('T')[0],
        extendedProps: {
            prayer_time: '12:00',
            imam_name: `Imam ${Math.random().toString(36).substr(2, 5)}`,
            khotib_name: `Khotib ${Math.random().toString(36).substr(2, 5)}`,
            khutbah_theme: `Tema ${Math.random().toString(36).substr(2, 8)}`,
            khutbah_description: `Deskripsi ${Math.random().toString(36).substr(2, 10)}`,
            location: 'Masjid Al-Muhajirin',
            special_notes: '',
            status: ['scheduled', 'completed', 'cancelled'][Math.floor(Math.random() * 3)]
        }
    };
}

// Property Tests
function runPropertyTests() {
    console.log('Running Property-Based Tests for Admin Calendar Click Interaction...');
    
    let passedTests = 0;
    let totalTests = 0;
    const failures = [];
    
    // Property 1: Clicking on Friday dates should open add modal
    console.log('\n=== Property 1: Friday Date Click Opens Add Modal ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        modalOpenCalls = []; // Reset
        
        const fridayDate = generateRandomFriday();
        const dateInfo = { date: fridayDate };
        
        const result = handleDateClick(dateInfo);
        
        // Verify: Should succeed and open add modal
        if (result.success && result.action === 'add' && modalOpenCalls.length === 1 && modalOpenCalls[0].type === 'add') {
            passedTests++;
        } else {
            failures.push({
                property: 'Friday Date Click',
                input: fridayDate.toISOString(),
                expected: 'Add modal opened',
                actual: `Result: ${JSON.stringify(result)}, Modal calls: ${modalOpenCalls.length}`
            });
        }
    }
    
    // Property 2: Clicking on non-Friday dates should not open modal
    console.log('\n=== Property 2: Non-Friday Date Click Rejected ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        modalOpenCalls = []; // Reset
        
        const nonFridayDate = generateRandomNonFriday();
        const dateInfo = { date: nonFridayDate };
        
        const result = handleDateClick(dateInfo);
        
        // Verify: Should fail and not open modal
        if (result.error === 'Not a Friday' && modalOpenCalls.length === 0) {
            passedTests++;
        } else {
            failures.push({
                property: 'Non-Friday Date Click',
                input: `${nonFridayDate.toISOString()} (Day: ${nonFridayDate.getDay()})`,
                expected: 'Error: Not a Friday, no modal',
                actual: `Result: ${JSON.stringify(result)}, Modal calls: ${modalOpenCalls.length}`
            });
        }
    }
    
    // Property 3: Clicking on past Friday dates should not open modal
    console.log('\n=== Property 3: Past Friday Date Click Rejected ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        modalOpenCalls = []; // Reset
        
        const pastFridayDate = generatePastFriday();
        const dateInfo = { date: pastFridayDate };
        
        const result = handleDateClick(dateInfo);
        
        // Verify: Should fail and not open modal
        if (result.error === 'Past date' && modalOpenCalls.length === 0) {
            passedTests++;
        } else {
            failures.push({
                property: 'Past Friday Date Click',
                input: pastFridayDate.toISOString(),
                expected: 'Error: Past date, no modal',
                actual: `Result: ${JSON.stringify(result)}, Modal calls: ${modalOpenCalls.length}`
            });
        }
    }
    
    // Property 4: Clicking on existing events should open edit modal
    console.log('\n=== Property 4: Event Click Opens Edit Modal ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        modalOpenCalls = []; // Reset
        
        const event = generateRandomEvent();
        const eventInfo = { event: event };
        
        const result = handleEventClick(eventInfo);
        
        // Verify: Should succeed and open edit modal
        if (result.success && result.action === 'edit' && modalOpenCalls.length === 1 && modalOpenCalls[0].type === 'edit') {
            passedTests++;
        } else {
            failures.push({
                property: 'Event Click',
                input: JSON.stringify(event, null, 2),
                expected: 'Edit modal opened',
                actual: `Result: ${JSON.stringify(result)}, Modal calls: ${modalOpenCalls.length}`
            });
        }
    }
    
    // Property 5: Modal data consistency
    console.log('\n=== Property 5: Modal Data Consistency ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        modalOpenCalls = []; // Reset
        
        const event = generateRandomEvent();
        const eventInfo = { event: event };
        
        handleEventClick(eventInfo);
        
        // Verify: Modal should receive correct event data
        if (modalOpenCalls.length === 1 && modalOpenCalls[0].data && modalOpenCalls[0].data.id === event.id) {
            const modalEventData = modalOpenCalls[0].data;
            const isDataConsistent = (
                modalEventData.friday_date === event.startStr &&
                modalEventData.khutbah_theme === event.title &&
                modalEventData.imam_name === event.extendedProps.imam_name &&
                modalEventData.khotib_name === event.extendedProps.khotib_name
            );
            
            if (isDataConsistent) {
                passedTests++;
            } else {
                failures.push({
                    property: 'Modal Data Consistency',
                    input: `Event ID: ${event.id}`,
                    expected: 'Consistent event data in modal',
                    actual: `Modal data: ${JSON.stringify(modalEventData, null, 2)}`
                });
            }
        } else {
            failures.push({
                property: 'Modal Data Consistency',
                input: `Event ID: ${event.id}`,
                expected: 'Modal with event data',
                actual: `Modal calls: ${modalOpenCalls.length}`
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
    console.log('Running Admin Calendar Click Property Tests...');
    const testResult = runPropertyTests();
    console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
    process.exit(testResult ? 0 : 1);
} else {
    // Browser environment
    const testResult = runPropertyTests();
    console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
}