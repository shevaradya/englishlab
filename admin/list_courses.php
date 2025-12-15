<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Handle delete success message
$deleted = isset($_GET['deleted']) ? true : false;

// Get all courses
$courses_query = "SELECT c.*, 
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
    FROM courses c 
    ORDER BY c.created_at DESC";
$courses = $conn->query($courses_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Courses</h1>
            <a href="add_course.php" class="btn btn-primary">Add New Course</a>
        </div>

        <?php if ($deleted): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Course deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php while ($course = $courses->fetch_assoc()): 
                $image_url = $course['image_url'] ?: 'https://source.unsplash.com/featured/400x300/?english,learning,education,study';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card course-card h-100">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                            <div class="mb-3">
                                <span class="badge bg-info"><?php echo $course['lesson_count']; ?> Lessons</span>
                                <span class="badge bg-success"><?php echo $course['enrollment_count']; ?> Enrollments</span>
                            </div>
                            <div class="btn-group w-100">
                                <a href="../course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="add_lesson.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-success">Add Lesson</a>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

