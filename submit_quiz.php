<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quiz_id']) || !isset($_POST['answer'])) {
    header('Location: index.php');
    exit;
}

$quiz_id = intval($_POST['quiz_id']);
$user_answer = strtoupper(trim($_POST['answer']));
$user_id = $_SESSION['user']['id'];

// Get quiz details
$quiz_query = "SELECT * FROM quizzes WHERE id = ?";
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

// Check if already taken (optional: allow retakes)
// For now, we'll allow retakes

// Prevent duplicate submissions
$attempt_check = $conn->prepare("SELECT score, max_score FROM quiz_results WHERE user_id = ? AND quiz_id = ? LIMIT 1");
$attempt_check->bind_param("ii", $user_id, $quiz_id);
$attempt_check->execute();
$existing_attempt = $attempt_check->get_result()->fetch_assoc();
$attempt_check->close();

if ($existing_attempt) {
    header('Location: course.php?id=' . $quiz['course_id'] . '&quiz_result=already_taken&score=' . $existing_attempt['score'] . '&max=' . $existing_attempt['max_score']);
    exit;
}

// Check answer
$is_correct = ($user_answer === $quiz['correct_answer']);
$score = $is_correct ? $quiz['points'] : 0;
$max_score = $quiz['points'];

// Save quiz result
$insert_result = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, max_score) VALUES (?, ?, ?, ?)");
$insert_result->bind_param("iiii", $user_id, $quiz_id, $score, $max_score);
$insert_result->execute();
$insert_result->close();

// Award XP equal to earned score on first attempt
if ($score > 0) {
    $update_xp = $conn->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
    $update_xp->bind_param("ii", $score, $user_id);
    $update_xp->execute();
    $update_xp->close();
    $_SESSION['user']['xp'] += $score;
}

// Get course ID for redirect
$course_query = "SELECT course_id FROM quizzes WHERE id = ?";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("i", $quiz_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
$course_data = $course_result->fetch_assoc();
$course_id = $course_data['course_id'];
$course_stmt->close();

// Redirect with result
$best_score = $score;
$status = $is_correct ? 'correct' : 'incorrect';
$params = http_build_query([
    'quiz_result' => $status,
    'score' => $score,
    'max' => $max_score,
    'best' => $best_score,
    'latest' => $score
]);
header('Location: course.php?id=' . $course_id . '&' . $params);
exit;
?>

