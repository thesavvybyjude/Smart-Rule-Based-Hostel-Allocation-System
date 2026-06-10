<?php
/**
 * Reports (Admin)
 * View allocated students, waitlisted students, and occupancy summary
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$allocatedStudents = getAllocatedStudents();
$waitlistedStudents = getWaitlistedStudents();
$occupancySummary = getOccupancySummary();

$pageTitle = 'Reports';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Reports</h1>

<!-- Tabs -->
<div class="tabs">
    <button class="tab-btn active" data-tab="tab-allocated">Allocated Students (<?php echo count($allocatedStudents); ?>)</button>
    <button class="tab-btn" data-tab="tab-waitlisted">Waitlisted Students (<?php echo count($waitlistedStudents); ?>)</button>
    <button class="tab-btn" data-tab="tab-occupancy">Occupancy Summary</button>
</div>

<!-- Tab 1: Allocated Students -->
<div id="tab-allocated" class="tab-content active">
    <div class="card">
        <?php if (empty($allocatedStudents)): ?>
            <p>No students have been allocated yet. <a href="<?php echo BASE_URL; ?>/admin/run_allocation.php">Run the allocation engine</a>.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Level</th>
                            <th>Gender</th>
                            <th>Medical Priority</th>
                            <th>Hostel</th>
                            <th>Room</th>
                            <th>Date Allocated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allocatedStudents as $index => $student): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo sanitize($student['fullname']); ?></td>
                                <td><?php echo $student['level']; ?></td>
                                <td><?php echo $student['gender']; ?></td>
                                <td><?php echo $student['medical_status'] ? '<span class="badge badge-red">Yes</span>' : 'No'; ?></td>
                                <td><?php echo sanitize($student['hostel_name']); ?></td>
                                <td><?php echo sanitize($student['room_number']); ?></td>
                                <td><?php echo formatDate($student['date_allocated'], 'M d, Y'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab 2: Waitlisted Students -->
<div id="tab-waitlisted" class="tab-content">
    <div class="card">
        <?php if (empty($waitlistedStudents)): ?>
            <p>No students are currently waitlisted.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th>Gender</th>
                            <th>Medical Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($waitlistedStudents as $index => $student): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo sanitize($student['fullname']); ?></td>
                                <td><?php echo sanitize($student['email']); ?></td>
                                <td><?php echo $student['level']; ?></td>
                                <td><?php echo $student['gender']; ?></td>
                                <td><?php echo $student['medical_status'] ? '<span class="badge badge-red">Yes</span>' : 'No'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab 3: Occupancy Summary -->
<div id="tab-occupancy" class="tab-content">
    <div class="card">
        <?php if (empty($occupancySummary)): ?>
            <p>No hostels found. <a href="<?php echo BASE_URL; ?>/admin/manage_hostels.php">Add hostels first</a>.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Hostel</th>
                            <th>Gender</th>
                            <th>Total Rooms</th>
                            <th>Total Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Occupancy %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($occupancySummary as $hostel): ?>
                            <tr>
                                <td><?php echo sanitize($hostel['hostel_name']); ?></td>
                                <td><span class="badge <?php echo $hostel['gender_category'] === 'Male' ? 'badge-blue' : 'badge-red'; ?>"><?php echo $hostel['gender_category']; ?></span></td>
                                <td><?php echo $hostel['total_rooms']; ?></td>
                                <td><?php echo $hostel['total_capacity']; ?></td>
                                <td><?php echo $hostel['total_occupied']; ?></td>
                                <td><?php echo $hostel['total_capacity'] - $hostel['total_occupied']; ?></td>
                                <td>
                                    <div style="background: #e2e8f0; border-radius: 4px; overflow: hidden; height: 20px; width: 100px; display: inline-block; vertical-align: middle;">
                                        <div style="background: <?php echo $hostel['occupancy_percent'] > 80 ? '#ef4444' : ($hostel['occupancy_percent'] > 50 ? '#f59e0b' : '#16a34a'); ?>; height: 100%; width: <?php echo $hostel['occupancy_percent']; ?>%;"></div>
                                    </div>
                                    <?php echo $hostel['occupancy_percent']; ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
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
