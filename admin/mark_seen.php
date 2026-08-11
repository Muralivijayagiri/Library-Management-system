<?php
session_start();
header('Content-Type: application/json');

// Verify admin session
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

include "../config/db.php";

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

// Get student_id from POST data
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

if (!$student_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

// Admin ID is fixed as 1
$admin_id = 1;

// Update messages: set seen_status = 1 for unread student messages to admin
$stmt = $conn->prepare("UPDATE messages 
                        SET seen_status = 1 
                        WHERE sender_type = 'student' 
                          AND sender_id = ? 
                          AND receiver_id = ? 
                          AND seen_status = 0");
$stmt->bind_param("ii", $student_id, $admin_id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update seen status"
    ]);
}

$stmt->close();
$conn->close();
?>

