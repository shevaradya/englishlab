<?php
require_once 'config/db.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$stats = [
    'students' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'],
    'courses'  => $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'],
    'lessons'  => $conn->query("SELECT COUNT(*) as count FROM lessons")->fetch_assoc()['count'],
    'quizzes'  => $conn->query("SELECT COUNT(*) as count FROM quizzes")->fetch_assoc()['count'],
];

$recent_enrollments = $conn->query("
    SELECT e.*, u.name as student_name, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
");

$top_students = $conn->query("
    SELECT name, email, xp, streak
    FROM users
    WHERE role = 'student'
    ORDER BY xp DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <section class="section-space pt-5">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3 fade-up">
                <div>
                    <span class="badge-outline mb-2 d-inline-block">Admin Console</span>
                    <h1 class="mb-0">Control Center</h1>
                    <p class="text-muted mb-0">Manage courses, monitor students, and keep the platform running smoothly.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="admin/add_course.php" class="btn btn-primary">Add Course</a>
                    <a href="admin/add_lesson.php" class="btn btn-outline-primary">Add Lesson</a>
                    <a href="admin/add_quiz.php" class="btn btn-outline-primary">Add Quiz</a>
                </div>
            </div>
            <div class="row g-4 mb-5 fade-up">
                <div class="col-md-3"><div class="stat-chip h-100"><h2><?php echo $stats['students']; ?></h2><span>Students</span></div></div>
                <div class="col-md-3"><div class="stat-chip h-100"><h2><?php echo $stats['courses']; ?></h2><span>Courses</span></div></div>
                <div class="col-md-3"><div class="stat-chip h-100"><h2><?php echo $stats['lessons']; ?></h2><span>Lessons</span></div></div>
                <div class="col-md-3"><div class="stat-chip h-100"><h2><?php echo $stats['quizzes']; ?></h2><span>Quizzes</span></div></div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6 fade-right">
                    <div class="soft-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Enrollments</h5>
                            <a href="admin/list_enrollments.php" class="badge-outline">Manage</a>
                        </div>
                        <?php if ($recent_enrollments->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle">
                                    <tbody>
                                        <?php while ($enrollment = $recent_enrollments->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No enrollments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 fade-left">
                    <div class="soft-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Top Students by XP</h5>
                            <a href="admin/list_students.php" class="badge-outline">View all</a>
                        </div>
                        <?php if ($top_students->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle">
                                    <tbody>
                                        <?php while ($student = $top_students->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small></td>
                                                <td><span class="xp-chip"><?php echo $student['xp']; ?> XP</span></td>
                                                <td><span class="streak-chip">🔥 <?php echo $student['streak']; ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No students yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Interactive English Lab · Admin view.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

