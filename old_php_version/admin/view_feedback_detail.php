<?php
/**
 * View Feedback Detail & Reply (Admin)
 * View full feedback and submit admin response
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

// Get feedback ID
$feedbackId = (int)($_GET['id'] ?? 0);

if ($feedbackId <= 0) {
    setFlashError("Invalid feedback ID.");
    header("Location: " . BASE_URL . "/admin/manage_feedback.php");
    exit();
}

$feedback = getFeedbackById($feedbackId);

if (!$feedback) {
    setFlashError("Feedback not found.");
    header("Location: " . BASE_URL . "/admin/manage_feedback.php");
    exit();
}

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_response'])) {
    $newStatus = $_POST['status'] ?? $feedback['status'];
    $adminResponse = trim($_POST['admin_response'] ?? '');
    
    if (!in_array($newStatus, ['pending', 'acknowledged', 'resolved', 'closed'])) {
        setFlashError("Invalid status selected.");
    } else {
        if (updateFeedback($feedbackId, $newStatus, $adminResponse)) {
            setFlashSuccess("Feedback response saved successfully.");
            header("Location: " . BASE_URL . "/admin/manage_feedback.php");
            exit();
        } else {
            setFlashError("Failed to save response.");
        }
    }
    
    // Refresh feedback data
    $feedback = getFeedbackById($feedbackId);
}

$pageTitle = 'Feedback Detail';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Feedback Detail #<?php echo $feedback['feedback_id']; ?></h1>

<div class="two-col">
    <!-- Left Panel: Feedback Details -->
    <div class="card">
        <h3>Student Feedback</h3>
        
        <div class="allocation-details">
            <p><strong>Student:</strong> <?php echo sanitize($feedback['fullname']); ?></p>
            <p><strong>Email:</strong> <?php echo sanitize($feedback['email']); ?></p>
            <p><strong>Subject:</strong> <?php echo sanitize($feedback['subject']); ?></p>
            <p><strong>Type:</strong> <span class="badge badge-blue"><?php echo strtoupper($feedback['type']); ?></span></p>
            <p><strong>Status:</strong> <span class="badge <?php echo getStatusBadgeClass($feedback['status']); ?>"><?php echo strtoupper($feedback['status']); ?></span></p>
            <p><strong>Submitted:</strong> <?php echo formatDate($feedback['created_at']); ?></p>
            <?php if ($feedback['updated_at'] !== $feedback['created_at']): ?>
                <p><strong>Last Updated:</strong> <?php echo formatDate($feedback['updated_at']); ?></p>
            <?php endif; ?>
        </div>
        
        <hr style="margin: 15px 0;">
        
        <h4>Message:</h4>
        <div style="background: #f8fafc; padding: 15px; border-radius: 4px; margin-top: 10px;">
            <?php echo nl2br(sanitize($feedback['message'])); ?>
        </div>
    </div>
    
    <!-- Right Panel: Admin Response -->
    <div class="card">
        <h3>Admin Response</h3>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="status">Update Status</label>
                <select id="status" name="status" required>
                    <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="acknowledged" <?php echo $feedback['status'] === 'acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                    <option value="resolved" <?php echo $feedback['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="closed" <?php echo $feedback['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admin_response">Response Message</label>
                <textarea id="admin_response" name="admin_response" rows="8" 
                          placeholder="Type your response to the student..."><?php echo sanitize($feedback['admin_response'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" name="save_response" class="btn btn-success" style="width:100%;">
                Save Response & Update Status
            </button>
        </form>
    </div>
</div>

<p style="margin-top: 15px;">
    <a href="<?php echo BASE_URL; ?>/admin/manage_feedback.php" class="btn btn-secondary">← Back to Feedback List</a>
</p>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
