<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$quiz_id = intval($_GET['id'] ?? 0);

if (!$quiz_id) {
    header('Location: index.php');
    exit;
}

// Get quiz details
$quiz_query = "SELECT q.*, c.title as course_title, c.id as course_id 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE q.id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$quiz = $quiz_result->fetch_assoc();
$stmt->close();

// Check enrollment
$user_id = $_SESSION['user']['id'];
$enroll_check = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$enroll_check->bind_param("ii", $user_id, $quiz['course_id']);
$enroll_check->execute();
$is_enrolled = $enroll_check->get_result()->num_rows > 0 || isAdmin();
$enroll_check->close();

if (!$is_enrolled) {
    header('Location: course.php?id=' . $quiz['course_id']);
    exit;
}

$attempt_stmt = $conn->prepare("SELECT score, max_score FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
$attempt_stmt->bind_param("ii", $user_id, $quiz_id);
$attempt_stmt->execute();
$previous_attempt = $attempt_stmt->get_result()->fetch_assoc();
$attempt_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Interactive English Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Courses</a></li>
                                    <li class="breadcrumb-item"><a href="course.php?id=<?php echo $quiz['course_id']; ?>"><?php echo htmlspecialchars($quiz['course_title']); ?></a></li>
                                    <li class="breadcrumb-item active">Quiz</li>
                                </ol>
                            </nav>
                            <?php if (isAdmin()): ?>
                                <a href="admin/edit_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-sm btn-outline-warning">
                                    Edit Quiz
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <span class="badge bg-info"><?php echo $quiz['points']; ?> XP</span>
                        </div>
                        
                        <h3 class="mb-4"><?php echo htmlspecialchars($quiz['question']); ?></h3>
                        
                        <?php if ($previous_attempt): ?>
                            <div class="alert alert-info">
                                <strong>You have already taken this quiz.</strong><br>
                                Score: <?php echo $previous_attempt['score']; ?> / <?php echo $previous_attempt['max_score']; ?>
                            </div>
                            <a href="course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-primary">Back to Course</a>
                        <?php else: ?>
                        <form method="POST" action="submit_quiz.php" id="quizForm">
                            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                            
                            <div class="quiz-options">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="answer" id="option_a" value="A" required>
                                    <label class="form-check-label" for="option_a">
                                        <strong>A)</strong> <?php echo htmlspecialchars($quiz['option_a']); ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="answer" id="option_b" value="B" required>
                                    <label class="form-check-label" for="option_b">
                                        <strong>B)</strong> <?php echo htmlspecialchars($quiz['option_b']); ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="answer" id="option_c" value="C" required>
                                    <label class="form-check-label" for="option_c">
                                        <strong>C)</strong> <?php echo htmlspecialchars($quiz['option_c']); ?>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="answer" id="option_d" value="D" required>
                                    <label class="form-check-label" for="option_d">
                                        <strong>D)</strong> <?php echo htmlspecialchars($quiz['option_d']); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-lg">Submit Answer</button>
                            </div>
                        </form>
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

