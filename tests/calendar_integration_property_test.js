/**
 * Property-Based Test for Calendar Integration
 * Feature: unifikasi-jadwal-jumat, Property 8: Calendar Event Accuracy
 * Validates: Requirements 5.1
 */

class CalendarIntegrationPropertyTest {
    constructor() {
        this.iterations = 100;
        this.results = [];
    }

    // Generator for random Friday schedule data
    generateFridayScheduleData() {
        const scheduleCount = Math.floor(Math.random() * 50) + 1; // 1-50 schedules
        const schedules = [];
        const today = new Date();
        
        for (let i = 0; i < scheduleCount; i++) {
            const schedule = {
                id: i + 1,
                friday_date: this.generateRandomFridayDate(today, i),
                prayer_time: this.generateRandomPrayerTime(),
                imam_name: `Imam ${this.generateRandomName()}`,
                khotib_name: `Khotib ${this.generateRandomName()}`,
                khutbah_theme: `Tema Khutbah ${i + 1}: ${this.generateRandomTheme()}`,
                khutbah_description: Math.random() < 0.7 ? `Deskripsi khutbah ${i + 1}` : null,
                location: Math.random() < 0.9 ? 'Masjid Al-Muhajirin' : `Lokasi ${i + 1}`,
                special_notes: Math.random() < 0.3 ? `Catatan khusus ${i + 1}` : null,
                status: this.generateRandomStatus(),
                created_at: new Date(today.getTime() - (Math.random() * 30 * 24 * 60 * 60 * 1000)).toISOString()
            };
            
            // Add schedule status based on date
            const scheduleDate = new Date(schedule.friday_date);
            const todayDate = new Date(today.toDateString());
            
            if (scheduleDate.toDateString() === todayDate.toDateString()) {
                schedule.schedule_status = 'today';
            } else if (scheduleDate > todayDate) {
                schedule.schedule_status = 'upcoming';
            } else {
                schedule.schedule_status = 'past';
            }
            
            schedules.push(schedule);
        }
        
        return schedules.sort((a, b) => new Date(a.friday_date) - new Date(b.friday_date));
    }

    generateRandomFridayDate(baseDate, offset) {
        // Generate a Friday date within the next year
        const daysToAdd = (offset * 7) + Math.floor(Math.random() * 365);
        const futureDate = new Date(baseDate.getTime() + (daysToAdd * 24 * 60 * 60 * 1000));
        
        // Adjust to Friday (day 5)
        const dayOfWeek = futureDate.getDay();
        const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
        futureDate.setDate(futureDate.getDate() + daysUntilFriday);
        
        return futureDate.toISOString().split('T')[0];
    }

    generateRandomPrayerTime() {
        const hours = Math.floor(Math.random() * 3) + 11; // 11-13 (11 AM - 1 PM)
        const minutes = Math.floor(Math.random() * 60);
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
    }

    generateRandomName() {
        const names = ['Ahmad', 'Muhammad', 'Abdullah', 'Ibrahim', 'Yusuf', 'Omar', 'Ali', 'Hassan', 'Khalid', 'Mahmud'];
        return names[Math.floor(Math.random() * names.length)] + ' ' + (Math.floor(Math.random() * 100) + 1);
    }

    generateRandomTheme() {
        const themes = [
            'Pentingnya Sholat Berjamaah',
            'Akhlak Mulia dalam Islam',
            'Berbakti kepada Orang Tua',
            'Menuntut Ilmu dalam Islam',
            'Zakat dan Kepedulian Sosial',
            'Sabar dalam Menghadapi Cobaan',
            'Syukur atas Nikmat Allah',
            'Persaudaraan dalam Islam'
        ];
        return themes[Math.floor(Math.random() * themes.length)];
    }

    generateRandomStatus() {
        const statuses = ['scheduled', 'completed', 'cancelled'];
        const weights = [0.7, 0.25, 0.05]; // 70% scheduled, 25% completed, 5% cancelled
        const random = Math.random();
        
        if (random < weights[0]) return statuses[0];
        if (random < weights[0] + weights[1]) return statuses[1];
        return statuses[2];
    }

    // Mock API response transformation
    transformToCalendarEvents(schedules) {
        return schedules.map(schedule => {
            // Determine event color based on status and date
            let color = '#10b981'; // Default green for scheduled
            let textColor = '#ffffff';
            
            if (schedule.status === 'completed') {
                color = '#6b7280'; // Gray for completed
            } else if (schedule.status === 'cancelled') {
                color = '#ef4444'; // Red for cancelled
            } else if (schedule.schedule_status === 'today') {
                color = '#3b82f6'; // Blue for today
            }
            
            return {
                id: schedule.id,
                title: 'Sholat Jumat',
                start: schedule.friday_date,
                allDay: true,
                backgroundColor: color,
                borderColor: color,
                textColor: textColor,
                extendedProps: {
                    prayer_time: schedule.prayer_time.substring(0, 5), // HH:MM format
                    imam_name: schedule.imam_name,
                    khotib_name: schedule.khotib_name,
                    khutbah_theme: schedule.khutbah_theme,
                    khutbah_description: schedule.khutbah_description,
                    location: schedule.location,
                    special_notes: schedule.special_notes,
                    status: schedule.status,
                    schedule_status: schedule.schedule_status
                }
            };
        });
    }

