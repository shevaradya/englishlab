<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$student_id = intval($_GET['id'] ?? 0);

if (!$student_id) {
    header('Location: list_students.php');
    exit;
}

// Get student details
$student_query = "SELECT * FROM users WHERE id = ? AND role = 'student'";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    header('Location: list_students.php');
    exit;
}

$student = $student_result->fetch_assoc();
$stmt->close();

// Get enrolled courses
$enrollments = $conn->prepare("
    SELECT c.*, e.progress_percent, e.enrolled_at,
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress lp 
     JOIN lessons l ON lp.lesson_id = l.id 
     WHERE l.course_id = c.id AND lp.user_id = ? AND lp.completed = 1) as completed_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$enrollments->bind_param("ii", $student_id, $student_id);
$enrollments->execute();
$enrolled_courses = $enrollments->get_result();

// Get quiz results
$quiz_results = $conn->prepare("
    SELECT qr.*, q.question, c.title as course_title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    WHERE qr.user_id = ?
    ORDER BY qr.taken_at DESC
    LIMIT 20
");
$quiz_results->bind_param("i", $student_id);
$quiz_results->execute();
$results = $quiz_results->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="badge-outline mb-2 d-inline-block">Admin · Student Detail</span>
                <h1 class="mb-0"><?php echo htmlspecialchars($student['name']); ?></h1>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($student['email']); ?></p>
            </div>
            <a href="list_students.php" class="btn btn-outline-secondary">← Back to Students</a>
        </div>

        <div class="soft-card mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                    <p class="mb-1"><strong>Last Active:</strong> <?php echo $student['last_active'] ? date('M d, Y', strtotime($student['last_active'])) : 'Never'; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-inline-flex flex-wrap gap-2">
                        <span class="xp-chip"><?php echo $student['xp']; ?> XP</span>
                        <span class="streak-chip">🔥 <?php echo $student['streak']; ?> days</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="soft-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Enrolled Courses</h5>
                    </div>
                    <?php if ($enrolled_courses->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($course = $enrolled_courses->fetch_assoc()): 
                                $progress = $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                            ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                                        <small class="text-muted"><?php echo $progress; ?>%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No enrolled courses.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="soft-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Recent Quiz Results</h5>
                    </div>
                    <?php if ($results->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($result = $results->fetch_assoc()): 
                                        $score_percent = round(($result['score'] / $result['max_score']) * 100);
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['course_title']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $score_percent >= 70 ? 'success' : ($score_percent >= 50 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $result['score']; ?>/<?php echo $result['max_score']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($result['taken_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No quiz results yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

