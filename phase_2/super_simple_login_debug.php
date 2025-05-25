<?php
// super_simple_login_debug.php - Very basic login with detailed debugging

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h2>Login Debug Tool</h2>";

// Database connection (replace with your actual settings)
try {
    $db = new PDO("mysql:host=localhost;dbname=iul_reg;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connected successfully<br><br>";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    exit();
}

// If form is submitted
if ($_POST) {
    echo "<h3>=== LOGIN ATTEMPT ===</h3>";
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    echo "Username entered: '" . htmlspecialchars($username) . "'<br>";
    echo "Password entered: '" . str_repeat('*', strlen($password)) . "' (length: " . strlen($password) . ")<br><br>";
    
    // Step 1: Look for user
    echo "<strong>Step 1: Looking for user...</strong><br>";
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "✗ User not found with username/email: '$username'<br>";
            echo "<br><strong>Let's see what users exist:</strong><br>";
            
            $allUsers = $db->prepare("SELECT username, email FROM users LIMIT 10");
            $allUsers->execute();
            $users = $allUsers->fetchAll();
            
            if (empty($users)) {
                echo "No users found in database!<br>";
            } else {
                echo "Available users:<br>";
                foreach ($users as $u) {
                    echo "- Username: '" . $u['username'] . "', Email: '" . $u['email'] . "'<br>";
                }
            }
        } else {
            echo "✓ User found!<br>";
            echo "- User ID: " . $user['user_id'] . "<br>";
            echo "- Username: '" . $user['username'] . "'<br>";
            echo "- Email: '" . $user['email'] . "'<br>";
            echo "- Active: " . ($user['is_active'] ? 'Yes' : 'No') . "<br>";
            echo "- Password in DB: '" . substr($user['password'], 0, 20) . "...' (length: " . strlen($user['password']) . ")<br><br>";
            
            // Step 2: Check if account is active
            echo "<strong>Step 2: Checking if account is active...</strong><br>";
            if (!$user['is_active']) {
                echo "✗ Account is deactivated<br>";
            } else {
                echo "✓ Account is active<br><br>";
                
                // Step 3: Check password
                echo "<strong>Step 3: Checking password...</strong><br>";
                
                // Check if password looks hashed
                if (strlen($user['password']) > 50) {
                    echo "Password appears to be hashed (length > 50)<br>";
                    if (password_verify($password, $user['password'])) {
                        echo "✓ Password verification successful!<br>";
                        
                        // Create session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        
                        echo "✓ Session created successfully!<br>";
                        echo "<strong style='color: green;'>LOGIN SUCCESSFUL!</strong><br>";
                        echo "<a href='dashboard.php'>Go to Dashboard</a><br>";
                        
                    } else {
                        echo "✗ Password verification failed<br>";
                        echo "The password you entered doesn't match the hashed password in database<br>";
                    }
                } else {
                    echo "Password appears to be plain text (length <= 50)<br>";
                    if ($password === $user['password']) {
                        echo "✓ Plain text password matches!<br>";
                        echo "<strong>Note: You should hash this password for security</strong><br>";
                        
                        // Create session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        
                        echo "✓ Session created successfully!<br>";
                        echo "<strong style='color: green;'>LOGIN SUCCESSFUL!</strong><br>";
                        echo "<a href='dashboard.php'>Go to Dashboard</a><br>";
                        
                    } else {
                        echo "✗ Plain text password doesn't match<br>";
                        echo "Password in DB: '" . $user['password'] . "'<br>";
                        echo "Password entered: '" . $password . "'<br>";
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
}

// Show all users for reference
echo "<h3>Current Users in Database:</h3>";
try {
    $allUsers = $db->prepare("SELECT user_id, username, email, first_name, last_name, password, is_active FROM users");
    $allUsers->execute();
    $users = $allUsers->fetchAll();
    
    if (empty($users)) {
        echo "No users found in database.<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Password</th><th>Active</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>";
            echo "<td>" . substr($user['password'], 0, 20) . "... (" . strlen($user['password']) . " chars)</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error loading users: " . $e->getMessage();
}
?>

<h3>Test Login Form:</h3>
<form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">
    <label>Username or Email:</label><br>
    <input type="text" name="username" required style="width: 100%; padding: 8px; margin: 5px 0;"><br>
    
    <label>Password:</label><br>
    <input type="password" name="password" required style="width: 100%; padding: 8px; margin: 5px 0;"><br><br>
    
    <button type="submit" style="padding: 10px 20px; background: #4682B4; color: white; border: none;">Test Login</button>
</form>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f0f0f0; }
</style>