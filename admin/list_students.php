<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Get all students
$students_query = "SELECT id, name, email, xp, streak, last_active, created_at FROM users WHERE role = 'student' ORDER BY xp DESC";
$students = $conn->query($students_query);

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'XP', 'Streak', 'Last Active', 'Created At']);
    
    $students->data_seek(0);
    while ($student = $students->fetch_assoc()) {
        fputcsv($output, [
            $student['id'],
            $student['name'],
            $student['email'],
            $student['xp'],
            $student['streak'],
            $student['last_active'] ?? 'Never',
            $student['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="badge-outline mb-2 d-inline-block">Admin · Students</span>
                <h1 class="mb-0">Manage Students</h1>
                <p class="text-muted mb-0">Monitor student progress, XP, and engagement.</p>
            </div>
            <a href="?export=csv" class="btn btn-success">Export CSV</a>
        </div>

        <div class="soft-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>XP</th>
                            <th>Streak</th>
                            <th>Last Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><span class="xp-chip"><?php echo $student['xp']; ?> XP</span></td>
                                <td><span class="streak-chip">🔥 <?php echo $student['streak']; ?></span></td>
                                <td><?php echo $student['last_active'] ? date('M d, Y', strtotime($student['last_active'])) : 'Never'; ?></td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

