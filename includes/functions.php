<?php
/**
 * Reusable Helper Functions
 * Allocation engine, data retrieval, and utility functions
 */

require_once __DIR__ . '/config.php';

// =============================================
// STUDENT FUNCTIONS
// =============================================

/**
 * Get a student's data by ID
 * @param int $studentId
 * @return array|null
 */
function getStudentById($studentId) {
    global $conn;
    $studentId = (int)$studentId;
    $query = "SELECT * FROM students WHERE student_id = $studentId LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
}

/**
 * Check if an email already exists
 * @param string $email
 * @return bool
 */
function emailExists($email) {
    global $conn;
    $email = mysqli_real_escape_string($conn, trim($email));
    $query = "SELECT student_id FROM students WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0);
}

/**
 * Register a new student
 * @param string $fullname
 * @param string $email
 * @param string $password (plain text, will be hashed)
 * @param string $gender
 * @param int $level
 * @param bool $medicalStatus
 * @return bool
 */
function registerStudent($fullname, $email, $password, $gender, $level, $medicalStatus) {
    global $conn;
    
    $fullname = mysqli_real_escape_string($conn, trim($fullname));
    $email = mysqli_real_escape_string($conn, trim($email));
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $gender = mysqli_real_escape_string($conn, $gender);
    $level = (int)$level;
    $medical = $medicalStatus ? 1 : 0;
    
    $query = "INSERT INTO students (fullname, email, password, gender, level, medical_status, allocation_status)
              VALUES ('$fullname', '$email', '$hashedPassword', '$gender', $level, $medical, 'not_requested')";
    
    return mysqli_query($conn, $query);
}

/**
 * Update a student's allocation status
 * @param int $studentId
 * @param string $status
 * @return bool
 */
function updateAllocationStatus($studentId, $status) {
    global $conn;
    $studentId = (int)$studentId;
    $status = mysqli_real_escape_string($conn, $status);
    $query = "UPDATE students SET allocation_status = '$status' WHERE student_id = $studentId";
    return mysqli_query($conn, $query);
}

/**
 * Get a student's allocation details (if allocated)
 * @param int $studentId
 * @return array|null
 */
function getStudentAllocation($studentId) {
    global $conn;
    $studentId = (int)$studentId;
    $query = "SELECT a.*, r.room_number, r.capacity, h.hostel_name, h.gender_category
              FROM allocations a
              JOIN rooms r ON a.room_id = r.room_id
              JOIN hostels h ON r.hostel_id = h.hostel_id
              WHERE a.student_id = $studentId
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
}

// =============================================
// HOSTEL FUNCTIONS
// =============================================

/**
 * Get all hostels
 * @return array
 */
function getAllHostels() {
    global $conn;
    $query = "SELECT * FROM hostels ORDER BY hostel_name ASC";
    $result = mysqli_query($conn, $query);
    $hostels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $hostels[] = $row;
    }
    return $hostels;
}

/**
 * Get a hostel by ID
 * @param int $hostelId
 * @return array|null
 */
function getHostelById($hostelId) {
    global $conn;
    $hostelId = (int)$hostelId;
    $query = "SELECT * FROM hostels WHERE hostel_id = $hostelId LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
}

/**
 * Add a new hostel
 * @param string $name
 * @param string $genderCategory
 * @return bool
 */
function addHostel($name, $genderCategory) {
    global $conn;
    $name = mysqli_real_escape_string($conn, trim($name));
    $genderCategory = mysqli_real_escape_string($conn, $genderCategory);
    $query = "INSERT INTO hostels (hostel_name, gender_category, total_rooms) VALUES ('$name', '$genderCategory', 0)";
    return mysqli_query($conn, $query);
}

/**
 * Update a hostel
 * @param int $hostelId
 * @param string $name
 * @param string $genderCategory
 * @return bool
 */
function updateHostel($hostelId, $name, $genderCategory) {
    global $conn;
    $hostelId = (int)$hostelId;
    $name = mysqli_real_escape_string($conn, trim($name));
    $genderCategory = mysqli_real_escape_string($conn, $genderCategory);
    $query = "UPDATE hostels SET hostel_name = '$name', gender_category = '$genderCategory' WHERE hostel_id = $hostelId";
    return mysqli_query($conn, $query);
}

