/**
 * Property-Based Test for Form Validation Consistency
 * 
 * Feature: unifikasi-jadwal-jumat, Property 4: Form Validation Consistency
 * Validates: Requirements 2.5
 * 
 * Property: For any input data submitted through the agenda modal, invalid data 
 * should be rejected with appropriate error messages, and valid data should be 
 * accepted and processed successfully
 */

// Mock validation functions from the modal JavaScript
function validateField(modalId, field) {
    const fieldName = field.name;
    const value = field.value.trim();
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        return { valid: false, error: 'Field ini wajib diisi' };
    }
    
    // Specific field validations
    switch (fieldName) {
        case 'friday_date':
            if (value) {
                const selectedDate = new Date(value);
                const dayOfWeek = selectedDate.getDay();
                
                if (dayOfWeek !== 5) {
                    return { valid: false, error: 'Tanggal harus hari Jumat' };
                }
                
                // Check if date is in the past
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    return { valid: false, error: 'Tanggal tidak boleh di masa lalu' };
                }
            }
            break;
            
        case 'prayer_time':
            if (value) {
                const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                if (!timePattern.test(value)) {
                    return { valid: false, error: 'Format waktu tidak valid (HH:MM)' };
                }
            }
            break;
            
        case 'imam_name':
        case 'khotib_name':
            if (value && value.length < 2) {
                return { valid: false, error: 'Nama minimal 2 karakter' };
            }
            break;
            
        case 'khutbah_theme':
            if (value && value.length < 5) {
                return { valid: false, error: 'Tema khutbah minimal 5 karakter' };
            }
            break;
    }
    
    return { valid: true, error: null };
}

// Mock form validation function
function validateForm(formData) {
    const errors = {};
    const requiredFields = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
    
    // Check for missing required fields first
    requiredFields.forEach(fieldName => {
        if (!formData[fieldName] || formData[fieldName].trim() === '') {
            errors[fieldName] = 'Field ini wajib diisi';
        }
    });
    
    // If there are missing required fields, return early
    if (Object.keys(errors).length > 0) {
        return {
            valid: false,
            errors: errors
        };
    }
    
    // Create mock field objects for validation
    const fields = {};
    Object.keys(formData).forEach(fieldName => {
        fields[fieldName] = {
            name: fieldName,
            value: formData[fieldName] || '',
            hasAttribute: (attr) => requiredFields.includes(fieldName) && attr === 'required'
        };
    });
    
    // Validate each field
    Object.keys(fields).forEach(fieldName => {
        const field = fields[fieldName];
        const result = validateField('testModal', field);
        
        if (!result.valid) {
            errors[fieldName] = result.error;
        }
    });
    
    return {
        valid: Object.keys(errors).length === 0,
        errors: errors
    };
}

// Property test generators
function generateValidFridayDate() {
    const today = new Date();
    const daysAhead = Math.floor(Math.random() * 365) + 1; // 1-365 days ahead
    const futureDate = new Date(today.getTime() + (daysAhead * 24 * 60 * 60 * 1000));
    
    // Find the next Friday from this future date
    const dayOfWeek = futureDate.getDay();
    const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
    const fridayDate = new Date(futureDate);
    fridayDate.setDate(futureDate.getDate() + daysUntilFriday);
    
    return fridayDate.toISOString().split('T')[0];
}

function generateInvalidDate() {
    const today = new Date();
    const daysAhead = Math.floor(Math.random() * 365) + 1;
    const futureDate = new Date(today.getTime() + (daysAhead * 24 * 60 * 60 * 1000));
    
    // Ensure it's not a Friday
    while (futureDate.getDay() === 5) {
        futureDate.setDate(futureDate.getDate() + 1);
    }
    
    return futureDate.toISOString().split('T')[0];
}

function generatePastDate() {
    const today = new Date();
    const daysBack = Math.floor(Math.random() * 365) + 1; // 1-365 days back
    const pastDate = new Date(today.getTime() - (daysBack * 24 * 60 * 60 * 1000));
    
    return pastDate.toISOString().split('T')[0];
}

function generateValidTime() {
    const hours = Math.floor(Math.random() * 24);
    const minutes = Math.floor(Math.random() * 60);
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
}

function generateInvalidTime() {
    const invalidTimes = [
        '25:00', // Invalid hour
        '12:60', // Invalid minute
        '1:5',   // Missing leading zeros
        'abc',   // Non-numeric
        '12',    // Missing minutes
        '12:',   // Missing minutes
        ':30',   // Missing hours
        '12:30:45' // Too many parts
    ];
    return invalidTimes[Math.floor(Math.random() * invalidTimes.length)];
}

