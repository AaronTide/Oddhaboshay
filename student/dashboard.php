<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit(); }

$sid = $_SESSION['student_id'];

// Count student stats
$approved_count = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id=$sid AND status='approved'")->fetch_assoc()['cnt'];
$pending_count  = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id=$sid AND status='pending'")->fetch_assoc()['cnt'];

// Get unread messages
$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='student' AND receiver_id=$sid AND is_read=0")->fetch_assoc()['cnt'];

// Recent approved courses
$courses = $conn->query("
    SELECT c.course_code, c.course_name, c.credits, t.name AS teacher_name
    FROM   enrollments e
    JOIN   courses  c ON e.course_id  = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE  e.student_id = $sid AND e.status = 'approved'
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Student Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a>
            <a href="enroll.php">         <span class="icon">📝</span> Enroll in Course</a>
            <a href="my_courses.php">     <span class="icon">📚</span> My Courses</a>
            <a href="messages.php">       <span class="icon">✉️</span> Messages
                <?php if ($unread_count > 0): ?><span class="badge badge-pending" style="margin-left:auto;"><?= $unread_count ?></span><?php endif; ?>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info"><?= htmlspecialchars($_SESSION['student_sid']) ?></div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['student_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div>
                <h1>Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['student_name'])[0]) ?>!</h1>
                <div class="breadcrumb">Student Dashboard</div>
            </div>
            <div style="font-size:0.82rem; color:#94a3b8;"><?= date('D, d M Y') ?></div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-info"><h3><?= $approved_count ?></h3><p>Enrolled Courses</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">⏳</div>
                <div class="stat-info"><h3><?= $pending_count ?></h3><p>Pending Requests</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">✉️</div>
                <div class="stat-info"><h3><?= $unread_count ?></h3><p>Unread Messages</p></div>
            </div>
        </div>

        <!-- My Active Courses -->
        <div class="card">
            <div class="card-header">
                <h3>My Active Courses</h3>
                <a href="my_courses.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <?php if ($courses->num_rows === 0): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>You have no approved courses yet. <a href="enroll.php" style="color:#3b82f6;">Enroll now &rarr;</a></p>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php while ($c = $courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <span class="course-code"><?= htmlspecialchars($c['course_code']) ?></span>
                        <h4><?= htmlspecialchars($c['course_name']) ?></h4>
                        <p class="teacher-name">👨‍🏫 <?= $c['teacher_name'] ? htmlspecialchars($c['teacher_name']) : 'TBA' ?></p>
                        <a href="materials.php?course_id=<?= '' ?>" class="btn btn-primary btn-sm">View Materials</a>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
