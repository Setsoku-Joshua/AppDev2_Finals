<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$availability = isset($_GET['availability']) ? $_GET['availability'] : '';

// Build query
$query = "SELECT * FROM book_table WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR ISBN LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($genre) {
    $query .= " AND genre = ?";
    $params[] = $genre;
}

if ($availability) {
    $query .= " AND availability_status = ?";
    $params[] = $availability;
}

$query .= " ORDER BY book_id DESC";

// Get books
$stmt = $con->prepare($query);
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unique genres for filter
$genres = $con->query("SELECT DISTINCT genre FROM book_table ORDER BY genre")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books | UST Library</title>
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
                <a class="nav-link" href="user.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="browse_books.php">Browse Books</a>
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
            <h2 class="mb-4">Browse Books</h2>

            <!-- Search and Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="search" placeholder="Search by title, author, or ISBN" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="genre">
                                <option value="">All Genres</option>
                                <?php foreach ($genres as $g): ?>
                                    <option value="<?= htmlspecialchars($g['genre']) ?>" <?= $genre === $g['genre'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['genre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="availability">
                                <option value="">All Status</option>
                                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="borrowed" <?= $availability === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if (empty($books)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No books found matching your criteria.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="col">
                            <div class="card h-100 book-card">
                                <div class="card-body">
                                    <span class="badge bg-<?= $book['availability_status'] === 'available' ? 'success' : 'warning' ?> status-badge">
                                        <?= ucfirst($book['availability_status']) ?>
                                    </span>
                                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($book['author']) ?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Genre: <?= htmlspecialchars($book['genre']) ?><br>
                                            ISBN: <?= htmlspecialchars($book['ISBN']) ?><br>
                                            Published: <?= date('M d, Y', strtotime($book['publication_date'])) ?>
                                        </small>
                                    </p>
                                    <?php if ($book['availability_status'] === 'available'): ?>
                                        <form method="post" action="user.php">
                                            <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                            <button type="submit" name="borrow_book" class="btn btn-primary">Borrow Now</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Currently Borrowed</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
