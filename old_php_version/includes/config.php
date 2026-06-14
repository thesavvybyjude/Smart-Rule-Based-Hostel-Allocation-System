<?php
/**
 * Configuration File
 * Database connection, session management, and global constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// Database Configuration
// =============================================
define('DB_HOST', 'localhost');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hostel_allocation');

// =============================================
// Application Constants
// =============================================
define('APP_NAME', 'Smart Hostel Allocation System');
define('APP_VERSION', '1.0.0');
define('ADMIN_EMAIL', 'admin@hostel.com');

// Priority scoring constants
define('PRIORITY_MEDICAL', 10);
define('PRIORITY_LEVEL_400', 5);
define('PRIORITY_LEVEL_300', 3);
define('PRIORITY_LEVEL_200', 2);
define('PRIORITY_LEVEL_100', 1);

// Pagination
define('ROWS_PER_PAGE', 20);

// =============================================
// Database Connection
// =============================================
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// =============================================
// Base URL Helper
// =============================================
// Determine the base path dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Calculate the base path from this config file's location relative to the document root.
// This file is always at: <project_root>/includes/config.php
// So the project root is one level up from this file's directory.
$projectRoot = realpath(__DIR__ . '/..');
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?: '.');

$basePath = '';
if ($projectRoot && $docRoot && strpos($projectRoot, $docRoot) === 0) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
}
// Remove trailing slash
$basePath = rtrim($basePath, '/');

define('BASE_URL', $protocol . '://' . $host . $basePath);

// =============================================
// Error Reporting (Development mode)
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
