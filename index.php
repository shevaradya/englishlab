<?php
require_once 'config/db.php';

// Get all courses with stats
$courses_query = "SELECT c.*, 
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
    FROM courses c 
    ORDER BY c.created_at DESC";
$courses_result = $conn->query($courses_query);

$enrolled_courses = [];
if (isLoggedIn()) {
    $user_id = $_SESSION['user']['id'];
    $enroll_query = "SELECT course_id FROM enrollments WHERE user_id = ?";
    $stmt = $conn->prepare($enroll_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $enroll_result = $stmt->get_result();
    while ($row = $enroll_result->fetch_assoc()) {
        $enrolled_courses[] = $row['course_id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive English Lab - Learn English Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 hero-content fade-right">
                    <span class="badge-outline mb-3">#InteractiveLearning</span>
                    <h1>Learn English the Fun &amp; Interactive Way</h1>
                    <p>Gamified lessons, instant feedback quizzes, streaks, and badges—everything you need to stay motivated and fluent.</p>
                    <div class="hero-cta">
                        <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg">Start Learning</a>
                        <a href="courses.php" class="btn btn-outline-primary btn-lg">Browse Courses</a>
                        <?php else: ?>
                            <a href="<?php echo isAdmin() ? 'dashboard_admin.php' : 'dashboard_student.php'; ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
                            <a href="courses.php" class="btn btn-outline-primary btn-lg">Explore Courses</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 fade-left">
                <div class="hero-art">
                <div style="text-align:center;">
    <img src="assets/img/hero.jpg" alt="Learning illustration" style="width:250px; height:auto;">
</div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" id="courses">
        <div class="container">
            <div class="section-title fade-up">
                <span>Courses</span>
                <h2>Elevate Your English Skills</h2>
                <p>Experience bite-sized lessons and real-life scenarios with immersive media and instant practice activities.</p>
            </div>
            <div class="course-grid">
                <?php $delay = 0; while ($course = $courses_result->fetch_assoc()):
                    $is_enrolled = in_array($course['id'], $enrolled_courses);
                    $image_url = safeImage($course['image_url'] ?? '');
                    $delay += 100;
                ?>
                    <div class="course-tile fade-up" data-delay="<?php echo $delay; ?>">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="course-info">
                            <div class="course-meta">
                                <span class="pill"><?php echo $course['lesson_count']; ?> lessons</span>
                                <span class="pill progress-badge"><?php echo $course['student_count']; ?> learners</span>
                                <?php if ($is_enrolled): ?>
                                    <span class="pill progress-badge">Enrolled</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 140)); ?>...</p>
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm w-100"><?php echo $is_enrolled ? 'Continue Learning' : 'Start Learning'; ?></a>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="register.php" class="btn btn-primary btn-sm w-100">Enroll for Free</a>
                            <?php else: ?>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary btn-sm w-100">Manage Course</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="section-space" id="about">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6 fade-right">
                    <span class="badge-outline mb-3">About Interactive English Lab</span>
                    <h2>Built for curious minds, shaped by educators.</h2>
                    <p>We design delightful learning journeys powered by micro lessons, measurable progress, and dopamine-boosting micro-interactions—so students stay confident, consistent, and curious.</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-3">✨ Gamified progression with streaks, XP, and badges.</li>
                        <li class="mb-3">🎧 Multimedia content including video, audio, and speaking practice.</li>
                        <li class="mb-3">📈 Personalized dashboards for learners and admins.</li>
                    </ul>
                    <a href="about.php" class="btn btn-outline-primary">Discover Our Story</a>
                </div>
                <div class="col-lg-6 fade-left">
                    <div class="soft-card">
                        <h4>Why learners love us</h4>
                        <p class="mb-4 text-muted">Over 5,000 global learners have reached their language goals with our interactive learning lab.</p>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-chip">
                                    <h2>4.9/5</h2>
                                    <span>Average rating</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-chip">
                                    <h2>+12k</h2>
                                    <span>Lessons completed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space">
        <div class="container">
            <div class="soft-card text-center fade-up">
                <h3>Need help or want to collaborate?</h3>
                <p class="text-muted mb-4">Our support team is ready to help you get the most out of Interactive English Lab.</p>
                <a href="contact.php" class="btn btn-primary">Contact Us</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Learn boldly, speak confidently.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

