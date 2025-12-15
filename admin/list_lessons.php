<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$lessons_query = "
    SELECT l.*, c.title AS course_title
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    ORDER BY c.title ASC, l.order_index ASC, l.title ASC
";
$lessons = $conn->query($lessons_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Manage Lessons</h1>
                <p class="text-muted mb-0">View and edit all lessons across courses.</p>
            </div>
            <a href="add_lesson.php" class="btn btn-primary">Add New Lesson</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Lesson deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="soft-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Lesson Title</th>
                            <th style="width: 120px;">Order</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($lessons->num_rows > 0): ?>
                            <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lesson['course_title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                    <td><?php echo (int)$lesson['order_index']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="../lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-outline-primary">View</a>
                                            <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-outline-warning">Edit</a>
                                            <a href="delete_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this lesson? This action cannot be undone.');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No lessons found.
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


