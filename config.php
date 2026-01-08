<?php
/**
 * Main Configuration File
 * Optimized for hosting
 */

// Load environment config
$env = require_once __DIR__ . '/env.php';

// Set timezone
date_default_timezone_set($env['timezone']);

// Error handling based on environment
if ($env['environment'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if ($env['environment'] === 'production') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Security headers for production
if ($env['environment'] === 'production') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}

// Constants
define('SITE_NAME', $env['site_name']);
define('SITE_URL', rtrim($env['site_url'], '/'));
define('BASE_PATH', __DIR__);
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('IS_PRODUCTION', $env['environment'] === 'production');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host={$env['db_host']};dbname={$env['db_name']};charset=utf8mb4",
        $env['db_user'],
        $env['db_pass'],
        [
            PDO::ATTR_ERRMODE => IS_PRODUCTION ? PDO::ERRMODE_SILENT : PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    if (IS_PRODUCTION) {
        die("Database connection error. Please try again later.");
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Helper Functions
function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function asset($path) {
    return SITE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

// CSRF Token functions
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}
?>
