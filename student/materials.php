<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit(); }

$sid       = $_SESSION['student_id'];
$course_id = intval($_GET['course_id'] ?? 0);

if (!$course_id) { header('Location: my_courses.php'); exit(); }

// Verify student is approved for this course
$access = $conn->prepare("SELECT e.id FROM enrollments e WHERE e.student_id=? AND e.course_id=? AND e.status='approved'");
$access->bind_param('ii', $sid, $course_id);
$access->execute();
if ($access->get_result()->num_rows === 0) {
    header('Location: my_courses.php');
    exit(); // Block unauthorized access
}

// Get course info
$course = $conn->query("SELECT c.*, t.name AS teacher_name FROM courses c LEFT JOIN teachers t ON c.teacher_id=t.id WHERE c.id=$course_id")->fetch_assoc();

// Get all materials for this course, grouped by type
$materials = $conn->query("SELECT * FROM course_materials WHERE course_id=$course_id ORDER BY type, created_at DESC");

$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='student' AND receiver_id=$sid AND is_read=0")->fetch_assoc()['cnt'];

// Separate materials by type
$videos  = [];
$pdfs    = [];
$notices = [];
while ($m = $materials->fetch_assoc()) {
    if ($m['type'] === 'video')  $videos[]  = $m;
    if ($m['type'] === 'pdf')    $pdfs[]    = $m;
    if ($m['type'] === 'notice') $notices[] = $m;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials - <?= htmlspecialchars($course['course_code']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Student Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">    <span class="icon">🏠</span> Dashboard</a>
            <a href="enroll.php">       <span class="icon">📝</span> Enroll in Course</a>
            <a href="my_courses.php" class="active"><span class="icon">📚</span> My Courses</a>
            <a href="messages.php">     <span class="icon">✉️</span> Messages
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
                <h1><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h1>
                <div class="breadcrumb">Teacher: <?= $course['teacher_name'] ? htmlspecialchars($course['teacher_name']) : 'TBA' ?></div>
            </div>
            <a href="my_courses.php" class="btn btn-secondary btn-sm">&#8592; Back</a>
        </div>

        <!-- NOTICES -->
        <div class="card">
            <div class="card-header"><h3>📢 Notices</h3></div>
            <?php if (empty($notices)): ?>
                <p style="color:#94a3b8;font-size:0.9rem;">No notices posted yet.</p>
            <?php else: ?>
                <?php foreach ($notices as $n): ?>
                <div class="material-card">
                    <div class="mat-info">
                        <h4><?= htmlspecialchars($n['title']) ?></h4>
                        <p><?= htmlspecialchars($n['content']) ?></p>
                        <small style="color:#94a3b8;"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></small>
                    </div>
                    <span class="badge badge-notice">Notice</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- VIDEO LECTURES -->
        <div class="card">
            <div class="card-header"><h3>🎬 Video Lectures</h3></div>
            <?php if (empty($videos)): ?>
                <p style="color:#94a3b8;font-size:0.9rem;">No video lectures uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($videos as $v): ?>
                <div class="material-card">
                    <div class="mat-info">
                        <h4><?= htmlspecialchars($v['title']) ?></h4>
                        <p style="font-size:0.82rem;color:#64748b;"><?= date('d M Y', strtotime($v['created_at'])) ?></p>
                    </div>
                    <a href="<?= htmlspecialchars($v['content']) ?>" target="_blank" class="btn btn-primary btn-sm">▶ Watch</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- PDF MATERIALS -->
        <div class="card">
            <div class="card-header"><h3>📄 PDF Materials</h3></div>
            <?php if (empty($pdfs)): ?>
                <p style="color:#94a3b8;font-size:0.9rem;">No PDF files uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($pdfs as $p): ?>
                <div class="material-card">
                    <div class="mat-info">
                        <h4><?= htmlspecialchars($p['title']) ?></h4>
                        <p><?= htmlspecialchars($p['content'] ?: '') ?></p>
                        <small style="color:#94a3b8;"><?= date('d M Y', strtotime($p['created_at'])) ?></small>
                    </div>
                    <?php if ($p['file_path']): ?>
                        <a href="../<?= htmlspecialchars($p['file_path']) ?>" target="_blank" class="btn btn-warning btn-sm">📥 Download</a>
                    <?php else: ?>
                        <span class="badge badge-pdf">PDF</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
