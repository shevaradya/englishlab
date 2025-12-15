<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$user_id = $user['id'];

// Calculate level
$level = floor($user['xp'] / 100);
$xp_for_next_level = ($level + 1) * 100;
$xp_progress = $user['xp'] % 100;
$xp_progress_percent = ($xp_progress / 100) * 100;

// Get all quiz results
$quiz_query = "SELECT qr.*, q.question, c.title as course_title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    WHERE qr.user_id = ?
    ORDER BY qr.taken_at DESC";
$quiz_stmt = $conn->prepare($quiz_query);
$quiz_stmt->bind_param("i", $user_id);
$quiz_stmt->execute();
$quiz_results = $quiz_stmt->get_result();

// Get course progress
$courses_query = "SELECT c.*, e.progress_percent,
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
    (SELECT COUNT(*) FROM lesson_progress lp 
     JOIN lessons l ON lp.lesson_id = l.id 
     WHERE l.course_id = c.id AND lp.user_id = ? AND lp.completed = 1) as completed_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("ii", $user_id, $user_id);
$courses_stmt->execute();
$enrolled_courses = $courses_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="me-2">📚</span> Interactive English Lab
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isAdmin() ? 'dashboard_admin.php' : 'dashboard_student.php'; ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Profile Header -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="profile-avatar" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: inline-flex; align-items: center; justify-content: center; font-size: 3rem; color: white;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <h3 class="text-primary"><?php echo $user['xp']; ?></h3>
                                <p class="text-muted mb-0">Total XP</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="text-warning">Level <?php echo $level; ?></h3>
                                <p class="text-muted mb-0">Current Level</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="text-danger">🔥 <?php echo $user['streak']; ?></h3>
                                <p class="text-muted mb-0">Day Streak</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- XP Progress -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Progress to Level <?php echo ($level + 1); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 style="width: <?php echo $xp_progress_percent; ?>%">
                                <?php echo $xp_progress; ?> / 100 XP
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <?php echo ($xp_for_next_level - $user['xp']); ?> XP needed for next level
                        </small>
                    </div>
                </div>

                <!-- Course Progress -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Course Progress</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($enrolled_courses->num_rows > 0): ?>
                            <?php while ($course = $enrolled_courses->fetch_assoc()): 
                                $progress = $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                            ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?php echo htmlspecialchars($course['title']); ?></span>
                                        <span><?php echo $progress; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $course['completed_lessons']; ?> / <?php echo $course['total_lessons']; ?> lessons completed</small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No enrolled courses yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quiz History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quiz History</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($quiz_results->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Question</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($result = $quiz_results->fetch_assoc()): 
                                            $score_percent = round(($result['score'] / $result['max_score']) * 100);
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['course_title']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($result['question'], 0, 50)) . '...'; ?></td>
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
                            <p class="text-muted">No quiz results yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Interactive English Lab. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

