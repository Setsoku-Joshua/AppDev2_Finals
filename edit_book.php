<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in and is admin/librarian
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')) {
    header("Location: login.php");
    exit();
}

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    $_SESSION['error'] = "No book specified for editing.";
    header("Location: manage_books.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $pub_date = $_POST['publication_date'];
    $availability = $_POST['availability_status'];

    try {
        // Update book details
        $update_query = $con->prepare("
            UPDATE book_table 
            SET title = ?, author = ?, ISBN = ?, genre = ?, 
                publication_date = ?, availability_status = ?
            WHERE book_id = ?
        ");
        $update_query->bind_param("ssssssi", 
            $title, $author, $isbn, $genre, $pub_date, $availability, $book_id
        );
        $update_query->execute();

        // Log the action
        $user_id = $_SESSION['user_id'];
        $action = "Updated book details for: " . $title;
        $con->query("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES ($user_id, '$action', NOW())");

        $_SESSION['success'] = "Book details updated successfully.";
        header("Location: manage_books.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating book details: " . $e->getMessage();
    }
}

// Get book details
$book_query = $con->prepare("SELECT * FROM book_table WHERE book_id = ?");
$book_query->bind_param("i", $book_id);
$book_query->execute();
$book = $book_query->get_result()->fetch_assoc();

if (!$book) {
    $_SESSION['error'] = "Book not found.";
    header("Location: manage_books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book | UST Library</title>
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
                <h2>Edit Book</h2>
                <a href="manage_books.php" class="btn btn-secondary">Back to Books</a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($book['title']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?= htmlspecialchars($book['author']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" 
                                       value="<?= htmlspecialchars($book['ISBN']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="genre" class="form-label">Genre</label>
                                <input type="text" class="form-control" id="genre" name="genre" 
                                       value="<?= htmlspecialchars($book['genre']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="publication_date" class="form-label">Publication Date</label>
                                <input type="date" class="form-control" id="publication_date" 
                                       name="publication_date" value="<?= $book['publication_date'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="availability_status" class="form-label">Availability Status</label>
                                <select class="form-select" id="availability_status" name="availability_status" required>
                                    <option value="available" <?= $book['availability_status'] === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="borrowed" <?= $book['availability_status'] === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 