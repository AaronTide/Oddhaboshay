<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }

$msg = '';
$msg_type = 'success';

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $sid   = trim($_POST['student_id']);
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $phone = trim($_POST['phone']);
    $dept  = trim($_POST['department']);

    $stmt = $conn->prepare('INSERT INTO students (student_id, name, email, password, phone, department) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssssss', $sid, $name, $email, $pass, $phone, $dept);
    if ($stmt->execute()) {
        $msg = 'Student added successfully!';
    } else {
        $msg = 'Error: ' . $conn->error;
        $msg_type = 'danger';
    }
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM students WHERE id = $del_id");
    $msg = 'Student removed.';
    $msg_type = 'warning';
}

// Fetch all students
$students = $conn->query('SELECT * FROM students ORDER BY created_at DESC');
$pending_count = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE status='pending'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students - Oddhaboshay Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Admin Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">   <span class="icon">📊</span> Dashboard</a>
            <a href="students.php" class="active"><span class="icon">🎓</span> Students</a>
            <a href="teachers.php">    <span class="icon">👨‍🏫</span> Teachers</a>
            <a href="courses.php">     <span class="icon">📚</span> Courses</a>
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
            <div><h1>Students</h1><div class="breadcrumb">Admin &rsaquo; Students</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Add Student Form -->
        <div class="card">
            <div class="card-header"><h3>➕ Add New Student</h3></div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" placeholder="e.g. STU004" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Full name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@student.edu" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Set a password" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="01XXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" placeholder="e.g. Computer Science">
                    </div>
                </div>
                <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
            </form>
        </div>

        <!-- Students Table -->
        <div class="card">
            <div class="card-header"><h3>All Students (<?= $students->num_rows ?>)</h3></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Student ID</th><th>Name</th><th>Email</th><th>Department</th><th>Phone</th><th>Joined</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['student_id']) ?></strong></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Delete this student?')">Delete</a>
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
