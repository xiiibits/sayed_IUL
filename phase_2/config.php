<?php
// config.php - Database configuration

// Database settings - CHANGE THESE TO MATCH YOUR SETUP
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');          // XAMPP default: 'root', MAMP: 'root'
define('DB_PASSWORD', '');              // XAMPP default: '', MAMP: 'root'
define('DB_NAME', 'iul_reg');           // Your database name

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[*\-#@$!%^&(){}[\]:;<>,.?\/~_+\\|])[a-zA-Z0-9*\-#@$!%^&(){}[\]:;<>,.?\/~_+\\|]{8,}$/', $password);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: moodle.html');
        exit();
    }
}
?>