/**
 * Delete a hostel (only if no rooms assigned)
 * @param int $hostelId
 * @return bool|string Returns true on success, error message string on failure
 */
function deleteHostel($hostelId) {
    global $conn;
    $hostelId = (int)$hostelId;
    
    // Check if hostel has rooms
    $query = "SELECT COUNT(*) as room_count FROM rooms WHERE hostel_id = $hostelId";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['room_count'] > 0) {
        return "Cannot delete hostel: it has {$row['room_count']} room(s) assigned. Remove all rooms first.";
    }
    
    $query = "DELETE FROM hostels WHERE hostel_id = $hostelId";
    return mysqli_query($conn, $query) ? true : "Failed to delete hostel.";
}

/**
 * Update hostel total_rooms count
 * @param int $hostelId
 */
function updateHostelRoomCount($hostelId) {
    global $conn;
    $hostelId = (int)$hostelId;
    $query = "UPDATE hostels SET total_rooms = (SELECT COUNT(*) FROM rooms WHERE hostel_id = $hostelId) WHERE hostel_id = $hostelId";
    mysqli_query($conn, $query);
}

// =============================================
// ROOM FUNCTIONS
// =============================================

/**
 * Get all rooms with hostel info
 * @return array
 */
function getAllRooms() {
    global $conn;
    $query = "SELECT r.*, h.hostel_name, h.gender_category 
              FROM rooms r 
              JOIN hostels h ON r.hostel_id = h.hostel_id 
              ORDER BY h.hostel_name, r.room_number ASC";
    $result = mysqli_query($conn, $query);
    $rooms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }
    return $rooms;
}

/**
 * Get rooms by hostel ID
 * @param int $hostelId
 * @return array
 */
function getRoomsByHostel($hostelId) {
    global $conn;
    $hostelId = (int)$hostelId;
    $query = "SELECT * FROM rooms WHERE hostel_id = $hostelId ORDER BY room_number ASC";
    $result = mysqli_query($conn, $query);
    $rooms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }
    return $rooms;
}

/**
 * Get a room by ID
 * @param int $roomId
 * @return array|null
 */
function getRoomById($roomId) {
    global $conn;
    $roomId = (int)$roomId;
    $query = "SELECT r.*, h.hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.hostel_id WHERE r.room_id = $roomId LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
}

/**
 * Add a new room
 * @param int $hostelId
 * @param string $roomNumber
 * @param int $capacity
 * @return bool
 */
function addRoom($hostelId, $roomNumber, $capacity) {
    global $conn;
    $hostelId = (int)$hostelId;
    $roomNumber = mysqli_real_escape_string($conn, trim($roomNumber));
    $capacity = (int)$capacity;
    
    $query = "INSERT INTO rooms (hostel_id, room_number, capacity, occupied_slots) VALUES ($hostelId, '$roomNumber', $capacity, 0)";
    $success = mysqli_query($conn, $query);
    
    if ($success) {
        updateHostelRoomCount($hostelId);
    }
    
    return $success;
}

/**
 * Update a room
 * @param int $roomId
 * @param string $roomNumber
 * @param int $capacity
 * @return bool|string
 */
function updateRoom($roomId, $roomNumber, $capacity) {
    global $conn;
    $roomId = (int)$roomId;
    $roomNumber = mysqli_real_escape_string($conn, trim($roomNumber));
    $capacity = (int)$capacity;
    
    // Check occupied_slots
    $room = getRoomById($roomId);
    if ($room && $capacity < $room['occupied_slots']) {
        return "Cannot reduce capacity below current occupied slots ({$room['occupied_slots']}).";
    }
    
    $query = "UPDATE rooms SET room_number = '$roomNumber', capacity = $capacity WHERE room_id = $roomId";
    return mysqli_query($conn, $query) ? true : "Failed to update room.";
}

/**
 * Delete a room
 * @param int $roomId
 * @return bool|string
 */
