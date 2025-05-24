<?php
session_start();
require_once 'databaseconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $user_id = $_SESSION['user_id'];
    
    // Get book details
    $book_stmt = $con->prepare("SELECT book_id FROM book_table WHERE title = ? AND availability_status = 'available'");
    $book_stmt->bind_param("s", $title);
    $book_stmt->execute();
    $book_result = $book_stmt->get_result();
    
    if ($book_result->num_rows === 1) {
        $book = $book_result->fetch_assoc();
        $book_id = $book['book_id'];
        
        // Calculate due date: (14 days from now)
        $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));
        
        // Create borrowing record
        $borrow_stmt = $con->prepare("
            INSERT INTO borrowing_records (book_id, user_id, due_date, status) 
            VALUES (?, ?, ?, 'borrowed')
        ");
        $borrow_stmt->bind_param("iis", $book_id, $user_id, $due_date);
        $borrow_stmt->execute();
        
        // Update book status
        $update_stmt = $con->prepare("UPDATE book_table SET availability_status = 'borrowed' WHERE book_id = ?");
        $update_stmt->bind_param("i", $book_id);
        $update_stmt->execute();
        
        // Log the action
        $log_stmt = $con->prepare("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES (?, ?, NOW())");
        $action = "Borrowed book: " . $title;
        $log_stmt->bind_param("is", $user_id, $action);
        $log_stmt->execute();
        
        $_SESSION['success'] = "Book borrowed successfully! Due date: " . date('M d, Y', strtotime($due_date));
    } else {
        $_SESSION['error'] = "Book not available for borrowing";
    }
    
    header("Location: user.php");
    exit();
}

// If not POST, redirect
header("Location: homepage.php");
exit();
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
