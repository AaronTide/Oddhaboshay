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
    <title>Oddhaboshai</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Topbar -->
<header class="topbar">
    <ion-icon name="book-outline"></ion-icon>
</header>

<main>
    <section class="hero-section">
        <div class="section-content">

            <div class="hero-details">
                <h2 class="title">Oddhaboshai</h2>

                <h3 class="subtitle">
                    Keep track of all class notices, contacts, course materials and more!
                </h3>

                <p class="description">Log in according to your portal</p>

                <div class="buttons">
                    <a href="admin/login.php" class="button">Admin </a>
                    <a href="teacher/login.php" class="button">Teacher </a>
                    <a href="student/login.php" class="button">Student </a>
                </div>
            </div>

            <div class="hero-image-wrapper">
                <img src="students.jpg" class="hero-image">
            </div>

        </div>
    </section>
</main>

<!-- Footer -->
<footer class="footer">
    <p>© 2026 Oddhaboshai • Built for smarter student management</p>
    <p>Built with love by Team 7</p>


</footer>

</body>
</html>