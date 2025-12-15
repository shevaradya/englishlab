<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
$manual_image = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $manual_image = trim($_POST['image_url'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    if (empty($title) || empty($description)) {
        $error = 'Title and description are required.';
    } else {
        $image_url = $manual_image !== '' ? $manual_image : DEFAULT_IMAGE_URL;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads/courses/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $filename = uniqid('course_') . '.' . $file_ext;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    $image_url = 'assets/uploads/courses/' . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WEBP.';
            }
        }
        
        if (empty($error)) {
            $slug_check = $conn->prepare("SELECT id FROM courses WHERE slug = ?");
            $slug_check->bind_param("s", $slug);
            $slug_check->execute();
            if ($slug_check->get_result()->num_rows > 0) {
                $slug .= '-' . time();
            }
            $slug_check->close();
            
            $stmt = $conn->prepare("INSERT INTO courses (title, slug, description, image_url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $slug, $description, $image_url);
            
            if ($stmt->execute()) {
                $success = 'Course added successfully!';
                $title = $description = $manual_image = '';
            } else {
                $error = 'Failed to add course.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="soft-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge-outline mb-1 d-inline-block">Admin · Course</span>
                            <h3 class="mb-0">Add New Course</h3>
                        </div>
                        <a href="list_courses.php" class="btn btn-sm btn-outline-secondary">Back to Courses</a>
                    </div>
                    <div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Course Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL (optional)</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($manual_image ?? ''); ?>" placeholder="https://example.com/cover.jpg">
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Upload Image (optional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Upload overrides URL. If neither provided, a curated image is used.</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="list_courses.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Course</button>
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

