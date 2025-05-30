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
    <link href="css/style.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .navbar {
            background-color: #0d47a1 !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 10px 0;
        }
        .navbar .container {
            background: transparent !important;
        }
        .navbar-brand, .nav-link {
            color: #fff !important;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: #90caf9 !important;
        }
        body {
            padding-top: 110px; /* Increased to ensure hero is visible */
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

        .hero-section {
            background-image: url('images/library-hero.jpg');
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 60px 0;
            text-align: center;
            margin-top: 0; /* Remove negative margin */
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

        #about h2 {
            color: #0d47a1;
            margin-bottom: 1.5rem;
        }

        #about p {
            color: #333;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        #products {
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

        .logo {
            height: 60px;
            margin-right: 15px;
        }

        #libraryCarousel {
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
        }

        .carousel-inner {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .carousel-item {
            height: 400px;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 8px;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
        }

        .carousel-caption h5 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .carousel-caption p {
            font-size: 1.1rem;
            margin-bottom: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .carousel-item {
            height: 400px;
            position: relative;
        }

        .carousel-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            pointer-events: none;
        }

        .carousel-indicators {
            margin-bottom: 1.5rem;
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
            background-color: #0d47a1;
            opacity: 0.5;
        }

        .carousel-indicators button.active {
            opacity: 1;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            opacity: 0.8;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(13, 71, 161, 0.8);
            border-radius: 50%;
            padding: 20px;
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
                        <li class="nav-item"><a class="nav-link" href="<?php echo ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'librarian') ? 'manage_borrowings.php' : 'cart.php'; ?>">My Borrowings</a></li>
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
            <p class="mb-4">We provide a wide range of books, e-books, and digital resources to support learning and reading for all ages.</p>
            
            <div id="libraryCarousel" class="carousel slide" data-bs-ride="carousel">
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <?php for($i = 0; $i < 9; $i++): ?>
                        <button type="button" data-bs-target="#libraryCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                            <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                            aria-label="Slide <?php echo $i + 1; ?>">
                        </button>
                    <?php endfor; ?>
                </div>

                <!-- Carousel Items -->
                <div class="carousel-inner">
                    <?php 
                    // Get all books from database
                    $books_query = $con->query("
                        SELECT * FROM book_table 
                        ORDER BY book_id ASC 
                        LIMIT 9
                    ");
                    $books = $books_query->fetch_all(MYSQLI_ASSOC);
                    $current_image = 1;

                    foreach ($books as $index => $book): 
                    ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="images/product<?php echo $current_image; ?>.png" class="d-block w-100" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <div class="carousel-caption">
                                <h5 class="text-white"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="text-white">Genre: <?php echo htmlspecialchars($book['genre']); ?></p>
                            </div>
                        </div>
                    <?php 
                        $current_image++;
                    endforeach; 
                    ?>
                </div>

                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#libraryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#libraryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
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
