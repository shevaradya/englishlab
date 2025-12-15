<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$quiz_id = intval($_GET['id'] ?? 0);

if (!$quiz_id) {
    header('Location: list_quizzes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $delete = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $delete->bind_param("i", $quiz_id);
    $delete->execute();
    $delete->close();
    
    header('Location: list_quizzes.php?deleted=1');
    exit;
}

// Get quiz for confirmation
$quiz_query = "SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz) {
    header('Location: list_quizzes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Quiz - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">Delete Quiz</h3>
                    </div>
                    <div class="card-body">
                        <p class="alert alert-warning">Are you sure you want to delete this quiz?</p>
                        <p class="text-muted">
                            <strong>Course:</strong> <?php echo htmlspecialchars($quiz['course_title']); ?><br>
                            <strong>Question:</strong> <?php echo htmlspecialchars($quiz['question']); ?><br>
                            <strong>Points:</strong> <?php echo (int)$quiz['points']; ?> XP<br><br>
                            This action cannot be undone. All quiz results for this quiz will also be deleted.
                        </p>
                        
                        <form method="POST">
                            <div class="d-flex justify-content-between">
                                <a href="list_quizzes.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="confirm" class="btn btn-danger">Yes, Delete Quiz</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

