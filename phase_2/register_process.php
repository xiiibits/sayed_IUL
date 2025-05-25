<?php
// register_process.php - Handle registration requests

require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fallback to form data
        $username = sanitizeInput($_POST['new-username'] ?? '');
        $password = $_POST['new-password'] ?? '';
        $email = sanitizeInput($_POST['email'] ?? '');
        $emailAgain = sanitizeInput($_POST['email-again'] ?? '');
        $firstName = sanitizeInput($_POST['firstname'] ?? '');
        $lastName = sanitizeInput($_POST['lastname'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $country = sanitizeInput($_POST['country'] ?? '');
    } else {
        $username = sanitizeInput($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $email = sanitizeInput($input['email'] ?? '');
        $emailAgain = sanitizeInput($input['emailAgain'] ?? '');
        $firstName = sanitizeInput($input['firstName'] ?? '');
        $lastName = sanitizeInput($input['lastName'] ?? '');
        $city = sanitizeInput($input['city'] ?? '');
        $country = sanitizeInput($input['country'] ?? '');
    }
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($email) || empty($emailAgain) || empty($firstName) || empty($lastName)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit();
    }
    
    // Validate username length
    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
        exit();
    }
    
    // Validate username format (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        exit();
    }
    
    // Validate email
    if (!validateEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }
    
    // Check if emails match
    if ($email !== $emailAgain) {
        echo json_encode(['success' => false, 'message' => 'Email addresses do not match']);
        exit();
    }
    
    // Validate password
    if (!validatePassword($password)) {
        echo json_encode(['success' => false, 'message' => 'Password must have at least 8 characters, including 1 digit, 1 lowercase letter, 1 uppercase letter, and 1 special character']);
        exit();
    }
    
    // Validate name lengths
    if (strlen($firstName) > 50 || strlen($lastName) > 50) {
        echo json_encode(['success' => false, 'message' => 'First name and last name must be less than 50 characters']);
        exit();
    }
    
    // Check if username already exists
    $checkUserStmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $checkUserStmt->execute([$username]);
    if ($checkUserStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose a different username']);
        exit();
    }
    
    // Check if email already exists
    $checkEmailStmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkEmailStmt->execute([$email]);
    if ($checkEmailStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address already registered. Please use a different email or try logging in']);
        exit();
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new user
    $insertStmt = $db->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, city, country) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insertResult = $insertStmt->execute([
        $username,
        $email,
        $hashedPassword,
        $firstName,
        $lastName,
        $city ?: null,
        $country ?: null
    ]);
    
    if ($insertResult) {
        $userId = $db->lastInsertId();
        
        // Automatically log in the new user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['user_type'] = 'student';
        $_SESSION['login_time'] = time();
        
        // Create session record
        $sessionId = generateSessionId();
        $sessionStmt = $db->prepare("INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
        $sessionStmt->execute([
            $sessionId,
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            SESSION_EXPIRE_TIME
        ]);
        
        $_SESSION['session_id'] = $sessionId;
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! You are now logged in.',
            'user' => [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_type' => 'student'
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account. Please try again.']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in registration: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("General error in registration: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>