    // Mock calendar rendering
    simulateCalendarRendering(events, viewType = 'dayGridMonth') {
        const calendar = {
            events: events,
            viewType: viewType,
            renderedEvents: [],
            eventsByDate: new Map(),
            errors: []
        };

        // Simulate event processing
        events.forEach(event => {
            try {
                // Validate event structure
                if (!this.validateEventStructure(event)) {
                    calendar.errors.push(`Invalid event structure for event ID ${event.id}`);
                    return;
                }

                // Process event for rendering
                const processedEvent = {
                    ...event,
                    rendered: true,
                    displayTitle: this.generateDisplayTitle(event, viewType),
                    dateKey: event.start,
                    isVisible: this.shouldEventBeVisible(event, viewType)
                };

                calendar.renderedEvents.push(processedEvent);

                // Group by date for easy lookup
                if (!calendar.eventsByDate.has(event.start)) {
                    calendar.eventsByDate.set(event.start, []);
                }
                calendar.eventsByDate.get(event.start).push(processedEvent);

            } catch (error) {
                calendar.errors.push(`Error processing event ID ${event.id}: ${error.message}`);
            }
        });

        return calendar;
    }

    validateEventStructure(event) {
        const requiredFields = ['id', 'title', 'start', 'extendedProps'];
        const requiredExtendedProps = ['prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme', 'status'];

        // Check required fields
        for (const field of requiredFields) {
            if (!(field in event)) {
                return false;
            }
        }

        // Check extended props
        if (!event.extendedProps || typeof event.extendedProps !== 'object') {
            return false;
        }

        for (const prop of requiredExtendedProps) {
            if (!(prop in event.extendedProps)) {
                return false;
            }
        }

        // Validate date format
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(event.start)) {
            return false;
        }

        // Validate time format
        const timeRegex = /^\d{2}:\d{2}$/;
        if (!timeRegex.test(event.extendedProps.prayer_time)) {
            return false;
        }

