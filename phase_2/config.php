<?php
// config.php - Database configuration file

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root'); // Change to your database username
define('DB_PASSWORD', ''); // Change to your database password
define('DB_NAME', 'iul_reg'); // Your database name

// Security settings
define('SESSION_EXPIRE_TIME', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);

// Site configuration
define('SITE_URL', 'http://localhost/your-project/'); // Change to your site URL
define('SITE_NAME', 'IUL - International University of Lebanon');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with secure settings
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => SESSION_EXPIRE_TIME,
        'cookie_secure' => false, // Set to true if using HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Database connection class
class Database {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USERNAME,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Initialize database connection
$db = new Database();

// Helper functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // At least 8 characters, 1 digit, 1 lowercase, 1 uppercase, 1 special character
    return preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[*\-#@$!%^&(){}[\]:;<>,.?\/~_+\\|])[a-zA-Z0-9*\-#@$!%^&(){}[\]:;<>,.?\/~_+\\|]{8,}$/', $password);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateSessionId() {
    return bin2hex(random_bytes(32));
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

function logout() {
    session_destroy();
    header('Location: moodle.html');
    exit();
}
?>