<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is admin/librarian
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')) {
    header("Location: login.php");
    exit();
}

// Handle return book action
if (isset($_POST['return_book']) && isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];
    $return_date = date('Y-m-d');
    
    // Update borrowing record
    $update_query = $con->prepare("UPDATE borrowing_records SET status = 'returned', return_date = ? WHERE record_id = ?");
    $update_query->bind_param("si", $return_date, $record_id);
    $update_query->execute();
    
    // Update book availability
    $book_id = $_POST['book_id'];
    $con->query("UPDATE book_table SET availability_status = 'available' WHERE book_id = $book_id");
    
    // Log the action
    $user_id = $_SESSION['user_id'];
    $action = "Returned book ID: " . $book_id;
    $con->query("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES ($user_id, '$action', NOW())");
}

// Get all borrowing records with book and user details
$records_query = $con->query("
    SELECT br.*, b.title, b.author, u.fname, u.lname, u.contact 
    FROM borrowing_records br
    JOIN book_table b ON br.book_id = b.book_id
    JOIN table_user u ON br.user_id = u.user_id
    ORDER BY br.borrow_date DESC
");
$records = $records_query ? $records_query->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Borrowings | UST Library</title>
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
        .status-badge {
            font-size: 0.875rem;
            padding: 0.5em 1em;
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
                <a class="nav-link active" href="manage_borrowings.php">Borrowing Records</a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">Manage Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="system_logs.php">System Logs</a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Borrowing Records</h2>

            <!-- Borrowing Records Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (empty($records)): ?>
                            <div class="alert alert-info">No borrowing records found.</div>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Book Title</th>
                                        <th>Borrower</th>
                                        <th>Contact</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record['record_id']) ?></td>
                                            <td><?= htmlspecialchars($record['title']) ?></td>
                                            <td><?= htmlspecialchars($record['fname'] . ' ' . $record['lname']) ?></td>
                                            <td><?= htmlspecialchars($record['contact']) ?></td>
                                            <td><?= date('M d, Y', strtotime($record['borrow_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($record['due_date'])) ?></td>
                                            <td><?= $record['return_date'] ? date('M d, Y', strtotime($record['return_date'])) : '-' ?></td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                if ($record['status'] === 'borrowed') {
                                                    $status_class = strtotime($record['due_date']) < time() ? 'danger' : 'warning';
                                                } elseif ($record['status'] === 'returned') {
                                                    $status_class = 'success';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $status_class ?> status-badge">
                                                    <?= ucfirst($record['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($record['status'] === 'borrowed'): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Mark this book as returned?');">
                                                        <input type="hidden" name="record_id" value="<?= $record['record_id'] ?>">
                                                        <input type="hidden" name="book_id" value="<?= $record['book_id'] ?>">
                                                        <button type="submit" name="return_book" class="btn btn-sm btn-success">Return</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 