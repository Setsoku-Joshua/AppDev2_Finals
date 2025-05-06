<?php
$title = $_POST['title'] ?? 'Unknown Book';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center p-5">
    <div class="container">
        <h1>Thank you!</h1>
        <p>You have successfully borrowed <strong><?= htmlspecialchars($title) ?></strong>.</p>
        <a href="homepage.php" class="btn btn-primary">Back to Homepage</a>
    </div>
</body>
</html>
