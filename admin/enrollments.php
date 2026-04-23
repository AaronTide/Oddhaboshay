<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }

$msg = ''; $msg_type = 'success';

// Handle Approve / Decline / Assign
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enroll_id = intval($_POST['enroll_id']);
    $action    = $_POST['action'];  // 'approved' or 'declined'

    // Update enrollment status
    $stmt = $conn->prepare('UPDATE enrollments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $action, $enroll_id);
    if ($stmt->execute()) {
        $msg = 'Enrollment ' . ucfirst($action) . ' successfully!';
        if ($action === 'declined') $msg_type = 'warning';
    } else {
        $msg = 'Error updating status.'; $msg_type = 'danger';
    }
}

// Fetch all enrollments with student and course info
$enrollments = $conn->query("
    SELECT e.id, e.status, e.requested_at,
           s.name AS student_name, s.student_id AS student_no, s.department,
           c.course_code, c.course_name,
           t.name AS teacher_name
    FROM   enrollments e
    JOIN   students s ON e.student_id = s.id
    JOIN   courses  c ON e.course_id  = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY
        FIELD(e.status, 'pending', 'approved', 'declined'),
        e.requested_at DESC
");

$pending_count = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE status='pending'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollments - Oddhaboshay Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Admin Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">   <span class="icon">📊</span> Dashboard</a>
            <a href="students.php">    <span class="icon">🎓</span> Students</a>
            <a href="teachers.php">    <span class="icon">👨‍🏫</span> Teachers</a>
            <a href="courses.php">     <span class="icon">📚</span> Courses</a>
            <a href="enrollments.php" class="active"><span class="icon">✅</span> Enrollments
                <?php if ($pending_count > 0): ?><span class="badge badge-pending" style="margin-left:auto;"><?= $pending_count ?></span><?php endif; ?>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">Logged in as</div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div><h1>Enrollment Requests</h1><div class="breadcrumb">Admin &rsaquo; Enrollments</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3>All Enrollment Requests</h3>
                <?php if ($pending_count > 0): ?>
                    <span class="badge badge-pending"><?= $pending_count ?> Pending</span>
                <?php endif; ?>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Student</th><th>Course</th><th>Teacher</th><th>Status</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $enrollments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['student_name']) ?></strong><br>
                                <small style="color:#94a3b8;"><?= htmlspecialchars($row['student_no']) ?> &bull; <?= htmlspecialchars($row['department']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['course_code']) ?></strong><br>
                                <small style="color:#64748b;"><?= htmlspecialchars($row['course_name']) ?></small>
                            </td>
                            <td><?= $row['teacher_name'] ? htmlspecialchars($row['teacher_name']) : '<em style="color:#94a3b8;">None</em>' ?></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($row['requested_at'])) ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <!-- Approve Button -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="enroll_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="approved">
                                        <button class="btn btn-success btn-sm" type="submit">✅ Approve</button>
                                    </form>
                                    <!-- Decline Button -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="enroll_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="declined">
                                        <button class="btn btn-danger btn-sm" type="submit"
                                                onclick="return confirmAction('Decline this enrollment?')">❌ Decline</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:0.82rem;">No action</span>
                                <?php endif; ?>
                            </td>
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
