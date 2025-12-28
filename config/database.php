<?php
/**
 * Database Configuration
 * Modify these values according to your setup
 */

define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_NAME', 'inventory_db');   // Database name
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', ''); 

/**
 * Create database connection
 * Returns mysqli connection object or dies with error
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Check if user is logged in
 * Returns user data if logged in, redirects to login if not
 */
function requireLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    return $_SESSION;
}

/**
 * Sanitize output for HTML display
 */
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>