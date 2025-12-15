<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$course_id = intval($_GET['id'] ?? 0);
if (!$course_id) {
    header('Location: index.php');
    exit;
}

$course_stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();
$course_stmt->close();

if (!$course) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$enrollment_stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$enrollment_stmt->bind_param("ii", $user_id, $course_id);
$enrollment_stmt->execute();
$enrollment = $enrollment_stmt->get_result()->fetch_assoc();
$enrollment_stmt->close();
$is_enrolled = (bool) $enrollment;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll']) && !$is_enrolled) {
    $insert = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $course_id);
    if ($insert->execute()) {
        header('Location: course.php?id=' . $course_id);
        exit;
    }
    $insert->close();
}

$lessons_stmt = $conn->prepare("SELECT l.*, 
    (SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as completed
    FROM lessons l WHERE l.course_id = ? ORDER BY l.order_index ASC");
$lessons_stmt->bind_param("ii", $user_id, $course_id);
$lessons_stmt->execute();
$lessons_result = $lessons_stmt->get_result();
$lessons = [];
$completed_count = 0;
while ($lesson = $lessons_result->fetch_assoc()) {
    $lessons[] = $lesson;
    if ($lesson['completed']) {
        $completed_count++;
    }
}
$lessons_stmt->close();
$progress_percent = count($lessons) ? round(($completed_count / count($lessons)) * 100) : 0;

if ($is_enrolled) {
    $update = $conn->prepare("UPDATE enrollments SET progress_percent = ? WHERE user_id = ? AND course_id = ?");
    $update->bind_param("iii", $progress_percent, $user_id, $course_id);
    $update->execute();
    $update->close();
}

$quiz_stmt = $conn->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY id ASC");
$quiz_stmt->bind_param("i", $course_id);
$quiz_stmt->execute();
$quizzes = $quiz_stmt->get_result();

$best_scores = [];
$best_stmt = $conn->prepare("SELECT quiz_id, MAX(score) as best FROM quiz_results WHERE user_id = ? GROUP BY quiz_id");
$best_stmt->bind_param("i", $user_id);
$best_stmt->execute();
$best_res = $best_stmt->get_result();
while ($row = $best_res->fetch_assoc()) {
    $best_scores[$row['quiz_id']] = (int)$row['best'];
}
$best_stmt->close();

$course_image = safeImage($course['image_url'] ?? '');
$quiz_message = null;
if (isset($_GET['quiz_result'])) {
    $quiz_message = [
        'status' => $_GET['quiz_result'],
        'score' => intval($_GET['score'] ?? 0),
        'max' => intval($_GET['max'] ?? 0),
        'best' => intval($_GET['best'] ?? 0),
        'latest' => intval($_GET['latest'] ?? 0)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <section class="section-space pt-5">
        <div class="container">
        <?php if ($quiz_message): ?>
                <div class="alert alert-<?php echo $quiz_message['status'] === 'correct' ? 'success' : 'warning'; ?> alert-dismissible fade show">
                    <?php if ($quiz_message['status'] === 'already_taken'): ?>
                        <strong>You have already taken this quiz.</strong>
                        <div>Score: <?php echo $quiz_message['score']; ?> / <?php echo $quiz_message['max']; ?></div>
                    <?php else: ?>
                        <strong><?php echo $quiz_message['status'] === 'correct' ? 'Nice work! 🎉' : 'Keep practicing!'; ?></strong>
                        <div>Best Score: <?php echo $quiz_message['best']; ?> — Latest Score: <?php echo $quiz_message['latest']; ?> (out of <?php echo $quiz_message['max']; ?>)</div>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="row g-4 align-items-center mb-4 fade-up">
                <div class="col-lg-5">
                    <img src="<?php echo htmlspecialchars($course_image); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid rounded-4 shadow">
                </div>
                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge-outline mb-2 d-inline-block">Course</span>
                            <h1 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h1>
                        </div>
                        <?php if (isAdmin()): ?>
                            <a href="admin/edit_course.php?id=<?php echo $course_id; ?>" class="btn btn-sm btn-outline-warning">
                                Edit Course
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
                    <?php if ($is_enrolled): ?>
                        <div class="soft-card mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Progress</strong>
                                <span class="text-muted"><?php echo $completed_count; ?> / <?php echo count($lessons); ?> lessons</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $progress_percent; ?>%;"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <button type="submit" name="enroll" value="1" class="btn btn-primary btn-lg">
                                Enroll in this course
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($is_enrolled || isAdmin()): ?>
                <div class="row g-4">
                    <div class="col-lg-6 fade-right">
                        <div class="soft-card h-100">
                            <h4 class="mb-3">Lessons</h4>
                            <?php if ($lessons): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($lessons as $lesson):
                                        $lesson_image = safeImage($lesson['image_url'] ?? '');
                                    ?>
                                        <div class="list-group-item">
                                            <div class="d-flex gap-3">
                                                <div class="flex-shrink-0">
                                                    <img src="<?php echo htmlspecialchars($lesson_image); ?>" alt="" style="width:90px;height:65px;object-fit:cover;border-radius:12px;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($lesson['title']); ?></h6>
                                                        <?php if ($lesson['completed']): ?><span class="badge bg-success">Completed</span><?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">Duration: <?php echo $lesson['duration_minutes']; ?> min<?php if ($lesson['video_url']): ?> · Video<?php endif; ?></small>
                                                    <div class="mt-2">
                                                        <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <?php echo $lesson['completed'] ? 'Review lesson' : 'Start lesson'; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Lessons coming soon.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6 fade-left">
                        <div class="soft-card h-100">
                            <h4 class="mb-3">Quizzes</h4>
                            <?php if ($quizzes->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($quiz = $quizzes->fetch_assoc()):
                                        $best = $best_scores[$quiz['id']] ?? 0;
                                        $quiz_image = safeImage($quiz['image_url'] ?? '');
                                    ?>
                                        <div class="list-group-item">
                                            <div class="d-flex gap-3">
                                                <div class="flex-shrink-0">
                                                    <img src="<?php echo htmlspecialchars($quiz_image); ?>" alt="" style="width:90px;height:65px;object-fit:cover;border-radius:12px;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($quiz['question']); ?></h6>
                                                    <small class="text-muted">Worth <?php echo $quiz['points']; ?> XP · Best score: <?php echo $best; ?></small>
                                                    <div class="mt-2">
                                                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-outline-primary">Take quiz</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Quizzes coming soon.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="soft-card text-center fade-up">
                    <p class="mb-0">Enroll in the course to unlock lessons and quizzes.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

