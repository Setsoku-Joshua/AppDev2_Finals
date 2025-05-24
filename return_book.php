<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if record_id is provided
if (!isset($_GET['record_id'])) {
    $_SESSION['error'] = "Invalid request. No record specified.";
    header("Location: " . ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'librarian' ? 'manage_borrowings.php' : 'user.php'));
    exit();
}

$record_id = $_GET['record_id'];
$user_id = $_SESSION['user_id'];

// Start transaction
$con->begin_transaction();

try {
    // Get borrowing record details
    $record_query = $con->prepare("
        SELECT br.*, b.title 
        FROM borrowing_records br
        JOIN book_table b ON br.book_id = b.book_id
        WHERE br.record_id = ? AND (br.user_id = ? OR ? IN (
            SELECT user_id FROM table_user WHERE role IN ('admin', 'librarian')
        ))
    ");
    $record_query->bind_param("iii", $record_id, $user_id, $user_id);
    $record_query->execute();
    $result = $record_query->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Invalid record or unauthorized access.");
    }
    
    $record = $result->fetch_assoc();
    $book_id = $record['book_id'];
    $return_date = date('Y-m-d');
    
    // Update borrowing record
    $update_query = $con->prepare("
        UPDATE borrowing_records 
        SET status = 'returned', return_date = ? 
        WHERE record_id = ?
    ");
    $update_query->bind_param("si", $return_date, $record_id);
    $update_query->execute();
    
    // Update book availability
    $book_query = $con->prepare("
        UPDATE book_table 
        SET availability_status = 'available' 
        WHERE book_id = ?
    ");
    $book_query->bind_param("i", $book_id);
    $book_query->execute();
    
    // Log the action
    $action = "Returned book: " . $record['title'];
    $log_query = $con->prepare("
        INSERT INTO tbl_logs (user_id, action, DateTime) 
        VALUES (?, ?, NOW())
    ");
    $log_query->bind_param("is", $user_id, $action);
    $log_query->execute();
    
    $con->commit();
    $_SESSION['success'] = "Book has been successfully returned.";
    
} catch (Exception $e) {
    $con->rollback();
    $_SESSION['error'] = "Error returning book: " . $e->getMessage();
}

// Redirect based on user role
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'librarian') {
    header("Location: manage_borrowings.php");
} else {
    header("Location: user.php");
}
exit();
?> 