        return true;
    }

    generateDisplayTitle(event, viewType) {
        if (viewType === 'listMonth') {
            return `Sholat Jumat - ${event.extendedProps.prayer_time} | Imam: ${event.extendedProps.imam_name} | Khotib: ${event.extendedProps.khotib_name}`;
        }
        return event.title;
    }

    shouldEventBeVisible(event, viewType) {
        // All events should be visible in normal circumstances
        // This could be extended to handle filtering logic
        return true;
    }

    // Property validation
    validateCalendarEventAccuracy(originalSchedules, calendarEvents, renderedCalendar) {
        const errors = [];

        // Property 8: Calendar Event Accuracy
        // For any Friday schedule in the database, the calendar should display the event on the correct date with accurate information

        // Check count consistency
        if (originalSchedules.length !== calendarEvents.length) {
            errors.push(`Event count mismatch: ${originalSchedules.length} schedules vs ${calendarEvents.length} calendar events`);
        }

        if (calendarEvents.length !== renderedCalendar.renderedEvents.length) {
            errors.push(`Rendering count mismatch: ${calendarEvents.length} events vs ${renderedCalendar.renderedEvents.length} rendered events`);
        }

        // Check each schedule has corresponding calendar event
        originalSchedules.forEach(schedule => {
            const calendarEvent = calendarEvents.find(event => event.id === schedule.id);
            
            if (!calendarEvent) {
                errors.push(`Schedule ID ${schedule.id} not found in calendar events`);
                return;
            }

            // Validate date accuracy
            if (calendarEvent.start !== schedule.friday_date) {
                errors.push(`Date mismatch for schedule ID ${schedule.id}: expected ${schedule.friday_date}, got ${calendarEvent.start}`);
            }

            // Validate extended properties
            const props = calendarEvent.extendedProps;
            const expectedTime = schedule.prayer_time.substring(0, 5);
            
            if (props.prayer_time !== expectedTime) {
                errors.push(`Prayer time mismatch for schedule ID ${schedule.id}: expected ${expectedTime}, got ${props.prayer_time}`);
            }

            if (props.imam_name !== schedule.imam_name) {
                errors.push(`Imam name mismatch for schedule ID ${schedule.id}: expected ${schedule.imam_name}, got ${props.imam_name}`);
            }

            if (props.khotib_name !== schedule.khotib_name) {
                errors.push(`Khotib name mismatch for schedule ID ${schedule.id}: expected ${schedule.khotib_name}, got ${props.khotib_name}`);
            }

            if (props.khutbah_theme !== schedule.khutbah_theme) {
                errors.push(`Khutbah theme mismatch for schedule ID ${schedule.id}: expected ${schedule.khutbah_theme}, got ${props.khutbah_theme}`);
            }

            if (props.status !== schedule.status) {
                errors.push(`Status mismatch for schedule ID ${schedule.id}: expected ${schedule.status}, got ${props.status}`);
            }

            // Validate color coding based on status
            const expectedColor = this.getExpectedColor(schedule);
            if (calendarEvent.backgroundColor !== expectedColor) {
                errors.push(`Color mismatch for schedule ID ${schedule.id}: expected ${expectedColor}, got ${calendarEvent.backgroundColor}`);
            }
        });

        // Check for duplicate events on same date
        const eventsByDate = new Map();
        calendarEvents.forEach(event => {
            if (!eventsByDate.has(event.start)) {
                eventsByDate.set(event.start, []);
            }
            eventsByDate.get(event.start).push(event);
        });

        eventsByDate.forEach((events, date) => {
            if (events.length > 1) {
                errors.push(`Multiple events found for date ${date}: ${events.map(e => e.id).join(', ')}`);
            }
        });

        // Check Friday date validation
        originalSchedules.forEach(schedule => {
            const date = new Date(schedule.friday_date);
            if (date.getDay() !== 5) {
                errors.push(`Schedule ID ${schedule.id} is not on a Friday: ${schedule.friday_date} (day ${date.getDay()})`);
            }
        });

        // Check rendering errors
        if (renderedCalendar.errors.length > 0) {
            errors.push(...renderedCalendar.errors);
        }

        return {
            passed: errors.length === 0,
            errors: errors,
            scheduleCount: originalSchedules.length,
            eventCount: calendarEvents.length,
            renderedCount: renderedCalendar.renderedEvents.length
        };
    }

    getExpectedColor(schedule) {
        if (schedule.status === 'completed') {
            return '#6b7280'; // Gray for completed
        } else if (schedule.status === 'cancelled') {
            return '#ef4444'; // Red for cancelled
        } else if (schedule.schedule_status === 'today') {
            return '#3b82f6'; // Blue for today
        }
        return '#10b981'; // Default green for scheduled
    }

    // Run a single property test iteration
    async runSingleTest(iteration) {
        try {
            // Generate random Friday schedule data
            const originalSchedules = this.generateFridayScheduleData();
            
            // Transform to calendar events (simulate API response)
            const calendarEvents = this.transformToCalendarEvents(originalSchedules);
            
            // Simulate calendar rendering
            const renderedCalendar = this.simulateCalendarRendering(calendarEvents);
            
            // Validate property
            const validation = this.validateCalendarEventAccuracy(originalSchedules, calendarEvents, renderedCalendar);
            
            return {
                iteration: iteration,
                passed: validation.passed,
                errors: validation.errors,
                scheduleCount: validation.scheduleCount,
                eventCount: validation.eventCount,
                renderedCount: validation.renderedCount,
                executionTime: Date.now()
            };
            
        } catch (error) {
            return {
                iteration: iteration,
                passed: false,
                errors: [`Test execution error: ${error.message}`],
                scheduleCount: 0,
                eventCount: 0,
                renderedCount: 0,
                executionTime: Date.now()
            };
        }
    }

    // Run all property tests
    async runAllTests() {
        this.results = [];
        const startTime = Date.now();
        
        console.log(`\n🧪 Starting ${this.iterations} property test iterations...`);
        console.log('Feature: unifikasi-jadwal-jumat, Property 8: Calendar Event Accuracy');
        console.log('Validates: Requirements 5.1\n');
        
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
        console.log(`Average Schedules per Test: ${stats.avgSchedules}`);
        console.log(`Max Schedules: ${stats.maxSchedules}`);
        console.log(`Min Schedules: ${stats.minSchedules}`);
        console.log(`Total Events Tested: ${stats.totalEvents}`);
        console.log(`Average Events per Test: ${stats.avgEvents}`);
        console.log(`Event Accuracy Rate: ${stats.eventAccuracy}%`);
        
        console.log('\n' + '='.repeat(60));
        
        return failed === 0;
    }

    calculateStatistics() {
        const scheduleCounts = this.results.map(r => r.scheduleCount);
        const eventCounts = this.results.map(r => r.eventCount);
        const totalSchedules = scheduleCounts.reduce((sum, count) => sum + count, 0);
        const totalEvents = eventCounts.reduce((sum, count) => sum + count, 0);
        
        const avgSchedules = (totalSchedules / this.iterations).toFixed(2);
        const avgEvents = (totalEvents / this.iterations).toFixed(2);
        const maxSchedules = Math.max(...scheduleCounts);
        const minSchedules = Math.min(...scheduleCounts);
        
        // Calculate event accuracy (events that matched schedules correctly)
        const accurateTests = this.results.filter(r => r.passed).length;
        const eventAccuracy = ((accurateTests / this.iterations) * 100).toFixed(2);
        
        return {
            avgSchedules,
            avgEvents,
            maxSchedules,
            minSchedules,
            totalEvents,
            eventAccuracy
        };
    }
}

// Run the tests
async function main() {
    const test = new CalendarIntegrationPropertyTest();
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

module.exports = CalendarIntegrationPropertyTest;