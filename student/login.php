<?php
session_start();
require_once '../config/db.php';
if (isset($_SESSION['student_id'])) { header('Location: dashboard.php'); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $student_id = trim($_POST['student_id']);

    // Match email AND student_id together for extra security
    $stmt = $conn->prepare('SELECT * FROM students WHERE email = ? AND student_id = ?');
    $stmt->bind_param('ss', $email, $student_id);
    $stmt->execute();
    $result  = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id']   = $student['id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_sid']  = $student['student_id'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid credentials. Check your Email, Password and Student ID.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <a href="../landingpage.php" class="back-link">&#8592; Back to Home</a>
        <h2>🎓 Student Login</h2>
        <p class="subtitle">Access your courses and materials</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. STU001" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@student.edu" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login as Student</button>
        </form>

        <p style="margin-top:16px; font-size:0.8rem; color:#94a3b8; text-align:center;">
            Demo: STU001 / rahim@student.edu / password
        </p>
    </div>
</div>
</body>
</html>
