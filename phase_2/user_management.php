<?php
// user_management.php - View and manage users

require_once 'config.php';

// Check if user is logged in
requireLogin();

// Get all users with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
    $searchCondition = "WHERE username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
    $searchTerm = "%{$search}%";
    $searchParams = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users $searchCondition";
$countStmt = $db->prepare($countSql);
$countStmt->execute($searchParams);
$totalUsers = $countStmt->fetch()['total'];
$totalPages = ceil($totalUsers / $limit);

// Get users
$sql = "SELECT user_id, username, email, first_name, last_name, city, country, user_type, is_active, created_at, last_login 
        FROM users $searchCondition 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($searchParams);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - IUL</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="login-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .management-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 250px;
        }
        .search-btn {
            background: #4682B4;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .users-table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .users-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .users-table tbody tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .user-type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .type-student {
            background: #cce5ff;
            color: #004085;
        }
        .type-teacher {
            background: #fff3cd;
            color: #856404;
        }
        .type-admin {
            background: #f8d7da;
            color: #721c24;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background: #4682B4;
            color: white;
        }
        .pagination .current {
            background: #4682B4;
            color: white;
            border-color: #4682B4;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        @media (max-width: 768px) {
            .users-table-container {
                overflow-x: auto;
            }
            .page-header {
                flex-direction: column;
                gap: 15px;
            }
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="#">About <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Admission <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Academics <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Library <i class="fas fa-chevron-down"></i></a></li>
                <li><a href="#">Campuses <i class="fas fa-chevron-down"></i></a></li>
            </ul>
            <div class="search-icon">
                <a href="#"><i class="fas fa-search"></i></a>
            </div>
        </div>
    </div>
    
    <div class="management-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-users"></i> User Management</h1>
                <p>Total Users: <?php echo $totalUsers; ?></p>
            </div>
            
            <div class="search-container">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="user_management.php" class="search-btn" style="background: #6c757d; text-decoration: none;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #666;">
                            <?php echo empty($search) ? 'No users found.' : 'No users match your search.'; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            $location = [];
                            if (!empty($user['city'])) $location[] = $user['city'];
                            if (!empty($user['country'])) $location[] = $user['country'];
                            echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'Not specified';
                            ?>
                        </td>
                        <td>
                            <span class="user-type-badge type-<?php echo $user['user_type']; ?>">
                                <?php echo ucfirst($user['user_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['last_login']): ?>
                                <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                            <?php else: ?>
                                <span style="color: #666;">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="view_user.php?id=<?php echo $user['user_id']; ?>" class="btn-sm btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $user['user_id']; ?>)" class="btn-sm btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">First</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                <a href="?page=<?php echo $totalPages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Last</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="bottom-nav">
        <a href="moodle.html">
            <i class="fas fa-user"></i>
            <span>Moodle</span>
        </a>
        <a href="dashboard.php">
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

    <script>
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                window.location.href = 'delete_user.php?id=' + userId;
            }
        }
    </script>
</body>
</html>