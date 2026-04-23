<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit(); }

$sid = $_SESSION['student_id'];
$msg = ''; $msg_type = 'success';

// Send a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $subject    = trim($_POST['subject']);
    $message    = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, subject, message) VALUES ('student',?,  'teacher',?,?,?)");
    $stmt->bind_param('iiss', $sid, $teacher_id, $subject, $message);
    if ($stmt->execute()) { $msg = 'Message sent successfully!'; }
    else { $msg = 'Error sending message.'; $msg_type = 'danger'; }
}

// Mark messages as read when inbox is opened
$conn->query("UPDATE messages SET is_read=1 WHERE receiver_type='student' AND receiver_id=$sid AND is_read=0");

// Fetch inbox (messages received by this student)
$inbox = $conn->query("
    SELECT m.*, t.name AS sender_name
    FROM   messages m
    JOIN   teachers t ON m.sender_id = t.id
    WHERE  m.receiver_type='student' AND m.receiver_id=$sid
    ORDER  BY m.created_at DESC
");

// Fetch sent messages
$sent = $conn->query("
    SELECT m.*, t.name AS receiver_name
    FROM   messages m
    JOIN   teachers t ON m.receiver_id = t.id
    WHERE  m.sender_type='student' AND m.sender_id=$sid
    ORDER  BY m.created_at DESC
");

// Fetch all teachers to send messages to
$teachers = $conn->query('SELECT id, name, department FROM teachers ORDER BY name');
$unread_count = 0; // Already marked as read
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tab-btn { padding:8px 20px; border:none; background:#f1f5f9; border-radius:8px; cursor:pointer; font-weight:600; color:#64748b; margin-right:8px; }
        .tab-btn.active { background:#3b82f6; color:#fff; }
        .tab-content { display:none; }
        .tab-content.show { display:block; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Student Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">    <span class="icon">🏠</span> Dashboard</a>
            <a href="enroll.php">       <span class="icon">📝</span> Enroll in Course</a>
            <a href="my_courses.php">   <span class="icon">📚</span> My Courses</a>
            <a href="messages.php" class="active"><span class="icon">✉️</span> Messages</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info"><?= htmlspecialchars($_SESSION['student_sid']) ?></div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['student_name']) ?></div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="margin-top:10px;">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div><h1>Messages</h1><div class="breadcrumb">Student &rsaquo; Messages</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Compose Message -->
        <div class="card">
            <div class="card-header"><h3>✏️ Compose New Message</h3></div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Send To (Teacher)</label>
                        <select name="teacher_id" required>
                            <option value="">-- Select Teacher --</option>
                            <?php while ($t = $teachers->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $t['department'] ?>)</option>
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
                    <textarea name="message" placeholder="Write your message here..." rows="4" required></textarea>
                </div>
                <button type="submit" name="send_msg" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <!-- Tabs: Inbox / Sent -->
        <div class="card">
            <div style="margin-bottom:18px;">
                <button class="tab-btn active" onclick="showTab('tab-inbox')">📥 Inbox (<?= $inbox->num_rows ?>)</button>
                <button class="tab-btn" onclick="showTab('tab-sent')">📤 Sent (<?= $sent->num_rows ?>)</button>
            </div>

            <!-- Inbox -->
            <div id="tab-inbox" class="tab-content show">
                <?php if ($inbox->num_rows === 0): ?>
                    <div class="empty-state"><span class="empty-icon">📭</span><p>No messages received.</p></div>
                <?php else: ?>
                    <?php while ($m = $inbox->fetch_assoc()): ?>
                    <div class="message-item">
                        <div class="msg-header">
                            <span class="msg-from">From: <?= htmlspecialchars($m['sender_name']) ?></span>
                            <span class="msg-time"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
                        </div>
                        <div class="msg-subject"><?= htmlspecialchars($m['subject']) ?></div>
                        <div class="msg-body"><?= htmlspecialchars($m['message']) ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- Sent -->
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
