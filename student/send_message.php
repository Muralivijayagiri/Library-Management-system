<?php
session_start();
header('Content-Type: application/json');

// Validate student session
if (!isset($_SESSION['student_logged_in'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

include "../config/db.php";

// Get student ID from session
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

// Get message from POST data
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate message
if (empty($message)) {
    echo json_encode([
        "status" => "error",
        "message" => "Message cannot be empty"
    ]);
    exit;
}

// Prepare and execute insert query
$stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, receiver_id, message, timestamp, seen_status) VALUES (?, 'student', 1, ?, NOW(), 0)");
$stmt->bind_param("is", $student_id, $message);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send message"
    ]);
}

$stmt->close();
$conn->close();
?>

