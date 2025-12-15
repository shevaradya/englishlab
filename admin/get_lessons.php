<?php
require_once '../config/db.php';

if (!isAdmin()) {
    http_response_code(403);
    exit;
}

$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id) {
    $lessons_query = "SELECT id, title FROM lessons WHERE course_id = ? ORDER BY order_index";
    $stmt = $conn->prepare($lessons_query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lessons = [];
    while ($lesson = $result->fetch_assoc()) {
        $lessons[] = $lesson;
    }
    
    header('Content-Type: application/json');
    echo json_encode($lessons);
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>

