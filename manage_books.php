<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is admin/librarian
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')) {
    header("Location: login.php");
    exit();
}

// Handle book deletion
if (isset($_POST['delete_book']) && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    
    // Check for active borrowings
    $check_query = $con->prepare("
        SELECT COUNT(*) as active_borrowings 
        FROM borrowing_records 
        WHERE book_id = ? AND status = 'borrowed'
    ");
    $check_query->bind_param("i", $book_id);
    $check_query->execute();
    $result = $check_query->get_result()->fetch_assoc();
    
    if ($result['active_borrowings'] > 0) {
        $_SESSION['error'] = "Cannot delete book: It has active borrowings.";
    } else {
        try {
            // Start transaction
            $con->begin_transaction();
            
            // Get book title for logging
            $title_query = $con->prepare("SELECT title FROM book_table WHERE book_id = ?");
            $title_query->bind_param("i", $book_id);
            $title_query->execute();
            $book_title = $title_query->get_result()->fetch_assoc()['title'];
            
            // Delete book
            $delete_query = $con->prepare("DELETE FROM book_table WHERE book_id = ?");
            $delete_query->bind_param("i", $book_id);
            $delete_query->execute();
            
            // Log the action
            $user_id = $_SESSION['user_id'];
            $action = "Deleted book: " . $book_title;
            $log_query = $con->prepare("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES (?, ?, NOW())");
            $log_query->bind_param("is", $user_id, $action);
            $log_query->execute();
            
            $con->commit();
            $_SESSION['success'] = "Book deleted successfully.";
        } catch (Exception $e) {
            $con->rollback();
            $_SESSION['error'] = "Error deleting book: " . $e->getMessage();
        }
    }
    
    header("Location: manage_books.php");
    exit();
}

// Handle book addition
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $pub_date = $_POST['publication_date'];
    
    $insert_query = $con->prepare("INSERT INTO book_table (title, author, ISBN, genre, publication_date, availability_status) VALUES (?, ?, ?, ?, ?, 'available')");
    $insert_query->bind_param("sssss", $title, $author, $isbn, $genre, $pub_date);
    $insert_query->execute();
    
    // Log the action
    $user_id = $_SESSION['user_id'];
    $action = "Added new book: " . $title;
    $con->query("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES ($user_id, '$action', NOW())");
}

// Get all books
$books_query = $con->query("SELECT * FROM book_table ORDER BY book_id DESC");
$books = $books_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books | UST Library</title>
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
                <a class="nav-link active" href="manage_books.php">Manage Books</a>
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
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage Books</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    Add New Book
                </button>
            </div>

            <!-- Books Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Genre</th>
                                    <th>Publication Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($book['book_id']) ?></td>
                                        <td><?= htmlspecialchars($book['title']) ?></td>
                                        <td><?= htmlspecialchars($book['author']) ?></td>
                                        <td><?= htmlspecialchars($book['ISBN']) ?></td>
                                        <td><?= htmlspecialchars($book['genre']) ?></td>
                                        <td><?= htmlspecialchars($book['publication_date']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $book['availability_status'] === 'available' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($book['availability_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_book.php?id=<?= $book['book_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.');">
                                                <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                                <button type="submit" name="delete_book" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required>
                        </div>
                        <div class="mb-3">
                            <label for="genre" class="form-label">Genre</label>
                            <input type="text" class="form-control" id="genre" name="genre" required>
                        </div>
                        <div class="mb-3">
                            <label for="publication_date" class="form-label">Publication Date</label>
                            <input type="date" class="form-control" id="publication_date" name="publication_date" required>
                        </div>
                        <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBook(bookId) {
            // Implement edit functionality
            alert('Edit functionality will be implemented here');
        }
    </script>
</body>
</html> 