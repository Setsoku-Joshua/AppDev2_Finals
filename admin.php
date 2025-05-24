<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is admin/librarian
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')) {
    header("Location: login.php");
    exit();
}

// Create borrowing_records table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS borrowing_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT,
    user_id INT,
    borrow_date DATE,
    due_date DATE,
    return_date DATE,
    status VARCHAR(20) DEFAULT 'borrowed',
    FOREIGN KEY (book_id) REFERENCES book_table(book_id),
    FOREIGN KEY (user_id) REFERENCES table_user(user_id)
)";
$con->query($create_table_query);

// Get all books
$books_query = $con->query("SELECT * FROM book_table");
$books = $books_query ? $books_query->fetch_all(MYSQLI_ASSOC) : [];

// Get all borrowing records with error handling
$records_query = $con->query("
    SELECT br.*, b.title, b.author, u.fname, u.lname 
    FROM borrowing_records br
    JOIN book_table b ON br.book_id = b.book_id
    JOIN table_user u ON br.user_id = u.user_id
    ORDER BY br.borrow_date DESC
");
$records = $records_query ? $records_query->fetch_all(MYSQLI_ASSOC) : [];

// Add availability_status column to book_table if it doesn't exist
$check_column = $con->query("SHOW COLUMNS FROM book_table LIKE 'availability_status'");
if ($check_column->num_rows === 0) {
    $con->query("ALTER TABLE book_table ADD COLUMN availability_status VARCHAR(20) DEFAULT 'available'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | UST Public Library</title>
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
        }
        .sidebar .nav-link {
            color: white;
        }
        .sidebar .nav-link:hover {
            color: #90caf9;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <img src="images/logo.png" alt="Library Logo" style="height: 60px; margin-right: 15px;">
                    <h4 class="text-white mt-2">UST Library</h4>
                    <p class="text-white-50">Welcome, <?= htmlspecialchars($_SESSION['fname']) ?></p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_books.php">Manage Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_borrowings.php">Borrowing Records</a>
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
            <div class="col-md-9 col-lg-10 main-content">
                <h2>Library Management Dashboard</h2>
                
                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Books</h5>
                                <p class="card-text display-6"><?= $books_query ? $books_query->num_rows : 0 ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Available Books</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $available_query = $con->query("SELECT COUNT(*) FROM book_table WHERE availability_status = 'available'");
                                    echo $available_query ? $available_query->fetch_row()[0] : 0;
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Active Borrowings</h5>
                                <p class="card-text display-6"><?= $records_query ? $records_query->num_rows : 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Borrowings Table -->
                <h3 class="mt-5">Recent Borrowing Records</h3>
                <div class="table-responsive">
                    <?php if (empty($records)): ?>
                        <div class="alert alert-info">No borrowing records found.</div>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Borrower</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($record['title']) ?></td>
                                        <td><?= htmlspecialchars($record['fname'] . ' ' . $record['lname']) ?></td>
                                        <td><?= date('M d, Y', strtotime($record['borrow_date'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($record['due_date'])) ?></td>
                                        <td><?= ucfirst($record['status']) ?></td>
                                        <td>
                                            <?php if ($record['status'] === 'borrowed'): ?>
                                                <a href="return_book.php?record_id=<?= $record['record_id'] ?>" class="btn btn-sm btn-success">Mark as Returned</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>