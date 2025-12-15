<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$quizzes_query = "
    SELECT q.*, c.title AS course_title
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    ORDER BY c.title ASC, q.id ASC
";
$quizzes = $conn->query($quizzes_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Manage Quizzes</h1>
                <p class="text-muted mb-0">View and edit all quizzes across courses.</p>
            </div>
            <a href="add_quiz.php" class="btn btn-primary">Add New Quiz</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Quiz deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="soft-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Question</th>
                            <th style="width: 120px;">Points</th>
                            <th style="width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($quizzes->num_rows > 0): ?>
                            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($quiz['course_title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($quiz['question'], 0, 80, '...')); ?></td>
                                    <td><?php echo (int)$quiz['points']; ?> XP</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="../quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-primary">View</a>
                                            <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-warning">Edit</a>
                                            <a href="delete_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No quizzes found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


