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
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title");

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
    $manual_image = trim($_POST['image_url'] ?? '');
    $image_url = $manual_image !== '' ? $manual_image : DEFAULT_IMAGE_URL;
    
    if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || !$course_id || !in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
        $error = 'All fields are required and correct answer must be A, B, C, or D.';
    } else {
        if ($manual_image === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
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
        $stmt = $conn->prepare("INSERT INTO quizzes (lesson_id, course_id, question, option_a, option_b, option_c, option_d, correct_answer, points, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssis", $lesson_id, $course_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $points, $image_url);
        
        if ($stmt->execute()) {
            $success = 'Quiz added successfully!';
            $question = $option_a = $option_b = $option_c = $option_d = '';
            $correct_answer = '';
            $points = 10;
        } else {
            $error = 'Failed to add quiz.';
        }
        $stmt->close();
    }
}

// Get lessons for selected course (if course_id is set)
$lessons = [];
if ($course_id) {
    $lessons_result = $conn->prepare("SELECT id, title FROM lessons WHERE course_id = ? ORDER BY order_index");
    $lessons_result->bind_param("i", $course_id);
    $lessons_result->execute();
    $lessons = $lessons_result->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quiz - Admin Panel</title>
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
                        <h3 class="mb-0">Add New Quiz</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="quizForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course *</label>
                                <select class="form-select" id="course_id" name="course_id" required onchange="loadLessons()">
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
                                            <option value="<?php echo $lesson['id']; ?>"><?php echo htmlspecialchars($lesson['title']); ?></option>
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
                                <label for="image_url" class="form-label">Quiz Image URL (optional)</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($manual_image ?? ''); ?>" placeholder="https://example.com/quiz-image.jpg">
                                <small class="text-muted">If provided, this URL will be used as the quiz image.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Or Upload Quiz Image (Optional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Upload is used only if URL is empty. Leave both empty to auto-use a curated image.</small>
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
                                <a href="list_courses.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Quiz</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadLessons() {
            const courseId = document.getElementById('course_id').value;
            const lessonSelect = document.getElementById('lesson_id');
            
            lessonSelect.innerHTML = '<option value="">No specific lesson</option>';
            
            if (courseId) {
                fetch(`get_lessons.php?course_id=${courseId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data)) {
                            data.forEach(lesson => {
                                const option = document.createElement('option');
                                option.value = lesson.id;
                                option.textContent = lesson.title;
                                lessonSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading lessons:', error);
                        lessonSelect.innerHTML = '<option value="">Error loading lessons</option>';
                    });
            }
        }
    </script>
</body>
</html>