function deleteRoom($roomId) {
    global $conn;
    $roomId = (int)$roomId;
    
    $room = getRoomById($roomId);
    if (!$room) {
        return "Room not found.";
    }
    
    if ($room['occupied_slots'] > 0) {
        return "Cannot delete room: it has {$room['occupied_slots']} student(s) allocated.";
    }
    
    $hostelId = $room['hostel_id'];
    $query = "DELETE FROM rooms WHERE room_id = $roomId";
    $success = mysqli_query($conn, $query);
    
    if ($success) {
        updateHostelRoomCount($hostelId);
    }
    
    return $success ? true : "Failed to delete room.";
}

// =============================================
// ALLOCATION ENGINE
// =============================================

/**
 * Calculate priority score for a student
 * @param array $student
 * @return int
 */
function calculatePriorityScore($student) {
    $score = 0;
    
    // Medical condition bonus
    if ($student['medical_status']) {
        $score += PRIORITY_MEDICAL;
    }
    
    // Level-based scoring
    switch ((int)$student['level']) {
        case 400: $score += PRIORITY_LEVEL_400; break;
        case 300: $score += PRIORITY_LEVEL_300; break;
        case 200: $score += PRIORITY_LEVEL_200; break;
        case 100: $score += PRIORITY_LEVEL_100; break;
    }
    
    return $score;
}

/**
 * Run the rule-based allocation engine
 * @return array Summary with 'allocated' and 'waitlisted' counts
 */
function runAllocationEngine() {
    global $conn;
    
    $summary = ['allocated' => 0, 'waitlisted' => 0, 'errors' => []];
    
    // Step 1: Reset all previous allocations
    mysqli_query($conn, "DELETE FROM allocations");
    mysqli_query($conn, "UPDATE rooms SET occupied_slots = 0");
    mysqli_query($conn, "UPDATE students SET allocation_status = 'pending' 
                         WHERE allocation_status IN ('allocated', 'waitlisted')");
    
    // Step 2: Load all pending students
    $query = "SELECT * FROM students WHERE allocation_status = 'pending' AND email != '" . ADMIN_EMAIL . "'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        $summary['errors'][] = "Failed to load students: " . mysqli_error($conn);
        return $summary;
    }
    
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['priority_score'] = calculatePriorityScore($row);
        $students[] = $row;
    }
    
    // Step 3: Sort by priority score descending
    usort($students, function($a, $b) {
        return $b['priority_score'] - $a['priority_score'];
    });
    
    // Step 4: Allocate rooms
    foreach ($students as $student) {
        // Find a matching room (same gender, has space)
        $gender = mysqli_real_escape_string($conn, $student['gender']);
        $roomQuery = "SELECT r.room_id 
                      FROM rooms r 
                      JOIN hostels h ON r.hostel_id = h.hostel_id 
                      WHERE h.gender_category = '$gender' 
                        AND r.occupied_slots < r.capacity 
                      ORDER BY r.occupied_slots ASC, r.room_id ASC 
                      LIMIT 1";
        $roomResult = mysqli_query($conn, $roomQuery);
        
        if ($roomResult && mysqli_num_rows($roomResult) === 1) {
            $room = mysqli_fetch_assoc($roomResult);
            $roomId = (int)$room['room_id'];
            $studentId = (int)$student['student_id'];
            
            // Insert allocation
            $insertQuery = "INSERT INTO allocations (student_id, room_id) VALUES ($studentId, $roomId)";
            if (mysqli_query($conn, $insertQuery)) {
                // Update room occupied slots
                mysqli_query($conn, "UPDATE rooms SET occupied_slots = occupied_slots + 1 WHERE room_id = $roomId");
                // Update student status
                updateAllocationStatus($studentId, 'allocated');
                $summary['allocated']++;
            } else {
                $summary['errors'][] = "Failed to allocate student {$student['fullname']}: " . mysqli_error($conn);
            }
        } else {
            // No room available - waitlist
            updateAllocationStatus((int)$student['student_id'], 'waitlisted');
            $summary['waitlisted']++;
        }
    }
    
    // Log the allocation run
    logAllocation($summary);
    
    return $summary;
}

/**
 * Log allocation results to file
 * @param array $summary
 */
