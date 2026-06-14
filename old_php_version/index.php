<?php
/**
 * Landing Page (index.php)
 * Redirects based on session: admin → admin dashboard, student → student dashboard, else → login
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/student/dashboard.php");
    }
    exit();
}

// Not logged in → redirect to login
header("Location: " . BASE_URL . "/login.php");
exit();
