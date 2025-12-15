<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$course_id = intval($_GET['course_id'] ?? 0);
$error = '';
$success = '';
$manual_image = '';

// Get courses for dropdown
$coursesQuery = "SELECT id, title FROM courses ORDER BY title";
$courses = $conn->query($coursesQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $order_index = intval($_POST['order_index'] ?? 0);
    $duration_minutes = intval($_POST['duration_minutes'] ?? 10);
    $video_url = sanitize($_POST['video_url'] ?? '');
    $manual_image = trim($_POST['image_url'] ?? '');
    $image_url = $manual_image !== '' ? $manual_image : DEFAULT_IMAGE_URL;
    
    if (empty($title) || empty($content) || !$course_id) {
        $error = 'Title, content, and course are required.';
    } else {
        if ($manual_image === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
                $filename = uniqid('lesson_') . '.' . $file_ext;
                $filepath = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    $image_url = 'assets/img/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Allowed: JPG, PNG, GIF';
            }
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO lessons (course_id, title, content, order_index, duration_minutes, video_url, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // Types: i = int, s = string → course_id(i), title(s), content(s), order_index(i), duration_minutes(i), video_url(s), image_url(s)
        $stmt->bind_param("issiiss", $course_id, $title, $content, $order_index, $duration_minutes, $video_url, $image_url);
        
        if ($stmt->execute()) {
            $success = 'Lesson added successfully!';
            $title = $content = $video_url = '';
            $order_index = 0;
            $duration_minutes = 10;
        } else {
            $error = 'Failed to add lesson.';
        }
        $stmt->close();
    }
}

// reset courses pointer
$courses = $conn->query($coursesQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lesson - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Add New Lesson</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course *</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">Select a course</option>
                                    <?php while ($course = $courses->fetch_assoc()): ?>
                                        <option value="<?php echo $course['id']; ?>" <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Lesson Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Lesson Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="order_index" class="form-label">Order Index</label>
                                    <input type="number" class="form-control" id="order_index" name="order_index" value="<?php echo $order_index ?? 0; ?>" min="0">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" value="<?php echo $duration_minutes ?? 10; ?>" min="1">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL (YouTube embed URL, optional)</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video_url ?? ''); ?>" placeholder="https://www.youtube.com/embed/...">
                                <small class="text-muted">Use YouTube embed URL format: https://www.youtube.com/embed/VIDEO_ID</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Featured Image URL (optional)</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($manual_image ?? ''); ?>" placeholder="https://example.com/lesson-cover.jpg">
                                <small class="text-muted">If provided, this URL will be used as the lesson image.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="image" class="form-label">Or Upload Featured Image (Optional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Upload is used only if URL is empty. If both are empty, a curated image is used.</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="list_courses.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Lesson</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

