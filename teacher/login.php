<?php
session_start();
require_once '../config/db.php';
if (isset($_SESSION['teacher_id'])) { header('Location: dashboard.php'); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $teacher_id = trim($_POST['teacher_id']);

    $stmt = $conn->prepare('SELECT * FROM teachers WHERE email = ? AND teacher_id = ?');
    $stmt->bind_param('ss', $email, $teacher_id);
    $stmt->execute();
    $result  = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    if ($teacher && password_verify($password, $teacher['password'])) {
        $_SESSION['teacher_id']   = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['name'];
        $_SESSION['teacher_tid']  = $teacher['teacher_id'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid credentials. Check your Email, Password and Teacher ID.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Login - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <a href="../landingpage.php" class="back-link">&#8592; Back to Home</a>
        <h2>👨‍🏫 Teacher Login</h2>
        <p class="subtitle">Access your teaching dashboard</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Teacher ID</label>
                <input type="text" name="teacher_id" placeholder="e.g. TCH001" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@teacher.edu" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">Login as Teacher</button>
        </form>

        <p style="margin-top:16px; font-size:0.8rem; color:#94a3b8; text-align:center;">
            Demo: TCH001 / anwar@teacher.edu / password
        </p>
    </div>
</div>
</body>
</html>
