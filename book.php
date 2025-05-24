<?php
session_start();
require_once 'databaseconnection.php';

// Redirect to registration if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: registration.php");
    exit();
}

// Get book ID from URL
$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    $_SESSION['error'] = "No book specified.";
    header("Location: homepage.php");
    exit();
}

// Get book details from database
$book_query = $con->prepare("SELECT * FROM book_table WHERE book_id = ? AND availability_status = 'available'");
$book_query->bind_param("i", $book_id);
$book_query->execute();
$book = $book_query->get_result()->fetch_assoc();

if (!$book) {
    $_SESSION['error'] = "Book not found or not available.";
    header("Location: homepage.php");
    exit();
}

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));

    try {
        // Start transaction
        $con->begin_transaction();

        // Insert borrowing record
        $borrow_query = $con->prepare("
            INSERT INTO borrowing_records (book_id, user_id, borrow_date, due_date, status) 
            VALUES (?, ?, ?, ?, 'borrowed')
        ");
        $borrow_query->bind_param("iiss", $book_id, $user_id, $borrow_date, $due_date);
        $borrow_query->execute();

        // Update book status
        $update_query = $con->prepare("
            UPDATE book_table 
            SET availability_status = 'borrowed' 
            WHERE book_id = ?
        ");
        $update_query->bind_param("i", $book_id);
        $update_query->execute();

        // Log the action
        $action = "Borrowed book: " . $book['title'];
        $log_query = $con->prepare("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES (?, ?, NOW())");
        $log_query->bind_param("is", $user_id, $action);
        $log_query->execute();

        $con->commit();
        $_SESSION['success'] = "Book borrowed successfully! Due date: " . date('M d, Y', strtotime($due_date));
        header("Location: user.php");
        exit();
    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = "Error borrowing book: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - UST Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e3f2fd;
            padding-top: 60px;
        }
        .book-container {
            max-width: 800px;
            margin: auto;
        }
        .book-image {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
        }
        .btn-borrow {
            background-color: #1976d2;
            border: none;
        }
        .btn-borrow:hover {
            background-color: #1565c0;
        }
        .navbar {
            background-color: #0d47a1;
        }
        .navbar-brand, .nav-link {
            color: #fff;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: #90caf9;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a href="homepage.php" class="navbar-brand">UST Public Library</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="homepage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="user.php">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="browse_books.php">Browse Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container book-container">
        <div class="card">
            <?php
            // Calculate image number based on book_id (1-9)
            $image_number = ($book['book_id'] - 1) % 9 + 1;
            ?>
            <img src="images/product<?= $image_number ?>.png" alt="<?= htmlspecialchars($book['title']) ?>" class="card-img-top book-image">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($book['title']) ?></h2>
                <div class="card-text">
                    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
                    <p><strong>ISBN:</strong> <?= htmlspecialchars($book['ISBN']) ?></p>
                    <p><strong>Publication Date:</strong> <?= date('M d, Y', strtotime($book['publication_date'])) ?></p>
                </div>
                <form method="post" onsubmit="return confirm('Are you sure you want to borrow this book? You will need to return it within 14 days.');">
                    <button type="submit" class="btn btn-primary btn-borrow">Confirm Borrow</button>
                    <a href="homepage.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