function logAllocation($summary) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " | Allocation Run | Allocated: {$summary['allocated']} | Waitlisted: {$summary['waitlisted']}";
    if (!empty($summary['errors'])) {
        $logEntry .= " | Errors: " . implode('; ', $summary['errors']);
    }
    $logEntry .= "\n";
    
    file_put_contents($logDir . '/allocation.log', $logEntry, FILE_APPEND);
}

// =============================================
// REPORTING FUNCTIONS
// =============================================

/**
 * Get all allocated students with details
 * @return array
 */
function getAllocatedStudents() {
    global $conn;
    $query = "SELECT s.fullname, s.email, s.gender, s.level, s.medical_status,
                     h.hostel_name, r.room_number, a.date_allocated
              FROM allocations a
              JOIN students s ON a.student_id = s.student_id
              JOIN rooms r ON a.room_id = r.room_id
              JOIN hostels h ON r.hostel_id = h.hostel_id
              ORDER BY h.hostel_name, r.room_number ASC";
    $result = mysqli_query($conn, $query);
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    return $students;
}

/**
 * Get all waitlisted students
 * @return array
 */
function getWaitlistedStudents() {
    global $conn;
    $query = "SELECT fullname, email, gender, level, medical_status 
              FROM students 
              WHERE allocation_status = 'waitlisted' AND email != '" . ADMIN_EMAIL . "'
              ORDER BY level DESC, medical_status DESC";
    $result = mysqli_query($conn, $query);
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    return $students;
}

/**
 * Get occupancy summary per hostel
 * @return array
 */
function getOccupancySummary() {
    global $conn;
    $query = "SELECT h.hostel_name, h.gender_category, h.total_rooms,
                     COALESCE(SUM(r.capacity), 0) as total_capacity,
                     COALESCE(SUM(r.occupied_slots), 0) as total_occupied
              FROM hostels h
              LEFT JOIN rooms r ON h.hostel_id = r.hostel_id
              GROUP BY h.hostel_id
              ORDER BY h.hostel_name ASC";
    $result = mysqli_query($conn, $query);
    $summary = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['occupancy_percent'] = $row['total_capacity'] > 0 
            ? round(($row['total_occupied'] / $row['total_capacity']) * 100, 1) 
            : 0;
        $summary[] = $row;
    }
    return $summary;
}

// =============================================
// STATISTICS FUNCTIONS
// =============================================

/**
 * Get dashboard statistics for admin
 * @return array
 */
