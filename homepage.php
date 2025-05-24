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
            }
            .card-title {
                color: #0d47a1;
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

    $books = [
        [
            "title" => "How to Kill A Mocking Bird",
            "description" => "A captivating journey through the world of fantasy and magic.",
            "image" => "images/product1.png"
        ],
        [
            "title" => "Elementary Differential Equations, 9th Edition",
            "description" => "An in-depth guide to mastering the art of storytelling.",
            "image" => "images/product2.png"
        ],
        [
            "title" => "Introduction to Observational Astrophysics",
            "description" => "Explore the wonders of science and discovery in this engaging book.",
            "image" => "images/product3.png"
        ],
    ];
    
    foreach ($books as $book) {
        echo '<div class="col-md-4 mb-4">
    <div class="card">
        <img src="' . $book['image'] . '" class="card-img-top" alt="' . $book['title'] . '">
        <div class="card-body">
            <h5 class="card-title">' . $book['title'] . '</h5>
            <p class="card-text">' . $book['description'] . '</p>
            <a href="book.php?title=' . urlencode($book['title']) . '&description=' . urlencode($book['description']) . '&image=' . urlencode($book['image']) . '" class="btn btn-primary">Borrow Now</a>
        </div>
    </div>
</div>';
      
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
