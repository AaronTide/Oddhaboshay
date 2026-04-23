<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit(); }

$sid = $_SESSION['student_id'];

// Fetch all enrollments for this student
$enrollments = $conn->query("
    SELECT e.status, e.requested_at,
           c.id AS course_id, c.course_code, c.course_name, c.description, c.credits,
           t.name AS teacher_name
    FROM   enrollments e
    JOIN   courses  c ON e.course_id  = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE  e.student_id = $sid
    ORDER BY e.status, e.requested_at DESC
");

$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='student' AND receiver_id=$sid AND is_read=0")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Student Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">                    <span class="icon">🏠</span> Dashboard</a>
            <a href="enroll.php">                       <span class="icon">📝</span> Enroll in Course</a>
            <a href="my_courses.php" class="active">    <span class="icon">📚</span> My Courses</a>
            <a href="messages.php">                     <span class="icon">✉️</span> Messages
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
            <div><h1>My Courses</h1><div class="breadcrumb">Student &rsaquo; My Courses</div></div>
        </div>

        <?php if ($enrollments->num_rows === 0): ?>
            <div class="card">
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No enrollment records yet. <a href="enroll.php" style="color:#3b82f6;">Enroll in a course &rarr;</a></p>
                </div>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php while ($c = $enrollments->fetch_assoc()): ?>
                <div class="course-card" style="border-top-color: <?= $c['status']==='approved' ? '#10b981' : ($c['status']==='pending' ? '#f59e0b' : '#ef4444') ?>">
                    <span class="course-code"><?= htmlspecialchars($c['course_code']) ?></span>
                    <h4><?= htmlspecialchars($c['course_name']) ?></h4>
                    <p><?= htmlspecialchars($c['description'] ?: 'No description.') ?></p>
                    <p class="teacher-name">👨‍🏫 <?= $c['teacher_name'] ? htmlspecialchars($c['teacher_name']) : 'TBA' ?></p>
                    <p style="margin-bottom:12px;">
                        <span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
                        <span style="font-size:0.78rem;color:#94a3b8;margin-left:8px;"><?= $c['credits'] ?> Credits</span>
                    </p>

                    <?php if ($c['status'] === 'approved'): ?>
                        <a href="materials.php?course_id=<?= $c['course_id'] ?>" class="btn btn-primary btn-sm">📂 View Materials</a>
                    <?php elseif ($c['status'] === 'pending'): ?>
                        <button class="btn btn-warning btn-sm" disabled>⏳ Awaiting Approval</button>
                    <?php else: ?>
                        <button class="btn btn-danger btn-sm" disabled>❌ Declined</button>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
