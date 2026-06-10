<?php
/**
 * View Feedback History (Student)
 * Shows all feedback submitted by the current student with admin responses
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireStudent();

$feedbackList = getStudentFeedback(getCurrentUserId());

// Handle detail view
$selectedFeedback = null;
if (isset($_GET['id'])) {
    $feedbackId = (int)$_GET['id'];
    $fb = getFeedbackById($feedbackId);
    // Make sure the feedback belongs to this student
    if ($fb && $fb['student_id'] == getCurrentUserId()) {
        $selectedFeedback = $fb;
    }
}

$pageTitle = 'My Feedback';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">My Feedback History</h1>

<?php if ($selectedFeedback): ?>
    <!-- Detail View -->
    <div class="card">
        <h3><?php echo sanitize($selectedFeedback['subject']); ?></h3>
        <p style="margin-bottom: 10px;">
            <span class="badge <?php echo getStatusBadgeClass($selectedFeedback['type']); ?>"><?php echo strtoupper($selectedFeedback['type']); ?></span>
            <span class="badge <?php echo getStatusBadgeClass($selectedFeedback['status']); ?>"><?php echo strtoupper($selectedFeedback['status']); ?></span>
            <span style="color: #64748b; font-size: 0.85rem; margin-left: 10px;">
                Submitted: <?php echo formatDate($selectedFeedback['created_at']); ?>
            </span>
        </p>
        
        <div style="background: #f8fafc; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <strong>Your Message:</strong>
            <p style="margin-top: 5px;"><?php echo nl2br(sanitize($selectedFeedback['message'])); ?></p>
        </div>
        
        <?php if (!empty($selectedFeedback['admin_response'])): ?>
            <div style="background: #f0f9ff; padding: 15px; border-radius: 4px; border-left: 4px solid #2563eb;">
                <strong>Admin Response:</strong>
                <p style="margin-top: 5px;"><?php echo nl2br(sanitize($selectedFeedback['admin_response'])); ?></p>
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 8px;">
                    Updated: <?php echo formatDate($selectedFeedback['updated_at']); ?>
                </p>
            </div>
        <?php else: ?>
            <p style="color: #64748b; font-style: italic;">No admin response yet.</p>
        <?php endif; ?>
        
        <p style="margin-top: 15px;">
            <a href="<?php echo BASE_URL; ?>/student/view_feedback.php" class="btn btn-secondary btn-sm">← Back to List</a>
        </p>
    </div>

<?php else: ?>
    <!-- List View -->
    <?php if (empty($feedbackList)): ?>
        <div class="card" style="text-align: center;">
            <p>You haven't submitted any feedback yet.</p>
            <a href="<?php echo BASE_URL; ?>/student/feedback.php" class="btn btn-primary" style="margin-top: 10px;">Submit Feedback</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Admin Response</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbackList as $fb): ?>
                            <tr>
                                <td><?php echo sanitize($fb['subject']); ?></td>
                                <td><span class="badge badge-blue"><?php echo strtoupper($fb['type']); ?></span></td>
                                <td><span class="badge <?php echo getStatusBadgeClass($fb['status']); ?>"><?php echo strtoupper($fb['status']); ?></span></td>
                                <td><?php echo formatDate($fb['created_at'], 'M d, Y'); ?></td>
                                <td>
                                    <?php if (!empty($fb['admin_response'])): ?>
                                        <?php echo sanitize(substr($fb['admin_response'], 0, 50)); ?><?php echo strlen($fb['admin_response']) > 50 ? '...' : ''; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $fb['feedback_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<p style="margin-top: 15px;">
    <a href="<?php echo BASE_URL; ?>/student/feedback.php" class="btn btn-primary">Submit New Feedback</a>
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</p>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
