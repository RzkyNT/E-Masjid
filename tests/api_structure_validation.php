<?php
/**
 * API Structure Validation Test
 * Validates that API files exist and have correct structure
 * Tests Requirements: 4.4, 6.2, 4.5
 */

class APIStructureValidator {
    private $test_results = [];
    private $api_path;
    
    public function __construct() {
        $this->api_path = dirname(__DIR__) . '/api/';
    }
    
    /**
     * Run all API structure validation tests
     */
    public function runAllTests() {
        echo "API Structure Validation Test Results\n";
        echo "=====================================\n\n";
        
        // Test API files exist
        $this->testAPIFilesExist();
        
        // Test API file structure
        $this->testAPIFileStructure();
        
        // Test database queries remain unchanged
        $this->testDatabaseQueries();
        
        // Display results
        $this->displayResults();
        
        return $this->test_results;
    }
    
    /**
     * Test that all required API files exist
     */
    private function testAPIFilesExist() {
        echo "Testing API Files Existence...\n";
        
        $required_files = [
            'friday_schedule_events.php',
            'friday_schedule_crud.php',
            'friday_schedule_ical.php'
        ];
        
        foreach ($required_files as $file) {
            $file_path = $this->api_path . $file;
            $exists = file_exists($file_path);
            
            $this->assertTrue(
                $exists,
                "API file exists: $file"
            );
        }
    }
    
    /**
     * Test API file structure and key functions
     */
    private function testAPIFileStructure() {
        echo "\nTesting API File Structure...\n";
        
        // Test friday_schedule_events.php structure
        $events_content = file_get_contents($this->api_path . 'friday_schedule_events.php');
        
        $this->assertTrue(
            strpos($events_content, 'header(\'Content-Type: application/json\')') !== false,
            'Events API sets JSON content type'
        );
        
        $this->assertTrue(
            strpos($events_content, 'friday_schedules') !== false,
            'Events API queries friday_schedules table'
        );
        
        $this->assertTrue(
            strpos($events_content, 'extendedProps') !== false,
            'Events API includes extendedProps for FullCalendar compatibility'
        );
        
        // Test friday_schedule_crud.php structure
        $crud_content = file_get_contents($this->api_path . 'friday_schedule_crud.php');
        
        $this->assertTrue(
            strpos($crud_content, 'case \'create\'') !== false,
            'CRUD API supports create action'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'case \'update\'') !== false,
            'CRUD API supports update action'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'case \'delete\'') !== false,
            'CRUD API supports delete action'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'case \'get_schedule\'') !== false,
            'CRUD API supports get_schedule action'
        );
        
        // Test friday_schedule_ical.php structure
        $ical_content = file_get_contents($this->api_path . 'friday_schedule_ical.php');
        
        $this->assertTrue(
            strpos($ical_content, 'text/calendar') !== false,
            'iCal API sets correct content type'
        );
        
        $this->assertTrue(
            strpos($ical_content, 'BEGIN:VCALENDAR') !== false,
            'iCal API generates valid iCalendar format'
        );
        
        $this->assertTrue(
            strpos($ical_content, 'TZID:Asia/Jakarta') !== false,
            'iCal API uses correct timezone'
        );
    }
    
    /**
     * Test that database queries remain unchanged
     */
    private function testDatabaseQueries() {
        echo "\nTesting Database Query Compatibility...\n";
        
        // Test events API query structure
        $events_content = file_get_contents($this->api_path . 'friday_schedule_events.php');
        
        $required_fields = [
            'id', 'friday_date', 'prayer_time', 'imam_name', 'khotib_name',
            'khutbah_theme', 'khutbah_description', 'location', 'special_notes', 'status'
        ];
        
        foreach ($required_fields as $field) {
            $this->assertTrue(
                strpos($events_content, $field) !== false,
                "Events API queries required field: $field"
            );
        }
        
        // Test CRUD API maintains all required operations
        $crud_content = file_get_contents($this->api_path . 'friday_schedule_crud.php');
        
        $this->assertTrue(
            strpos($crud_content, 'INSERT INTO friday_schedules') !== false,
            'CRUD API maintains INSERT operation'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'UPDATE friday_schedules') !== false,
            'CRUD API maintains UPDATE operation'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'DELETE FROM friday_schedules') !== false,
            'CRUD API maintains DELETE operation'
        );
        
        $this->assertTrue(
            strpos($crud_content, 'SELECT * FROM friday_schedules') !== false,
            'CRUD API maintains SELECT operation'
        );
        
        // Test iCal API query structure
        $ical_content = file_get_contents($this->api_path . 'friday_schedule_ical.php');
        
        $this->assertTrue(
            strpos($ical_content, 'friday_schedules') !== false,
            'iCal API queries friday_schedules table'
        );
        
        $this->assertTrue(
            strpos($ical_content, 'status != \'cancelled\'') !== false,
            'iCal API excludes cancelled events'
        );
    }
    
    /**
     * Assert true with message
     */
    private function assertTrue($condition, $message) {
        $this->addResult($message, $condition, $condition ? 'PASS' : 'FAIL');
        echo ($condition ? '✓' : '✗') . " $message\n";
    }
    
    /**
     * Add test result
     */
    private function addResult($test, $passed, $message) {
        $this->test_results[] = [
            'test' => $test,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Display test results summary
     */
    private function displayResults() {
        $total = count($this->test_results);
        $passed = array_filter($this->test_results, function($result) {
            return $result['passed'];
        });
        $passed_count = count($passed);
        
        echo "\nTest Summary\n";
        echo "============\n";
        echo "Total Tests: $total\n";
        echo "Passed: $passed_count\n";
        echo "Failed: " . ($total - $passed_count) . "\n\n";
        
        if ($passed_count === $total) {
            echo "✓ All API structure validation tests passed!\n";
        } else {
            echo "✗ Some tests failed. Please review the results above.\n";
        }
        
        // Save results to file
        file_put_contents(
            __DIR__ . '/api_structure_validation_results.json',
            json_encode($this->test_results, JSON_PRETTY_PRINT)
        );
    }
}

// Run tests if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'api_structure_validation.php') {
    try {
        $validator = new APIStructureValidator();
        $results = $validator->runAllTests();
        
    } catch (Exception $e) {
        echo "Error running validation: " . $e->getMessage() . "\n";
    }
}
?>