<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$lesson_id = intval($_GET['id'] ?? 0);

if (!$lesson_id) {
    header('Location: index.php');
    exit;
}

// Get lesson details
$lesson_query = "SELECT l.*, c.title as course_title, c.id as course_id 
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ?";
$stmt = $conn->prepare($lesson_query);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson_result = $stmt->get_result();

if ($lesson_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$lesson = $lesson_result->fetch_assoc();
$stmt->close();

// Check enrollment
$user_id = $_SESSION['user']['id'];
$enroll_check = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$enroll_check->bind_param("ii", $user_id, $lesson['course_id']);
$enroll_check->execute();
$is_enrolled = $enroll_check->get_result()->num_rows > 0 || isAdmin();
$enroll_check->close();

if (!$is_enrolled) {
    header('Location: course.php?id=' . $lesson['course_id']);
    exit;
}

// Handle lesson completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    // Check if already completed
    $check = $conn->prepare("SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
    $check->bind_param("ii", $user_id, $lesson_id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();
    
    if (!$exists) {
        // Mark as completed
        $insert = $conn->prepare("INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
        $insert->bind_param("ii", $user_id, $lesson_id);
        $insert->execute();
        $insert->close();
        
        // Award XP (10 XP per lesson)
        $xp_award = 10;
        $update_xp = $conn->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
        $update_xp->bind_param("ii", $xp_award, $user_id);
        $update_xp->execute();
        $update_xp->close();
        
        // Update session XP
        $_SESSION['user']['xp'] += $xp_award;
        
        // Update streak
        $today = date('Y-m-d');
        $user_query = $conn->prepare("SELECT last_active, streak FROM users WHERE id = ?");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_data = $user_query->get_result()->fetch_assoc();
        $user_query->close();
        
        if ($user_data['last_active'] === $today) {
            // Already active today, no change
        } elseif ($user_data['last_active'] === date('Y-m-d', strtotime('-1 day'))) {
            // Consecutive day, increment streak
            $new_streak = $user_data['streak'] + 1;
            $update_streak = $conn->prepare("UPDATE users SET streak = ?, last_active = ? WHERE id = ?");
            $update_streak->bind_param("isi", $new_streak, $today, $user_id);
            $update_streak->execute();
            $update_streak->close();
            $_SESSION['user']['streak'] = $new_streak;
        } else {
            // Reset streak
            $update_streak = $conn->prepare("UPDATE users SET streak = 1, last_active = ? WHERE id = ?");
            $update_streak->bind_param("si", $today, $user_id);
            $update_streak->execute();
            $update_streak->close();
            $_SESSION['user']['streak'] = 1;
        }
        
        // Redirect with success message
        header('Location: lesson.php?id=' . $lesson_id . '&completed=1');
        exit;
    }
}

// Check if already completed
$progress_check = $conn->prepare("SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ? AND completed = 1");
$progress_check->bind_param("ii", $user_id, $lesson_id);
$progress_check->execute();
$is_completed = $progress_check->get_result()->num_rows > 0;
$progress_check->close();

// Get next lesson
$next_lesson_query = "SELECT id FROM lessons WHERE course_id = ? AND order_index > ? ORDER BY order_index ASC LIMIT 1";
$next_stmt = $conn->prepare($next_lesson_query);
$next_stmt->bind_param("ii", $lesson['course_id'], $lesson['order_index']);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
$next_lesson = $next_result->num_rows > 0 ? $next_result->fetch_assoc() : null;
$next_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <div class="container my-5">
        <?php if (isset($_GET['completed'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Congratulations!</strong> You completed this lesson and earned 10 XP! 🎉
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Courses</a></li>
                                    <li class="breadcrumb-item"><a href="course.php?id=<?php echo $lesson['course_id']; ?>"><?php echo htmlspecialchars($lesson['course_title']); ?></a></li>
                                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($lesson['title']); ?></li>
                                </ol>
                            </nav>
                            <?php if (isAdmin()): ?>
                                <a href="admin/edit_lesson.php?id=<?php echo $lesson_id; ?>" class="btn btn-sm btn-outline-warning">
                                    Edit Lesson
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <h1 class="mb-4"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                        
                        <?php if ($is_completed): ?>
                            <div class="alert alert-info">
                                <span class="badge bg-success me-2">✓ Completed</span> You've already completed this lesson.
                            </div>
                        <?php endif; ?>

                        <div class="lesson-content mb-4">
                            <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                        </div>

                        <?php if ($lesson['video_url']): ?>
                            <div class="video-wrapper mb-4">
                                <div class="ratio ratio-16x9">
                                    <iframe src="<?php echo htmlspecialchars($lesson['video_url']); ?>" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="course.php?id=<?php echo $lesson['course_id']; ?>" class="btn btn-secondary">Back to Course</a>
                            <div>
                                <?php if (!$is_completed && !isAdmin()): ?>
                                    <form method="POST" class="d-inline">
                                        <button type="submit" name="complete" class="btn btn-success">Mark as Complete (+10 XP)</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($next_lesson): ?>
                                    <a href="lesson.php?id=<?php echo $next_lesson['id']; ?>" class="btn btn-primary ms-2">Next Lesson →</a>
                                <?php else: ?>
                                    <a href="course.php?id=<?php echo $lesson['course_id']; ?>" class="btn btn-primary ms-2">Back to Course</a>
                                <?php endif; ?>
                            </div>
                        </div>
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

