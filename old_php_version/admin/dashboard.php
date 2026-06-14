<?php
/**
 * Admin Dashboard
 * Shows system statistics and quick action buttons
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$stats = getAdminStats();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Admin Dashboard</h1>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_students']; ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #f59e0b;"><?php echo $stats['pending_requests']; ?></div>
        <div class="stat-label">Pending Requests</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #16a34a;"><?php echo $stats['allocated_students']; ?></div>
        <div class="stat-label">Allocated Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #2563eb;"><?php echo $stats['remaining_capacity']; ?></div>
        <div class="stat-label">Remaining Capacity</div>
    </div>
</div>

<!-- Pending Feedback Alert -->
<?php if ($stats['pending_feedback'] > 0): ?>
    <div class="alert alert-error" style="text-align:center;">
        You have <strong><?php echo $stats['pending_feedback']; ?></strong> pending feedback submission(s) to review. 
        <a href="<?php echo BASE_URL; ?>/admin/manage_feedback.php?status=pending">View now →</a>
    </div>
<?php endif; ?>

<!-- Action Buttons -->
<h2 style="margin-bottom: 15px;">Quick Actions</h2>
<div class="actions-grid">
    <a href="<?php echo BASE_URL; ?>/admin/run_allocation.php" class="action-card" style="border-left: 4px solid #16a34a;">
        <span class="action-title">Run Allocation</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Execute the rule-based allocation engine</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/admin/manage_hostels.php" class="action-card" style="border-left: 4px solid #2563eb;">
        <span class="action-title">Manage Hostels</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Add, edit, or remove hostels</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/admin/manage_rooms.php" class="action-card" style="border-left: 4px solid #8b5cf6;">
        <span class="action-title">Manage Rooms</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Add, edit, or remove rooms</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="action-card" style="border-left: 4px solid #f59e0b;">
        <span class="action-title">View Reports</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Allocated, waitlisted, occupancy reports</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/admin/manage_feedback.php" class="action-card" style="border-left: 4px solid #ef4444;">
        <span class="action-title">Manage Feedback</span>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Review and respond to student feedback</p>
    </a>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
