<?php
// logout.php - Handle user logout

require_once 'config.php';

// If user is logged in, clean up session
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $sessionId = $_SESSION['session_id'] ?? null;
    
    try {
        // Deactivate session in database
        if ($sessionId) {
            $stmt = $db->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_id = ? AND user_id = ?");
            $stmt->execute([$sessionId, $userId]);
        }
        
        // Clear all session data
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
    } catch (Exception $e) {
        error_log("Error during logout: " . $e->getMessage());
    }
}

// Redirect to login page
header('Location: moodle.html?logout=1');
exit();
?>