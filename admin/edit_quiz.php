<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$quiz_id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
if (!$quiz_id) {
    header('Location: ../dashboard_admin.php');
    exit;
}

$error = '';
$success = '';

// Get quiz
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
if ($quiz_result->num_rows === 0) {
    $stmt->close();
    header('Location: ../dashboard_admin.php');
    exit;
}
$quiz = $quiz_result->fetch_assoc();
$stmt->close();

// Get courses for dropdown
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title");

// Initialize form values
$course_id = (int)$quiz['course_id'];
$lesson_id = $quiz['lesson_id'] ? (int)$quiz['lesson_id'] : null;
$question = $quiz['question'];
$option_a = $quiz['option_a'];
$option_b = $quiz['option_b'];
$option_c = $quiz['option_c'];
$option_d = $quiz['option_d'];
$correct_answer = $quiz['correct_answer'];
$points = (int)$quiz['points'];
$current_image_url = $quiz['image_url'];
$manual_image = $current_image_url;
$lessons = [];

// Load lessons for current course
if ($course_id) {
    $lessons_stmt = $conn->prepare("SELECT id, title FROM lessons WHERE course_id = ? ORDER BY order_index");
    $lessons_stmt->bind_param("i", $course_id);
    $lessons_stmt->execute();
    $lessons = $lessons_stmt->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $lesson_id = !empty($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null;
    $question = sanitize($_POST['question'] ?? '');
    $option_a = sanitize($_POST['option_a'] ?? '');
    $option_b = sanitize($_POST['option_b'] ?? '');
    $option_c = sanitize($_POST['option_c'] ?? '');
    $option_d = sanitize($_POST['option_d'] ?? '');
    $correct_answer = strtoupper(trim($_POST['correct_answer'] ?? ''));
    $points = intval($_POST['points'] ?? 10);
    $manual_image = trim($_POST['image_url'] ?? $manual_image ?? '');
    $image_url = $manual_image !== '' ? $manual_image : ($current_image_url ?: DEFAULT_IMAGE_URL);

    if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || !$course_id || !in_array($correct_answer, ['A', 'B', 'C', 'D'], true)) {
        $error = 'All fields are required and correct answer must be A, B, C, or D.';
    } else {
        if ($manual_image === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts, true)) {
                $filename = uniqid('quiz_') . '.' . $file_ext;
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
        $update = $conn->prepare("
            UPDATE quizzes
            SET lesson_id = ?, course_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, points = ?, image_url = ?
            WHERE id = ?
        ");
        $update->bind_param(
            "iissssssisi",
            $lesson_id,
            $course_id,
            $question,
            $option_a,
            $option_b,
            $option_c,
            $option_d,
            $correct_answer,
            $points,
            $image_url,
            $quiz_id
        );

        if ($update->execute()) {
            $success = 'Quiz updated successfully!';
            $current_image_url = $image_url;
        } else {
            $error = 'Failed to update quiz.';
        }
        $update->close();

        // Reload lessons for (potentially) new course
        $lessons_stmt = $conn->prepare("SELECT id, title FROM lessons WHERE course_id = ? ORDER BY order_index");
        $lessons_stmt->bind_param("i", $course_id);
        $lessons_stmt->execute();
        $lessons = $lessons_stmt->get_result();
    }

    // reset courses pointer
    $courses = $conn->query("SELECT id, title FROM courses ORDER BY title");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - Admin Panel</title>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Edit Quiz</h3>
                        <a href="../quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-sm btn-outline-secondary">
                            View Quiz
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $quiz_id; ?>">

                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course *</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">Select a course</option>
                                    <?php 
                                    $courses->data_seek(0);
                                    while ($course = $courses->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $course['id']; ?>" <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="lesson_id" class="form-label">Lesson (Optional)</label>
                                <select class="form-select" id="lesson_id" name="lesson_id">
                                    <option value="">No specific lesson</option>
                                    <?php if ($lessons && $lessons->num_rows > 0): ?>
                                        <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                            <option value="<?php echo $lesson['id']; ?>" <?php echo ($lesson_id === (int)$lesson['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($lesson['title']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="question" class="form-label">Question *</label>
                                <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($question ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="option_a" class="form-label">Option A *</label>
                                <input type="text" class="form-control" id="option_a" name="option_a" value="<?php echo htmlspecialchars($option_a ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="option_b" class="form-label">Option B *</label>
                                <input type="text" class="form-control" id="option_b" name="option_b" value="<?php echo htmlspecialchars($option_b ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="option_c" class="form-label">Option C *</label>
                                <input type="text" class="form-control" id="option_c" name="option_c" value="<?php echo htmlspecialchars($option_c ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="option_d" class="form-label">Option D *</label>
                                <input type="text" class="form-control" id="option_d" name="option_d" value="<?php echo htmlspecialchars($option_d ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Quiz Image</label>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($current_image_url ?: DEFAULT_IMAGE_URL); ?>" alt="Quiz image" class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">Quiz Image URL (optional)</label>
                                    <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($manual_image ?? ''); ?>" placeholder="https://example.com/quiz-image.jpg">
                                    <small class="text-muted">If provided, this URL will be used and override the current/uploaded image.</small>
                                </div>
                                <label for="image" class="form-label">Or Upload New Quiz Image (Optional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Upload is used only if URL is empty. Leave both empty to keep the current image.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="correct_answer" class="form-label">Correct Answer *</label>
                                    <select class="form-select" id="correct_answer" name="correct_answer" required>
                                        <option value="">Select answer</option>
                                        <option value="A" <?php echo (($correct_answer ?? '') === 'A') ? 'selected' : ''; ?>>A</option>
                                        <option value="B" <?php echo (($correct_answer ?? '') === 'B') ? 'selected' : ''; ?>>B</option>
                                        <option value="C" <?php echo (($correct_answer ?? '') === 'C') ? 'selected' : ''; ?>>C</option>
                                        <option value="D" <?php echo (($correct_answer ?? '') === 'D') ? 'selected' : ''; ?>>D</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="points" class="form-label">Points (XP)</label>
                                    <input type="number" class="form-control" id="points" name="points" value="<?php echo $points ?? 10; ?>" min="1">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="../course.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Back to Course</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
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


