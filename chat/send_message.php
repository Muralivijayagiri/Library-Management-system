<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

include "../config/db.php";

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

// Determine if student or admin is sending
$is_student = isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'];
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

if (!$is_student && !$is_admin) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
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

$student_id = null;
$sender = '';

if ($is_student) {
    // Student sending message
    $student_id = $_SESSION['student_id'] ?? null;
    if (!$student_id) {
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized"
        ]);
        exit;
    }
    $sender = 'student';
} else if ($is_admin) {
    // Admin sending message - student_id must be provided in POST
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    if (!$student_id) {
        echo json_encode([
            "status" => "error",
            "message" => "Student ID required"
        ]);
        exit;
    }
    $sender = 'admin';
}

// Insert message into chat_messages table
$stmt = $conn->prepare("INSERT INTO chat_messages (student_id, sender, message) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $student_id, $sender, $message);

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

