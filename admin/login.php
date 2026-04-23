<?php
session_start();
require_once '../config/db.php';

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare('SELECT id, username, password FROM admins WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Verify password using bcrypt
    if ($admin && password_verify($password, $admin['password'])) {
        // Login successful - store admin info in session
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <a href="../landingpage.php" class="back-link">&#8592; Back to Home</a>
        <h2>🛡️ Admin Login</h2>
        <p class="subtitle">Sign in to the administration panel</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter admin username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login as Admin</button>
        </form>

        <p style="margin-top:16px; font-size:0.8rem; color:#94a3b8; text-align:center;">
            Default: admin / password
        </p>
    </div>
</div>
</body>
</html>
