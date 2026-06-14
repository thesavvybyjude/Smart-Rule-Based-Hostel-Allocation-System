<?php
/**
 * Manage Hostels (Admin)
 * CRUD operations for hostels
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$errors = [];
$editHostel = null;

// Handle Add Hostel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hostel'])) {
    $name = trim($_POST['hostel_name'] ?? '');
    $gender = $_POST['gender_category'] ?? '';
    
    if (strlen($name) < 2) {
        $errors[] = "Hostel name must be at least 2 characters.";
    }
    if (!in_array($gender, ['Male', 'Female'])) {
        $errors[] = "Please select a valid gender category.";
    }
    
    if (empty($errors)) {
        if (addHostel($name, $gender)) {
            setFlashSuccess("Hostel '$name' added successfully.");
            header("Location: " . BASE_URL . "/admin/manage_hostels.php");
            exit();
        } else {
            $errors[] = "Failed to add hostel. It may already exist.";
        }
    }
}

// Handle Edit Hostel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_hostel'])) {
    $hostelId = (int)$_POST['hostel_id'];
    $name = trim($_POST['hostel_name'] ?? '');
    $gender = $_POST['gender_category'] ?? '';
    
    if (strlen($name) < 2) {
        $errors[] = "Hostel name must be at least 2 characters.";
    }
    if (!in_array($gender, ['Male', 'Female'])) {
        $errors[] = "Please select a valid gender category.";
    }
    
    if (empty($errors)) {
        if (updateHostel($hostelId, $name, $gender)) {
            setFlashSuccess("Hostel updated successfully.");
            header("Location: " . BASE_URL . "/admin/manage_hostels.php");
            exit();
        } else {
            $errors[] = "Failed to update hostel.";
        }
    }
}

// Handle Delete Hostel
if (isset($_GET['delete'])) {
    $hostelId = (int)$_GET['delete'];
    $result = deleteHostel($hostelId);
    
    if ($result === true) {
        setFlashSuccess("Hostel deleted successfully.");
    } else {
        setFlashError($result);
    }
    header("Location: " . BASE_URL . "/admin/manage_hostels.php");
    exit();
}

// Load hostel for editing
if (isset($_GET['edit'])) {
    $editHostel = getHostelById((int)$_GET['edit']);
}

// Load all hostels
$hostels = getAllHostels();

$pageTitle = 'Manage Hostels';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Manage Hostels</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo sanitize($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add / Edit Hostel Form -->
<div class="card">
    <h3><?php echo $editHostel ? 'Edit Hostel' : 'Add New Hostel'; ?></h3>
    <form method="POST" action="" class="form-inline">
        <?php if ($editHostel): ?>
            <input type="hidden" name="hostel_id" value="<?php echo $editHostel['hostel_id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="hostel_name">Hostel Name</label>
            <input type="text" id="hostel_name" name="hostel_name" required
                   value="<?php echo $editHostel ? sanitize($editHostel['hostel_name']) : ''; ?>"
                   placeholder="e.g., Independence Hall">
        </div>
        
        <div class="form-group">
            <label for="gender_category">Gender Category</label>
            <select id="gender_category" name="gender_category" required>
                <option value="">-- Select --</option>
                <option value="Male" <?php echo ($editHostel && $editHostel['gender_category'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($editHostel && $editHostel['gender_category'] === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>&nbsp;</label>
            <?php if ($editHostel): ?>
                <button type="submit" name="edit_hostel" class="btn btn-success">Update Hostel</button>
                <a href="<?php echo BASE_URL; ?>/admin/manage_hostels.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_hostel" class="btn btn-primary">Add Hostel</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Hostels List -->
<div class="card">
    <h3>All Hostels (<?php echo count($hostels); ?>)</h3>
    
    <?php if (empty($hostels)): ?>
        <p>No hostels have been added yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hostel Name</th>
                        <th>Gender Category</th>
                        <th>Total Rooms</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hostels as $index => $hostel): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo sanitize($hostel['hostel_name']); ?></td>
                            <td><span class="badge <?php echo $hostel['gender_category'] === 'Male' ? 'badge-blue' : 'badge-red'; ?>"><?php echo $hostel['gender_category']; ?></span></td>
                            <td><?php echo $hostel['total_rooms']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $hostel['hostel_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?php echo $hostel['hostel_id']; ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirmDelete('hostel')">Delete</a>
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
