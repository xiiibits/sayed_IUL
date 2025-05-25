<?php
// simple_register_process.php - Simplified registration

header('Content-Type: application/json');
session_start();

// Database connection
$host = 'localhost';
$dbname = 'iul_reg';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get form data
$user_username = trim($_POST['new-username'] ?? '');
$user_password = $_POST['new-password'] ?? '';
$user_email = trim($_POST['email'] ?? '');
$user_email_again = trim($_POST['email-again'] ?? '');
$user_firstname = trim($_POST['firstname'] ?? '');
$user_lastname = trim($_POST['lastname'] ?? '');
$user_city = trim($_POST['city'] ?? '');
$user_country = $_POST['country'] ?? '';

// Basic validation
if (empty($user_username) || empty($user_password) || empty($user_email) || 
    empty($user_email_again) || empty($user_firstname) || empty($user_lastname)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

if ($user_email !== $user_email_again) {
    echo json_encode(['success' => false, 'message' => 'Email addresses do not match']);
    exit();
}

if (strlen($user_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit();
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

try {
    // Check if username exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$user_username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$user_email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, city, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $user_username,
        $user_email,
        $hashed_password,
        $user_firstname,
        $user_lastname,
        $user_city ?: null,
        $user_country ?: null
    ]);
    
    if ($result) {
        $user_id = $pdo->lastInsertId();
        
        // Auto login
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $user_username;
        $_SESSION['email'] = $user_email;
        $_SESSION['first_name'] = $user_firstname;
        $_SESSION['last_name'] = $user_lastname;
        $_SESSION['user_type'] = 'student';
        $_SESSION['login_time'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully!',
            'user' => [
                'username' => $user_username,
                'first_name' => $user_firstname,
                'last_name' => $user_lastname
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>