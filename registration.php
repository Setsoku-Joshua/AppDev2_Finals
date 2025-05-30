<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            z-index: 2000;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
        }
        .form-control:focus {
            border-color: #0d47a1;
            box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.25);
        }
    </style>
</head>
<body>
    <a href="homepage.php" class="btn btn-outline-primary back-button">
        <i class="bi bi-arrow-left"></i> Back to Homepage
    </a>
    <div class="container" style="margin-top:0;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" style="padding-top:2rem; padding-bottom:2rem;">
                    <div class="card-header">
                        <h3 class="text-center">User Registration</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                    echo $_SESSION['error'];
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="process_registration.php" method="POST">
                            <div class="mb-3">
                                <label for="fname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="fname" name="fname" required>
                            </div>
                            <div class="mb-3">
                                <label for="lname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lname" name="lname" required>
                            </div>
                            <div class="mb-3">
                                <label for="mname" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="mname" name="mname">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="user">Regular User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contact" name="contact" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php">Already have an account? Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 