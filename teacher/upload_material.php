<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['teacher_id'])) { header('Location: login.php'); exit(); }

$tid = $_SESSION['teacher_id'];
$msg = ''; $msg_type = 'success';

// Handle material upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $course_id = intval($_POST['course_id']);
    $type      = $_POST['type'];             // video | pdf | notice
    $title     = trim($_POST['title']);
    $content   = trim($_POST['content']);    // URL for video, text for notice
    $file_path = null;

    // Validate that this course belongs to this teacher
    $check = $conn->prepare('SELECT id FROM courses WHERE id=? AND teacher_id=?');
    $check->bind_param('ii', $course_id, $tid);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $msg = 'Unauthorized: This is not your course.'; $msg_type = 'danger';
    } else {
        // Handle PDF file upload
        if ($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
            $upload_dir = '../uploads/pdfs/';
            $filename   = time() . '_' . basename($_FILES['pdf_file']['name']);
            $target     = $upload_dir . $filename;

            // Only allow PDF files
            if ($_FILES['pdf_file']['type'] === 'application/pdf') {
                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target)) {
                    $file_path = 'uploads/pdfs/' . $filename;
                } else {
                    $msg = 'File upload failed.'; $msg_type = 'danger';
                }
            } else {
                $msg = 'Only PDF files allowed.'; $msg_type = 'danger';
            }
        }

        if (!$msg) {
            $stmt = $conn->prepare('INSERT INTO course_materials (course_id, teacher_id, type, title, content, file_path) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('iissss', $course_id, $tid, $type, $title, $content, $file_path);
            if ($stmt->execute()) { $msg = ucfirst($type) . ' uploaded successfully!'; }
            else { $msg = 'DB Error: ' . $conn->error; $msg_type = 'danger'; }
        }
    }
}
$courses = $conn->query("SELECT id, course_code, course_name FROM courses WHERE teacher_id=$tid ORDER BY course_code");
$pre_course = intval($_GET['course_id'] ?? 0);
$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_type='teacher' AND receiver_id=$tid AND is_read=0")->fetch_assoc()['cnt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Material - Oddhaboshay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>Oddha<span>boshay</span></h2><p>Teacher Panel</p></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">            <span class="icon">🏠</span> Dashboard</a>
            <a href="my_courses.php">           <span class="icon">📚</span> My Courses</a>
            <a href="upload_material.php" class="active"><span class="icon">⬆️</span> Upload Material</a>
            <a href="messages.php">             <span class="icon">✉️</span> Messages
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
            <div><h1>Upload Course Material</h1><div class="breadcrumb">Teacher &rsaquo; Upload</div></div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>📤 Upload New Material</h3></div>
            <!-- enctype required for file upload -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Course</label>
                        <select name="course_id" required id="course_select">
                            <option value="">-- Select Course --</option>
                            <?php while ($c = $courses->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $pre_course ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Material Type</label>
                        <select name="type" id="type_select" required onchange="toggleFields()">
                            <option value="notice">📢 Notice</option>
                            <option value="video">🎬 Video Link</option>
                            <option value="pdf">📄 PDF File</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="e.g., Lecture 1 Notes / Midterm Notice" required>
                </div>

                <!-- Notice / Video URL field -->
                <div class="form-group" id="content_field">
                    <label id="content_label">Notice Text / Video URL</label>
                    <textarea name="content" id="content_input" placeholder="Write notice text OR paste YouTube/video URL here..." rows="4"></textarea>
                </div>

                <!-- PDF Upload field (hidden by default) -->
                <div class="form-group" id="pdf_field" style="display:none;">
                    <label>Upload PDF File</label>
                    <input type="file" name="pdf_file" accept=".pdf" id="pdf_input">
                    <small style="color:#64748b;">Only .pdf files are accepted. Max 10MB.</small>
                </div>

                <button type="submit" name="upload" class="btn btn-success">Upload Material</button>
            </form>
        </div>

        <!-- Quick Tips -->
        <div class="card">
            <div class="card-header"><h3>💡 Upload Guide</h3></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div style="background:#f0f7ff;border-radius:10px;padding:16px;">
                    <h4 style="color:#1e40af;margin-bottom:6px;">📢 Notice</h4>
                    <p style="font-size:0.85rem;color:#475569;">Write announcements, reminders, or schedule changes in the text box.</p>
                </div>
                <div style="background:#f0fdf4;border-radius:10px;padding:16px;">
                    <h4 style="color:#065f46;margin-bottom:6px;">🎬 Video</h4>
                    <p style="font-size:0.85rem;color:#475569;">Paste a YouTube or any public video URL for students to watch.</p>
                </div>
                <div style="background:#fdf4ff;border-radius:10px;padding:16px;">
                    <h4 style="color:#6b21a8;margin-bottom:6px;">📄 PDF</h4>
                    <p style="font-size:0.85rem;color:#475569;">Upload lecture notes, assignments, or handout PDFs from your computer.</p>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
<script>
// Show/hide fields based on material type
function toggleFields() {
    var type = document.getElementById('type_select').value;
    var contentField = document.getElementById('content_field');
    var pdfField     = document.getElementById('pdf_field');
    var contentLabel = document.getElementById('content_label');
    var contentInput = document.getElementById('content_input');

    if (type === 'pdf') {
        pdfField.style.display     = 'block';
        contentField.style.display = 'none';
        contentInput.required      = false;
    } else {
        pdfField.style.display     = 'none';
        contentField.style.display = 'block';
        contentInput.required      = true;
        contentLabel.textContent   = (type === 'video') ? 'Video URL (YouTube link)' : 'Notice Text';
        contentInput.placeholder   = (type === 'video') ? 'Paste video URL here...' : 'Write your notice/announcement here...';
    }
}
toggleFields(); // Run on page load
</script>
</body>
</html>
