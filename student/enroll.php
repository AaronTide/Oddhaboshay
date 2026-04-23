<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit(); }

$sid = $_SESSION['student_id'];
$msg = ''; $msg_type = 'success';

// Handle enrollment request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);

    // Check if already requested
    $check = $conn->prepare('SELECT id, status FROM enrollments WHERE student_id=? AND course_id=?');
    $check->bind_param('ii', $sid, $course_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $msg = 'You already ' . ($exists['status'] === 'pending' ? 'have a pending request' : 'are enrolled') . ' for this course.';
        $msg_type = 'warning';
    } else {
        $stmt = $conn->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?,?)');
        $stmt->bind_param('ii', $sid, $course_id);
        if ($stmt->execute()) {
            $msg = 'Enrollment request sent! Wait for admin approval.';
        } else {
            $msg = 'Error sending request.'; $msg_type = 'danger';
        }
    }
}

// Get IDs of courses student already requested/enrolled in
$enrolled_ids = [];
$enrolled_res = $conn->query("SELECT course_id FROM enrollments WHERE student_id=$sid");
while ($r = $enrolled_res->fetch_assoc()) { $enrolled_ids[] = $r['course_id']; }

// Fetch all available courses
$courses = $conn->query("
    SELECT c.*, t.name AS teacher_name, t.department
    FROM courses c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.course_code
");

$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='student' AND receiver_id=$sid AND is_read=0")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Student Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">              <span class="icon">🏠</span> Dashboard</a>
            <a href="enroll.php" class="active">  <span class="icon">📝</span> Enroll in Course</a>
            <a href="my_courses.php">             <span class="icon">📚</span> My Courses</a>
            <a href="messages.php">               <span class="icon">✉️</span> Messages
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
            <div><h1>Enroll in Course</h1><div class="breadcrumb">Student &rsaquo; Course Enrollment</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="alert alert-info">
            Browse available courses below. Click <strong>Request Enrollment</strong> to send a request. The admin will approve or decline it.
        </div>

        <!-- Available Courses Grid -->
        <div class="course-grid">
            <?php while ($c = $courses->fetch_assoc()): ?>
            <div class="course-card">
                <span class="course-code"><?= htmlspecialchars($c['course_code']) ?></span>
                <h4><?= htmlspecialchars($c['course_name']) ?></h4>
                <p><?= htmlspecialchars($c['description'] ?: 'No description available.') ?></p>
                <p class="teacher-name">👨‍🏫 <?= $c['teacher_name'] ? htmlspecialchars($c['teacher_name']) : 'TBA' ?></p>
                <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:12px;"><?= $c['credits'] ?> Credits</p>

                <?php if (in_array($c['id'], $enrolled_ids)): ?>
                    <button class="btn btn-secondary btn-sm" disabled>Already Requested</button>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">📝 Request Enrollment</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
