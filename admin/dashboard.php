<?php
session_start();
require_once '../config/db.php';

// Auth check - redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch statistics for dashboard cards
$total_students  = $conn->query('SELECT COUNT(*) as cnt FROM students')->fetch_assoc()['cnt'];
$total_teachers  = $conn->query('SELECT COUNT(*) as cnt FROM teachers')->fetch_assoc()['cnt'];
$total_courses   = $conn->query('SELECT COUNT(*) as cnt FROM courses')->fetch_assoc()['cnt'];
$pending_count   = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE status='pending'")->fetch_assoc()['cnt'];

// Fetch recent enrollment requests (last 5)
$recent = $conn->query("
    SELECT e.id, s.name AS student_name, c.course_name, e.status, e.requested_at
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN courses  c ON e.course_id  = c.id
    ORDER BY e.requested_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>Oddha<span>boshay</span></h2>
            <p>Admin Panel</p>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"    class="active"><span class="icon">📊</span> Dashboard</a>
            <a href="students.php">    <span class="icon">🎓</span> Students</a>
            <a href="teachers.php">    <span class="icon">👨‍🏫</span> Teachers</a>
            <a href="courses.php">     <span class="icon">📚</span> Courses</a>
            <a href="enrollments.php"> <span class="icon">✅</span> Enrollments
                <?php if ($pending_count > 0): ?>
                    <span class="badge badge-pending" style="margin-left:auto;"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">Logged in as</div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-header">
            <div>
                <h1>Dashboard</h1>
                <div class="breadcrumb">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            </div>
            <div style="font-size:0.82rem; color:#94a3b8;"><?= date('D, d M Y') ?></div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">🎓</div>
                <div class="stat-info">
                    <h3><?= $total_students ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">👨‍🏫</div>
                <div class="stat-info">
                    <h3><?= $total_teachers ?></h3>
                    <p>Total Teachers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📚</div>
                <div class="stat-info">
                    <h3><?= $total_courses ?></h3>
                    <p>Active Courses</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">⏳</div>
                <div class="stat-info">
                    <h3><?= $pending_count ?></h3>
                    <p>Pending Requests</p>
                </div>
            </div>
        </div>

        <!-- Recent Enrollment Requests -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Enrollment Requests</h3>
                <a href="enrollments.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($row['requested_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
