<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

include "../config/db.php";

// Check if student_id is provided
if (!isset($_POST['student_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

$student_id = (int)$_POST['student_id'];

// Verify session - allow both student and admin
$is_student = isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'];
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

if (!$is_student && !$is_admin) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

// If student, verify they can only fetch their own messages
if ($is_student && $_SESSION['student_id'] != $student_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}
// Admin can fetch any student's messages (no additional check needed)

// Fetch messages for this student
$query = "SELECT id, sender, message, created_at 
          FROM chat_messages 
          WHERE student_id = ? 
          ORDER BY created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => (int)$row['id'],
        "sender_type" => $row['sender'],
        "message" => $row['message'],
        "timestamp" => $row['created_at']
    ];
}

$stmt->close();

// Mark messages as seen based on who is fetching
if ($is_student) {
    // Student is viewing: mark all admin messages as seen
    $updateQuery = "UPDATE chat_messages 
                    SET seen_status = 1 
                    WHERE sender = 'admin' 
                    AND student_id = ? 
                    AND seen_status = 0";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $student_id);
    $updateStmt->execute();
    $updateStmt->close();
} elseif ($is_admin) {
    // Admin is viewing: mark all student messages as seen
    $updateQuery = "UPDATE chat_messages 
                    SET seen_status = 1 
                    WHERE sender = 'student' 
                    AND student_id = ? 
                    AND seen_status = 0";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $student_id);
    $updateStmt->execute();
    $updateStmt->close();
}

$conn->close();

// Return JSON response
echo json_encode([
    "status" => "success",
    "messages" => $messages
]);
?>
