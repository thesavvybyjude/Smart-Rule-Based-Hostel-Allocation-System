<?php
/**
 * Run Allocation (Admin)
 * Trigger the rule-based allocation engine with confirmation
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$allocationResult = null;
$confirmed = false;

// Handle confirmation and run
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_allocation'])) {
    $confirmed = true;
    $allocationResult = runAllocationEngine();
}

$pageTitle = 'Run Allocation';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Run Allocation Engine</h1>

<?php if ($allocationResult): ?>
    <!-- Results Screen -->
    <div class="card" style="text-align: center;">
        <h2 style="color: #16a34a; margin-bottom: 20px;">✓ Allocation Complete</h2>
        
        <div class="stats-grid" style="max-width: 400px; margin: 0 auto 20px;">
            <div class="stat-card">
                <div class="stat-value" style="color: #16a34a;"><?php echo $allocationResult['allocated']; ?></div>
                <div class="stat-label">Students Allocated</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ef4444;"><?php echo $allocationResult['waitlisted']; ?></div>
                <div class="stat-label">Students Waitlisted</div>
            </div>
        </div>
        
        <?php if (!empty($allocationResult['errors'])): ?>
            <div class="alert alert-error" style="text-align: left;">
                <strong>Errors encountered:</strong>
                <ul style="margin-top: 5px; padding-left: 20px;">
                    <?php foreach ($allocationResult['errors'] as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px;">
            <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="btn btn-primary">View Detailed Reports</a>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </p>
    </div>

<?php else: ?>
    <!-- Confirmation Screen -->
    <div class="card confirm-box">
        <h2 style="color: #f59e0b;">⚠ Warning</h2>
        <p style="margin-top: 15px;">
            Running the allocation engine will:
        </p>
        <ul style="text-align: left; max-width: 500px; margin: 15px auto; line-height: 2;">
            <li><strong>Clear</strong> all existing room allocations</li>
            <li><strong>Reset</strong> all room occupancy to 0</li>
            <li><strong>Re-allocate</strong> all students who have submitted requests</li>
            <li><strong>Apply</strong> priority rules (medical condition + level)</li>
        </ul>
        <p style="color: #ef4444; margin-bottom: 20px;">
            <strong>This action cannot be undone.</strong> Make sure all hostels and rooms are properly configured.
        </p>
        
        <form method="POST" action="">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary" style="margin-right: 10px;">Cancel</a>
            <button type="submit" name="confirm_allocation" class="btn btn-success"
                    onclick="return confirm('Are you absolutely sure you want to run the allocation engine?')">
                Proceed with Allocation
            </button>
        </form>
    </div>
    
    <!-- Algorithm Info -->
    <div class="card">
        <h3>Allocation Algorithm</h3>
        <p>The system uses the following priority scoring:</p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Criteria</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Medical Condition</td><td>+<?php echo PRIORITY_MEDICAL; ?> points</td></tr>
                    <tr><td>400 Level</td><td>+<?php echo PRIORITY_LEVEL_400; ?> points</td></tr>
                    <tr><td>300 Level</td><td>+<?php echo PRIORITY_LEVEL_300; ?> points</td></tr>
                    <tr><td>200 Level</td><td>+<?php echo PRIORITY_LEVEL_200; ?> points</td></tr>
                    <tr><td>100 Level</td><td>+<?php echo PRIORITY_LEVEL_100; ?> points</td></tr>
                </tbody>
            </table>
        </div>
        <p style="color: #64748b; font-size: 0.85rem;">
            Students are sorted by total priority score (highest first). Gender-matching rooms are assigned in order of availability.
        </p>
    </div>
<?php endif; ?>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
