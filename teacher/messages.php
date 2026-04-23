<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }

$tid = $_SESSION['teacher_id'];
$msg = ''; $msg_type = 'success';

// Send message to a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $student_id = intval($_POST['student_id']);
    $subject    = trim($_POST['subject']);
    $message    = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, subject, message) VALUES ('teacher',?,'student',?,?,?)");
    $stmt->bind_param('iiss', $tid, $student_id, $subject, $message);
    if ($stmt->execute()) { $msg = 'Message sent!'; }
    else { $msg = 'Error sending message.'; $msg_type = 'danger'; }
}

// Mark inbox as read
$conn->query("UPDATE messages SET is_read=1 WHERE receiver_type='teacher' AND receiver_id=$tid AND is_read=0");

// Inbox
$inbox = $conn->query("
    SELECT m.*, s.name AS sender_name, s.student_id AS sender_sid
    FROM   messages m
    JOIN   students s ON m.sender_id = s.id
    WHERE  m.receiver_type='teacher' AND m.receiver_id=$tid
    ORDER  BY m.created_at DESC
");

// Sent
$sent = $conn->query("
    SELECT m.*, s.name AS receiver_name
    FROM   messages m
    JOIN   students s ON m.receiver_id = s.id
    WHERE  m.sender_type='teacher' AND m.sender_id=$tid
    ORDER  BY m.created_at DESC
");

// All students to compose message
$students = $conn->query('SELECT id, student_id, name, department FROM students ORDER BY name');
$unread_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages - Oddhaboshay Teacher</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tab-btn { padding:8px 20px; border:none; background:#f1f5f9; border-radius:8px; cursor:pointer; font-weight:600; color:#64748b; margin-right:8px; }
        .tab-btn.active { background:#10b981; color:#fff; }
        .tab-content { display:none; }
        .tab-content.show { display:block; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Teacher Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">    <span class="icon">🏠</span> Dashboard</a>
            <a href="my_courses.php">   <span class="icon">📚</span> My Courses</a>
            <a href="upload_material.php"><span class="icon">⬆️</span> Upload Material</a>
            <a href="messages.php" class="active"><span class="icon">✉️</span> Messages</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info"><?= htmlspecialchars($_SESSION['teacher_tid']) ?></div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['teacher_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div><h1>Messages</h1><div class="breadcrumb">Teacher &rsaquo; Messages</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Compose -->
        <div class="card">
            <div class="card-header"><h3>✏️ Send Message to Student</h3></div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Send To (Student)</label>
                        <select name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php while ($s = $students->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= $s['student_id'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="Message subject" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="4" placeholder="Write your message..." required></textarea>
                </div>
                <button type="submit" name="send_msg" class="btn btn-success">Send Message</button>
            </form>
        </div>

        <!-- Inbox & Sent Tabs -->
        <div class="card">
            <div style="margin-bottom:18px;">
                <button class="tab-btn active" onclick="showTab('tab-inbox')">📥 Inbox (<?= $inbox->num_rows ?>)</button>
                <button class="tab-btn" onclick="showTab('tab-sent')">📤 Sent (<?= $sent->num_rows ?>)</button>
            </div>

            <div id="tab-inbox" class="tab-content show">
                <?php if ($inbox->num_rows === 0): ?>
                    <div class="empty-state"><span class="empty-icon">📭</span><p>No messages received.</p></div>
                <?php else: ?>
                    <?php while ($m = $inbox->fetch_assoc()): ?>
                    <div class="message-item">
                        <div class="msg-header">
                            <span class="msg-from">From: <?= htmlspecialchars($m['sender_name']) ?> (<?= htmlspecialchars($m['sender_sid']) ?>)</span>
                            <span class="msg-time"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
                        </div>
                        <div class="msg-subject"><?= htmlspecialchars($m['subject']) ?></div>
                        <div class="msg-body"><?= htmlspecialchars($m['message']) ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <div id="tab-sent" class="tab-content">
                <?php if ($sent->num_rows === 0): ?>
                    <div class="empty-state"><span class="empty-icon">📤</span><p>No messages sent.</p></div>
                <?php else: ?>
                    <?php while ($m = $sent->fetch_assoc()): ?>
                    <div class="message-item">
                        <div class="msg-header">
                            <span class="msg-from">To: <?= htmlspecialchars($m['receiver_name']) ?></span>
                            <span class="msg-time"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
                        </div>
                        <div class="msg-subject"><?= htmlspecialchars($m['subject']) ?></div>
                        <div class="msg-body"><?= htmlspecialchars($m['message']) ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
