<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get all logs with user details
$logs_query = $con->query("
    SELECT l.*, u.username, u.fname, u.lname 
    FROM tbl_logs l
    JOIN table_user u ON l.user_id = u.user_id
    ORDER BY l.DateTime DESC
");
$logs = $logs_query ? $logs_query->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Logs | UST Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e3f2fd;
        }
        .sidebar {
            height: 100vh;
            background-color: #0d47a1;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            background-color: #1565c0;
        }
        .sidebar .nav-link.active {
            background-color: #1565c0;
        }
        .log-entry {
            border-left: 4px solid #1976d2;
            margin-bottom: 10px;
            padding: 10px;
            background-color: white;
        }
        .log-time {
            color: #666;
            font-size: 0.875rem;
        }
        .log-user {
            color: #1976d2;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3">
            <img src="images/logo.png" alt="Library Logo" style="height: 60px; margin-right: 15px;">
            <h4 class="mt-3">UST Library</h4>
            <p class="text-white-50">Welcome, <?= htmlspecialchars($_SESSION['fname']) ?></p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="homepage.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_books.php">Manage Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_borrowings.php">Borrowing Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_users.php">Manage Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="system_logs.php">System Logs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">System Logs</h2>

            <!-- Logs Display -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-info">No system logs found.</div>
                    <?php else: ?>
                        <div class="logs-container">
                            <?php foreach ($logs as $log): ?>
                                <div class="log-entry">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="log-user"><?= htmlspecialchars($log['fname'] . ' ' . $log['lname']) ?></span>
                                            <span class="text-muted">(@<?= htmlspecialchars($log['username']) ?>)</span>
                                            <div class="mt-1">
                                                <?= htmlspecialchars($log['action']) ?>
                                            </div>
                                        </div>
                                        <span class="log-time">
                                            <?= date('M d, Y h:i A', strtotime($log['DateTime'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 