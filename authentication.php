<?php
session_start();
require_once 'databaseconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    $stmt = $con->prepare("SELECT * FROM table_user WHERE username = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            

            if ($user['role'] === 'admin' || $user['role'] === 'librarian') {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid username or password";
    header("Location: login.php");
    exit();
}
?>