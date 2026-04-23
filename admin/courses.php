<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }

$msg = ''; $msg_type = 'success';

// Handle Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $code    = trim($_POST['course_code']);
    $name    = trim($_POST['course_name']);
    $desc    = trim($_POST['description']);
    $tid     = intval($_POST['teacher_id']) ?: null;
    $credits = intval($_POST['credits']);

    $stmt = $conn->prepare('INSERT INTO courses (course_code, course_name, description, teacher_id, credits) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssii', $code, $name, $desc, $tid, $credits);
    if ($stmt->execute()) { $msg = 'Course created successfully!'; }
    else { $msg = 'Error: ' . $conn->error; $msg_type = 'danger'; }
}

// Handle Assign Teacher to Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_teacher'])) {
    $course_id  = intval($_POST['course_id']);
    $teacher_id = intval($_POST['teacher_id']) ?: null;
    $stmt = $conn->prepare('UPDATE courses SET teacher_id = ? WHERE id = ?');
    $stmt->bind_param('ii', $teacher_id, $course_id);
    if ($stmt->execute()) { $msg = 'Teacher assigned to course!'; }
    else { $msg_type = 'danger'; $msg = 'Error: ' . $conn->error; }
}

// Handle Delete Course
if (isset($_GET['delete'])) {
    $conn->query('DELETE FROM courses WHERE id = ' . intval($_GET['delete']));
    $msg = 'Course deleted.'; $msg_type = 'warning';
}

$courses  = $conn->query('SELECT c.*, t.name AS teacher_name FROM courses c LEFT JOIN teachers t ON c.teacher_id = t.id ORDER BY c.created_at DESC');
$teachers = $conn->query('SELECT id, name, department FROM teachers ORDER BY name');
$pending_count = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE status='pending'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courses - Oddhaboshay Admin</title>
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
            <a href="courses.php" class="active"><span class="icon">📚</span> Courses</a>
            <a href="enrollments.php"> <span class="icon">✅</span> Enrollments
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
            <div><h1>Courses</h1><div class="breadcrumb">Admin &rsaquo; Courses</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Add Course Form -->
        <div class="card">
            <div class="card-header"><h3>➕ Create New Course</h3></div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" placeholder="e.g. CSE401" required>
                    </div>
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="course_name" placeholder="Full course title" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assign Teacher</label>
                        <select name="teacher_id">
                            <option value="">-- Select Teacher (Optional) --</option>
                            <?php
                            $teachers->data_seek(0);
                            while ($t = $teachers->fetch_assoc()):
                            ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $t['department'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Credits</label>
                        <select name="credits">
                            <option value="1">1 Credit</option>
                            <option value="2">2 Credits</option>
                            <option value="3" selected>3 Credits</option>
                            <option value="4">4 Credits</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief course description..."></textarea>
                </div>
                <button type="submit" name="add_course" class="btn btn-primary">Create Course</button>
            </form>
        </div>

        <!-- Courses Table -->
        <div class="card">
            <div class="card-header"><h3>All Courses (<?= $courses->num_rows ?>)</h3></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Code</th><th>Course Name</th><th>Teacher</th><th>Credits</th><th>Reassign Teacher</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['course_code']) ?></strong></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= $row['teacher_name'] ? htmlspecialchars($row['teacher_name']) : '<em style="color:#94a3b8;">Unassigned</em>' ?></td>
                            <td><?= $row['credits'] ?></td>
                            <td>
                                <!-- Quick reassign teacher -->
                                <form method="POST" style="display:flex;gap:6px;align-items:center;">
                                    <input type="hidden" name="course_id" value="<?= $row['id'] ?>">
                                    <select name="teacher_id" style="padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:0.82rem;">
                                        <option value="">None</option>
                                        <?php
                                        $teachers->data_seek(0);
                                        while ($t = $teachers->fetch_assoc()):
                                        ?>
                                        <option value="<?= $t['id'] ?>" <?= $row['teacher_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="assign_teacher" class="btn btn-success btn-sm">Save</button>
                                </form>
                            </td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Delete course and all its data?')">Delete</a>
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
