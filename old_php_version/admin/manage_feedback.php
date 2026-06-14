<?php
/**
 * Manage Feedback (Admin)
 * List, filter, and manage all student feedback submissions
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

// Handle Delete
if (isset($_GET['delete'])) {
    $feedbackId = (int)$_GET['delete'];
    if (deleteFeedback($feedbackId)) {
        setFlashSuccess("Feedback deleted successfully.");
    } else {
        setFlashError("Failed to delete feedback.");
    }
    // Preserve filters in redirect
    $redirectParams = [];
    if (!empty($_GET['status'])) $redirectParams[] = 'status=' . urlencode($_GET['status']);
    if (!empty($_GET['type'])) $redirectParams[] = 'type=' . urlencode($_GET['type']);
    if (!empty($_GET['search'])) $redirectParams[] = 'search=' . urlencode($_GET['search']);
    $redirectUrl = BASE_URL . "/admin/manage_feedback.php" . (!empty($redirectParams) ? '?' . implode('&', $redirectParams) : '');
    header("Location: $redirectUrl");
    exit();
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$searchTerm = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

// Fetch feedback
$feedbackData = getAllFeedback($statusFilter, $typeFilter, $searchTerm, $page);
$feedbackList = $feedbackData['data'];
$totalPages = $feedbackData['pages'];
$totalCount = $feedbackData['total'];

$pageTitle = 'Manage Feedback';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Manage Feedback (<?php echo $totalCount; ?> total)</h1>

<!-- Filters -->
<div class="card">
    <form method="GET" action="" class="form-inline">
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="acknowledged" <?php echo $statusFilter === 'acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="">All Types</option>
                <option value="bug" <?php echo $typeFilter === 'bug' ? 'selected' : ''; ?>>Bug</option>
                <option value="error" <?php echo $typeFilter === 'error' ? 'selected' : ''; ?>>Error</option>
                <option value="suggestion" <?php echo $typeFilter === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                <option value="other" <?php echo $typeFilter === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" value="<?php echo sanitize($searchTerm); ?>" placeholder="Student name or subject...">
        </div>
        
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?php echo BASE_URL; ?>/admin/manage_feedback.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Feedback Table -->
<div class="card">
    <?php if (empty($feedbackList)): ?>
        <p>No feedback found matching your criteria.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbackList as $fb): ?>
                        <tr>
                            <td><?php echo $fb['feedback_id']; ?></td>
                            <td><?php echo sanitize($fb['fullname']); ?></td>
                            <td><?php echo sanitize(substr($fb['subject'], 0, 40)); ?><?php echo strlen($fb['subject']) > 40 ? '...' : ''; ?></td>
                            <td><span class="badge badge-blue"><?php echo strtoupper($fb['type']); ?></span></td>
                            <td><span class="badge <?php echo getStatusBadgeClass($fb['status']); ?>"><?php echo strtoupper($fb['status']); ?></span></td>
                            <td><?php echo formatDate($fb['created_at'], 'M d, Y'); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/admin/view_feedback_detail.php?id=<?php echo $fb['feedback_id']; ?>" class="btn btn-primary btn-sm">View/Reply</a>
                                <a href="?delete=<?php echo $fb['feedback_id']; ?>&status=<?php echo urlencode($statusFilter); ?>&type=<?php echo urlencode($typeFilter); ?>&search=<?php echo urlencode($searchTerm); ?>" 
                                   class="btn btn-danger btn-sm" onclick="return confirmDelete('feedback')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $params = [];
                    if (!empty($statusFilter)) $params[] = 'status=' . urlencode($statusFilter);
                    if (!empty($typeFilter)) $params[] = 'type=' . urlencode($typeFilter);
                    if (!empty($searchTerm)) $params[] = 'search=' . urlencode($searchTerm);
                    $params[] = 'page=' . $i;
                    $url = '?' . implode('&', $params);
                    ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo $url; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<p style="margin-top: 15px;">
    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</p>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
