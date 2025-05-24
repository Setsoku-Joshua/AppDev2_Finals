<?php
session_start();
require_once 'databaseconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $mname = trim($_POST['mname']);
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);

    // Validate input
    if (empty($fname) || empty($lname) || empty($username) || empty($password) || empty($email) || empty($contact)) {
        $_SESSION['error'] = "All required fields must be filled out";
        header("Location: registration.php");
        exit();
    }

    // Validate password match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: registration.php");
        exit();
    }

    // Check if username already exists
    $stmt = $con->prepare("SELECT username FROM table_user WHERE username = ?");
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $con->error;
        header("Location: registration.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username already exists";
        header("Location: registration.php");
        exit();
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $con->prepare("SELECT email FROM table_user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: registration.php");
        exit();
    }
    $stmt->close();

    // Insert new user
    $stmt = $con->prepare("INSERT INTO table_user (fname, lname, mname, role, username, password, email, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $con->error;
        header("Location: registration.php");
        exit();
    }

    $stmt->bind_param("ssssssss", $fname, $lname, $mname, $role, $username, $password, $email, $contact);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error creating account: " . $stmt->error;
        header("Location: registration.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: registration.php");
    exit();
}
?> 