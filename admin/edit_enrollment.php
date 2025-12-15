<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list_enrollments.php');
    exit;
}

$error = '';
$success = '';

// Load enrollment
$stmt = $conn->prepare("SELECT * FROM enrollments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$enrollment_res = $stmt->get_result();
if ($enrollment_res->num_rows === 0) {
    $stmt->close();
    header('Location: list_enrollments.php');
    exit;
}
$enrollment = $enrollment_res->fetch_assoc();
$stmt->close();

$current_user_id = (int)$enrollment['user_id'];
$current_course_id = (int)$enrollment['course_id'];
$progress_percent = (int)($enrollment['progress_percent'] ?? 0);

$students = $conn->query("SELECT id, name, email FROM users WHERE role = 'student' ORDER BY name ASC");
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = intval($_POST['user_id'] ?? 0);
    $current_course_id = intval($_POST['course_id'] ?? 0);
    $progress_percent = intval($_POST['progress_percent'] ?? 0);

    if (!$current_user_id || !$current_course_id) {
        $error = 'Student and course are required.';
    } else {
        // prevent duplicate enrollment for same student/course (excluding this record)
        $check = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND id != ?");
        $check->bind_param("iii", $current_user_id, $current_course_id, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'This student is already enrolled in the selected course.';
        }
        $check->close();

        if (empty($error)) {
            $upd = $conn->prepare("UPDATE enrollments SET user_id = ?, course_id = ?, progress_percent = ? WHERE id = ?");
            $upd->bind_param("iiii", $current_user_id, $current_course_id, $progress_percent, $id);
            if ($upd->execute()) {
                $success = 'Enrollment updated successfully.';
            } else {
                $error = 'Failed to update enrollment.';
            }
            $upd->close();
        }
    }

    $students = $conn->query("SELECT id, name, email FROM users WHERE role = 'student' ORDER BY name ASC");
    $courses = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-7 mx-auto">
                <div class="soft-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge-outline mb-1 d-inline-block">Admin · Enrollments</span>
                            <h3 class="mb-0">Edit Enrollment</h3>
                        </div>
                        <a href="list_enrollments.php" class="btn btn-sm btn-outline-secondary">Back to Enrollments</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Student *</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Select a student</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo $current_user_id === (int)$student['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course *</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select a course</option>
                                <?php while ($course = $courses->fetch_assoc()): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $current_course_id === (int)$course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="progress_percent" class="form-label">Progress (%)</label>
                            <input type="number" class="form-control" id="progress_percent" name="progress_percent" value="<?php echo $progress_percent; ?>" min="0" max="100">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


