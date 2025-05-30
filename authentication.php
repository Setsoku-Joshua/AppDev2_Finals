<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Debug logging
    error_log("=== Login Attempt Debug ===");
    error_log("Username entered: " . $username);
    error_log("Password entered: [" . $password . "]");

    // Read the CSV file
    $users = array();
    if (($handle = fopen("database/table_user.csv", "r")) !== FALSE) {
        // Skip the header row
        fgetcsv($handle, 1000, ";");
        
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $users[] = array(
                'user_id' => $data[0],
                'fname' => $data[1],
                'lname' => $data[2],
                'mname' => $data[3],
                'role' => $data[4],
                'username' => $data[5],
                'password' => $data[6],
                'email' => $data[7],
                'contact' => $data[8]
            );
        }
        fclose($handle);
    }

    // Find the user
    $user = null;
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            $user = $u;
            break;
        }
    }

    error_log("User found: " . ($user !== null ? "Yes" : "No"));
    
    if ($user !== null) {
        error_log("Stored username: [" . $user['username'] . "]");
        error_log("Stored password: [" . $user['password'] . "]");
        error_log("Stored role: " . $user['role']);
        
        if ($password === $user['password']) {
            error_log("Password match successful");
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            
            error_log("Session variables set successfully");
            error_log("Redirecting to: " . ($user['role'] === 'admin' || $user['role'] === 'librarian' ? 'admin.php' : 'user.php'));

            if ($user['role'] === 'admin' || $user['role'] === 'librarian') {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit();
        } else {
            error_log("Password match failed");
            error_log("Entered password: [" . $password . "]");
            error_log("Stored password: [" . $user['password'] . "]");
        }
    } else {
        error_log("No user found with username: " . $username);
    }
    
    $_SESSION['error'] = "Invalid username or password";
    header("Location: login.php");
    exit();
}
?>