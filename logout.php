<?php
/**
 * Logout Page
 * Destroys session and redirects to login
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

logoutUser();

// Start a new session for the flash message
session_start();
setFlashSuccess("You have been logged out successfully.");

header("Location: " . BASE_URL . "/login.php");
exit();
