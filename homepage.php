<?php
session_start();
require_once 'databaseconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Santo Tomas Library Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .card-img-top {
    height: 300px;
    object-fit: cover;
    width: 100%;
}

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 1;
        }

        .status-borrowed {
            background-color: #dc3545;
            color: white;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e3f2fd;
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
        .hero-section {
            background-image: url('images/library-hero.jpg');
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 60px 0;
            text-align: center;
        }
        .hero-section h1 {
    font-size: 3rem;
    font-weight: 700;
    color:#0d47a1; 
}

.hero-section p {
    font-size: 1.5rem;
    color:#0d47a1; 
}

        #about {
            padding: 50px 0;
            text-align: center;
        }
        #products {
            background-color: #e3f2fd;
            padding: 60px 0;
        }
        #products h2 {
            text-align: center;
            color: #0d47a1;
            margin-bottom: 40px;
        }
        .card {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            height: 100%;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card-text {
            flex-grow: 1;
            margin-bottom: 1rem;
        }
        .card-title {
            color: #0d47a1;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            min-height: 2.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .btn-primary {
            background-color: #1976d2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }
        footer {
            background-color: #0d47a1;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }

        .logo {
    height: 60px;
    margin-right: 15px;
}

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">
            <img src="images/logo.png" alt="Library Logo" class="logo" style="height: 60px; margin-right: 15px;">
            UST Public Library
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#products">Catalog</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="user.php">My Account</a></li>
                        <li class="nav-item"><a class="nav-link" href="cart.php">My Borrowings</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="registration.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <h1>Welcome to The University of Santo Tomas Public Library</h1>
        <p>Discover a world of books and knowledge</p>
    </div>

    <section id="about">
        <div class="container">
            <h2>About Our Library</h2>
            <p>We provide a wide range of books, e-books, and digital resources to support learning and reading for all ages.</p>
        </div>
    </section>

    <section id="products">
        <div class="container">
            <h2>Featured Books</h2>
            <div class="row">
                <?php
                // Get all books from database (removed availability filter)
                $books_query = $con->query("
                    SELECT * FROM book_table 
                    ORDER BY book_id ASC 
                    LIMIT 9
                ");
                $books = $books_query->fetch_all(MYSQLI_ASSOC);
                $current_image = 1; // Start from first image

                foreach ($books as $book) {
                    $is_borrowed = $book['availability_status'] === 'borrowed';
                    echo '<div class="col-md-4 mb-4">
                        <div class="card h-100 position-relative">
                            ' . ($is_borrowed ? '<div class="status-badge status-borrowed">Borrowed</div>' : '') . '
                            <img src="images/product' . $current_image . '.png" class="card-img-top" alt="' . htmlspecialchars($book['title']) . '">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">' . htmlspecialchars($book['title']) . '</h5>
                                <p class="card-text flex-grow-1">
                                    By: ' . htmlspecialchars($book['author']) . '<br>
                                    Genre: ' . htmlspecialchars($book['genre']) . '<br>
                                    ISBN: ' . htmlspecialchars($book['ISBN']) . '
                                </p>
                                <a href="' . (isset($_SESSION['user_id']) ? ($is_borrowed ? '#' : 'book.php?id=' . $book['book_id']) : 'registration.php') . '" 
                                   class="btn btn-primary mt-auto' . ($is_borrowed ? ' disabled' : '') . '">' . 
                                   (isset($_SESSION['user_id']) ? ($is_borrowed ? 'Currently Borrowed' : 'Borrow Now') : 'Register to Borrow') . 
                                '</a>
                            </div>
                        </div>
                    </div>';
                    
                    // Increment image counter
                    $current_image++;
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 University of Santo Tomas Public Library. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
