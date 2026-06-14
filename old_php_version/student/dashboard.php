<?php
/**
 * Student Dashboard
 * Shows allocation status, details, and action buttons
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireStudent();

$studentId = getCurrentUserId();
$student = getStudentById($studentId);
$allocation = null;

if ($student['allocation_status'] === 'allocated') {
    $allocation = getStudentAllocation($studentId);
}

// Handle hostel request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    if ($student['allocation_status'] === 'not_requested') {
        if (updateAllocationStatus($studentId, 'pending')) {
            setFlashSuccess("Your hostel request has been submitted successfully! You will be notified when allocation is complete.");
            header("Location: " . BASE_URL . "/student/dashboard.php");
            exit();
        } else {
            setFlashError("Failed to submit request. Please try again.");
        }
    } else {
        setFlashError("You have already submitted a request.");
    }
    // Refresh student data
    $student = getStudentById($studentId);
}

$pageTitle = 'Student Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Student Dashboard</h1>

<!-- Status Card -->
<div class="status-card status-<?php echo $student['allocation_status']; ?>">
    <h2>Your Allocation Status</h2>
    <span class="badge <?php echo getStatusBadgeClass($student['allocation_status']); ?>" style="font-size: 1rem; padding: 6px 16px;">
        <?php echo strtoupper(str_replace('_', ' ', $student['allocation_status'])); ?>
    </span>
    
    <?php if ($student['allocation_status'] === 'not_requested'): ?>
        <p style="margin-top: 15px;">You have not yet submitted a hostel request.</p>
        <form method="POST" action="" style="margin-top: 15px;">
            <button type="submit" name="submit_request" class="btn btn-primary" 
                    onclick="return confirm('Are you sure you want to submit a hostel request?')">
                Submit Hostel Request
            </button>
        </form>
    
    <?php elseif ($student['allocation_status'] === 'pending'): ?>
        <p style="margin-top: 15px;">Your request is pending. Please wait for the admin to run the allocation process.</p>
    
    <?php elseif ($student['allocation_status'] === 'allocated' && $allocation): ?>
        <div class="allocation-details" style="margin-top: 15px; text-align: left; display: inline-block;">
            <p><strong>Hostel:</strong> <?php echo sanitize($allocation['hostel_name']); ?></p>
            <p><strong>Room Number:</strong> <?php echo sanitize($allocation['room_number']); ?></p>
            <p><strong>Room Capacity:</strong> <?php echo sanitize($allocation['capacity']); ?> students</p>
            <p><strong>Date Allocated:</strong> <?php echo formatDate($allocation['date_allocated']); ?></p>
        </div>
    
    <?php elseif ($student['allocation_status'] === 'waitlisted'): ?>
        <p style="margin-top: 15px;">Unfortunately, all rooms are currently full. You have been placed on the waiting list.</p>
        <p style="color: #64748b; font-size: 0.9rem;">You will be allocated a room when space becomes available.</p>
    <?php endif; ?>
</div>

<!-- Student Info Card -->
<div class="card">
    <h3>Your Information</h3>
    <div class="allocation-details">
        <p><strong>Name:</strong> <?php echo sanitize($student['fullname']); ?></p>
        <p><strong>Email:</strong> <?php echo sanitize($student['email']); ?></p>
        <p><strong>Gender:</strong> <?php echo sanitize($student['gender']); ?></p>
        <p><strong>Level:</strong> <?php echo sanitize($student['level']); ?></p>
        <p><strong>Medical Priority:</strong> <?php echo $student['medical_status'] ? 'Yes' : 'No'; ?></p>
    </div>
</div>

<!-- Quick Links -->
<div class="actions-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <a href="<?php echo BASE_URL; ?>/student/feedback.php" class="action-card">
        <span class="action-title">Submit Feedback</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Report bugs or make suggestions</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/student/view_feedback.php" class="action-card">
        <span class="action-title">My Feedback</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">View your feedback history</p>
    </a>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
