<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }

$tid       = $_SESSION['teacher_id'];
$course_id = intval($_GET['course_id'] ?? 0);
$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='teacher' AND receiver_id=$tid AND is_read=0")->fetch_assoc()['cnt'];

// Get all courses for this teacher
$courses = $conn->query("SELECT * FROM courses WHERE teacher_id=$tid ORDER BY course_code");

// If a course is selected, get its students and materials
$selected_course = null;
$students = null;
$materials = null;
if ($course_id) {
    $selected_course = $conn->query("SELECT * FROM courses WHERE id=$course_id AND teacher_id=$tid")->fetch_assoc();
    if ($selected_course) {
        // Get approved students
        $students = $conn->query("
            SELECT s.student_id, s.name, s.email, s.department, e.requested_at
            FROM   enrollments e
            JOIN   students s ON e.student_id = s.id
            WHERE  e.course_id=$course_id AND e.status='approved'
            ORDER  BY s.name
        ");
        $materials = $conn->query("SELECT * FROM course_materials WHERE course_id=$course_id ORDER BY type, created_at DESC");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - Oddhaboshay Teacher</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Teacher Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">        <span class="icon">🏠</span> Dashboard</a>
            <a href="my_courses.php" class="active"><span class="icon">📚</span> My Courses</a>
            <a href="upload_material.php">  <span class="icon">⬆️</span> Upload Material</a>
            <a href="messages.php">         <span class="icon">✉️</span> Messages
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
            <div><h1>My Courses</h1><div class="breadcrumb">Teacher &rsaquo; Courses</div></div>
        </div>

        <!-- Course Selector -->
        <div class="card">
            <div class="card-header"><h3>Select a Course to View Details</h3></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php while ($c = $courses->fetch_assoc()): ?>
                <a href="?course_id=<?= $c['id'] ?>"
                   class="btn <?= $c['id'] == $course_id ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <?php if ($selected_course): ?>

        <!-- Enrolled Students -->
        <div class="card">
            <div class="card-header">
                <h3>✅ Enrolled Students - <?= htmlspecialchars($selected_course['course_name']) ?></h3>
                <span style="font-size:0.82rem;color:#64748b;"><?= $students->num_rows ?> student(s)</span>
            </div>
            <?php if ($students->num_rows === 0): ?>
                <div class="empty-state"><span class="empty-icon">📭</span><p>No students enrolled yet.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Enrolled On</th></tr></thead>
                        <tbody>
                            <?php while ($s = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['student_id']) ?></td>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
                                <td><?= htmlspecialchars($s['department']) ?></td>
                                <td><?= date('d M Y', strtotime($s['requested_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Course Materials -->
        <div class="card">
            <div class="card-header">
                <h3>📂 Course Materials</h3>
                <a href="upload_material.php?course_id=<?= $selected_course['id'] ?>" class="btn btn-success btn-sm">⬆️ Upload New</a>
            </div>
            <?php if ($materials->num_rows === 0): ?>
                <div class="empty-state"><span class="empty-icon">📂</span><p>No materials uploaded for this course.</p></div>
            <?php else: ?>
                <?php while ($m = $materials->fetch_assoc()): ?>
                <div class="material-card">
                    <div class="mat-info">
                        <h4><?= htmlspecialchars($m['title']) ?></h4>
                        <p><?= htmlspecialchars($m['content'] ?: $m['file_path'] ?: '') ?></p>
                        <small style="color:#94a3b8;"><?= date('d M Y', strtotime($m['created_at'])) ?></small>
                    </div>
                    <span class="badge badge-<?= $m['type'] ?>"><?= ucfirst($m['type']) ?></span>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
