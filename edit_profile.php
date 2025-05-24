<?php
session_start();
require_once 'databaseconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    $verify_query = $con->prepare("SELECT password FROM table_user WHERE user_id = ?");
    $verify_query->bind_param("i", $user_id);
    $verify_query->execute();
    $result = $verify_query->get_result()->fetch_assoc();

    if (password_verify($current_password, $result['password'])) {
        // Start transaction
        $con->begin_transaction();

        try {
            // Update basic info
            $update_query = $con->prepare("
                UPDATE table_user 
                SET fname = ?, lname = ?, email = ?, contact = ?
                WHERE user_id = ?
            ");
            $update_query->bind_param("ssssi", $fname, $lname, $email, $contact, $user_id);
            $update_query->execute();

            // Update password if provided
            if (!empty($new_password) && $new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_query = $con->prepare("UPDATE table_user SET password = ? WHERE user_id = ?");
                $password_query->bind_param("si", $hashed_password, $user_id);
                $password_query->execute();
            }

            // Log the action
            $action = "Updated profile information";
            $con->query("INSERT INTO tbl_logs (user_id, action, DateTime) VALUES ($user_id, '$action', NOW())");

            $con->commit();
            $_SESSION['success'] = "Profile updated successfully!";
            
            // Update session variables
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
        } catch (Exception $e) {
            $con->rollback();
            $_SESSION['error'] = "Error updating profile. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect.";
    }
    
    header("Location: edit_profile.php");
    exit();
}

// Get user data
$user_query = $con->prepare("SELECT * FROM table_user WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | UST Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e3f2fd;
        }
        .sidebar {
            height: 100vh;
            background-color: #0d47a1;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            background-color: #1565c0;
        }
        .sidebar .nav-link.active {
            background-color: #1565c0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3">
            <img src="images/logo.png" alt="Library Logo" style="height: 60px; margin-right: 15px;">
            <h4 class="mt-3">UST Library</h4>
            <p class="text-white-50">Welcome, <?= htmlspecialchars($_SESSION['fname']) ?></p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="homepage.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="user.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="browse_books.php">Browse Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="edit_profile.php">Edit Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Edit Profile</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="fname" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lname" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact" name="contact" value="<?= htmlspecialchars($user['contact']) ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Change Password</h5>
                        <p class="text-muted">Leave new password fields empty if you don't want to change it.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            if (this.value !== document.getElementById('new_password').value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 