function getAdminStats() {
    global $conn;
    
    $stats = [];
    
    // Total students (excluding admin)
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE email != '" . ADMIN_EMAIL . "'");
    $stats['total_students'] = mysqli_fetch_assoc($result)['count'];
    
    // Pending requests
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE allocation_status = 'pending'");
    $stats['pending_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // Allocated students
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE allocation_status = 'allocated'");
    $stats['allocated_students'] = mysqli_fetch_assoc($result)['count'];
    
    // Total capacity
    $result = mysqli_query($conn, "SELECT COALESCE(SUM(capacity), 0) as total_cap, COALESCE(SUM(occupied_slots), 0) as total_occ FROM rooms");
    $row = mysqli_fetch_assoc($result);
    $stats['total_capacity'] = $row['total_cap'];
    $stats['remaining_capacity'] = $row['total_cap'] - $row['total_occ'];
    
    // Pending feedback
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'");
    $stats['pending_feedback'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

// =============================================
// FEEDBACK FUNCTIONS
// =============================================

/**
 * Submit new feedback
 * @param int $studentId
 * @param string $subject
 * @param string $message
 * @param string $type
 * @return bool
 */
function submitFeedback($studentId, $subject, $message, $type) {
    global $conn;
    $studentId = (int)$studentId;
    $subject = mysqli_real_escape_string($conn, trim($subject));
    $message = mysqli_real_escape_string($conn, trim($message));
    $type = mysqli_real_escape_string($conn, $type);
    
    $query = "INSERT INTO feedback (student_id, subject, message, type, status) 
              VALUES ($studentId, '$subject', '$message', '$type', 'pending')";
    return mysqli_query($conn, $query);
}

/**
 * Get feedback by student ID
 * @param int $studentId
 * @return array
 */
function getStudentFeedback($studentId) {
    global $conn;
    $studentId = (int)$studentId;
    $query = "SELECT * FROM feedback WHERE student_id = $studentId ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    $feedback = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $feedback[] = $row;
    }
    return $feedback;
}

/**
 * Get a single feedback entry by ID
 * @param int $feedbackId
 * @return array|null
 */
function getFeedbackById($feedbackId) {
    global $conn;
    $feedbackId = (int)$feedbackId;
    $query = "SELECT f.*, s.fullname, s.email 
              FROM feedback f 
              JOIN students s ON f.student_id = s.student_id 
              WHERE f.feedback_id = $feedbackId LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
}

/**
 * Get all feedback with student info (for admin)
 * Supports filtering and pagination
 * @param string $statusFilter
 * @param string $typeFilter
 * @param string $searchTerm
 * @param int $page
 * @return array ['data' => array, 'total' => int, 'pages' => int]
 */
function getAllFeedback($statusFilter = '', $typeFilter = '', $searchTerm = '', $page = 1) {
    global $conn;
    
    $where = "1=1";
    
    if (!empty($statusFilter)) {
        $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
        $where .= " AND f.status = '$statusFilter'";
    }
    
    if (!empty($typeFilter)) {
        $typeFilter = mysqli_real_escape_string($conn, $typeFilter);
        $where .= " AND f.type = '$typeFilter'";
    }
    
    if (!empty($searchTerm)) {
        $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
        $where .= " AND (f.subject LIKE '%$searchTerm%' OR s.fullname LIKE '%$searchTerm%')";
    }
    
    // Count total
    $countQuery = "SELECT COUNT(*) as total FROM feedback f JOIN students s ON f.student_id = s.student_id WHERE $where";
    $countResult = mysqli_query($conn, $countQuery);
    $total = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($total / ROWS_PER_PAGE);
    
    // Get data
    $offset = ($page - 1) * ROWS_PER_PAGE;
    $query = "SELECT f.*, s.fullname, s.email 
              FROM feedback f 
              JOIN students s ON f.student_id = s.student_id 
              WHERE $where 
              ORDER BY f.created_at DESC 
              LIMIT $offset, " . ROWS_PER_PAGE;
    $result = mysqli_query($conn, $query);
    $feedback = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $feedback[] = $row;
    }
    
    return ['data' => $feedback, 'total' => $total, 'pages' => $totalPages];
}

/**
 * Update feedback status and admin response
 * @param int $feedbackId
 * @param string $status
 * @param string $adminResponse
 * @return bool
 */
function updateFeedback($feedbackId, $status, $adminResponse) {
    global $conn;
    $feedbackId = (int)$feedbackId;
    $status = mysqli_real_escape_string($conn, $status);
    $adminResponse = mysqli_real_escape_string($conn, trim($adminResponse));
    
    $query = "UPDATE feedback SET status = '$status', admin_response = '$adminResponse', updated_at = NOW() 
              WHERE feedback_id = $feedbackId";
    return mysqli_query($conn, $query);
}

/**
 * Delete feedback
 * @param int $feedbackId
 * @return bool
 */
function deleteFeedback($feedbackId) {
    global $conn;
    $feedbackId = (int)$feedbackId;
    $query = "DELETE FROM feedback WHERE feedback_id = $feedbackId";
    return mysqli_query($conn, $query);
}

// =============================================
// UTILITY FUNCTIONS
// =============================================

/**
 * Sanitize output for HTML display
 * @param string $str
 * @return string
 */
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date string
 * @param string $dateStr
 * @param string $format
 * @return string
 */
function formatDate($dateStr, $format = 'M d, Y h:i A') {
    return date($format, strtotime($dateStr));
}

/**
 * Get CSS class for allocation status badges
 * @param string $status
 * @return string
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'not_requested': return 'badge-grey';
        case 'pending': return 'badge-yellow';
        case 'allocated': return 'badge-green';
        case 'waitlisted': return 'badge-red';
        case 'acknowledged': return 'badge-blue';
        case 'resolved': return 'badge-green';
        case 'closed': return 'badge-grey';
        default: return 'badge-grey';
    }
}
