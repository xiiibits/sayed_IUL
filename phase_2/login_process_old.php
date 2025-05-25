<?php
// login_process.php - Handle login requests

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
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
    } else {
        $username = sanitizeInput($input['username'] ?? '');
        $password = $input['password'] ?? '';
    }
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both username and password']);
        exit();
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT user_id, username, email, password, first_name, last_name, user_type, is_active FROM users WHERE (username = ? OR email = ?)");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact administrator.']);
        exit();
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
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
    $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
    $updateStmt->execute([$user['user_id']]);
    
    // Create session record for security
    $sessionId = generateSessionId();
    $sessionStmt = $db->prepare("INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
    $sessionStmt->execute([
        $sessionId,
        $user['user_id'],
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        SESSION_EXPIRE_TIME
    ]);
    
    $_SESSION['session_id'] = $sessionId;
    
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
    error_log("Database error in login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("General error in login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>