function generateValidName() {
    const names = [
        'Ahmad Abdullah',
        'Muhammad Yusuf',
        'Abdul Rahman',
        'Umar bin Khattab',
        'Ali bin Abi Talib',
        'Imam Syafi\'i',
        'Dr. Abdullah',
        'Ustadz Ahmad'
    ];
    return names[Math.floor(Math.random() * names.length)];
}

function generateInvalidName() {
    const invalidNames = [
        '', // Empty
        ' ', // Whitespace only
        'A', // Too short
        '   ', // Multiple whitespace
        '\t', // Tab character
        '\n'  // Newline character
    ];
    return invalidNames[Math.floor(Math.random() * invalidNames.length)];
}

function generateValidTheme() {
    const themes = [
        'Pentingnya Sholat Berjamaah',
        'Akhlak Mulia dalam Islam',
        'Berbakti kepada Orang Tua',
        'Menuntut Ilmu dalam Islam',
        'Persaudaraan dalam Islam',
        'Sabar dalam Menghadapi Cobaan',
        'Syukur atas Nikmat Allah',
        'Taubat dan Ampunan Allah'
    ];
    return themes[Math.floor(Math.random() * themes.length)];
}

function generateInvalidTheme() {
    const invalidThemes = [
        '', // Empty
        '   ', // Whitespace only
        'Test', // Too short (< 5 chars)
        'A', // Way too short
        '    ', // Multiple whitespace
        '\t\t', // Tab characters
        'Hi'  // Too short
    ];
    return invalidThemes[Math.floor(Math.random() * invalidThemes.length)];
}

function generateValidFormData() {
    return {
        friday_date: generateValidFridayDate(),
        prayer_time: generateValidTime(),
        imam_name: generateValidName(),
        khotib_name: generateValidName(),
        khutbah_theme: generateValidTheme(),
        khutbah_description: 'Deskripsi khutbah yang menjelaskan isi dan tujuan khutbah.',
        location: 'Masjid Al-Muhajirin',
        special_notes: 'Catatan khusus untuk jamaah.',
        status: 'scheduled'
    };
}

function generateFormDataWithInvalidField(fieldName) {
    const formData = generateValidFormData();
    
    switch (fieldName) {
        case 'friday_date':
            formData.friday_date = Math.random() < 0.5 ? generateInvalidDate() : generatePastDate();
            break;
        case 'prayer_time':
            formData.prayer_time = generateInvalidTime();
            break;
        case 'imam_name':
            formData.imam_name = generateInvalidName();
            break;
        case 'khotib_name':
            formData.khotib_name = generateInvalidName();
            break;
        case 'khutbah_theme':
            formData.khutbah_theme = generateInvalidTheme();
            break;
    }
    
    return formData;
}

function generateFormDataWithMissingField(fieldName) {
    const formData = generateValidFormData();
    delete formData[fieldName];
    return formData;
}

