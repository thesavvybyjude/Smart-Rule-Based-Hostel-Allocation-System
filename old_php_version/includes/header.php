<?php
/**
 * Common Header Template
 * Included at the top of every page for consistent navigation
 */

// Ensure config and auth are loaded
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart Rule-Based Hostel Allocation System for tertiary institutions">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' | ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="nav-overlay" id="navOverlay"></div>
    <header class="main-header">
        <div class="container header-content">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo"><?php echo APP_NAME; ?></a>
            
            <?php if (isLoggedIn()): ?>
            <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" id="mainNav">
                <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/admin/manage_hostels.php">Hostels</a>
                    <a href="<?php echo BASE_URL; ?>/admin/manage_rooms.php">Rooms</a>
                    <a href="<?php echo BASE_URL; ?>/admin/reports.php">Reports</a>
                    <a href="<?php echo BASE_URL; ?>/admin/manage_feedback.php">Feedback</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/student/dashboard.php">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/student/feedback.php">Submit Feedback</a>
                    <a href="<?php echo BASE_URL; ?>/student/view_feedback.php">My Feedback</a>
                <?php endif; ?>
                <span class="nav-user">Welcome, <?php echo sanitize(getCurrentUserName()); ?></span>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">Logout</a>
            </nav>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="container main-content">
        <?php echo displayFlashMessages(); ?>
