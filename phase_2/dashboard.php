<?php
// dashboard.php - User dashboard after login

require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: moodle.html');
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: moodle.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IUL Moodle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="login-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #4682B4;
        }
        .info-card h3 {
            color: #4682B4;
            margin-bottom: 10px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-link {
            background: linear-gradient(135deg, #4682B4, #5a9fd4);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(70, 130, 180, 0.3);
        }
        .dashboard-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(70, 130, 180, 0.4);
        }
        .dashboard-link i {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }
        .dashboard-link h4 {
            margin: 10px 0 5px;
            font-size: 1.2em;
        }
        .dashboard-link p {
            font-size: 0.9em;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="IUL Logo">
            </div>
        </div>
    </div>
    
    <div class="nav-menu">
        <div class="container nav-container">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="#">About <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Admission <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Academics <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Library <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Campuses <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Centers <i class="fas fa-chevron-down"></i></a></li>
            </ul>
            <div class="search-icon">
                <a href="#"><i class="fas fa-search"></i></a>
            </div>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1><i class="fas fa-tachometer-alt"></i> Welcome to Your Dashboard</h1>
            <h2>Hello, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
            <p>You are successfully logged into the IUL Moodle system.</p>
            
            <div class="user-info">
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Account Information</h3>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>User Type:</strong> <?php echo ucfirst(htmlspecialchars($user['user_type'] ?? 'student')); ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                    <p><strong>City:</strong> <?php echo $user['city'] ? htmlspecialchars($user['city']) : 'Not specified'; ?></p>
                    <p><strong>Country:</strong> <?php echo $user['country'] ? htmlspecialchars($user['country']) : 'Not specified'; ?></p>
                    <?php if ($user['last_login']): ?>
                    <p><strong>Last Login:</strong> <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="welcome-section">
            <h3><i class="fas fa-th-large"></i> Quick Access</h3>
            <div class="dashboard-links">
                <a href="#" class="dashboard-link">
                    <i class="fas fa-book"></i>
                    <h4>My Courses</h4>
                    <p>Access your enrolled courses</p>
                </a>
                
                <a href="#" class="dashboard-link">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>Schedule</h4>
                    <p>View your class schedule</p>
                </a>
                
                <a href="#" class="dashboard-link">
                    <i class="fas fa-graduation-cap"></i>
                    <h4>Grades</h4>
                    <p>Check your academic progress</p>
                </a>
                
                <a href="#" class="dashboard-link">
                    <i class="fas fa-envelope"></i>
                    <h4>Messages</h4>
                    <p>View your messages</p>
                </a>
                
                <a href="#" class="dashboard-link">
                    <i class="fas fa-cog"></i>
                    <h4>Settings</h4>
                    <p>Manage your preferences</p>
                </a>
                
                <a href="#" class="dashboard-link">
                    <i class="fas fa-question-circle"></i>
                    <h4>Help & Support</h4>
                    <p>Get assistance</p>
                </a>
            </div>
        </div>
        
        <div class="welcome-section">
            <h3><i class="fas fa-chart-bar"></i> Quick Stats</h3>
            <div class="user-info">
                <div class="info-card">
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <?php
                    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
                    $countStmt->execute();
                    $totalUsers = $countStmt->fetch()['total'];
                    ?>
                    <p style="font-size: 24px; font-weight: bold; color: #4682B4;"><?php echo $totalUsers; ?></p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-clock"></i> Session Info</h3>
                    <p><strong>Login Time:</strong> <?php echo date('g:i A', $_SESSION['login_time']); ?></p>
                    <p><strong>Session Duration:</strong> <?php echo gmdate('H:i:s', time() - $_SESSION['login_time']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bottom-nav">
        <a href="moodle.html">
            <i class="fas fa-user"></i>
            <span>Moodle</span>
        </a>
        <a href="#" class="active">
            <i class="fas fa-user-graduate"></i>
            <span>Dashboard</span>
        </a>
        <a href="#">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Teacher Portal</span>
        </a>
        <a href="#">
            <i class="fas fa-envelope"></i>
            <span>Academic Email</span>
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

</body>
</html>