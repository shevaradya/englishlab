<?php
require_once 'config/db.php';

if (!isLoggedIn() || isAdmin()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();
$user_id = $user['id'];

$level = floor($user['xp'] / 100);
$xp_for_next_level = ($level + 1) * 100;
$xp_progress = $user['xp'] % 100;
$xp_progress_percent = ($xp_progress / 100) * 100;

$enrolled_query = "SELECT c.*, e.progress_percent, 
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress lp 
     JOIN lessons l ON lp.lesson_id = l.id 
     WHERE l.course_id = c.id AND lp.user_id = ? AND lp.completed = 1) as completed_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC";
$stmt = $conn->prepare($enrolled_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

$quiz_query = "SELECT qr.*, q.question, c.title as course_title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    WHERE qr.user_id = ?
    ORDER BY qr.taken_at DESC
    LIMIT 5";
$quiz_stmt = $conn->prepare($quiz_query);
$quiz_stmt->bind_param("i", $user_id);
$quiz_stmt->execute();
$quiz_results = $quiz_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <section class="section-space pt-5">
        <div class="container">
            <div class="row align-items-center mb-5 fade-up">
                <div class="col-lg-8">
                    <span class="badge-outline mb-2 d-inline-block">Learning streaks</span>
                    <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?> 👋</h1>
                    <p class="text-muted mb-0">You’re currently level <strong><?php echo $level; ?></strong>. Keep up the daily practice to unlock new badges and XP boosts.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <span class="xp-chip me-2"><?php echo $user['xp']; ?> XP</span>
                    <span class="streak-chip">🔥 <?php echo $user['streak']; ?> day streak</span>
                </div>
            </div>
            <div class="row g-4 mb-5 fade-up">
                <div class="col-md-4">
                    <div class="stat-chip h-100">
                        <h2><?php echo $user['xp']; ?></h2>
                        <span>Total XP</span>
                        <small class="text-muted">Level <?php echo $level; ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-chip h-100">
                        <h2>🔥 <?php echo $user['streak']; ?></h2>
                        <span>Current streak</span>
                        <small class="text-muted">Every day counts</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-chip h-100">
                        <h2><?php echo $enrolled_courses->num_rows; ?></h2>
                        <span>Active courses</span>
                        <small class="text-muted">Stay consistent</small>
                    </div>
                </div>
            </div>
            <div class="soft-card mb-5 fade-up">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0">Progress to level <?php echo $level + 1; ?></h5>
                    <span class="text-muted"><?php echo ($xp_for_next_level - $user['xp']); ?> XP to level up</span>
                </div>
                <div class="progress" style="height: 16px;">
                    <div class="progress-bar bg-gradient" style="width: <?php echo $xp_progress_percent; ?>%;"
                        aria-valuenow="<?php echo $xp_progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">My learning path</h3>
                    <a href="index.php#courses" class="btn btn-outline-primary btn-sm">Browse more courses</a>
                </div>
                <?php if ($enrolled_courses->num_rows > 0): ?>
                    <div class="course-grid">
                        <?php while ($course = $enrolled_courses->fetch_assoc()):
                            $image_url = safeImage($course['image_url'] ?? '');
                            $progress = $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                        ?>
                        <div class="course-tile fade-up">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <div class="course-info">
                                <div class="course-meta">
                                    <span class="pill progress-badge"><?php echo $progress; ?>% complete</span>
                                    <span class="pill"><?php echo $course['total_lessons']; ?> lessons</span>
                                </div>
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%;"></div>
                                </div>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">Continue</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No courses yet. <a href="index.php#courses">Start one now</a>!</div>
                <?php endif; ?>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="soft-card h-100 fade-left">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Quiz Results</h5>
                        </div>
                        <?php if ($quiz_results->num_rows > 0): ?>
                            <?php while ($result = $quiz_results->fetch_assoc()):
                                $score_percent = round(($result['score'] / $result['max_score']) * 100);
                                $state = $score_percent >= 70 ? 'success' : ($score_percent >= 50 ? 'warning' : 'danger');
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <strong><?php echo htmlspecialchars($result['course_title']); ?></strong>
                                        <p class="text-muted mb-0"><?php echo date('M d, Y', strtotime($result['taken_at'])); ?></p>
                                    </div>
                                    <span class="badge bg-<?php echo $state; ?>">
                                        <?php echo $result['score']; ?>/<?php echo $result['max_score']; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Complete a quiz to see your results here.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Keep the streak alive!</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

