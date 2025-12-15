<?php
require_once 'config/db.php';

$courses_query = "SELECT c.*, 
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count
    FROM courses c ORDER BY c.created_at DESC";
$courses = $conn->query($courses_query);

$enrolled = [];
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user']['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $enrolled[] = $row['course_id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <section class="hero hero--compact">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7 fade-right">
                    <span class="badge-outline mb-2 d-inline-block">Course Library</span>
                    <h1>Browse interactive English tracks</h1>
                    <p class="text-muted">Pick a course, earn XP, and see your progress instantly. Each track blends micro-lessons, quizzes, and speaking practice.</p>
                </div>
                <div class="col-lg-5 fade-left">
                    <div class="soft-card floating-card">
                        <h5 class="mb-3">Why learn with IELab?</h5>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2">✔️ Bite-sized lessons with video & audio</li>
                            <li class="mb-2">✔️ XP, streaks, and badges keep you motivated</li>
                            <li class="mb-2">✔️ Perfect for phones, tablets, and laptops</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="course-grid">
                <?php $delay = 0; while ($course = $courses->fetch_assoc()):
                    $image = safeImage($course['image_url'] ?? '');
                    $delay += 75;
                ?>
                    <div class="course-tile fade-up" data-delay="<?php echo $delay; ?>">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="course-info">
                            <div class="course-meta">
                                <span class="pill"><?php echo $course['lesson_count']; ?> lessons</span>
                                <span class="pill"><?php echo $course['student_count']; ?> learners</span>
                                <?php if (in_array($course['id'], $enrolled, true)): ?>
                                    <span class="pill progress-badge">Enrolled</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 140)); ?>...</p>
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">
                                    <?php echo in_array($course['id'], $enrolled, true) ? 'Continue Learning' : 'Start Course'; ?>
                                </a>
                            <?php elseif (!isLoggedIn()): ?>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#authModal">Enroll Free</button>
                            <?php else: ?>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary w-100">View Course</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Pick a path, keep the streak.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

