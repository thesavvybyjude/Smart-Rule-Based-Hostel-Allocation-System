<?php
/**
 * Submit Feedback Page (Student)
 * Allows students to submit bug reports, suggestions, and other feedback
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireStudent();

$errors = [];
$formData = ['subject' => '', 'type' => '', 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['type'] = $_POST['type'] ?? '';
    $formData['message'] = trim($_POST['message'] ?? '');
    
    // Validation
    if (strlen($formData['subject']) < 3) {
        $errors[] = "Subject must be at least 3 characters.";
    }
    
    if (!in_array($formData['type'], ['bug', 'error', 'suggestion', 'other'])) {
        $errors[] = "Please select a valid feedback type.";
    }
    
    if (strlen($formData['message']) < 10) {
        $errors[] = "Message must be at least 10 characters.";
    }
    
    if (empty($errors)) {
        $success = submitFeedback(
            getCurrentUserId(),
            $formData['subject'],
            $formData['message'],
            $formData['type']
        );
        
        if ($success) {
            setFlashSuccess("Feedback submitted successfully! Thank you for your input.");
            header("Location: " . BASE_URL . "/student/dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to submit feedback. Please try again.";
        }
    }
}

$pageTitle = 'Submit Feedback';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-container">
    <div class="card">
        <h1 class="page-title">Submit Feedback</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateFeedbackForm(this)" id="feedbackForm">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="<?php echo sanitize($formData['subject']); ?>" required placeholder="Brief title for your feedback">
            </div>
            
            <div class="form-group">
                <label for="type">Feedback Type</label>
                <select id="type" name="type" required>
                    <option value="">-- Select Type --</option>
                    <option value="bug" <?php echo $formData['type'] === 'bug' ? 'selected' : ''; ?>>Bug Report</option>
                    <option value="error" <?php echo $formData['type'] === 'error' ? 'selected' : ''; ?>>Error Report</option>
                    <option value="suggestion" <?php echo $formData['type'] === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                    <option value="other" <?php echo $formData['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required placeholder="Describe your feedback in detail..."><?php echo sanitize($formData['message']); ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%;">Submit Feedback</button>
        </form>
        
        <p style="text-align:center; margin-top:15px;">
            <a href="<?php echo BASE_URL; ?>/student/view_feedback.php">View my feedback history</a> | 
            <a href="<?php echo BASE_URL; ?>/student/dashboard.php">Back to Dashboard</a>
        </p>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
