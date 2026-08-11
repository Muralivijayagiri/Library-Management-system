<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

include "../config/db.php";

// Verify student session
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

// Get student ID from session
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Student ID not found"
    ]);
    exit;
}

// Query unread messages (admin messages that student hasn't seen)
$query = "SELECT COUNT(*) AS unread 
          FROM chat_messages 
          WHERE student_id = ? 
            AND sender = 'admin' 
            AND seen_status = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_count = (int)$row['unread'];

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    "status" => "success",
    "unread" => $unread_count
]);
?>

