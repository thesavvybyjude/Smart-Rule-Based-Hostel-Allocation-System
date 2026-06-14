<?php
/**
 * Login Page
 * Handles student and admin authentication
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$error = '';
$emailValue = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($emailValue) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $user = authenticateUser($emailValue, $password);
        
        if ($user) {
            setLoginSession($user);
            
            if (isAdmin()) {
                header("Location: " . BASE_URL . "/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="card">
        <h1 class="page-title">Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateLoginForm(this)" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo sanitize($emailValue); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>
        
        <p style="text-align:center; margin-top:15px;">
            Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register here</a>
        </p>
        
        <hr style="margin: 15px 0;">
        <p style="text-align:center; font-size:0.85rem; color:#64748b;">
            Admin login: Use <strong>admin@hostel.com</strong>
        </p>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
