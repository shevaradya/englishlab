<?php
require_once '../config/db.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id) {
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header('Location: list_enrollments.php');
exit;


