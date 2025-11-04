<?php
/**
 * Configuration File - Enhanced Security
 * Database connection, Session management, Security settings
 * Updated: December 2025
 */

// Set UTF-8 encoding for all pages
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Error reporting (tắt trong production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'shop_db');
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    die('Không thể kết nối database. Vui lòng thử lại sau.');
}

// Set charset to prevent SQL injection
mysqli_set_charset($conn, 'utf8mb4');

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    session_start();
    
    // Regenerate session ID sau khi login (chống session fixation)
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Session timeout (30 phút = 1800 giây)
define('SESSION_TIMEOUT', 1800);

if (isset($_SESSION['LAST_ACTIVITY'])) {
    if ((time() - $_SESSION['LAST_ACTIVITY']) > SESSION_TIMEOUT) {
        // Session expired
        $user_id = $_SESSION['user_id'] ?? null;
        session_unset();
        session_destroy();
        
        if ($user_id) {
            header('location: auth/login.php?timeout=1');
            exit;
        }
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

// Include security functions
require_once __DIR__ . '/includes/db_functions.php';

// Include admin utilities if admin is logged in
if (isset($_SESSION['admin_id'])) {
    require_once __DIR__ . '/includes/admin_functions.php';
}

// Initialize CSRF token
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    generate_csrf_token();
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Define site constants
define('SITE_URL', 'http://localhost/flower-shop');
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/products/');
define('IMAGE_PATH', __DIR__ . '/assets/images/');
define('MAX_FILE_SIZE', 2097152); // 2MB

?>
