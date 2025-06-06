<?php
// login_process.php - Handle user login

require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both username and password']);
        exit();
    }
    
    // Find user in database
    $stmt = $db->prepare("SELECT user_id, username, email, password, first_name, last_name, user_type, is_active FROM users WHERE (username = ? OR email = ?)");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated']);
        exit();
    }
    
    // Verify password (handle both hashed and plain text)
    $passwordValid = false;
    
    if (strlen($user['password']) > 50) {
        // Password is hashed
        $passwordValid = verifyPassword($password, $user['password']);
    } else {
        // Password is plain text (for existing users)
        $passwordValid = ($password === $user['password']);
    }
    
    if (!$passwordValid) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Create session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['login_time'] = time();
    
    // Update last login time
    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $updateStmt->execute([$user['user_id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'user_type' => $user['user_type']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>