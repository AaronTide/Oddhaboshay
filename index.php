<?php
// If someone is already logged in, redirect them
session_start();
if (isset($_SESSION['admin_id']))   { header('Location: admin/dashboard.php');   exit(); }
if (isset($_SESSION['student_id'])) { header('Location: student/dashboard.php'); exit(); }
if (isset($_SESSION['teacher_id'])) { header('Location: teacher/dashboard.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oddhaboshay - University LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="home-page">
    <!-- Logo -->
    <div class="home-logo">Oddha<span>boshay</span></div>
    <div class="home-subtitle">University Learning Management System</div>
   <img a href =students.jpg>
    <!-- Three login cards -->
    <div class="login-cards">

        <!-- Admin Card -->
        <a href="admin/login.php" class="login-card card-admin">
            <span class="icon">🛡️</span>
            <h3>Admin</h3>
            <p>Manage students, teachers, courses & enrollments</p>
        </a>

        <!-- Student Card -->
        <a href="student/login.php" class="login-card card-student">
            <span class="icon">🎓</span>
            <h3>Student</h3>
            <p>View courses, materials, and send messages</p>
        </a>

        <!-- Teacher Card -->
        <a href="teacher/login.php" class="login-card card-teacher">
            <span class="icon">👨‍🏫</span>
            <h3>Teacher</h3>
            <p>Upload materials and communicate with students</p>
        </a>

    </div>

    <p style="color:#475569; margin-top:40px; font-size:0.8rem;">
        Demo password for all accounts: <strong style="color:#94a3b8;">password</strong>
    </p>
</div>
</body>
</html>
