<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Handle book borrowing
if (isset($_POST['borrow_book']) && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days')); // 2 weeks borrowing period

    // Check if book is available
    $check_query = $con->prepare("SELECT availability_status FROM book_table WHERE book_id = ?");
    $check_query->bind_param("i", $book_id);
    $check_query->execute();
    $result = $check_query->get_result();
    $book = $result->fetch_assoc();

    if ($book && $book['availability_status'] === 'available') {
        // Start transaction
        $con->begin_transaction();

        try {
            // Insert borrowing record
            $insert_query = $con->prepare("
                INSERT INTO borrowing_records (book_id, user_id, borrow_date, due_date, status) 
                VALUES (?, ?, ?, ?, 'borrowed')
            ");
            $insert_query->bind_param("iiss", $book_id, $user_id, $borrow_date, $due_date);
            $insert_query->execute();

            // Update book status
            $update_query = $con->prepare("
                UPDATE book_table SET availability_status = 'borrowed' 
                WHERE book_id = ?
            ");
            $update_query->bind_param("i", $book_id);
            $update_query->execute();

            // Log the action
            $action = "Borrowed book ID: " . $book_id;
            $con->query("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES ($user_id, '$action', NOW())");

            $con->commit();
            $_SESSION['success'] = "Book borrowed successfully!";
        } catch (Exception $e) {
            $con->rollback();
            $_SESSION['error'] = "Error borrowing book. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Book is not available for borrowing.";
    }
    header("Location: user.php");
    exit();
}

// Get user's active borrowings
$user_id = $_SESSION['user_id'];
$borrowings_query = $con->query("
    SELECT br.*, b.title, b.author 
    FROM borrowing_records br
    JOIN book_table b ON br.book_id = b.book_id
    WHERE br.user_id = $user_id AND br.status = 'borrowed'
    ORDER BY br.borrow_date DESC
");
$active_borrowings = $borrowings_query->fetch_all(MYSQLI_ASSOC);

// Get user's borrowing history
$history_query = $con->query("
    SELECT br.*, b.title, b.author 
    FROM borrowing_records br
    JOIN book_table b ON br.book_id = b.book_id
    WHERE br.user_id = $user_id AND br.status = 'returned'
    ORDER BY br.return_date DESC
");
$borrowing_history = $history_query->fetch_all(MYSQLI_ASSOC);

// Get available books for borrowing
$available_books_query = $con->query("
    SELECT * FROM book_table 
    WHERE availability_status = 'available'
    ORDER BY book_id DESC
");
$available_books = $available_books_query->fetch_all(MYSQLI_ASSOC);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | UST Library</title>
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
        .book-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
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
                <a class="nav-link active" href="user.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="browse_books.php">Browse Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="edit_profile.php">Edit Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Active Borrowings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Currently Borrowed Books</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($active_borrowings)): ?>
                        <div class="alert alert-info">You have no active borrowings.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_borrowings as $borrowing): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($borrowing['title']) ?></td>
                                            <td><?= htmlspecialchars($borrowing['author']) ?></td>
                                            <td><?= date('M d, Y', strtotime($borrowing['borrow_date'])) ?></td>
                                            <td>
                                                <?php
                                                $due_date = strtotime($borrowing['due_date']);
                                                $is_overdue = $due_date < time();
                                                ?>
                                                <span class="<?= $is_overdue ? 'text-danger' : '' ?>">
                                                    <?= date('M d, Y', $due_date) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($is_overdue): ?>
                                                    <span class="badge bg-danger">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Borrowed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="return_book.php?record_id=<?= $borrowing['record_id'] ?>" 
                                                   class="btn btn-sm btn-success"
                                                   onclick="return confirm('Are you sure you want to return this book?')">
                                                    Return Book
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Books -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Available Books</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($available_books)): ?>
                        <div class="alert alert-info">No books available for borrowing at the moment.</div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <?php foreach ($available_books as $book): ?>
                                <div class="col">
                                    <div class="card h-100 book-card">
                                        <div class="card-body">
                                            <span class="badge bg-success status-badge">Available</span>
                                            <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($book['author']) ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Genre: <?= htmlspecialchars($book['genre']) ?><br>
                                                    ISBN: <?= htmlspecialchars($book['ISBN']) ?>
                                                </small>
                                            </p>
                                            <form method="post" class="mt-3">
                                                <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                                <button type="submit" name="borrow_book" class="btn btn-primary">Borrow Now</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Borrowing History -->
            <div class="card">
                <div class="card-header">
                    <h4>Borrowing History</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($borrowing_history)): ?>
                        <div class="alert alert-info">No borrowing history found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($borrowing_history as $history): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($history['title']) ?></td>
                                            <td><?= htmlspecialchars($history['author']) ?></td>
                                            <td><?= date('M d, Y', strtotime($history['borrow_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($history['return_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>