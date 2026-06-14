<?php
/**
 * Manage Rooms (Admin)
 * CRUD operations for rooms
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$errors = [];
$editRoom = null;
$hostels = getAllHostels();

// Handle Add Room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $hostelId = (int)($_POST['hostel_id'] ?? 0);
    $roomNumber = trim($_POST['room_number'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    
    if ($hostelId <= 0) {
        $errors[] = "Please select a hostel.";
    }
    if (strlen($roomNumber) < 1) {
        $errors[] = "Room number is required.";
    }
    if ($capacity < 1) {
        $errors[] = "Capacity must be at least 1.";
    }
    
    if (empty($errors)) {
        if (addRoom($hostelId, $roomNumber, $capacity)) {
            setFlashSuccess("Room '$roomNumber' added successfully.");
            header("Location: " . BASE_URL . "/admin/manage_rooms.php");
            exit();
        } else {
            $errors[] = "Failed to add room. The room number may already exist in this hostel.";
        }
    }
}

// Handle Edit Room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_room'])) {
    $roomId = (int)$_POST['room_id'];
    $roomNumber = trim($_POST['room_number'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    
    if (strlen($roomNumber) < 1) {
        $errors[] = "Room number is required.";
    }
    if ($capacity < 1) {
        $errors[] = "Capacity must be at least 1.";
    }
    
    if (empty($errors)) {
        $result = updateRoom($roomId, $roomNumber, $capacity);
        if ($result === true) {
            setFlashSuccess("Room updated successfully.");
            header("Location: " . BASE_URL . "/admin/manage_rooms.php");
            exit();
        } else {
            $errors[] = $result;
        }
    }
}

// Handle Delete Room
if (isset($_GET['delete'])) {
    $roomId = (int)$_GET['delete'];
    $result = deleteRoom($roomId);
    
    if ($result === true) {
        setFlashSuccess("Room deleted successfully.");
    } else {
        setFlashError($result);
    }
    header("Location: " . BASE_URL . "/admin/manage_rooms.php");
    exit();
}

// Load room for editing
if (isset($_GET['edit'])) {
    $editRoom = getRoomById((int)$_GET['edit']);
}

// Load all rooms
$rooms = getAllRooms();

$pageTitle = 'Manage Rooms';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Manage Rooms</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo sanitize($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add / Edit Room Form -->
<div class="card">
    <h3><?php echo $editRoom ? 'Edit Room' : 'Add New Room'; ?></h3>
    <form method="POST" action="" class="form-inline">
        <?php if ($editRoom): ?>
            <input type="hidden" name="room_id" value="<?php echo $editRoom['room_id']; ?>">
        <?php endif; ?>
        
        <?php if (!$editRoom): ?>
        <div class="form-group">
            <label for="hostel_id">Hostel</label>
            <select id="hostel_id" name="hostel_id" required>
                <option value="">-- Select Hostel --</option>
                <?php foreach ($hostels as $hostel): ?>
                    <option value="<?php echo $hostel['hostel_id']; ?>">
                        <?php echo sanitize($hostel['hostel_name']); ?> (<?php echo $hostel['gender_category']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <div class="form-group">
                <label>Hostel</label>
                <input type="text" value="<?php echo sanitize($editRoom['hostel_name']); ?>" disabled>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="room_number">Room Number</label>
            <input type="text" id="room_number" name="room_number" required
                   value="<?php echo $editRoom ? sanitize($editRoom['room_number']) : ''; ?>"
                   placeholder="e.g., A101">
        </div>
        
        <div class="form-group">
            <label for="capacity">Capacity</label>
            <input type="number" id="capacity" name="capacity" required min="1" max="20"
                   value="<?php echo $editRoom ? $editRoom['capacity'] : '2'; ?>">
        </div>
        
        <div class="form-group">
            <label>&nbsp;</label>
            <?php if ($editRoom): ?>
                <button type="submit" name="edit_room" class="btn btn-success">Update Room</button>
                <a href="<?php echo BASE_URL; ?>/admin/manage_rooms.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_room" class="btn btn-primary">Add Room</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Rooms List -->
<div class="card">
    <h3>All Rooms (<?php echo count($rooms); ?>)</h3>
    
    <?php if (empty($rooms)): ?>
        <p>No rooms have been added yet. <a href="<?php echo BASE_URL; ?>/admin/manage_hostels.php">Add hostels first</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hostel</th>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $index => $room): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo sanitize($room['hostel_name']); ?></td>
                            <td><?php echo sanitize($room['room_number']); ?></td>
                            <td><?php echo $room['capacity']; ?></td>
                            <td><?php echo $room['occupied_slots']; ?></td>
                            <td><?php echo $room['capacity'] - $room['occupied_slots']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $room['room_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?php echo $room['room_id']; ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirmDelete('room')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
