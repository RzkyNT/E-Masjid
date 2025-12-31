<?php
/**
 * Bimbel Management Functions
 * Core business logic for student and mentor management
 */

require_once __DIR__ . '/../config/config.php';

// ============================================================================
// STUDENT MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Generate unique student number
 * @return string Generated student number
 */
function generateStudentNumber() {
    global $pdo;
    
    try {
        // Get prefix from settings
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'student_number_prefix'");
        $stmt->execute();
        $prefix = $stmt->fetchColumn() ?: 'ALM';
        
        // Get current year
        $year = date('Y');
        
        // Find the next sequential number
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(CAST(SUBSTRING(student_number, LENGTH(?) + 1) AS UNSIGNED)), 0) + 1 as next_id 
            FROM students 
            WHERE student_number LIKE CONCAT(?, '%')
        ");
        $stmt->execute([$prefix, $prefix]);
        $nextId = $stmt->fetchColumn();
        
        return $prefix . $year . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        error_log("Error generating student number: " . $e->getMessage());
        return $prefix . $year . '001'; // Fallback
    }
}

/**
 * Add new student
 * @param array $data Student data
 * @return array Result with success status and message
 */
function addStudent($data) {
    global $pdo;
    
    try {
        // Validate required fields
        $required = ['full_name', 'level', 'class', 'parent_name', 'parent_phone', 'address', 'monthly_fee'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field $field is required"];
            }
        }
        
        // Validate level
        if (!in_array($data['level'], ['SD', 'SMP', 'SMA'])) {
            return ['success' => false, 'message' => 'Invalid level. Must be SD, SMP, or SMA'];
        }
        
        // Validate phone number format
        if (!preg_match('/^[0-9+\-\s()]+$/', $data['parent_phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format'];
        }
        
        // Validate monthly fee
        if (!is_numeric($data['monthly_fee']) || $data['monthly_fee'] <= 0) {
            return ['success' => false, 'message' => 'Monthly fee must be a positive number'];
        }
        
        // Check for duplicate student (same name and parent)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM students 
            WHERE full_name = ? AND parent_name = ? AND status != 'graduated'
        ");
        $stmt->execute([$data['full_name'], $data['parent_name']]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Student with same name and parent already exists'];
        }
        
        // Generate student number if not provided
        $studentNumber = !empty($data['student_number']) ? $data['student_number'] : generateStudentNumber();
        
        // Set registration date if not provided
        $registrationDate = !empty($data['registration_date']) ? $data['registration_date'] : date('Y-m-d');
        
        // Insert student
        $stmt = $pdo->prepare("
            INSERT INTO students (
                student_number, full_name, level, class, parent_name, parent_phone, 
                address, registration_date, monthly_fee, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $studentNumber,
            $data['full_name'],
            $data['level'],
            $data['class'],
            $data['parent_name'],
            $data['parent_phone'],
            $data['address'],
            $registrationDate,
            $data['monthly_fee']
        ]);
        
        $studentId = $pdo->lastInsertId();
        
        return [
            'success' => true, 
            'message' => 'Student added successfully',
            'student_id' => $studentId,
            'student_number' => $studentNumber
        ];
        
    } catch (PDOException $e) {
        error_log("Error adding student: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Update student information
 * @param int $id Student ID
 * @param array $data Updated student data
 * @return array Result with success status and message
 */
function updateStudent($id, $data) {
    global $pdo;
    
    try {
        // Check if student exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        // Validate level if provided
        if (isset($data['level']) && !in_array($data['level'], ['SD', 'SMP', 'SMA'])) {
            return ['success' => false, 'message' => 'Invalid level. Must be SD, SMP, or SMA'];
        }
        
        // Validate phone number if provided
        if (isset($data['parent_phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['parent_phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format'];
        }
        
        // Validate monthly fee if provided
        if (isset($data['monthly_fee']) && (!is_numeric($data['monthly_fee']) || $data['monthly_fee'] <= 0)) {
            return ['success' => false, 'message' => 'Monthly fee must be a positive number'];
        }
        
        // Validate status if provided
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'graduated'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'level', 'class', 'parent_name', 'parent_phone', 'address', 'monthly_fee', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $id; // Add ID for WHERE clause
        
        $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Student updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating student: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get student by ID
 * @param int $id Student ID
 * @return array|null Student data or null if not found
 */
function getStudentById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting student: " . $e->getMessage());
        return null;
    }
}

/**
 * Get students by level
 * @param string $level Student level (SD, SMP, SMA)
 * @param string $status Optional status filter
 * @return array List of students
 */
function getStudentsByLevel($level, $status = 'active') {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM students WHERE level = ?";
        $params = [$level];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY class, full_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting students by level: " . $e->getMessage());
        return [];
    }
}

/**
 * Get students by status
 * @param string $status Student status
 * @return array List of students
 */
function getStudentsByStatus($status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE status = ? ORDER BY level, class, full_name");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting students by status: " . $e->getMessage());
        return [];
    }
}

/**
 * Search students by keyword
 * @param string $keyword Search keyword
 * @param array $filters Optional filters (level, status, class)
 * @return array List of matching students
 */
function searchStudents($keyword, $filters = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM students WHERE 1=1";
        $params = [];
        
        // Add keyword search
        if (!empty($keyword)) {
            $sql .= " AND (full_name LIKE ? OR student_number LIKE ? OR parent_name LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add filters
        if (!empty($filters['level'])) {
            $sql .= " AND level = ?";
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['class'])) {
            $sql .= " AND class = ?";
            $params[] = $filters['class'];
        }
        
        $sql .= " ORDER BY level, class, full_name LIMIT 100"; // Limit results for performance
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error searching students: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all students with pagination
 * @param int $page Page number (1-based)
 * @param int $limit Records per page
 * @param array $filters Optional filters
 * @return array Paginated results with metadata
 */
function getAllStudents($page = 1, $limit = 20, $filters = []) {
    global $pdo;
    
    try {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['level'])) {
            $whereClause .= " AND level = ?";
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['class'])) {
            $whereClause .= " AND class = ?";
            $params[] = $filters['class'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (full_name LIKE ? OR student_number LIKE ? OR parent_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM students $whereClause";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get paginated data
        $dataSql = "SELECT * FROM students $whereClause ORDER BY level, class, full_name LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($dataSql);
        $stmt->execute($dataParams);
        $students = $stmt->fetchAll();
        
        return [
            'data' => $students,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting all students: " . $e->getMessage());
        return ['data' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total_records' => 0, 'total_pages' => 0]];
    }
}

/**
 * Delete student (soft delete by setting status to inactive)
 * @param int $id Student ID
 * @return array Result with success status and message
 */
function deleteStudent($id) {
    global $pdo;
    
    try {
        // Check if student exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        // Soft delete by setting status to inactive
        $stmt = $pdo->prepare("UPDATE students SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Student deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Error deleting student: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get student statistics
 * @return array Statistics data
 */
function getStudentStatistics() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total students by level
        $stmt = $pdo->prepare("
            SELECT level, COUNT(*) as count 
            FROM students 
            WHERE status = 'active' 
            GROUP BY level
        ");
        $stmt->execute();
        $levelStats = $stmt->fetchAll();
        
        foreach ($levelStats as $stat) {
            $stats['by_level'][$stat['level']] = $stat['count'];
        }
        
        // Total students by status
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as count 
            FROM students 
            GROUP BY status
        ");
        $stmt->execute();
        $statusStats = $stmt->fetchAll();
        
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Recent registrations (last 30 days)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM students 
            WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $stats['recent_registrations'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error getting student statistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Validate student data
 * @param array $data Student data to validate
 * @param bool $isUpdate Whether this is an update operation
 * @return array Validation result
 */
function validateStudentData($data, $isUpdate = false) {
    $errors = [];
    
    // Required fields for new students
    if (!$isUpdate) {
        $required = ['full_name', 'level', 'class', 'parent_name', 'parent_phone', 'address', 'monthly_fee'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field $field is required";
            }
        }
    }
    
    // Validate full name
    if (isset($data['full_name'])) {
        if (strlen($data['full_name']) < 2) {
            $errors[] = "Full name must be at least 2 characters";
        }
        if (strlen($data['full_name']) > 100) {
            $errors[] = "Full name must not exceed 100 characters";
        }
        if (!preg_match('/^[a-zA-Z\s.\']+$/', $data['full_name'])) {
            $errors[] = "Full name contains invalid characters";
        }
    }
    
    // Validate level
    if (isset($data['level']) && !in_array($data['level'], ['SD', 'SMP', 'SMA'])) {
        $errors[] = "Level must be SD, SMP, or SMA";
    }
    
    // Validate class
    if (isset($data['class'])) {
        if (empty($data['class']) || strlen($data['class']) > 10) {
            $errors[] = "Class must be provided and not exceed 10 characters";
        }
    }
    
    // Validate parent name
    if (isset($data['parent_name'])) {
        if (strlen($data['parent_name']) < 2) {
            $errors[] = "Parent name must be at least 2 characters";
        }
        if (strlen($data['parent_name']) > 100) {
            $errors[] = "Parent name must not exceed 100 characters";
        }
    }
    
    // Validate phone number
    if (isset($data['parent_phone'])) {
        if (!preg_match('/^[0-9+\-\s()]+$/', $data['parent_phone'])) {
            $errors[] = "Invalid phone number format";
        }
        if (strlen($data['parent_phone']) < 8 || strlen($data['parent_phone']) > 20) {
            $errors[] = "Phone number must be between 8 and 20 characters";
        }
    }
    
    // Validate address
    if (isset($data['address'])) {
        if (strlen($data['address']) < 10) {
            $errors[] = "Address must be at least 10 characters";
        }
        if (strlen($data['address']) > 500) {
            $errors[] = "Address must not exceed 500 characters";
        }
    }
    
    // Validate monthly fee
    if (isset($data['monthly_fee'])) {
        if (!is_numeric($data['monthly_fee']) || $data['monthly_fee'] <= 0) {
            $errors[] = "Monthly fee must be a positive number";
        }
        if ($data['monthly_fee'] > 10000000) { // 10 million max
            $errors[] = "Monthly fee seems too high";
        }
    }
    
    // Validate status
    if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'graduated'])) {
        $errors[] = "Invalid status";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// ============================================================================
// MENTOR MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Generate unique mentor code
 * @return string Generated mentor code
 */
function generateMentorCode() {
    global $pdo;
    
    try {
        // Get prefix from settings
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mentor_code_prefix'");
        $stmt->execute();
        $prefix = $stmt->fetchColumn() ?: 'MNT';
        
        // Find the next sequential number
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(CAST(SUBSTRING(mentor_code, LENGTH(?) + 1) AS UNSIGNED)), 0) + 1 as next_id 
            FROM mentors 
            WHERE mentor_code LIKE CONCAT(?, '%')
        ");
        $stmt->execute([$prefix, $prefix]);
        $nextId = $stmt->fetchColumn();
        
        return $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        error_log("Error generating mentor code: " . $e->getMessage());
        return $prefix . '001'; // Fallback
    }
}

/**
 * Add new mentor
 * @param array $data Mentor data
 * @return array Result with success status and message
 */
function addMentor($data) {
    global $pdo;
    
    try {
        // Validate required fields
        $required = ['full_name', 'phone', 'address', 'teaching_levels', 'hourly_rate'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field $field is required"];
            }
        }
        
        // Validate teaching levels
        if (is_string($data['teaching_levels'])) {
            $teachingLevels = json_decode($data['teaching_levels'], true);
        } else {
            $teachingLevels = $data['teaching_levels'];
        }
        
        if (!is_array($teachingLevels) || empty($teachingLevels)) {
            return ['success' => false, 'message' => 'Teaching levels must be provided as an array'];
        }
        
        $validLevels = ['SD', 'SMP', 'SMA'];
        foreach ($teachingLevels as $level) {
            if (!in_array($level, $validLevels)) {
                return ['success' => false, 'message' => 'Invalid teaching level. Must be SD, SMP, or SMA'];
            }
        }
        
        // Validate phone number format
        if (!preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format'];
        }
        
        // Validate email if provided
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Validate hourly rate
        if (!is_numeric($data['hourly_rate']) || $data['hourly_rate'] <= 0) {
            return ['success' => false, 'message' => 'Hourly rate must be a positive number'];
        }
        
        // Check for duplicate mentor (same name and phone)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM mentors 
            WHERE full_name = ? AND phone = ? AND status != 'inactive'
        ");
        $stmt->execute([$data['full_name'], $data['phone']]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Mentor with same name and phone already exists'];
        }
        
        // Generate mentor code if not provided
        $mentorCode = !empty($data['mentor_code']) ? $data['mentor_code'] : generateMentorCode();
        
        // Set join date if not provided
        $joinDate = !empty($data['join_date']) ? $data['join_date'] : date('Y-m-d');
        
        // Insert mentor
        $stmt = $pdo->prepare("
            INSERT INTO mentors (
                mentor_code, full_name, phone, email, address, 
                teaching_levels, hourly_rate, join_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $mentorCode,
            $data['full_name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'],
            json_encode($teachingLevels),
            $data['hourly_rate'],
            $joinDate
        ]);
        
        $mentorId = $pdo->lastInsertId();
        
        // Record rate history
        recordMentorRateHistory($mentorId, $data['hourly_rate'], 'Initial rate', getCurrentUserId());
        
        return [
            'success' => true, 
            'message' => 'Mentor added successfully',
            'mentor_id' => $mentorId,
            'mentor_code' => $mentorCode
        ];
        
    } catch (PDOException $e) {
        error_log("Error adding mentor: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Update mentor information
 * @param int $id Mentor ID
 * @param array $data Updated mentor data
 * @return array Result with success status and message
 */
function updateMentor($id, $data) {
    global $pdo;
    
    try {
        // Check if mentor exists
        $stmt = $pdo->prepare("SELECT * FROM mentors WHERE id = ?");
        $stmt->execute([$id]);
        $currentMentor = $stmt->fetch();
        if (!$currentMentor) {
            return ['success' => false, 'message' => 'Mentor not found'];
        }
        
        // Validate teaching levels if provided
        if (isset($data['teaching_levels'])) {
            if (is_string($data['teaching_levels'])) {
                $teachingLevels = json_decode($data['teaching_levels'], true);
            } else {
                $teachingLevels = $data['teaching_levels'];
            }
            
            if (!is_array($teachingLevels) || empty($teachingLevels)) {
                return ['success' => false, 'message' => 'Teaching levels must be provided as an array'];
            }
            
            $validLevels = ['SD', 'SMP', 'SMA'];
            foreach ($teachingLevels as $level) {
                if (!in_array($level, $validLevels)) {
                    return ['success' => false, 'message' => 'Invalid teaching level. Must be SD, SMP, or SMA'];
                }
            }
            $data['teaching_levels'] = json_encode($teachingLevels);
        }
        
        // Validate phone number if provided
        if (isset($data['phone']) && !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format'];
        }
        
        // Validate email if provided
        if (isset($data['email']) && !empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Validate hourly rate if provided
        if (isset($data['hourly_rate']) && (!is_numeric($data['hourly_rate']) || $data['hourly_rate'] <= 0)) {
            return ['success' => false, 'message' => 'Hourly rate must be a positive number'];
        }
        
        // Validate status if provided
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'phone', 'email', 'address', 'teaching_levels', 'hourly_rate', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $id; // Add ID for WHERE clause
        
        $sql = "UPDATE mentors SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Record rate history if rate changed
        if (isset($data['hourly_rate']) && $data['hourly_rate'] != $currentMentor['hourly_rate']) {
            recordMentorRateHistory($id, $data['hourly_rate'], 'Rate updated', getCurrentUserId());
        }
        
        return ['success' => true, 'message' => 'Mentor updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating mentor: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get mentor by ID
 * @param int $id Mentor ID
 * @return array|null Mentor data or null if not found
 */
function getMentorById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mentors WHERE id = ?");
        $stmt->execute([$id]);
        $mentor = $stmt->fetch();
        
        if ($mentor) {
            // Decode teaching levels JSON
            $mentor['teaching_levels'] = json_decode($mentor['teaching_levels'], true);
        }
        
        return $mentor;
    } catch (PDOException $e) {
        error_log("Error getting mentor: " . $e->getMessage());
        return null;
    }
}

/**
 * Get mentors by teaching level
 * @param string $level Teaching level (SD, SMP, SMA)
 * @param string $status Optional status filter
 * @return array List of mentors
 */
function getMentorsByLevel($level, $status = 'active') {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM mentors WHERE JSON_CONTAINS(teaching_levels, JSON_QUOTE(?))";
        $params = [$level];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY full_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $mentors = $stmt->fetchAll();
        
        // Decode teaching levels for each mentor
        foreach ($mentors as &$mentor) {
            $mentor['teaching_levels'] = json_decode($mentor['teaching_levels'], true);
        }
        
        return $mentors;
    } catch (PDOException $e) {
        error_log("Error getting mentors by level: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all mentors with pagination
 * @param int $page Page number (1-based)
 * @param int $limit Records per page
 * @param array $filters Optional filters
 * @return array Paginated results with metadata
 */
function getAllMentors($page = 1, $limit = 20, $filters = []) {
    global $pdo;
    
    try {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['teaching_level'])) {
            $whereClause .= " AND JSON_CONTAINS(teaching_levels, JSON_QUOTE(?))";
            $params[] = $filters['teaching_level'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (full_name LIKE ? OR mentor_code LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM mentors $whereClause";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get paginated data
        $dataSql = "SELECT * FROM mentors $whereClause ORDER BY full_name LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($dataSql);
        $stmt->execute($dataParams);
        $mentors = $stmt->fetchAll();
        
        // Decode teaching levels for each mentor
        foreach ($mentors as &$mentor) {
            $mentor['teaching_levels'] = json_decode($mentor['teaching_levels'], true);
        }
        
        return [
            'data' => $mentors,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting all mentors: " . $e->getMessage());
        return ['data' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total_records' => 0, 'total_pages' => 0]];
    }
}

/**
 * Search mentors by keyword
 * @param string $keyword Search keyword
 * @param array $filters Optional filters
 * @return array List of matching mentors
 */
function searchMentors($keyword, $filters = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM mentors WHERE 1=1";
        $params = [];
        
        // Add keyword search
        if (!empty($keyword)) {
            $sql .= " AND (full_name LIKE ? OR mentor_code LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add filters
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['teaching_level'])) {
            $sql .= " AND JSON_CONTAINS(teaching_levels, JSON_QUOTE(?))";
            $params[] = $filters['teaching_level'];
        }
        
        $sql .= " ORDER BY full_name LIMIT 100"; // Limit results for performance
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $mentors = $stmt->fetchAll();
        
        // Decode teaching levels for each mentor
        foreach ($mentors as &$mentor) {
            $mentor['teaching_levels'] = json_decode($mentor['teaching_levels'], true);
        }
        
        return $mentors;
    } catch (PDOException $e) {
        error_log("Error searching mentors: " . $e->getMessage());
        return [];
    }
}

/**
 * Update mentor rate with history tracking
 * @param int $mentorId Mentor ID
 * @param float $newRate New hourly rate
 * @param string $reason Reason for rate change
 * @return array Result with success status and message
 */
function updateMentorRate($mentorId, $newRate, $reason = '') {
    global $pdo;
    
    try {
        // Validate rate
        if (!is_numeric($newRate) || $newRate <= 0) {
            return ['success' => false, 'message' => 'Rate must be a positive number'];
        }
        
        // Get current mentor data
        $mentor = getMentorById($mentorId);
        if (!$mentor) {
            return ['success' => false, 'message' => 'Mentor not found'];
        }
        
        // Update mentor rate
        $stmt = $pdo->prepare("UPDATE mentors SET hourly_rate = ? WHERE id = ?");
        $stmt->execute([$newRate, $mentorId]);
        
        // Record rate history
        recordMentorRateHistory($mentorId, $newRate, $reason, getCurrentUserId());
        
        return ['success' => true, 'message' => 'Mentor rate updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating mentor rate: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Record mentor rate history
 * @param int $mentorId Mentor ID
 * @param float $rate New rate
 * @param string $reason Reason for change
 * @param int $userId User who made the change
 */
function recordMentorRateHistory($mentorId, $rate, $reason, $userId) {
    global $pdo;
    
    try {
        // Create rate history table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS mentor_rate_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                mentor_id INT NOT NULL,
                old_rate DECIMAL(10,2),
                new_rate DECIMAL(10,2) NOT NULL,
                reason TEXT,
                changed_by INT NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mentor_id) REFERENCES mentors(id),
                FOREIGN KEY (changed_by) REFERENCES users(id)
            )
        ");
        
        // Get previous rate
        $stmt = $pdo->prepare("
            SELECT new_rate FROM mentor_rate_history 
            WHERE mentor_id = ? 
            ORDER BY changed_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$mentorId]);
        $oldRate = $stmt->fetchColumn();
        
        // Insert rate history
        $stmt = $pdo->prepare("
            INSERT INTO mentor_rate_history (mentor_id, old_rate, new_rate, reason, changed_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$mentorId, $oldRate, $rate, $reason, $userId]);
        
    } catch (PDOException $e) {
        error_log("Error recording mentor rate history: " . $e->getMessage());
    }
}

/**
 * Get mentor rate history
 * @param int $mentorId Mentor ID
 * @return array Rate history
 */
function getMentorRateHistory($mentorId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT mrh.*, u.full_name as changed_by_name
            FROM mentor_rate_history mrh
            LEFT JOIN users u ON mrh.changed_by = u.id
            WHERE mrh.mentor_id = ?
            ORDER BY mrh.changed_at DESC
        ");
        $stmt->execute([$mentorId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting mentor rate history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mentor payment history
 * @param int $mentorId Mentor ID
 * @param int $months Number of months to look back
 * @return array Payment history
 */
function getMentorPaymentHistory($mentorId, $months = 6) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                YEAR(ma.attendance_date) as year,
                MONTH(ma.attendance_date) as month,
                ma.level,
                COUNT(CASE WHEN ma.status = 'present' THEN 1 END) as present_days,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught * m.hourly_rate ELSE 0 END) as total_payment
            FROM mentor_attendance ma
            JOIN mentors m ON ma.mentor_id = m.id
            WHERE ma.mentor_id = ?
            AND ma.attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY YEAR(ma.attendance_date), MONTH(ma.attendance_date), ma.level
            ORDER BY year DESC, month DESC, ma.level
        ");
        $stmt->execute([$mentorId, $months]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting mentor payment history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mentor statistics
 * @return array Statistics data
 */
function getMentorStatistics() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total mentors by status
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as count 
            FROM mentors 
            GROUP BY status
        ");
        $stmt->execute();
        $statusStats = $stmt->fetchAll();
        
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Mentors by teaching level
        $stmt = $pdo->prepare("
            SELECT 
                'SD' as level,
                COUNT(*) as count
            FROM mentors 
            WHERE JSON_CONTAINS(teaching_levels, '\"SD\"') AND status = 'active'
            UNION ALL
            SELECT 
                'SMP' as level,
                COUNT(*) as count
            FROM mentors 
            WHERE JSON_CONTAINS(teaching_levels, '\"SMP\"') AND status = 'active'
            UNION ALL
            SELECT 
                'SMA' as level,
                COUNT(*) as count
            FROM mentors 
            WHERE JSON_CONTAINS(teaching_levels, '\"SMA\"') AND status = 'active'
        ");
        $stmt->execute();
        $levelStats = $stmt->fetchAll();
        
        foreach ($levelStats as $stat) {
            $stats['by_level'][$stat['level']] = $stat['count'];
        }
        
        // Average hourly rate
        $stmt = $pdo->prepare("
            SELECT AVG(hourly_rate) as avg_rate 
            FROM mentors 
            WHERE status = 'active'
        ");
        $stmt->execute();
        $stats['average_rate'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error getting mentor statistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Validate mentor teaching level assignment
 * @param int $mentorId Mentor ID
 * @param string $level Level to validate
 * @return bool Whether mentor can teach this level
 */
function validateMentorTeachingLevel($mentorId, $level) {
    $mentor = getMentorById($mentorId);
    if (!$mentor) {
        return false;
    }
    
    return in_array($level, $mentor['teaching_levels']);
}

/**
 * Delete mentor (soft delete by setting status to inactive)
 * @param int $id Mentor ID
 * @return array Result with success status and message
 */
function deleteMentor($id) {
    global $pdo;
    
    try {
        // Check if mentor exists
        $stmt = $pdo->prepare("SELECT id FROM mentors WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Mentor not found'];
        }
        
        // Soft delete by setting status to inactive
        $stmt = $pdo->prepare("UPDATE mentors SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Mentor deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Error deleting mentor: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Get current user ID from session
 * @return int|null Current user ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format phone number for display
 * @param string $phone Phone number to format
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters except +
    $cleaned = preg_replace('/[^0-9+]/', '', $phone);
    
    // Format Indonesian phone numbers
    if (substr($cleaned, 0, 1) === '0') {
        return '+62' . substr($cleaned, 1);
    } elseif (substr($cleaned, 0, 3) === '+62') {
        return $cleaned;
    } elseif (substr($cleaned, 0, 2) === '62') {
        return '+' . $cleaned;
    }
    
    return $phone; // Return original if format not recognized
}

/**
 * Get academic year setting
 * @return string Current academic year
 */
function getCurrentAcademicYear() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'academic_year'");
        $stmt->execute();
        return $stmt->fetchColumn() ?: date('Y') . '/' . (date('Y') + 1);
    } catch (PDOException $e) {
        return date('Y') . '/' . (date('Y') + 1);
    }
}

/**
 * Get fee by level
 * @param string $level Student level
 * @return float Monthly fee for the level
 */
function getFeeByLevel($level) {
    global $pdo;
    
    try {
        $settingKey = 'fee_' . strtolower($level);
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$settingKey]);
        $fee = $stmt->fetchColumn();
        
        return $fee ? (float)$fee : 0;
    } catch (PDOException $e) {
        // Default fees if settings not found
        $defaultFees = ['SD' => 200000, 'SMP' => 300000, 'SMA' => 400000];
        return $defaultFees[$level] ?? 0;
    }
}

/**
 * Get mentor rate by level
 * @param string $level Teaching level
 * @return float Hourly rate for the level
 */
function getMentorRateByLevel($level) {
    global $pdo;
    
    try {
        $settingKey = 'mentor_rate_' . strtolower($level);
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$settingKey]);
        $rate = $stmt->fetchColumn();
        
        return $rate ? (float)$rate : 0;
    } catch (PDOException $e) {
        // Default rates if settings not found
        $defaultRates = ['SD' => 75000, 'SMP' => 100000, 'SMA' => 125000];
        return $defaultRates[$level] ?? 0;
    }
}

// ============================================================================
// ATTENDANCE MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Record student attendance for a specific date and class
 * @param string $date Attendance date (Y-m-d format)
 * @param string $level Student level (SD, SMP, SMA)
 * @param string $class Class identifier
 * @param array $attendanceData Array of student_id => status
 * @return array Result with success status and message
 */
function recordStudentAttendance($date, $level, $class, $attendanceData) {
    global $pdo;
    
    try {
        // Validate date
        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            return ['success' => false, 'message' => 'Invalid date format'];
        }
        
        // Validate level
        if (!in_array($level, ['SD', 'SMP', 'SMA'])) {
            return ['success' => false, 'message' => 'Invalid level'];
        }
        
        // Validate attendance data
        if (empty($attendanceData) || !is_array($attendanceData)) {
            return ['success' => false, 'message' => 'Attendance data is required'];
        }
        
        $validStatuses = ['present', 'absent', 'sick', 'permission'];
        $recordedCount = 0;
        $updatedCount = 0;
        $errors = [];
        
        foreach ($attendanceData as $studentId => $status) {
            // Validate status
            if (!in_array($status, $validStatuses)) {
                $errors[] = "Invalid status for student ID $studentId";
                continue;
            }
            
            // Validate student exists and belongs to the class/level
            $stmt = $pdo->prepare("
                SELECT id FROM students 
                WHERE id = ? AND level = ? AND class = ? AND status = 'active'
            ");
            $stmt->execute([$studentId, $level, $class]);
            if (!$stmt->fetch()) {
                $errors[] = "Student ID $studentId not found or not in specified class/level";
                continue;
            }
            
            // Check if attendance already exists for this date
            $stmt = $pdo->prepare("
                SELECT id FROM student_attendance 
                WHERE student_id = ? AND attendance_date = ?
            ");
            $stmt->execute([$studentId, $date]);
            $existingAttendance = $stmt->fetch();
            
            if ($existingAttendance) {
                // Update existing attendance
                $stmt = $pdo->prepare("
                    UPDATE student_attendance 
                    SET status = ?, notes = NULL 
                    WHERE student_id = ? AND attendance_date = ?
                ");
                $stmt->execute([$status, $studentId, $date]);
                $updatedCount++;
            } else {
                // Insert new attendance record
                $stmt = $pdo->prepare("
                    INSERT INTO student_attendance (
                        student_id, attendance_date, status, recorded_by
                    ) VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$studentId, $date, $status, getCurrentUserId()]);
                $recordedCount++;
            }
        }
        
        $message = "Attendance recorded: $recordedCount new, $updatedCount updated";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }
        
        return [
            'success' => true, 
            'message' => $message,
            'recorded' => $recordedCount,
            'updated' => $updatedCount,
            'errors' => $errors
        ];
        
    } catch (PDOException $e) {
        error_log("Error recording student attendance: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get student attendance for a specific date and class
 * @param string $date Attendance date (Y-m-d format)
 * @param string $level Student level (SD, SMP, SMA)
 * @param string $class Class identifier
 * @return array List of students with attendance status
 */
function getStudentAttendanceByClass($date, $level, $class) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.id, s.student_number, s.full_name, s.level, s.class,
                sa.status, sa.notes, sa.created_at as recorded_at
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ?
            WHERE s.level = ? AND s.class = ? AND s.status = 'active'
            ORDER BY s.full_name
        ");
        $stmt->execute([$date, $level, $class]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting student attendance: " . $e->getMessage());
        return [];
    }
}

/**
 * Get student attendance history
 * @param int $studentId Student ID
 * @param int $months Number of months to look back
 * @return array Attendance history
 */
function getStudentAttendanceHistory($studentId, $months = 3) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                attendance_date, status, notes,
                YEAR(attendance_date) as year,
                MONTH(attendance_date) as month
            FROM student_attendance 
            WHERE student_id = ?
            AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            ORDER BY attendance_date DESC
        ");
        $stmt->execute([$studentId, $months]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting student attendance history: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate student attendance rate
 * @param int $studentId Student ID
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Attendance statistics
 */
function calculateStudentAttendanceRate($studentId, $month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick_days,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission_days
            FROM student_attendance 
            WHERE student_id = ?
            AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?
        ");
        $stmt->execute([$studentId, $month, $year]);
        $result = $stmt->fetch();
        
        $totalDays = $result['total_days'] ?? 0;
        $presentDays = $result['present_days'] ?? 0;
        
        return [
            'student_id' => $studentId,
            'month' => $month,
            'year' => $year,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $result['absent_days'] ?? 0,
            'sick_days' => $result['sick_days'] ?? 0,
            'permission_days' => $result['permission_days'] ?? 0,
            'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0
        ];
        
    } catch (PDOException $e) {
        error_log("Error calculating attendance rate: " . $e->getMessage());
        return [];
    }
}

/**
 * Record mentor attendance for a specific date and level
 * @param string $date Attendance date (Y-m-d format)
 * @param int $mentorId Mentor ID
 * @param string $level Teaching level (SD, SMP, SMA)
 * @param string $status Attendance status
 * @param float $hoursTaught Hours taught (if present)
 * @param string $notes Optional notes
 * @return array Result with success status and message
 */
// function recordMentorAttendance($date, $mentorId, $level, $status, $hoursTaught = 0, $notes = '') {
//     global $pdo;
    
//     try {
//         // Validate date
//         if (!DateTime::createFromFormat('Y-m-d', $date)) {
//             return ['success' => false, 'message' => 'Invalid date format'];
//         }
        
//         // Validate mentor exists and can teach this level
//         if (!validateMentorTeachingLevel($mentorId, $level)) {
//             return ['success' => false, 'message' => 'Mentor cannot teach this level'];
//         }
        
//         // Validate status
//         $validStatuses = ['present', 'absent', 'sick', 'permission'];
//         if (!in_array($status, $validStatuses)) {
//             return ['success' => false, 'message' => 'Invalid attendance status'];
//         }
        
//         // Validate hours taught
//         if ($status === 'present' && (!is_numeric($hoursTaught) || $hoursTaught <= 0)) {
//             return ['success' => false, 'message' => 'Hours taught must be specified for present status'];
//         }
        
//         if ($status !== 'present') {
//             $hoursTaught = 0; // Reset hours if not present
//         }
        
//         // Check if attendance already exists
//         $stmt = $pdo->prepare("
//             SELECT id FROM mentor_attendance 
//             WHERE mentor_id = ? AND attendance_date = ? AND level = ?
//         ");
//         $stmt->execute([$mentorId, $date, $level]);
//         $existingAttendance = $stmt->fetch();
        
//         if ($existingAttendance) {
//             // Update existing attendance
//             $stmt = $pdo->prepare("
//                 UPDATE mentor_attendance 
//                 SET status = ?, hours_taught = ?, notes = ? 
//                 WHERE mentor_id = ? AND attendance_date = ? AND level = ?
//             ");
//             $stmt->execute([$status, $hoursTaught, $notes, $mentorId, $date, $level]);
//             $message = 'Mentor attendance updated successfully';
//         } else {
//             // Insert new attendance record
//             $stmt = $pdo->prepare("
//                 INSERT INTO mentor_attendance (
//                     mentor_id, attendance_date, level, status, 
//                     hours_taught, notes, recorded_by
//                 ) VALUES (?, ?, ?, ?, ?, ?, ?)
//             ");
//             $stmt->execute([
//                 $mentorId, $date, $level, $status, 
//                 $hoursTaught, $notes, getCurrentUserId()
//             ]);
//             $message = 'Mentor attendance recorded successfully';
//         }
        
//         return ['success' => true, 'message' => $message];
        
//     } catch (PDOException $e) {
//         error_log("Error recording mentor attendance: " . $e->getMessage());
//         return ['success' => false, 'message' => 'Database error occurred'];
//     }
// }

/**
 * Get mentor attendance for a specific date
 * @param string $date Attendance date (Y-m-d format)
 * @param string $level Optional level filter
 * @return array List of mentors with attendance status
 */
function getMentorAttendanceByDate($date, $level = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                m.id, m.mentor_code, m.full_name, m.teaching_levels, m.hourly_rate,
                ma.level as teaching_level, ma.status, ma.hours_taught, ma.notes,
                ma.created_at as recorded_at
            FROM mentors m
            CROSS JOIN (SELECT 'SD' as level UNION SELECT 'SMP' UNION SELECT 'SMA') levels
            LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                AND ma.attendance_date = ? AND ma.level = levels.level
            WHERE m.status = 'active' 
            AND JSON_CONTAINS(m.teaching_levels, JSON_QUOTE(levels.level))
        ";
        
        $params = [$date];
        
        if ($level) {
            $sql .= " AND levels.level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY m.full_name, levels.level";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        // Decode teaching levels for each mentor
        foreach ($results as &$result) {
            $result['teaching_levels'] = json_decode($result['teaching_levels'], true);
        }
        
        return $results;
    } catch (PDOException $e) {
        error_log("Error getting mentor attendance: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate mentor attendance rate and payment
 * @param int $mentorId Mentor ID
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Mentor attendance statistics and payment calculation
 */
function calculateMentorAttendanceRate($mentorId, $month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                ma.level,
                COUNT(*) as total_days,
                SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught * m.hourly_rate ELSE 0 END) as total_payment
            FROM mentor_attendance ma
            JOIN mentors m ON ma.mentor_id = m.id
            WHERE ma.mentor_id = ?
            AND MONTH(ma.attendance_date) = ? AND YEAR(ma.attendance_date) = ?
            GROUP BY ma.level
        ");
        $stmt->execute([$mentorId, $month, $year]);
        $results = $stmt->fetchAll();
        
        $summary = [
            'mentor_id' => $mentorId,
            'month' => $month,
            'year' => $year,
            'by_level' => [],
            'totals' => [
                'total_days' => 0,
                'present_days' => 0,
                'total_hours' => 0,
                'total_payment' => 0,
                'attendance_rate' => 0
            ]
        ];
        
        foreach ($results as $result) {
            $attendanceRate = $result['total_days'] > 0 
                ? round(($result['present_days'] / $result['total_days']) * 100, 2) 
                : 0;
                
            $summary['by_level'][$result['level']] = [
                'total_days' => $result['total_days'],
                'present_days' => $result['present_days'],
                'total_hours' => $result['total_hours'],
                'total_payment' => $result['total_payment'],
                'attendance_rate' => $attendanceRate
            ];
            
            // Add to totals
            $summary['totals']['total_days'] += $result['total_days'];
            $summary['totals']['present_days'] += $result['present_days'];
            $summary['totals']['total_hours'] += $result['total_hours'];
            $summary['totals']['total_payment'] += $result['total_payment'];
        }
        
        // Calculate overall attendance rate
        if ($summary['totals']['total_days'] > 0) {
            $summary['totals']['attendance_rate'] = round(
                ($summary['totals']['present_days'] / $summary['totals']['total_days']) * 100, 2
            );
        }
        
        return $summary;
        
    } catch (PDOException $e) {
        error_log("Error calculating mentor attendance rate: " . $e->getMessage());
        return [];
    }
}

/**
 * Get attendance statistics for dashboard
 * @return array Attendance statistics
 */
function getAttendanceStatistics() {
    global $pdo;
    
    try {
        $today = date('Y-m-d');
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $stats = [];
        
        // Today's student attendance
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT s.id) as total_students,
                COUNT(DISTINCT sa.student_id) as students_recorded,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as students_present
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ?
            WHERE s.status = 'active'
        ");
        $stmt->execute([$today]);
        $todayStudent = $stmt->fetch();
        
        $stats['today']['students'] = [
            'total' => $todayStudent['total_students'],
            'recorded' => $todayStudent['students_recorded'],
            'present' => $todayStudent['students_present'],
            'attendance_rate' => $todayStudent['students_recorded'] > 0 
                ? round(($todayStudent['students_present'] / $todayStudent['students_recorded']) * 100, 2)
                : 0
        ];
        
        // Today's mentor attendance
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as sessions_present,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours
            FROM mentors m
            CROSS JOIN (SELECT 'SD' as level UNION SELECT 'SMP' UNION SELECT 'SMA') levels
            LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                AND ma.attendance_date = ? AND ma.level = levels.level
            WHERE m.status = 'active' 
            AND JSON_CONTAINS(m.teaching_levels, JSON_QUOTE(levels.level))
        ");
        $stmt->execute([$today]);
        $todayMentor = $stmt->fetch();
        
        $stats['today']['mentors'] = [
            'total_sessions' => $todayMentor['total_sessions'],
            'sessions_present' => $todayMentor['sessions_present'],
            'total_hours' => $todayMentor['total_hours'],
            'attendance_rate' => $todayMentor['total_sessions'] > 0 
                ? round(($todayMentor['sessions_present'] / $todayMentor['total_sessions']) * 100, 2)
                : 0
        ];
        
        // Monthly student attendance rates by level
        $stmt = $pdo->prepare("
            SELECT 
                s.level,
                COUNT(DISTINCT s.id) as total_students,
                COUNT(sa.id) as total_records,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_records
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
            WHERE s.status = 'active'
            GROUP BY s.level
        ");
        $stmt->execute([$currentMonth, $currentYear]);
        $monthlyStudent = $stmt->fetchAll();
        
        foreach ($monthlyStudent as $level) {
            $attendanceRate = $level['total_records'] > 0 
                ? round(($level['present_records'] / $level['total_records']) * 100, 2)
                : 0;
                
            $stats['monthly']['students'][$level['level']] = [
                'total_students' => $level['total_students'],
                'total_records' => $level['total_records'],
                'present_records' => $level['present_records'],
                'attendance_rate' => $attendanceRate
            ];
        }
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error getting attendance statistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Get monthly attendance summary for students
 * @param int $month Month (1-12)
 * @param int $year Year
 * @param string $level Optional level filter
 * @return array Monthly attendance summary
 */
function getMonthlyStudentAttendanceSummary($month = null, $year = null, $level = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $sql = "
            SELECT 
                s.id, s.student_number, s.full_name, s.level, s.class,
                COUNT(sa.id) as total_days,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN sa.status = 'sick' THEN 1 ELSE 0 END) as sick_days,
                SUM(CASE WHEN sa.status = 'permission' THEN 1 ELSE 0 END) as permission_days,
                ROUND(
                    (SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(sa.id), 0)) * 100, 2
                ) as attendance_rate
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
            WHERE s.status = 'active'
        ";
        
        $params = [$month, $year];
        
        if ($level) {
            $sql .= " AND s.level = ?";
            $params[] = $level;
        }
        
        $sql .= " GROUP BY s.id, s.student_number, s.full_name, s.level, s.class
                  ORDER BY s.level, s.class, s.full_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting monthly student attendance summary: " . $e->getMessage());
        return [];
    }
}

/**
 * Get monthly attendance summary for mentors
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Monthly mentor attendance summary
 */
function getMonthlyMentorAttendanceSummary($month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.mentor_code, m.full_name, m.hourly_rate,
                ma.level,
                COUNT(ma.id) as total_sessions,
                SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as present_sessions,
                SUM(CASE WHEN ma.status = 'absent' THEN 1 ELSE 0 END) as absent_sessions,
                SUM(CASE WHEN ma.status = 'sick' THEN 1 ELSE 0 END) as sick_sessions,
                SUM(CASE WHEN ma.status = 'permission' THEN 1 ELSE 0 END) as permission_sessions,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught * m.hourly_rate ELSE 0 END) as total_payment,
                ROUND(
                    (SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(ma.id), 0)) * 100, 2
                ) as attendance_rate
            FROM mentors m
            LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                AND MONTH(ma.attendance_date) = ? AND YEAR(ma.attendance_date) = ?
            WHERE m.status = 'active'
            GROUP BY m.id, m.mentor_code, m.full_name, m.hourly_rate, ma.level
            HAVING COUNT(ma.id) > 0
            ORDER BY m.full_name, ma.level
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting monthly mentor attendance summary: " . $e->getMessage());
        return [];
    }
}

/**
 * Get attendance trends over multiple months
 * @param int $months Number of months to analyze
 * @param string $type Type: 'student' or 'mentor'
 * @return array Attendance trends data
 */
function getAttendanceTrends($months = 6, $type = 'student') {
    global $pdo;
    
    try {
        $trends = [];
        
        for ($i = 0; $i < $months; $i++) {
            $date = new DateTime();
            $date->sub(new DateInterval("P{$i}M"));
            $month = $date->format('n');
            $year = $date->format('Y');
            $monthName = $date->format('M Y');
            
            if ($type === 'student') {
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT s.id) as total_active,
                        COUNT(sa.id) as total_records,
                        SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_records,
                        ROUND(
                            (SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / 
                             NULLIF(COUNT(sa.id), 0)) * 100, 2
                        ) as attendance_rate
                    FROM students s
                    LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                        AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
                    WHERE s.status = 'active'
                ");
                $stmt->execute([$month, $year]);
                $result = $stmt->fetch();
                
                $trends[] = [
                    'month' => $monthName,
                    'month_num' => $month,
                    'year' => $year,
                    'total_active' => $result['total_active'],
                    'total_records' => $result['total_records'],
                    'present_records' => $result['present_records'],
                    'attendance_rate' => $result['attendance_rate'] ?? 0
                ];
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT m.id) as total_active,
                        COUNT(ma.id) as total_sessions,
                        SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as present_sessions,
                        SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours,
                        ROUND(
                            (SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) / 
                             NULLIF(COUNT(ma.id), 0)) * 100, 2
                        ) as attendance_rate
                    FROM mentors m
                    LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                        AND MONTH(ma.attendance_date) = ? AND YEAR(ma.attendance_date) = ?
                    WHERE m.status = 'active'
                ");
                $stmt->execute([$month, $year]);
                $result = $stmt->fetch();
                
                $trends[] = [
                    'month' => $monthName,
                    'month_num' => $month,
                    'year' => $year,
                    'total_active' => $result['total_active'],
                    'total_sessions' => $result['total_sessions'],
                    'present_sessions' => $result['present_sessions'],
                    'total_hours' => $result['total_hours'],
                    'attendance_rate' => $result['attendance_rate'] ?? 0
                ];
            }
        }
        
        return array_reverse($trends); // Show oldest to newest
        
    } catch (PDOException $e) {
        error_log("Error getting attendance trends: " . $e->getMessage());
        return [];
    }
}

/**
 * Get students with low attendance rates
 * @param float $threshold Attendance rate threshold (default 75%)
 * @param int $month Month to check (default current month)
 * @param int $year Year to check (default current year)
 * @return array Students with low attendance
 */
function getStudentsWithLowAttendance($threshold = 75, $month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                s.id, s.student_number, s.full_name, s.level, s.class,
                s.parent_name, s.parent_phone,
                COUNT(sa.id) as total_days,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_days,
                ROUND(
                    (SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(sa.id), 0)) * 100, 2
                ) as attendance_rate
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
            WHERE s.status = 'active'
            GROUP BY s.id, s.student_number, s.full_name, s.level, s.class, s.parent_name, s.parent_phone
            HAVING COUNT(sa.id) > 0 AND attendance_rate < ?
            ORDER BY attendance_rate ASC, s.level, s.class, s.full_name
        ");
        $stmt->execute([$month, $year, $threshold]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting students with low attendance: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mentors with low attendance rates
 * @param float $threshold Attendance rate threshold (default 80%)
 * @param int $month Month to check (default current month)
 * @param int $year Year to check (default current year)
 * @return array Mentors with low attendance
 */
function getMentorsWithLowAttendance($threshold = 80, $month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.mentor_code, m.full_name, m.phone,
                COUNT(ma.id) as total_sessions,
                SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as present_sessions,
                ROUND(
                    (SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(ma.id), 0)) * 100, 2
                ) as attendance_rate
            FROM mentors m
            LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                AND MONTH(ma.attendance_date) = ? AND YEAR(ma.attendance_date) = ?
            WHERE m.status = 'active'
            GROUP BY m.id, m.mentor_code, m.full_name, m.phone
            HAVING COUNT(ma.id) > 0 AND attendance_rate < ?
            ORDER BY attendance_rate ASC, m.full_name
        ");
        $stmt->execute([$month, $year, $threshold]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting mentors with low attendance: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate attendance report data
 * @param string $reportType Type of report: 'monthly', 'weekly', 'daily'
 * @param array $filters Filters: date, level, class, mentor_id
 * @return array Report data
 */
function generateAttendanceReport($reportType, $filters = []) {
    global $pdo;
    
    try {
        $report = [
            'type' => $reportType,
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => []
        ];
        
        switch ($reportType) {
            case 'monthly':
                $month = $filters['month'] ?? date('n');
                $year = $filters['year'] ?? date('Y');
                $level = $filters['level'] ?? null;
                
                $report['data']['students'] = getMonthlyStudentAttendanceSummary($month, $year, $level);
                $report['data']['mentors'] = getMonthlyMentorAttendanceSummary($month, $year);
                $report['data']['low_attendance_students'] = getStudentsWithLowAttendance(75, $month, $year);
                $report['data']['low_attendance_mentors'] = getMentorsWithLowAttendance(80, $month, $year);
                break;
                
            case 'weekly':
                $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
                $endDate = $filters['end_date'] ?? date('Y-m-d', strtotime('friday this week'));
                $level = $filters['level'] ?? null;
                
                // Get daily attendance for the week
                $sql = "
                    SELECT 
                        sa.attendance_date,
                        s.level,
                        COUNT(DISTINCT s.id) as total_students,
                        COUNT(sa.id) as recorded_students,
                        SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_students
                    FROM students s
                    LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                        AND sa.attendance_date BETWEEN ? AND ?
                    WHERE s.status = 'active'
                ";
                
                $params = [$startDate, $endDate];
                
                if ($level) {
                    $sql .= " AND s.level = ?";
                    $params[] = $level;
                }
                
                $sql .= " GROUP BY sa.attendance_date, s.level
                          ORDER BY sa.attendance_date, s.level";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $report['data']['daily_summary'] = $stmt->fetchAll();
                break;
                
            case 'daily':
                $date = $filters['date'] ?? date('Y-m-d');
                $level = $filters['level'] ?? null;
                $class = $filters['class'] ?? null;
                
                if ($level && $class) {
                    $report['data']['students'] = getStudentAttendanceByClass($date, $level, $class);
                }
                
                $report['data']['mentors'] = getMentorAttendanceByDate($date, $level);
                break;
        }
        
        return $report;
        
    } catch (PDOException $e) {
        error_log("Error generating attendance report: " . $e->getMessage());
        return ['error' => 'Failed to generate report'];
    }
}

/**
 * Get classes by level
 * @param string $level Student level (SD, SMP, SMA)
 * @return array List of unique classes for the level
 */
function getClassesByLevel($level) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT class 
            FROM students 
            WHERE level = ? AND status = 'active' 
            ORDER BY class
        ");
        $stmt->execute([$level]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error getting classes by level: " . $e->getMessage());
        return [];
    }
}


// ============================================================================
// SPP PAYMENT MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Record SPP payment for a student
 * @param int $studentId Student ID
 * @param int $month Payment month (1-12)
 * @param int $year Payment year
 * @param float $amount Payment amount
 * @param string $paymentMethod Payment method
 * @param string $notes Optional notes
 * @return array Result with success status and message
 */
function recordSPPPayment($studentId, $month, $year, $amount, $paymentMethod = 'cash', $notes = '') {
    global $pdo;
    
    try {
        // Validate student exists
        $student = getStudentById($studentId);
        if (!$student) {
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        // Validate month and year
        if ($month < 1 || $month > 12) {
            return ['success' => false, 'message' => 'Invalid month'];
        }
        
        if ($year < 2020 || $year > date('Y') + 1) {
            return ['success' => false, 'message' => 'Invalid year'];
        }
        
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return ['success' => false, 'message' => 'Invalid payment amount'];
        }
        
        // Check if payment already exists
        $stmt = $pdo->prepare("
            SELECT id FROM spp_payments 
            WHERE student_id = ? AND payment_month = ? AND payment_year = ?
        ");
        $stmt->execute([$studentId, $month, $year]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Payment for this month already exists'];
        }
        
        // Record SPP payment
        $stmt = $pdo->prepare("
            INSERT INTO spp_payments (
                student_id, payment_month, payment_year, amount, 
                payment_date, payment_method, notes, recorded_by
            ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?)
        ");
        
        $stmt->execute([
            $studentId, $month, $year, $amount, 
            $paymentMethod, $notes, getCurrentUserId()
        ]);
        
        $paymentId = $pdo->lastInsertId();
        
        // Record financial transaction
        $stmt = $pdo->prepare("
            INSERT INTO financial_transactions (
                transaction_type, category, amount, description, 
                transaction_date, reference_id, recorded_by
            ) VALUES (
                'income', 'spp', ?, 
                CONCAT('Pembayaran SPP ', ?, ' - ', ?, ' ', ?),
                CURDATE(), ?, ?
            )
        ");
        
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $stmt->execute([
            $amount, $student['full_name'], $monthNames[$month], $year,
            $studentId, getCurrentUserId()
        ]);
        
        return [
            'success' => true, 
            'message' => 'SPP payment recorded successfully',
            'payment_id' => $paymentId
        ];
        
    } catch (PDOException $e) {
        error_log("Error recording SPP payment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get SPP payment status for a student
 * @param int $studentId Student ID
 * @param int $month Optional month filter
 * @param int $year Optional year filter
 * @return array Payment status information
 */
function getStudentSPPStatus($studentId, $month = null, $year = null) {
    global $pdo;
    
    try {
        $currentMonth = $month ?? date('n');
        $currentYear = $year ?? date('Y');
        
        // Get payment for specific month/year
        $stmt = $pdo->prepare("
            SELECT * FROM spp_payments 
            WHERE student_id = ? AND payment_month = ? AND payment_year = ?
        ");
        $stmt->execute([$studentId, $currentMonth, $currentYear]);
        $payment = $stmt->fetch();
        
        // Get student info
        $student = getStudentById($studentId);
        
        $status = [
            'student_id' => $studentId,
            'student_name' => $student['full_name'] ?? '',
            'month' => $currentMonth,
            'year' => $currentYear,
            'expected_amount' => $student['monthly_fee'] ?? 0,
            'paid' => !empty($payment),
            'payment_date' => $payment['payment_date'] ?? null,
            'paid_amount' => $payment['amount'] ?? 0,
            'payment_method' => $payment['payment_method'] ?? null,
            'notes' => $payment['notes'] ?? '',
            'status' => !empty($payment) ? 'paid' : 'unpaid'
        ];
        
        return $status;
        
    } catch (PDOException $e) {
        error_log("Error getting SPP status: " . $e->getMessage());
        return [];
    }
}

/**
 * Get outstanding SPP payments
 * @param string $level Optional level filter
 * @return array List of students with outstanding payments
 */
function getOutstandingSPPPayments($level = null) {
    global $pdo;
    
    try {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $sql = "
            SELECT 
                s.id, s.student_number, s.full_name, s.level, s.class,
                s.parent_name, s.parent_phone, s.monthly_fee,
                sp.payment_date, sp.amount as paid_amount
            FROM students s
            LEFT JOIN spp_payments sp ON s.id = sp.student_id 
                AND sp.payment_month = ? AND sp.payment_year = ?
            WHERE s.status = 'active' AND sp.id IS NULL
        ";
        
        $params = [$currentMonth, $currentYear];
        
        if ($level) {
            $sql .= " AND s.level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY s.level, s.class, s.full_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting outstanding payments: " . $e->getMessage());
        return [];
    }
}

/**
 * Get SPP payment history for a student
 * @param int $studentId Student ID
 * @param int $months Number of months to look back
 * @return array Payment history
 */
function getStudentSPPHistory($studentId, $months = 12) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                payment_month, payment_year, amount, payment_date,
                payment_method, notes
            FROM spp_payments 
            WHERE student_id = ?
            AND payment_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            ORDER BY payment_year DESC, payment_month DESC
        ");
        $stmt->execute([$studentId, $months]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting SPP history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get SPP statistics
 * @return array SPP statistics
 */
function getSPPStatistics() {
    global $pdo;
    
    try {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $stats = [];
        
        // Total students
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE status = 'active'");
        $stmt->execute();
        $stats['total_students'] = $stmt->fetchColumn();
        
        // Students who paid this month
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT sp.student_id) 
            FROM spp_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE sp.payment_month = ? AND sp.payment_year = ?
            AND s.status = 'active'
        ");
        $stmt->execute([$currentMonth, $currentYear]);
        $stats['paid_students'] = $stmt->fetchColumn();
        
        // Outstanding students
        $stats['outstanding_students'] = $stats['total_students'] - $stats['paid_students'];
        
        // Total SPP income this month
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(sp.amount), 0)
            FROM spp_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE sp.payment_month = ? AND sp.payment_year = ?
            AND s.status = 'active'
        ");
        $stmt->execute([$currentMonth, $currentYear]);
        $stats['total_income'] = $stmt->fetchColumn();
        
        // Expected SPP income this month
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(s.monthly_fee), 0)
            FROM students s
            WHERE s.status = 'active'
        ");
        $stmt->execute();
        $stats['expected_income'] = $stmt->fetchColumn();
        
        // Outstanding amount
        $stats['outstanding_amount'] = $stats['expected_income'] - $stats['total_income'];
        
        // Payment rate
        $stats['payment_rate'] = $stats['total_students'] > 0 
            ? round(($stats['paid_students'] / $stats['total_students']) * 100, 2)
            : 0;
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error getting SPP statistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Update SPP payment
 * @param int $paymentId Payment ID
 * @param array $data Updated payment data
 * @return array Result with success status and message
 */
function updateSPPPayment($paymentId, $data) {
    global $pdo;
    
    try {
        // Check if payment exists
        $stmt = $pdo->prepare("SELECT * FROM spp_payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }
        
        // Validate amount if provided
        if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
            return ['success' => false, 'message' => 'Invalid payment amount'];
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['amount', 'payment_date', 'payment_method', 'notes'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $paymentId; // Add ID for WHERE clause
        
        $sql = "UPDATE spp_payments SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Payment updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating SPP payment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Delete SPP payment
 * @param int $paymentId Payment ID
 * @return array Result with success status and message
 */
function deleteSPPPayment($paymentId) {
    global $pdo;
    
    try {
        // Check if payment exists
        $stmt = $pdo->prepare("SELECT * FROM spp_payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }
        
        // Delete the payment
        $stmt = $pdo->prepare("DELETE FROM spp_payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        
        // Also delete related financial transaction
        $stmt = $pdo->prepare("
            DELETE FROM financial_transactions 
            WHERE category = 'spp' AND reference_id = ? 
            AND transaction_type = 'income'
        ");
        $stmt->execute([$payment['student_id']]);
        
        return ['success' => true, 'message' => 'Payment deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Error deleting SPP payment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get SPP payment summary by level
 * @param string $level Student level (SD, SMP, SMA)
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Payment summary for the level
 */
function getSPPSummaryByLevel($level, $month = null, $year = null) {
    global $pdo;
    
    try {
        $currentMonth = $month ?? date('n');
        $currentYear = $year ?? date('Y');
        
        // Get total students for this level
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_students
            FROM students 
            WHERE level = ? AND status = 'active'
        ");
        $stmt->execute([$level]);
        $total_students = $stmt->fetchColumn();
        
        // Get students who paid for this month
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT sp.student_id) as paid_students,
                   COALESCE(SUM(sp.amount), 0) as total_income
            FROM spp_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE s.level = ? AND s.status = 'active'
            AND sp.payment_month = ? AND sp.payment_year = ?
        ");
        $stmt->execute([$level, $currentMonth, $currentYear]);
        $payment_data = $stmt->fetch();
        
        // Get expected income for this level
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monthly_fee), 0) as expected_income
            FROM students 
            WHERE level = ? AND status = 'active'
        ");
        $stmt->execute([$level]);
        $expected_income = $stmt->fetchColumn();
        
        $paid_students = $payment_data['paid_students'] ?? 0;
        $outstanding_students = $total_students - $paid_students;
        $payment_rate = $total_students > 0 ? ($paid_students / $total_students) * 100 : 0;
        
        return [
            'level' => $level,
            'total_students' => $total_students,
            'paid_students' => $paid_students,
            'outstanding_students' => $outstanding_students,
            'payment_rate' => $payment_rate,
            'total_income' => $payment_data['total_income'] ?? 0,
            'expected_income' => $expected_income,
            'outstanding_amount' => $expected_income - ($payment_data['total_income'] ?? 0)
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting SPP summary by level: " . $e->getMessage());
        return [
            'level' => $level,
            'total_students' => 0,
            'paid_students' => 0,
            'outstanding_students' => 0,
            'payment_rate' => 0,
            'total_income' => 0,
            'expected_income' => 0,
            'outstanding_amount' => 0
        ];
    }
}

/**
 * Get overdue SPP payments (students who haven't paid for multiple months)
 * @param int $months_overdue Number of months overdue to check
 * @return array List of students with overdue payments
 */
function getOverdueSPPPayments($months_overdue = 2) {
    global $pdo;
    
    try {
        $current_date = new DateTime();
        $overdue_students = [];
        
        // Check each active student
        $stmt = $pdo->prepare("
            SELECT id, student_number, full_name, level, class, parent_name, 
                   parent_phone, monthly_fee, registration_date
            FROM students 
            WHERE status = 'active'
            ORDER BY level, class, full_name
        ");
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        foreach ($students as $student) {
            $overdue_months = [];
            $total_overdue_amount = 0;
            
            // Check last N months
            for ($i = 0; $i < $months_overdue; $i++) {
                $check_date = clone $current_date;
                $check_date->modify("-$i months");
                $check_month = $check_date->format('n');
                $check_year = $check_date->format('Y');
                
                // Skip months before registration
                $registration_date = new DateTime($student['registration_date']);
                if ($check_date < $registration_date) {
                    continue;
                }
                
                // Check if payment exists for this month
                $payment_stmt = $pdo->prepare("
                    SELECT id FROM spp_payments 
                    WHERE student_id = ? AND payment_month = ? AND payment_year = ?
                ");
                $payment_stmt->execute([$student['id'], $check_month, $check_year]);
                
                if (!$payment_stmt->fetch()) {
                    $overdue_months[] = [
                        'month' => $check_month,
                        'year' => $check_year,
                        'amount' => $student['monthly_fee']
                    ];
                    $total_overdue_amount += $student['monthly_fee'];
                }
            }
            
            if (count($overdue_months) >= $months_overdue) {
                $overdue_students[] = array_merge($student, [
                    'overdue_months' => $overdue_months,
                    'overdue_count' => count($overdue_months),
                    'total_overdue_amount' => $total_overdue_amount
                ]);
            }
        }
        
        return $overdue_students;
        
    } catch (PDOException $e) {
        error_log("Error getting overdue SPP payments: " . $e->getMessage());
        return [];
    }
}

/**
 * Get SPP payment trends (monthly comparison)
 * @param int $months Number of months to analyze
 * @return array Payment trends data
 */
function getSPPPaymentTrends($months = 6) {
    global $pdo;
    
    try {
        $trends = [];
        $current_date = new DateTime();
        
        for ($i = 0; $i < $months; $i++) {
            $check_date = clone $current_date;
            $check_date->modify("-$i months");
            $month = $check_date->format('n');
            $year = $check_date->format('Y');
            
            // Get payment statistics for this month
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(DISTINCT sp.student_id) as paid_students,
                    COALESCE(SUM(sp.amount), 0) as total_income,
                    COUNT(DISTINCT s.id) as total_active_students
                FROM students s
                LEFT JOIN spp_payments sp ON s.id = sp.student_id 
                    AND sp.payment_month = ? AND sp.payment_year = ?
                WHERE s.status = 'active'
                AND s.registration_date <= ?
            ");
            $stmt->execute([$month, $year, $check_date->format('Y-m-t')]);
            $data = $stmt->fetch();
            
            $paid_students = $data['paid_students'] ?? 0;
            $total_students = $data['total_active_students'] ?? 0;
            $payment_rate = $total_students > 0 ? ($paid_students / $total_students) * 100 : 0;
            
            $trends[] = [
                'month' => $month,
                'year' => $year,
                'month_name' => $check_date->format('M Y'),
                'paid_students' => $paid_students,
                'total_students' => $total_students,
                'outstanding_students' => $total_students - $paid_students,
                'payment_rate' => $payment_rate,
                'total_income' => $data['total_income'] ?? 0
            ];
        }
        
        return array_reverse($trends); // Return chronological order
        
    } catch (PDOException $e) {
        error_log("Error getting SPP payment trends: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate SPP payment reminder data
 * @return array Students who need payment reminders
 */
function getSPPPaymentReminders() {
    global $pdo;
    
    try {
        $current_month = date('n');
        $current_year = date('Y');
        $current_day = date('j');
        
        $reminders = [];
        
        // Get students who haven't paid this month (after 10th of the month)
        if ($current_day >= 10) {
            $outstanding = getOutstandingSPPPayments();
            
            foreach ($outstanding as $student) {
                $reminders[] = [
                    'type' => 'current_month_overdue',
                    'priority' => 'high',
                    'student' => $student,
                    'message' => "Belum membayar SPP bulan " . date('F Y'),
                    'days_overdue' => $current_day - 10
                ];
            }
        }
        
        // Get students with multiple months overdue
        $overdue_students = getOverdueSPPPayments(2);
        
        foreach ($overdue_students as $student) {
            $reminders[] = [
                'type' => 'multiple_months_overdue',
                'priority' => 'critical',
                'student' => $student,
                'message' => "Tunggakan {$student['overdue_count']} bulan - Rp " . number_format($student['total_overdue_amount'], 0, ',', '.'),
                'overdue_months' => $student['overdue_count']
            ];
        }
        
        return $reminders;
        
    } catch (PDOException $e) {
        error_log("Error getting SPP payment reminders: " . $e->getMessage());
        return [];
    }
}

/**
 * Bulk record SPP payments for multiple students
 * @param array $student_ids Array of student IDs
 * @param int $month Payment month
 * @param int $year Payment year
 * @param string $payment_method Payment method
 * @param string $notes Optional notes
 * @return array Result with success count and errors
 */
function bulkRecordSPPPayments($student_ids, $month, $year, $payment_method = 'cash', $notes = '') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        foreach ($student_ids as $student_id) {
            $student = getStudentById($student_id);
            if (!$student) {
                $error_count++;
                $errors[] = "Student ID $student_id not found";
                continue;
            }
            
            $result = recordSPPPayment(
                $student_id,
                $month,
                $year,
                $student['monthly_fee'],
                $payment_method,
                $notes ?: 'Bulk payment processing'
            );
            
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Student {$student['full_name']}: {$result['message']}";
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors,
            'message' => "Bulk payment completed: $success_count successful, $error_count failed"
        ];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error in bulk SPP payment: " . $e->getMessage());
        return [
            'success' => false,
            'success_count' => 0,
            'error_count' => count($student_ids),
            'errors' => ['Database error occurred'],
            'message' => 'Bulk payment failed due to database error'
        ];
    }
}
// ============================================================================
// FINANCIAL MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Record financial transaction
 * @param string $type Transaction type (income/expense)
 * @param string $category Transaction category
 * @param float $amount Transaction amount
 * @param string $description Transaction description
 * @param string $date Transaction date (Y-m-d format)
 * @param int $referenceId Optional reference ID (student_id, mentor_id, etc)
 * @param string $notes Optional notes
 * @return array Result with success status and message
 */
function recordFinancialTransaction($type, $category, $amount, $description, $date = null, $referenceId = null, $notes = '') {
    global $pdo;
    
    try {
        // Validate transaction type
        if (!in_array($type, ['income', 'expense'])) {
            return ['success' => false, 'message' => 'Invalid transaction type. Must be income or expense'];
        }
        
        // Validate category
        $validCategories = ['spp', 'registration', 'operational', 'mentor_payment', 'utilities', 'other'];
        if (!in_array($category, $validCategories)) {
            return ['success' => false, 'message' => 'Invalid category'];
        }
        
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be a positive number'];
        }
        
        // Validate description
        if (empty($description)) {
            return ['success' => false, 'message' => 'Description is required'];
        }
        
        // Set date if not provided
        $transactionDate = $date ?: date('Y-m-d');
        
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $transactionDate)) {
            return ['success' => false, 'message' => 'Invalid date format'];
        }
        
        // Insert transaction
        $stmt = $pdo->prepare("
            INSERT INTO financial_transactions (
                transaction_type, category, amount, description, 
                transaction_date, reference_id, notes, recorded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $type,
            $category,
            $amount,
            $description,
            $transactionDate,
            $referenceId,
            $notes,
            getCurrentUserId()
        ]);
        
        $transactionId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'message' => 'Financial transaction recorded successfully',
            'transaction_id' => $transactionId
        ];
        
    } catch (PDOException $e) {
        error_log("Error recording financial transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Update financial transaction
 * @param int $transactionId Transaction ID
 * @param array $data Updated transaction data
 * @return array Result with success status and message
 */
function updateFinancialTransaction($transactionId, $data) {
    global $pdo;
    
    try {
        // Check if transaction exists
        $stmt = $pdo->prepare("SELECT id FROM financial_transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }
        
        // Validate transaction type if provided
        if (isset($data['transaction_type']) && !in_array($data['transaction_type'], ['income', 'expense'])) {
            return ['success' => false, 'message' => 'Invalid transaction type'];
        }
        
        // Validate category if provided
        if (isset($data['category'])) {
            $validCategories = ['spp', 'registration', 'operational', 'mentor_payment', 'utilities', 'other'];
            if (!in_array($data['category'], $validCategories)) {
                return ['success' => false, 'message' => 'Invalid category'];
            }
        }
        
        // Validate amount if provided
        if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
            return ['success' => false, 'message' => 'Amount must be a positive number'];
        }
        
        // Validate date if provided
        if (isset($data['transaction_date']) && !DateTime::createFromFormat('Y-m-d', $data['transaction_date'])) {
            return ['success' => false, 'message' => 'Invalid date format'];
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['transaction_type', 'category', 'amount', 'description', 'transaction_date', 'reference_id', 'notes'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $transactionId; // Add ID for WHERE clause
        
        $sql = "UPDATE financial_transactions SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Transaction updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating financial transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Delete financial transaction
 * @param int $transactionId Transaction ID
 * @return array Result with success status and message
 */
function deleteFinancialTransaction($transactionId) {
    global $pdo;
    
    try {
        // Check if transaction exists
        $stmt = $pdo->prepare("SELECT id FROM financial_transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }
        
        // Delete transaction
        $stmt = $pdo->prepare("DELETE FROM financial_transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        
        return ['success' => true, 'message' => 'Transaction deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Error deleting financial transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get financial transaction by ID
 * @param int $transactionId Transaction ID
 * @return array|null Transaction data or null if not found
 */
function getFinancialTransactionById($transactionId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT ft.*, u.full_name as recorded_by_name
            FROM financial_transactions ft
            LEFT JOIN users u ON ft.recorded_by = u.id
            WHERE ft.id = ?
        ");
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting financial transaction: " . $e->getMessage());
        return null;
    }
}

/**
 * Get financial transactions with filters and pagination
 * @param int $page Page number (1-based)
 * @param int $limit Records per page
 * @param array $filters Optional filters
 * @return array Paginated results with metadata
 */
function getFinancialTransactions($page = 1, $limit = 20, $filters = []) {
    global $pdo;
    
    try {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['type'])) {
            $whereClause .= " AND transaction_type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category'])) {
            $whereClause .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND transaction_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $whereClause .= " AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?";
            $params[] = $filters['month'];
            $params[] = $filters['year'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (description LIKE ? OR notes LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM financial_transactions $whereClause";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get paginated data
        $dataSql = "
            SELECT ft.*, u.full_name as recorded_by_name
            FROM financial_transactions ft
            LEFT JOIN users u ON ft.recorded_by = u.id
            $whereClause 
            ORDER BY ft.transaction_date DESC, ft.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($dataSql);
        $stmt->execute($dataParams);
        $transactions = $stmt->fetchAll();
        
        return [
            'data' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting financial transactions: " . $e->getMessage());
        return ['data' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total_records' => 0, 'total_pages' => 0]];
    }
}

/**
 * Get financial summary for a specific period
 * @param int $month Month (1-12)
 * @param int $year Year
 * @return array Financial summary
 */
function getFinancialSummary($month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                transaction_type,
                category,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            FROM financial_transactions 
            WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
            GROUP BY transaction_type, category
            ORDER BY transaction_type, category
        ");
        $stmt->execute([$month, $year]);
        $results = $stmt->fetchAll();
        
        $summary = [
            'month' => $month,
            'year' => $year,
            'income' => [
                'total' => 0,
                'by_category' => []
            ],
            'expense' => [
                'total' => 0,
                'by_category' => []
            ],
            'net_balance' => 0
        ];
        
        foreach ($results as $result) {
            $type = $result['transaction_type'];
            $category = $result['category'];
            $amount = $result['total_amount'];
            
            $summary[$type]['by_category'][$category] = [
                'amount' => $amount,
                'count' => $result['transaction_count']
            ];
            $summary[$type]['total'] += $amount;
        }
        
        $summary['net_balance'] = $summary['income']['total'] - $summary['expense']['total'];
        
        return $summary;
        
    } catch (PDOException $e) {
        error_log("Error getting financial summary: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate real-time balance
 * @param string $upToDate Calculate balance up to this date (Y-m-d format)
 * @return array Balance information
 */
function calculateRealTimeBalance($upToDate = null) {
    global $pdo;
    
    try {
        $upToDate = $upToDate ?: date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense,
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as current_balance
            FROM financial_transactions 
            WHERE transaction_date <= ?
        ");
        $stmt->execute([$upToDate]);
        $result = $stmt->fetch();
        
        return [
            'total_income' => $result['total_income'] ?? 0,
            'total_expense' => $result['total_expense'] ?? 0,
            'current_balance' => $result['current_balance'] ?? 0,
            'calculated_date' => $upToDate
        ];
        
    } catch (PDOException $e) {
        error_log("Error calculating real-time balance: " . $e->getMessage());
        return [
            'total_income' => 0,
            'total_expense' => 0,
            'current_balance' => 0,
            'calculated_date' => $upToDate
        ];
    }
}

/**
 * Get financial transactions by category
 * @param string $category Transaction category
 * @param int $month Optional month filter
 * @param int $year Optional year filter
 * @return array List of transactions
 */
function getTransactionsByCategory($category, $month = null, $year = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT ft.*, u.full_name as recorded_by_name
            FROM financial_transactions ft
            LEFT JOIN users u ON ft.recorded_by = u.id
            WHERE ft.category = ?
        ";
        $params = [$category];
        
        if ($month && $year) {
            $sql .= " AND MONTH(ft.transaction_date) = ? AND YEAR(ft.transaction_date) = ?";
            $params[] = $month;
            $params[] = $year;
        }
        
        $sql .= " ORDER BY ft.transaction_date DESC, ft.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting transactions by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Validate financial transaction data
 * @param array $data Transaction data to validate
 * @param bool $isUpdate Whether this is an update operation
 * @return array Validation result
 */
function validateFinancialTransactionData($data, $isUpdate = false) {
    $errors = [];
    
    // Required fields for new transactions
    if (!$isUpdate) {
        $required = ['transaction_type', 'category', 'amount', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field $field is required";
            }
        }
    }
    
    // Validate transaction type
    if (isset($data['transaction_type']) && !in_array($data['transaction_type'], ['income', 'expense'])) {
        $errors[] = "Transaction type must be 'income' or 'expense'";
    }
    
    // Validate category
    if (isset($data['category'])) {
        $validCategories = ['spp', 'registration', 'operational', 'mentor_payment', 'utilities', 'other'];
        if (!in_array($data['category'], $validCategories)) {
            $errors[] = "Invalid category";
        }
    }
    
    // Validate amount
    if (isset($data['amount'])) {
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = "Amount must be a positive number";
        }
        if ($data['amount'] > 100000000) { // 100 million max
            $errors[] = "Amount seems too high";
        }
    }
    
    // Validate description
    if (isset($data['description'])) {
        if (strlen($data['description']) < 5) {
            $errors[] = "Description must be at least 5 characters";
        }
        if (strlen($data['description']) > 500) {
            $errors[] = "Description must not exceed 500 characters";
        }
    }
    
    // Validate date
    if (isset($data['transaction_date']) && !DateTime::createFromFormat('Y-m-d', $data['transaction_date'])) {
        $errors[] = "Invalid date format (use Y-m-d)";
    }
    
    // Validate reference ID
    if (isset($data['reference_id']) && !empty($data['reference_id']) && (!is_numeric($data['reference_id']) || $data['reference_id'] <= 0)) {
        $errors[] = "Reference ID must be a positive number";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Get financial statistics for dashboard
 * @return array Financial statistics
 */
function getFinancialStatistics() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Current month statistics
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as monthly_income,
                SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as monthly_expense,
                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as monthly_net
            FROM financial_transactions 
            WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
        ");
        $stmt->execute([$currentMonth, $currentYear]);
        $monthlyStats = $stmt->fetch();
        
        $stats['current_month'] = [
            'income' => $monthlyStats['monthly_income'] ?? 0,
            'expense' => $monthlyStats['monthly_expense'] ?? 0,
            'net' => $monthlyStats['monthly_net'] ?? 0
        ];
        
        // Total balance
        $balanceInfo = calculateRealTimeBalance();
        $stats['total_balance'] = $balanceInfo['current_balance'];
        
        // Transaction counts by category (current month)
        $stmt = $pdo->prepare("
            SELECT category, COUNT(*) as count
            FROM financial_transactions 
            WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
            GROUP BY category
            ORDER BY count DESC
        ");
        $stmt->execute([$currentMonth, $currentYear]);
        $categoryStats = $stmt->fetchAll();
        
        $stats['transactions_by_category'] = [];
        foreach ($categoryStats as $stat) {
            $stats['transactions_by_category'][$stat['category']] = $stat['count'];
        }
        
        // Recent transactions (last 5)
        $stmt = $pdo->prepare("
            SELECT ft.*, u.full_name as recorded_by_name
            FROM financial_transactions ft
            LEFT JOIN users u ON ft.recorded_by = u.id
            ORDER BY ft.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $stats['recent_transactions'] = $stmt->fetchAll();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error getting financial statistics: " . $e->getMessage());
        return [];
    }
}
// ============================================================================
// AUTOMATED FINANCIAL CALCULATIONS
// ============================================================================

/**
 * Automatically generate financial transaction from SPP payment
 * @param int $studentId Student ID
 * @param int $month Payment month
 * @param int $year Payment year
 * @param float $amount Payment amount
 * @param string $paymentMethod Payment method
 * @return array Result with success status and message
 */
function generateSPPTransaction($studentId, $month, $year, $amount, $paymentMethod = 'cash') {
    global $pdo;
    
    try {
        // Get student information
        $student = getStudentById($studentId);
        if (!$student) {
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        // Create transaction description
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $description = "Pembayaran SPP {$student['full_name']} - {$monthName} {$year}";
        
        // Record financial transaction
        $result = recordFinancialTransaction(
            'income',
            'spp',
            $amount,
            $description,
            date('Y-m-d'), // Use current date for transaction
            $studentId,
            "Metode pembayaran: {$paymentMethod}"
        );
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error generating SPP transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error generating transaction'];
    }
}

/**
 * Calculate and generate mentor payment transactions
 * @param int $mentorId Mentor ID
 * @param int $month Payment month
 * @param int $year Payment year
 * @return array Result with success status and message
 */
function generateMentorPaymentTransaction($mentorId, $month, $year) {
    global $pdo;
    
    try {
        // Get mentor information
        $mentor = getMentorById($mentorId);
        if (!$mentor) {
            return ['success' => false, 'message' => 'Mentor not found'];
        }
        
        // Calculate mentor payment for the month
        $attendanceData = calculateMentorAttendanceRate($mentorId, $month, $year);
        $totalPayment = $attendanceData['totals']['total_payment'] ?? 0;
        
        if ($totalPayment <= 0) {
            return ['success' => false, 'message' => 'No payment due for this mentor in the specified period'];
        }
        
        // Create transaction description
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $description = "Honor mentor {$mentor['full_name']} - {$monthName} {$year}";
        
        // Add details about hours taught
        $notes = "Total jam mengajar: {$attendanceData['totals']['total_hours']} jam";
        foreach ($attendanceData['by_level'] as $level => $data) {
            if ($data['total_hours'] > 0) {
                $notes .= "\n{$level}: {$data['total_hours']} jam";
            }
        }
        
        // Record financial transaction
        $result = recordFinancialTransaction(
            'expense',
            'mentor_payment',
            $totalPayment,
            $description,
            date('Y-m-d'),
            $mentorId,
            $notes
        );
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error generating mentor payment transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error generating mentor payment transaction'];
    }
}

/**
 * Auto-generate transactions for all mentor payments in a month
 * @param int $month Payment month
 * @param int $year Payment year
 * @return array Result with success count and errors
 */
function autoGenerateMentorPayments($month, $year) {
    global $pdo;
    
    try {
        // Get all active mentors
        $mentors = getAllMentors(1, 1000, ['status' => 'active']);
        $mentorList = $mentors['data'];
        
        $successCount = 0;
        $errors = [];
        
        foreach ($mentorList as $mentor) {
            // Check if payment already exists for this mentor and month
            $existingTransaction = $pdo->prepare("
                SELECT id FROM financial_transactions 
                WHERE category = 'mentor_payment' 
                AND reference_id = ? 
                AND MONTH(transaction_date) = ? 
                AND YEAR(transaction_date) = ?
            ");
            $existingTransaction->execute([$mentor['id'], $month, $year]);
            
            if ($existingTransaction->fetch()) {
                $errors[] = "Payment already exists for {$mentor['full_name']}";
                continue;
            }
            
            // Generate payment transaction
            $result = generateMentorPaymentTransaction($mentor['id'], $month, $year);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "{$mentor['full_name']}: {$result['message']}";
            }
        }
        
        return [
            'success' => true,
            'success_count' => $successCount,
            'error_count' => count($errors),
            'errors' => $errors,
            'message' => "Auto-generated {$successCount} mentor payments"
        ];
        
    } catch (Exception $e) {
        error_log("Error auto-generating mentor payments: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error auto-generating mentor payments'];
    }
}

/**
 * Calculate projected monthly income based on active students
 * @param int $month Target month
 * @param int $year Target year
 * @return array Projected income breakdown
 */
function calculateProjectedIncome($month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        // Get active students by level
        $stmt = $pdo->prepare("
            SELECT 
                level,
                COUNT(*) as student_count,
                SUM(monthly_fee) as total_spp
            FROM students 
            WHERE status = 'active'
            GROUP BY level
        ");
        $stmt->execute();
        $studentData = $stmt->fetchAll();
        
        $projection = [
            'month' => $month,
            'year' => $year,
            'by_level' => [],
            'totals' => [
                'total_students' => 0,
                'projected_spp' => 0,
                'projected_registration' => 0,
                'total_projected' => 0
            ]
        ];
        
        // Calculate SPP projections
        foreach ($studentData as $data) {
            $projection['by_level'][$data['level']] = [
                'student_count' => $data['student_count'],
                'projected_spp' => $data['total_spp']
            ];
            
            $projection['totals']['total_students'] += $data['student_count'];
            $projection['totals']['projected_spp'] += $data['total_spp'];
        }
        
        // Estimate registration fees (assume 2-3 new students per month)
        $registrationFee = 50000; // Default registration fee
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'registration_fee'");
        $stmt->execute();
        $settingFee = $stmt->fetchColumn();
        if ($settingFee) {
            $registrationFee = (float)$settingFee;
        }
        
        $estimatedNewStudents = 2; // Conservative estimate
        $projection['totals']['projected_registration'] = $estimatedNewStudents * $registrationFee;
        $projection['totals']['total_projected'] = $projection['totals']['projected_spp'] + $projection['totals']['projected_registration'];
        
        return $projection;
        
    } catch (Exception $e) {
        error_log("Error calculating projected income: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate projected monthly expenses based on active mentors and operational costs
 * @param int $month Target month
 * @param int $year Target year
 * @return array Projected expense breakdown
 */
function calculateProjectedExpenses($month = null, $year = null) {
    global $pdo;
    
    try {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        // Get active mentors and their rates
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.full_name,
                m.hourly_rate,
                m.teaching_levels
            FROM mentors m
            WHERE m.status = 'active'
        ");
        $stmt->execute();
        $mentors = $stmt->fetchAll();
        
        $projection = [
            'month' => $month,
            'year' => $year,
            'mentor_payments' => [],
            'totals' => [
                'projected_mentor_payments' => 0,
                'projected_operational' => 0,
                'projected_utilities' => 0,
                'total_projected' => 0
            ]
        ];
        
        // Calculate mentor payment projections (assume 20 teaching days per month, 2 hours per day per level)
        $teachingDaysPerMonth = 20;
        $hoursPerDayPerLevel = 2;
        
        foreach ($mentors as $mentor) {
            $teachingLevels = json_decode($mentor['teaching_levels'], true);
            $levelCount = count($teachingLevels);
            
            // Estimate monthly hours: levels * days * hours per day per level
            $estimatedMonthlyHours = $levelCount * $teachingDaysPerMonth * $hoursPerDayPerLevel;
            $estimatedPayment = $estimatedMonthlyHours * $mentor['hourly_rate'];
            
            $projection['mentor_payments'][] = [
                'mentor_name' => $mentor['full_name'],
                'teaching_levels' => $teachingLevels,
                'hourly_rate' => $mentor['hourly_rate'],
                'estimated_hours' => $estimatedMonthlyHours,
                'estimated_payment' => $estimatedPayment
            ];
            
            $projection['totals']['projected_mentor_payments'] += $estimatedPayment;
        }
        
        // Estimate operational costs based on historical data
        $stmt = $pdo->prepare("
            SELECT AVG(amount) as avg_operational
            FROM financial_transactions 
            WHERE category = 'operational' 
            AND transaction_type = 'expense'
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");
        $stmt->execute();
        $avgOperational = $stmt->fetchColumn() ?: 500000; // Default 500k if no history
        
        // Estimate utilities based on historical data
        $stmt = $pdo->prepare("
            SELECT AVG(amount) as avg_utilities
            FROM financial_transactions 
            WHERE category = 'utilities' 
            AND transaction_type = 'expense'
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");
        $stmt->execute();
        $avgUtilities = $stmt->fetchColumn() ?: 300000; // Default 300k if no history
        
        $projection['totals']['projected_operational'] = $avgOperational;
        $projection['totals']['projected_utilities'] = $avgUtilities;
        $projection['totals']['total_projected'] = 
            $projection['totals']['projected_mentor_payments'] + 
            $projection['totals']['projected_operational'] + 
            $projection['totals']['projected_utilities'];
        
        return $projection;
        
    } catch (Exception $e) {
        error_log("Error calculating projected expenses: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate financial projections and cash flow forecast
 * @param int $months Number of months to project
 * @return array Financial projections
 */
function calculateFinancialProjections($months = 3) {
    try {
        $projections = [];
        $currentBalance = calculateRealTimeBalance()['current_balance'];
        $runningBalance = $currentBalance;
        
        for ($i = 0; $i < $months; $i++) {
            $targetMonth = date('n', strtotime("+{$i} months"));
            $targetYear = date('Y', strtotime("+{$i} months"));
            
            $incomeProjection = calculateProjectedIncome($targetMonth, $targetYear);
            $expenseProjection = calculateProjectedExpenses($targetMonth, $targetYear);
            
            $projectedIncome = $incomeProjection['totals']['total_projected'] ?? 0;
            $projectedExpense = $expenseProjection['totals']['total_projected'] ?? 0;
            $netProjection = $projectedIncome - $projectedExpense;
            $runningBalance += $netProjection;
            
            $projections[] = [
                'month' => $targetMonth,
                'year' => $targetYear,
                'month_name' => date('F Y', mktime(0, 0, 0, $targetMonth, 1, $targetYear)),
                'projected_income' => $projectedIncome,
                'projected_expense' => $projectedExpense,
                'net_projection' => $netProjection,
                'projected_balance' => $runningBalance,
                'income_details' => $incomeProjection,
                'expense_details' => $expenseProjection
            ];
        }
        
        return [
            'current_balance' => $currentBalance,
            'projections' => $projections,
            'summary' => [
                'total_projected_income' => array_sum(array_column($projections, 'projected_income')),
                'total_projected_expense' => array_sum(array_column($projections, 'projected_expense')),
                'total_net_projection' => array_sum(array_column($projections, 'net_projection')),
                'final_projected_balance' => end($projections)['projected_balance'] ?? $currentBalance
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error calculating financial projections: " . $e->getMessage());
        return [];
    }
}

/**
 * Update SPP payment and automatically create financial transaction
 * This function extends the existing recordSPPPayment to include automatic transaction generation
 * @param int $studentId Student ID
 * @param int $month Payment month
 * @param int $year Payment year
 * @param float $amount Payment amount
 * @param string $paymentMethod Payment method
 * @param string $notes Optional notes
 * @return array Result with success status and message
 */
function recordSPPPaymentWithTransaction($studentId, $month, $year, $amount, $paymentMethod = 'cash', $notes = '') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Record SPP payment using existing function
        $sppResult = recordSPPPayment($studentId, $month, $year, $amount, $paymentMethod, $notes);
        
        if (!$sppResult['success']) {
            $pdo->rollBack();
            return $sppResult;
        }
        
        // Generate financial transaction
        $transactionResult = generateSPPTransaction($studentId, $month, $year, $amount, $paymentMethod);
        
        if (!$transactionResult['success']) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'SPP recorded but failed to create financial transaction: ' . $transactionResult['message']
            ];
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'SPP payment recorded and financial transaction created successfully',
            'spp_payment_id' => $sppResult['payment_id'] ?? null,
            'transaction_id' => $transactionResult['transaction_id'] ?? null
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error recording SPP payment with transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Calculate mentor payment based on attendance and automatically create financial transaction
 * @param int $mentorId Mentor ID
 * @param int $month Payment month
 * @param int $year Payment year
 * @param bool $createTransaction Whether to create financial transaction
 * @return array Result with payment calculation and transaction details
 */
function calculateAndRecordMentorPayment($mentorId, $month, $year, $createTransaction = true) {
    try {
        // Calculate mentor payment
        $attendanceData = calculateMentorAttendanceRate($mentorId, $month, $year);
        $totalPayment = $attendanceData['totals']['total_payment'] ?? 0;
        
        if ($totalPayment <= 0) {
            return [
                'success' => false,
                'message' => 'No payment due for this mentor in the specified period',
                'payment_amount' => 0,
                'attendance_data' => $attendanceData
            ];
        }
        
        $result = [
            'success' => true,
            'message' => 'Mentor payment calculated successfully',
            'payment_amount' => $totalPayment,
            'attendance_data' => $attendanceData
        ];
        
        // Create financial transaction if requested
        if ($createTransaction) {
            $transactionResult = generateMentorPaymentTransaction($mentorId, $month, $year);
            $result['transaction_created'] = $transactionResult['success'];
            $result['transaction_id'] = $transactionResult['transaction_id'] ?? null;
            
            if (!$transactionResult['success']) {
                $result['message'] .= ' but failed to create financial transaction: ' . $transactionResult['message'];
            } else {
                $result['message'] = 'Mentor payment calculated and financial transaction created successfully';
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error calculating and recording mentor payment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error calculating mentor payment'];
    }
}
// ============================================================================
// MONTHLY RECAP GENERATION SYSTEM
// ============================================================================

/**
 * Generate monthly financial recap
 * @param int $month Recap month (1-12)
 * @param int $year Recap year
 * @param bool $forceRegenerate Whether to regenerate if recap already exists
 * @return array Result with success status and recap data
 */
function generateMonthlyRecap($month, $year, $forceRegenerate = false) {
    global $pdo;
    
    try {
        // Check if recap already exists
        $stmt = $pdo->prepare("
            SELECT * FROM monthly_recap 
            WHERE recap_month = ? AND recap_year = ?
        ");
        $stmt->execute([$month, $year]);
        $existingRecap = $stmt->fetch();
        
        if ($existingRecap && !$forceRegenerate) {
            return [
                'success' => true,
                'message' => 'Monthly recap already exists',
                'recap_data' => $existingRecap,
                'regenerated' => false
            ];
        }
        
        // Get opening balance (closing balance from previous month)
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        
        $stmt = $pdo->prepare("
            SELECT closing_balance FROM monthly_recap 
            WHERE recap_month = ? AND recap_year = ?
        ");
        $stmt->execute([$prevMonth, $prevYear]);
        $openingBalance = $stmt->fetchColumn() ?: 0;
        
        // If no previous recap, calculate opening balance from all transactions before this month
        if ($openingBalance == 0) {
            $stmt = $pdo->prepare("
                SELECT SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as balance
                FROM financial_transactions 
                WHERE transaction_date < ?
            ");
            $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
            $stmt->execute([$firstDayOfMonth]);
            $openingBalance = $stmt->fetchColumn() ?: 0;
        }
        
        // Calculate monthly financial data
        $stmt = $pdo->prepare("
            SELECT 
                transaction_type,
                category,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            FROM financial_transactions 
            WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
            GROUP BY transaction_type, category
        ");
        $stmt->execute([$month, $year]);
        $transactionData = $stmt->fetchAll();
        
        // Initialize totals
        $totalIncome = 0;
        $totalExpense = 0;
        $sppIncome = 0;
        $registrationIncome = 0;
        $mentorPaymentExpense = 0;
        $operationalExpense = 0;
        
        // Process transaction data
        foreach ($transactionData as $data) {
            $amount = $data['total_amount'];
            
            if ($data['transaction_type'] === 'income') {
                $totalIncome += $amount;
                
                switch ($data['category']) {
                    case 'spp':
                        $sppIncome += $amount;
                        break;
                    case 'registration':
                        $registrationIncome += $amount;
                        break;
                }
            } else {
                $totalExpense += $amount;
                
                switch ($data['category']) {
                    case 'mentor_payment':
                        $mentorPaymentExpense += $amount;
                        break;
                    case 'operational':
                    case 'utilities':
                    case 'other':
                        $operationalExpense += $amount;
                        break;
                }
            }
        }
        
        $closingBalance = $openingBalance + $totalIncome - $totalExpense;
        
        // Get student and mentor counts for the month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM students 
            WHERE status = 'active' 
            AND registration_date <= LAST_DAY(?)
        ");
        $lastDayOfMonth = sprintf('%04d-%02d-%02d', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
        $stmt->execute([$lastDayOfMonth]);
        $totalStudents = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM mentors 
            WHERE status = 'active' 
            AND join_date <= LAST_DAY(?)
        ");
        $stmt->execute([$lastDayOfMonth]);
        $totalMentors = $stmt->fetchColumn();
        
        // Prepare recap data
        $recapData = [
            'recap_month' => $month,
            'recap_year' => $year,
            'opening_balance' => $openingBalance,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'closing_balance' => $closingBalance,
            'spp_income' => $sppIncome,
            'registration_income' => $registrationIncome,
            'mentor_payment_expense' => $mentorPaymentExpense,
            'operational_expense' => $operationalExpense,
            'total_students' => $totalStudents,
            'total_mentors' => $totalMentors,
            'generated_by' => getCurrentUserId()
        ];
        
        // Insert or update recap
        if ($existingRecap) {
            // Update existing recap
            $stmt = $pdo->prepare("
                UPDATE monthly_recap SET 
                    opening_balance = ?, total_income = ?, total_expense = ?, 
                    closing_balance = ?, spp_income = ?, registration_income = ?,
                    mentor_payment_expense = ?, operational_expense = ?, 
                    total_students = ?, total_mentors = ?, 
                    generated_at = CURRENT_TIMESTAMP, generated_by = ?
                WHERE recap_month = ? AND recap_year = ?
            ");
            $stmt->execute([
                $openingBalance, $totalIncome, $totalExpense, $closingBalance,
                $sppIncome, $registrationIncome, $mentorPaymentExpense, $operationalExpense,
                $totalStudents, $totalMentors, getCurrentUserId(), $month, $year
            ]);
            $recapData['id'] = $existingRecap['id'];
        } else {
            // Insert new recap
            $stmt = $pdo->prepare("
                INSERT INTO monthly_recap (
                    recap_month, recap_year, opening_balance, total_income, total_expense,
                    closing_balance, spp_income, registration_income, mentor_payment_expense,
                    operational_expense, total_students, total_mentors, generated_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $month, $year, $openingBalance, $totalIncome, $totalExpense,
                $closingBalance, $sppIncome, $registrationIncome, $mentorPaymentExpense,
                $operationalExpense, $totalStudents, $totalMentors, getCurrentUserId()
            ]);
            $recapData['id'] = $pdo->lastInsertId();
        }
        
        return [
            'success' => true,
            'message' => $existingRecap ? 'Monthly recap updated successfully' : 'Monthly recap generated successfully',
            'recap_data' => $recapData,
            'regenerated' => $existingRecap ? true : false
        ];
        
    } catch (Exception $e) {
        error_log("Error generating monthly recap: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error generating monthly recap'];
    }
}

/**
 * Get monthly recap by month and year
 * @param int $month Recap month (1-12)
 * @param int $year Recap year
 * @return array|null Monthly recap data or null if not found
 */
function getMonthlyRecap($month, $year) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT mr.*, u.full_name as generated_by_name
            FROM monthly_recap mr
            LEFT JOIN users u ON mr.generated_by = u.id
            WHERE mr.recap_month = ? AND mr.recap_year = ?
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting monthly recap: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all monthly recaps with pagination
 * @param int $page Page number (1-based)
 * @param int $limit Records per page
 * @param array $filters Optional filters
 * @return array Paginated results with metadata
 */
function getAllMonthlyRecaps($page = 1, $limit = 12, $filters = []) {
    global $pdo;
    
    try {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['year'])) {
            $whereClause .= " AND recap_year = ?";
            $params[] = $filters['year'];
        }
        
        if (!empty($filters['month'])) {
            $whereClause .= " AND recap_month = ?";
            $params[] = $filters['month'];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM monthly_recap $whereClause";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get paginated data
        $dataSql = "
            SELECT mr.*, u.full_name as generated_by_name
            FROM monthly_recap mr
            LEFT JOIN users u ON mr.generated_by = u.id
            $whereClause 
            ORDER BY mr.recap_year DESC, mr.recap_month DESC 
            LIMIT ? OFFSET ?
        ";
        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($dataSql);
        $stmt->execute($dataParams);
        $recaps = $stmt->fetchAll();
        
        return [
            'data' => $recaps,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error getting monthly recaps: " . $e->getMessage());
        return ['data' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total_records' => 0, 'total_pages' => 0]];
    }
}

/**
 * Generate detailed monthly statistics
 * @param int $month Target month
 * @param int $year Target year
 * @return array Detailed monthly statistics
 */
function generateMonthlyStatistics($month, $year) {
    global $pdo;
    
    try {
        $stats = [
            'month' => $month,
            'year' => $year,
            'month_name' => date('F Y', mktime(0, 0, 0, $month, 1, $year))
        ];
        
        // Student statistics
        $stmt = $pdo->prepare("
            SELECT 
                level,
                COUNT(*) as student_count,
                SUM(monthly_fee) as total_monthly_fee
            FROM students 
            WHERE status = 'active'
            AND registration_date <= LAST_DAY(?)
            GROUP BY level
        ");
        $lastDayOfMonth = sprintf('%04d-%02d-%02d', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
        $stmt->execute([$lastDayOfMonth]);
        $studentStats = $stmt->fetchAll();
        
        $stats['students'] = [
            'by_level' => [],
            'total_count' => 0,
            'total_monthly_fee' => 0
        ];
        
        foreach ($studentStats as $stat) {
            $stats['students']['by_level'][$stat['level']] = [
                'count' => $stat['student_count'],
                'monthly_fee' => $stat['total_monthly_fee']
            ];
            $stats['students']['total_count'] += $stat['student_count'];
            $stats['students']['total_monthly_fee'] += $stat['total_monthly_fee'];
        }
        
        // Mentor statistics
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as mentor_count
            FROM mentors 
            WHERE status = 'active'
            AND join_date <= LAST_DAY(?)
        ");
        $stmt->execute([$lastDayOfMonth]);
        $stats['mentors']['total_count'] = $stmt->fetchColumn();
        
        // Attendance statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                ROUND(AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100, 2) as attendance_rate
            FROM student_attendance 
            WHERE MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?
        ");
        $stmt->execute([$month, $year]);
        $attendanceStats = $stmt->fetch();
        
        $stats['attendance'] = [
            'student_attendance_rate' => $attendanceStats['attendance_rate'] ?? 0,
            'total_attendance_records' => $attendanceStats['total_records'] ?? 0
        ];
        
        // Mentor attendance and payment statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught ELSE 0 END) as total_hours,
                SUM(CASE WHEN ma.status = 'present' THEN ma.hours_taught * m.hourly_rate ELSE 0 END) as total_payment
            FROM mentor_attendance ma
            JOIN mentors m ON ma.mentor_id = m.id
            WHERE MONTH(ma.attendance_date) = ? AND YEAR(ma.attendance_date) = ?
        ");
        $stmt->execute([$month, $year]);
        $mentorStats = $stmt->fetch();
        
        $stats['mentors']['attendance_records'] = $mentorStats['total_records'] ?? 0;
        $stats['mentors']['total_hours_taught'] = $mentorStats['total_hours'] ?? 0;
        $stats['mentors']['total_payment_due'] = $mentorStats['total_payment'] ?? 0;
        
        // Payment statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as payment_count,
                SUM(amount) as total_collected,
                COUNT(DISTINCT student_id) as students_paid
            FROM spp_payments 
            WHERE payment_month = ? AND payment_year = ?
        ");
        $stmt->execute([$month, $year]);
        $paymentStats = $stmt->fetch();
        
        $stats['payments'] = [
            'spp_payments_count' => $paymentStats['payment_count'] ?? 0,
            'total_spp_collected' => $paymentStats['total_collected'] ?? 0,
            'students_paid' => $paymentStats['students_paid'] ?? 0,
            'payment_rate' => $stats['students']['total_count'] > 0 
                ? round(($paymentStats['students_paid'] ?? 0) / $stats['students']['total_count'] * 100, 2) 
                : 0
        ];
        
        // Outstanding payments
        $stmt = $pdo->prepare("
            SELECT 
                s.level,
                COUNT(*) as outstanding_count,
                SUM(s.monthly_fee) as outstanding_amount
            FROM students s
            LEFT JOIN spp_payments sp ON s.id = sp.student_id 
                AND sp.payment_month = ? AND sp.payment_year = ?
            WHERE s.status = 'active' 
            AND sp.id IS NULL
            GROUP BY s.level
        ");
        $stmt->execute([$month, $year]);
        $outstandingStats = $stmt->fetchAll();
        
        $stats['outstanding'] = [
            'by_level' => [],
            'total_count' => 0,
            'total_amount' => 0
        ];
        
        foreach ($outstandingStats as $stat) {
            $stats['outstanding']['by_level'][$stat['level']] = [
                'count' => $stat['outstanding_count'],
                'amount' => $stat['outstanding_amount']
            ];
            $stats['outstanding']['total_count'] += $stat['outstanding_count'];
            $stats['outstanding']['total_amount'] += $stat['outstanding_amount'];
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error generating monthly statistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Auto-generate monthly recaps for multiple months
 * @param int $startMonth Starting month
 * @param int $startYear Starting year
 * @param int $endMonth Ending month
 * @param int $endYear Ending year
 * @return array Result with success count and errors
 */
function autoGenerateMonthlyRecaps($startMonth, $startYear, $endMonth, $endYear) {
    try {
        $successCount = 0;
        $errors = [];
        
        $currentMonth = $startMonth;
        $currentYear = $startYear;
        
        while (($currentYear < $endYear) || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
            $result = generateMonthlyRecap($currentMonth, $currentYear);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = sprintf('%s %d: %s', 
                    date('F', mktime(0, 0, 0, $currentMonth, 1)), 
                    $currentYear, 
                    $result['message']
                );
            }
            
            // Move to next month
            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }
        
        return [
            'success' => true,
            'success_count' => $successCount,
            'error_count' => count($errors),
            'errors' => $errors,
            'message' => "Generated {$successCount} monthly recaps"
        ];
        
    } catch (Exception $e) {
        error_log("Error auto-generating monthly recaps: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error auto-generating monthly recaps'];
    }
}

/**
 * Get monthly recap comparison between periods
 * @param int $month1 First month
 * @param int $year1 First year
 * @param int $month2 Second month
 * @param int $year2 Second year
 * @return array Comparison data
 */
function compareMonthlyRecaps($month1, $year1, $month2, $year2) {
    try {
        $recap1 = getMonthlyRecap($month1, $year1);
        $recap2 = getMonthlyRecap($month2, $year2);
        
        if (!$recap1 || !$recap2) {
            return ['success' => false, 'message' => 'One or both recaps not found'];
        }
        
        $comparison = [
            'period1' => [
                'month' => $month1,
                'year' => $year1,
                'month_name' => date('F Y', mktime(0, 0, 0, $month1, 1, $year1)),
                'data' => $recap1
            ],
            'period2' => [
                'month' => $month2,
                'year' => $year2,
                'month_name' => date('F Y', mktime(0, 0, 0, $month2, 1, $year2)),
                'data' => $recap2
            ],
            'differences' => []
        ];
        
        // Calculate differences
        $compareFields = [
            'total_income', 'total_expense', 'closing_balance',
            'spp_income', 'registration_income', 'mentor_payment_expense',
            'operational_expense', 'total_students', 'total_mentors'
        ];
        
        foreach ($compareFields as $field) {
            $value1 = $recap1[$field] ?? 0;
            $value2 = $recap2[$field] ?? 0;
            $difference = $value2 - $value1;
            $percentageChange = $value1 != 0 ? round(($difference / $value1) * 100, 2) : 0;
            
            $comparison['differences'][$field] = [
                'value1' => $value1,
                'value2' => $value2,
                'difference' => $difference,
                'percentage_change' => $percentageChange,
                'trend' => $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'stable')
            ];
        }
        
        return ['success' => true, 'comparison' => $comparison];
        
    } catch (Exception $e) {
        error_log("Error comparing monthly recaps: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error comparing monthly recaps'];
    }
}

/**
 * Delete monthly recap
 * @param int $month Recap month
 * @param int $year Recap year
 * @return array Result with success status and message
 */
function deleteMonthlyRecap($month, $year) {
    global $pdo;
    
    try {
        // Check if recap exists
        $stmt = $pdo->prepare("SELECT id FROM monthly_recap WHERE recap_month = ? AND recap_year = ?");
        $stmt->execute([$month, $year]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Monthly recap not found'];
        }
        
        // Delete recap
        $stmt = $pdo->prepare("DELETE FROM monthly_recap WHERE recap_month = ? AND recap_year = ?");
        $stmt->execute([$month, $year]);
        
        return ['success' => true, 'message' => 'Monthly recap deleted successfully'];
        
    } catch (Exception $e) {
        error_log("Error deleting monthly recap: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Get recent SPP payments for dashboard display
 *
 * @param int $limit Number of recent payments to retrieve
 * @return array Recent SPP payments with student information
 */
function getRecentSPPPayments($limit = 5) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT
                sp.payment_date,
                sp.amount,
                sp.month,
                sp.year,
                s.full_name as student_name,
                s.level
            FROM spp_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE s.status = 'active'
            ORDER BY sp.payment_date DESC, sp.created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error getting recent SPP payments: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mentor attendance by date and class
 * @param string $date Attendance date
 * @param string $level Optional level filter
 * @param string $class Optional class filter
 * @return array List of mentors with attendance status by class
 */
function getMentorAttendanceByDateAndClass($date, $level = null, $class = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                m.id as mentor_id, m.mentor_code, m.full_name, m.teaching_levels, m.hourly_rate,
                s.level as teaching_level, s.class,
                ma.status, ma.hours_taught, ma.notes,
                ma.created_at as recorded_at
            FROM mentors m
            CROSS JOIN (
                SELECT DISTINCT level, class 
                FROM students 
                WHERE status = 'active'
            ) s
            LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
                AND ma.attendance_date = ? 
                AND ma.level = s.level 
                AND ma.class = s.class
            WHERE m.status = 'active' 
            AND JSON_CONTAINS(m.teaching_levels, JSON_QUOTE(s.level))
        ";
        
        $params = [$date];
        
        if ($level) {
            $sql .= " AND s.level = ?";
            $params[] = $level;
        }
        
        if ($class) {
            $sql .= " AND s.class = ?";
            $params[] = $class;
        }
        
        $sql .= " ORDER BY m.full_name, s.level, s.class";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        // Decode teaching levels for each mentor
        foreach ($results as &$result) {
            $result['teaching_levels'] = json_decode($result['teaching_levels'], true);
        }
        
        return $results;
        
    } catch (PDOException $e) {
        error_log("Error getting mentor attendance by date and class: " . $e->getMessage());
        return [];
    }
}

/**
 * Record mentor attendance with class support
 * @param string $date Attendance date
 * @param int $mentorId Mentor ID
 * @param string $level Teaching level
 * @param string $class Teaching class
 * @param string $status Attendance status
 * @param float $hoursTaught Hours taught (for present status)
 * @param string $notes Optional notes
 * @return array Result with success status and message
 */
function recordMentorAttendance($date, $mentorId, $level, $class, $status, $hoursTaught = 0, $notes = '') {
    global $pdo;
    
    try {
        // Validate inputs
        if (!in_array($status, ['present', 'absent', 'sick', 'permission'])) {
            return ['success' => false, 'message' => 'Invalid attendance status'];
        }
        
        if (!in_array($level, ['SD', 'SMP', 'SMA'])) {
            return ['success' => false, 'message' => 'Invalid teaching level'];
        }
        
        // Check if mentor exists and can teach this level
        $stmt = $pdo->prepare("
            SELECT id, teaching_levels 
            FROM mentors 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$mentorId]);
        $mentor = $stmt->fetch();
        
        if (!$mentor) {
            return ['success' => false, 'message' => 'Mentor not found or inactive'];
        }
        
        $teachingLevels = json_decode($mentor['teaching_levels'], true);
        if (!in_array($level, $teachingLevels)) {
            return ['success' => false, 'message' => 'Mentor cannot teach this level'];
        }
        
        // Validate hours taught for present status
        if ($status === 'present') {
            if (!is_numeric($hoursTaught) || $hoursTaught <= 0) {
                return ['success' => false, 'message' => 'Hours taught must be provided for present status'];
            }
        } else {
            $hoursTaught = 0; // Reset hours for non-present status
        }
        
        // Check if attendance already exists
        $stmt = $pdo->prepare("
            SELECT id FROM mentor_attendance 
            WHERE mentor_id = ? AND attendance_date = ? AND level = ? AND class = ?
        ");
        $stmt->execute([$mentorId, $date, $level, $class]);
        $existingAttendance = $stmt->fetch();
        
        if ($existingAttendance) {
            // Update existing attendance
            $stmt = $pdo->prepare("
                UPDATE mentor_attendance 
                SET status = ?, hours_taught = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $hoursTaught, $notes, $existingAttendance['id']]);
            $message = 'Attendance updated successfully';
        } else {
            // Insert new attendance
            $stmt = $pdo->prepare("
                INSERT INTO mentor_attendance 
                (mentor_id, attendance_date, level, class, status, hours_taught, notes, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$mentorId, $date, $level, $class, $status, $hoursTaught, $notes]);
            $message = 'Attendance recorded successfully';
        }
        
        return ['success' => true, 'message' => $message];
        
    } catch (PDOException $e) {
        error_log("Error recording mentor attendance: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}
/**
 * Get student attendance by multiple classes
 * @param string $date Attendance date
 * @param string $level Student level
 * @param array $classes Array of class names
 * @return array List of students with attendance status
 */
function getStudentAttendanceByMultipleClasses($date, $level, $classes) {
    global $pdo;
    
    try {
        if (empty($classes)) {
            return [];
        }
        
        // Create placeholders for classes
        $classPlaceholders = str_repeat('?,', count($classes) - 1) . '?';
        
        $sql = "
            SELECT 
                s.id, s.student_number, s.full_name, s.level, s.class,
                sa.status, sa.created_at as recorded_at
            FROM students s
            LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                AND sa.attendance_date = ?
            WHERE s.status = 'active' 
            AND s.level = ?
            AND s.class IN ($classPlaceholders)
            ORDER BY s.class, s.full_name
        ";
        
        $params = array_merge([$date, $level], $classes);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting student attendance by multiple classes: " . $e->getMessage());
        return [];
    }
}

/**
 * Record student attendance for multiple classes
 * @param string $date Attendance date
 * @param string $level Student level
 * @param array $classes Array of class names
 * @param array $attendanceData Attendance data [student_id => status]
 * @return array Result with success status and message
 */
function recordMultiClassStudentAttendance($date, $level, $classes, $attendanceData) {
    global $pdo;
    
    try {
        if (empty($classes) || empty($attendanceData)) {
            return ['success' => false, 'message' => 'No attendance data provided'];
        }
        
        $pdo->beginTransaction();
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        // Get current user ID for recorded_by
        $recordedBy = getCurrentUserId();
        
        foreach ($attendanceData as $studentId => $status) {
            if (empty($status)) {
                continue; // Skip empty status
            }
            
            // Validate status
            if (!in_array($status, ['present', 'absent', 'sick', 'permission'])) {
                $errors[] = "Invalid status for student ID $studentId";
                $errorCount++;
                continue;
            }
            
            // Verify student exists and is in one of the selected classes
            $stmt = $pdo->prepare("
                SELECT id, class FROM students 
                WHERE id = ? AND level = ? AND status = 'active'
            ");
            $stmt->execute([$studentId, $level]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $errors[] = "Student ID $studentId not found or inactive";
                $errorCount++;
                continue;
            }
            
            if (!in_array($student['class'], $classes)) {
                $errors[] = "Student ID $studentId is not in selected classes";
                $errorCount++;
                continue;
            }
            
            // Check if attendance already exists
            $stmt = $pdo->prepare("
                SELECT id FROM student_attendance 
                WHERE student_id = ? AND attendance_date = ?
            ");
            $stmt->execute([$studentId, $date]);
            $existingAttendance = $stmt->fetch();
            
            if ($existingAttendance) {
                // Update existing attendance
                $stmt = $pdo->prepare("
                    UPDATE student_attendance 
                    SET status = ?, recorded_by = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$status, $recordedBy, $existingAttendance['id']]);
            } else {
                // Insert new attendance
                $stmt = $pdo->prepare("
                    INSERT INTO student_attendance 
                    (student_id, attendance_date, status, recorded_by, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$studentId, $date, $status, $recordedBy]);
            }
            
            $successCount++;
        }
        
        $pdo->commit();
        
        if ($successCount > 0 && $errorCount == 0) {
            return [
                'success' => true, 
                'message' => "Successfully recorded attendance for $successCount students"
            ];
        } elseif ($successCount > 0 && $errorCount > 0) {
            return [
                'success' => true, 
                'message' => "Recorded attendance for $successCount students with $errorCount errors: " . implode(', ', $errors)
            ];
        } else {
            return [
                'success' => false, 
                'message' => "Failed to record attendance. Errors: " . implode(', ', $errors)
            ];
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error recording multi-class student attendance: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
    }
}
?>