// Property Tests
function runPropertyTests() {
    console.log('Running Property-Based Tests for Form Validation Consistency...');
    
    let passedTests = 0;
    let totalTests = 0;
    const failures = [];
    
    // Property 1: Valid form data should pass validation
    console.log('\n=== Property 1: Valid Form Data Passes Validation ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const formData = generateValidFormData();
        const result = validateForm(formData);
        
        // Verify: Should be valid with no errors
        if (result.valid && Object.keys(result.errors).length === 0) {
            passedTests++;
        } else {
            failures.push({
                property: 'Valid Form Data',
                input: JSON.stringify(formData, null, 2),
                expected: 'Valid form with no errors',
                actual: `Valid: ${result.valid}, Errors: ${JSON.stringify(result.errors)}`
            });
        }
    }
    
    // Property 2: Missing required fields should be rejected
    console.log('\n=== Property 2: Missing Required Fields Rejected ===');
    const requiredFields = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
    
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const fieldToRemove = requiredFields[Math.floor(Math.random() * requiredFields.length)];
        const formData = generateFormDataWithMissingField(fieldToRemove);
        const result = validateForm(formData);
        
        // Verify: Should be invalid with error for missing field
        if (!result.valid && result.errors[fieldToRemove] && result.errors[fieldToRemove].includes('wajib diisi')) {
            passedTests++;
        } else {
            failures.push({
                property: 'Missing Required Fields',
                input: `Missing field: ${fieldToRemove}`,
                expected: `Invalid form with error for ${fieldToRemove}`,
                actual: `Valid: ${result.valid}, Errors: ${JSON.stringify(result.errors)}`
            });
        }
    }
    
    // Property 3: Invalid field formats should be rejected
    console.log('\n=== Property 3: Invalid Field Formats Rejected ===');
    const fieldsToTest = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
    
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const fieldToInvalidate = fieldsToTest[Math.floor(Math.random() * fieldsToTest.length)];
        const formData = generateFormDataWithInvalidField(fieldToInvalidate);
        const result = validateForm(formData);
        
        // Verify: Should be invalid with error for the specific field
        if (!result.valid && result.errors[fieldToInvalidate]) {
            passedTests++;
        } else {
            failures.push({
                property: 'Invalid Field Formats',
                input: `Invalid field: ${fieldToInvalidate} = "${formData[fieldToInvalidate]}"`,
                expected: `Invalid form with error for ${fieldToInvalidate}`,
                actual: `Valid: ${result.valid}, Errors: ${JSON.stringify(result.errors)}`
            });
        }
    }
    
    // Property 4: Friday date validation consistency
    console.log('\n=== Property 4: Friday Date Validation Consistency ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const isValidFriday = Math.random() < 0.5;
        const formData = generateValidFormData();
        
        if (isValidFriday) {
            formData.friday_date = generateValidFridayDate();
        } else {
            formData.friday_date = generateInvalidDate();
        }
        
        const result = validateForm(formData);
        
        // Verify: Validation result should match expected validity
        const expectedValid = isValidFriday;
        const actualValid = result.valid || !result.errors.friday_date;
        
        if (expectedValid === actualValid) {
            passedTests++;
        } else {
            failures.push({
                property: 'Friday Date Validation',
                input: `Date: ${formData.friday_date}, Expected Valid: ${expectedValid}`,
                expected: `Validation should ${expectedValid ? 'pass' : 'fail'}`,
                actual: `Validation ${actualValid ? 'passed' : 'failed'}, Errors: ${JSON.stringify(result.errors)}`
            });
        }
    }
    
    // Property 5: Time format validation consistency
    console.log('\n=== Property 5: Time Format Validation Consistency ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const isValidTime = Math.random() < 0.5;
        const formData = generateValidFormData();
        
        if (isValidTime) {
            formData.prayer_time = generateValidTime();
        } else {
            formData.prayer_time = generateInvalidTime();
        }
        
        const result = validateForm(formData);
        
        // Verify: Validation result should match expected validity
        const expectedValid = isValidTime;
        const actualValid = result.valid || !result.errors.prayer_time;
        
        if (expectedValid === actualValid) {
            passedTests++;
        } else {
            failures.push({
                property: 'Time Format Validation',
                input: `Time: ${formData.prayer_time}, Expected Valid: ${expectedValid}`,
                expected: `Validation should ${expectedValid ? 'pass' : 'fail'}`,
                actual: `Validation ${actualValid ? 'passed' : 'failed'}, Errors: ${JSON.stringify(result.errors)}`
            });
        }
    }
    
    // Property 6: Name length validation consistency
    console.log('\n=== Property 6: Name Length Validation Consistency ===');
    for (let i = 0; i < 100; i++) {
        totalTests++;
        
        const nameField = Math.random() < 0.5 ? 'imam_name' : 'khotib_name';
        const isValidName = Math.random() < 0.5;
        const formData = generateValidFormData();
        
        if (isValidName) {
            formData[nameField] = generateValidName();
        } else {
            formData[nameField] = generateInvalidName();
        }
        
        const result = validateForm(formData);
        
        // Verify: Validation result should match expected validity
        const expectedValid = isValidName;
        const actualValid = result.valid || !result.errors[nameField];
        
        if (expectedValid === actualValid) {
            passedTests++;
        } else {
            failures.push({
                property: 'Name Length Validation',
                input: `${nameField}: "${formData[nameField]}", Expected Valid: ${expectedValid}`,
                expected: `Validation should ${expectedValid ? 'pass' : 'fail'}`,
                actual: `Validation ${actualValid ? 'passed' : 'failed'}, Errors: ${JSON.stringify(result.errors)}`
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
    console.log('Running Form Validation Property Tests...');
    const testResult = runPropertyTests();
    console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
    process.exit(testResult ? 0 : 1);
} else {
    // Browser environment
    const testResult = runPropertyTests();
    console.log(`\nProperty Test Result: ${testResult ? 'PASSED' : 'FAILED'}`);
}