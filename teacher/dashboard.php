<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }

$tid = $_SESSION['teacher_id'];

// Stats
$course_count   = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE teacher_id=$tid")->fetch_assoc()['cnt'];
$student_count  = $conn->query("SELECT COUNT(DISTINCT e.student_id) as cnt FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE c.teacher_id=$tid AND e.status='approved'")->fetch_assoc()['cnt'];
$material_count = $conn->query("SELECT COUNT(*) as cnt FROM course_materials WHERE teacher_id=$tid")->fetch_assoc()['cnt'];
$unread_count   = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='teacher' AND receiver_id=$tid AND is_read=0")->fetch_assoc()['cnt'];

// My assigned courses
$courses = $conn->query("SELECT * FROM courses WHERE teacher_id=$tid ORDER BY course_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Teacher Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">   <span class="icon">🏠</span> Dashboard</a>
            <a href="my_courses.php">                 <span class="icon">📚</span> My Courses</a>
            <a href="upload_material.php">            <span class="icon">⬆️</span> Upload Material</a>
            <a href="messages.php">                   <span class="icon">✉️</span> Messages
                <?php if ($unread_count > 0): ?><span class="badge badge-pending" style="margin-left:auto;"><?= $unread_count ?></span><?php endif; ?>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info"><?= htmlspecialchars($_SESSION['teacher_tid']) ?></div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['teacher_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div>
                <h1>Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['teacher_name'])[0]) ?>!</h1>
                <div class="breadcrumb">Teacher Dashboard</div>
            </div>
            <div style="font-size:0.82rem; color:#94a3b8;"><?= date('D, d M Y') ?></div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-info"><h3><?= $course_count ?></h3><p>My Courses</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">🎓</div>
                <div class="stat-info"><h3><?= $student_count ?></h3><p>My Students</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📂</div>
                <div class="stat-info"><h3><?= $material_count ?></h3><p>Materials Uploaded</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">✉️</div>
                <div class="stat-info"><h3><?= $unread_count ?></h3><p>Unread Messages</p></div>
            </div>
        </div>

        <!-- Assigned Courses Quick View -->
        <div class="card">
            <div class="card-header">
                <h3>My Assigned Courses</h3>
                <a href="upload_material.php" class="btn btn-success btn-sm">⬆️ Upload Material</a>
            </div>
            <?php if ($courses->num_rows === 0): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No courses assigned yet. Contact the admin.</p>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php while ($c = $courses->fetch_assoc()): ?>
                    <div class="course-card" style="border-top-color:#10b981;">
                        <span class="course-code"><?= htmlspecialchars($c['course_code']) ?></span>
                        <h4><?= htmlspecialchars($c['course_name']) ?></h4>
                        <p><?= htmlspecialchars($c['description'] ?: 'No description.') ?></p>
                        <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:12px;"><?= $c['credits'] ?> Credits</p>
                        <a href="my_courses.php?course_id=<?= $c['id'] ?>" class="btn btn-primary btn-sm">View Students</a>
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
