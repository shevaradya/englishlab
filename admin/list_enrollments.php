<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$query = "
    SELECT e.*, u.name AS student_name, u.email AS student_email, c.title AS course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
";
$enrollments = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="badge-outline mb-2 d-inline-block">Admin · Enrollments</span>
                <h1 class="mb-0">Manage Enrollments</h1>
                <p class="text-muted mb-0">View and manage which students are enrolled in which courses.</p>
            </div>
            <a href="add_enrollment.php" class="btn btn-primary">Add Enrollment</a>
        </div>

        <div class="soft-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Progress</th>
                            <th>Enrolled At</th>
                            <th style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($enrollments->num_rows > 0): ?>
                            <?php while ($row = $enrollments->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['student_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                    <td><?php echo (int)($row['progress_percent'] ?? 0); ?>%</td>
                                    <td><?php echo date('M d, Y', strtotime($row['enrolled_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="edit_enrollment.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning">Edit</a>
                                            <a href="delete_enrollment.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this enrollment?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No enrollments found.</td>
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


