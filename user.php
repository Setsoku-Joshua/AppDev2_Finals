<?php
session_start();
require_once 'databaseconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT * FROM table_user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get borrowed books
$borrowed_query = $con->prepare("
    SELECT b.title, b.author, br.borrow_date, br.due_date, br.status 
    FROM borrowing_records br
    JOIN book_table b ON br.book_id = b.book_id
    WHERE br.user_id = ? AND br.status != 'returned'
");
$borrowed_query->bind_param("i", $user_id);
$borrowed_query->execute();
$borrowed_books = $borrowed_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account | UST Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e3f2fd;
        }
        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0d47a1;">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="Library Logo" style="height: 60px; margin-right: 15px;">
                UST Public Library
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="homepage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#products">Catalog</a></li>
                    <li class="nav-item"><a class="nav-link active" href="user.php">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">My Borrowings</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card">
                    <h3>My Profile</h3>
                    <p><strong>Name:</strong> <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact']) ?></p>
                    <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="profile-card">
                    <h3>My Borrowed Books</h3>
                    <?php if ($borrowed_books->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($book = $borrowed_books->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($book['title']) ?></td>
                                        <td><?= htmlspecialchars($book['author']) ?></td>
                                        <td><?= date('M d, Y', strtotime($book['borrow_date'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($book['due_date'])) ?></td>
                                        <td><?= ucfirst(htmlspecialchars($book['status'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>You have no currently borrowed books.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>