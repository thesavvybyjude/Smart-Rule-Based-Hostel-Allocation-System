<?php
/**
 * Student Registration Page
 * Handles new student account creation
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$errors = [];
$formData = [
    'fullname' => '',
    'email' => '',
    'gender' => '',
    'level' => '',
    'medical_status' => false
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['fullname'] = trim($_POST['fullname'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $formData['gender'] = $_POST['gender'] ?? '';
    $formData['level'] = $_POST['level'] ?? '';
    $formData['medical_status'] = isset($_POST['medical_status']);
    
    // Validation
    if (strlen($formData['fullname']) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }
    
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if ($formData['email'] === ADMIN_EMAIL) {
        $errors[] = "This email address is reserved.";
    }
    
    if (emailExists($formData['email'])) {
        $errors[] = "An account with this email already exists.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    if (!in_array($formData['gender'], ['Male', 'Female'])) {
        $errors[] = "Please select a valid gender.";
    }
    
    if (!in_array((int)$formData['level'], [100, 200, 300, 400])) {
        $errors[] = "Please select a valid level.";
    }
    
    // If no errors, register
    if (empty($errors)) {
        $success = registerStudent(
            $formData['fullname'],
            $formData['email'],
            $password,
            $formData['gender'],
            (int)$formData['level'],
            $formData['medical_status']
        );
        
        if ($success) {
            setFlashSuccess("Registration successful! Please log in.");
            header("Location: " . BASE_URL . "/login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="card">
        <h1 class="page-title">Student Registration</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateRegistrationForm(this)" id="registerForm">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo sanitize($formData['fullname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo sanitize($formData['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male" <?php echo $formData['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $formData['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="level">Level</label>
                <select id="level" name="level" required>
                    <option value="">-- Select Level --</option>
                    <option value="100" <?php echo $formData['level'] == '100' ? 'selected' : ''; ?>>100 Level</option>
                    <option value="200" <?php echo $formData['level'] == '200' ? 'selected' : ''; ?>>200 Level</option>
                    <option value="300" <?php echo $formData['level'] == '300' ? 'selected' : ''; ?>>300 Level</option>
                    <option value="400" <?php echo $formData['level'] == '400' ? 'selected' : ''; ?>>400 Level</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="medical_status" <?php echo $formData['medical_status'] ? 'checked' : ''; ?>>
                    I have a medical condition (Priority allocation)
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
        </form>
        
        <p style="text-align:center; margin-top:15px;">
            Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login here</a>
        </p>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
