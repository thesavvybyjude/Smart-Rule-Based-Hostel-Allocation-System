<?php
/**
 * Authentication Helper Functions
 * Handles login verification, session checks, and access control
 */

require_once __DIR__ . '/config.php';

/**
 * Check if a user is currently logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['student_id']) && !empty($_SESSION['student_id']);
}

/**
 * Check if the logged-in user is an admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Require the user to be logged in. Redirects to login if not.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

/**
 * Require the user to be an admin. Redirects if not.
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['flash_error'] = "Access denied. Admin privileges required.";
        header("Location: " . BASE_URL . "/student/dashboard.php");
        exit();
    }
}

/**
 * Require the user to be a student (non-admin). Redirects if not.
 */
function requireStudent() {
    requireLogin();
    if (isAdmin()) {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
        exit();
    }
}

/**
 * Authenticate a user by email and password
 * @param string $email
 * @param string $password
 * @return array|false Returns user data array on success, false on failure
 */
function authenticateUser($email, $password) {
    global $conn;
    
    $email = mysqli_real_escape_string($conn, trim($email));
    $query = "SELECT student_id, fullname, email, password, gender, level, medical_status, allocation_status 
              FROM students WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}

/**
 * Set session variables after successful login
 * @param array $user User data from database
 */
function setLoginSession($user) {
    $_SESSION['student_id'] = $user['student_id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['gender'] = $user['gender'];
    $_SESSION['level'] = $user['level'];
    $_SESSION['is_admin'] = ($user['email'] === ADMIN_EMAIL);
}

/**
 * Destroy the current session and log out
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Get the current logged-in user's ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['student_id'] ?? null;
}

/**
 * Get the current logged-in user's full name
 * @return string|null
 */
function getCurrentUserName() {
    return $_SESSION['fullname'] ?? null;
}

/**
 * Set a flash message (success)
 * @param string $message
 */
function setFlashSuccess($message) {
    $_SESSION['flash_success'] = $message;
}

/**
 * Set a flash message (error)
 * @param string $message
 */
function setFlashError($message) {
    $_SESSION['flash_error'] = $message;
}

/**
 * Display and clear flash messages
 * @return string HTML for flash messages
 */
function displayFlashMessages() {
    $html = '';
    
    if (isset($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    
    if (isset($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-error">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    
    return $html;
}
