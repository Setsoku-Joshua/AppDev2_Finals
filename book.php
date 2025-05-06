<?php
$title = $_GET['title'] ?? 'Unknown Book';
$description = $_GET['description'] ?? 'No description available.';
$image = $_GET['image'] ?? 'images/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> - UST Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>

<div class="container book-container">
    <div class="card">
        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>" class="card-img-top book-image">
        <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($title) ?></h2>
            <p class="card-text"><?= htmlspecialchars($description) ?></p>
            <form method="post" action="purchase.php">
                <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
                <button type="submit" class="btn btn-borrow">Confirm Borrow</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
