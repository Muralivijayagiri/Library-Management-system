<?php
session_start();
require_once "../config/db.php";

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access."
    ]);
    exit;
}

// Validate POST data
if (!isset($_POST['student_id']) || !isset($_POST['book_name']) || !isset($_POST['expected_return'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields."
    ]);
    exit;
}

$student_id = $_POST['student_id'];
$book_name = $_POST['book_name'];
$expected_return = $_POST['expected_return'];

// Reminder message
$message = "Reminder: Your borrowed book \"$book_name\" is due on $expected_return. Please return it as soon as possible.";

// Prepare SQL
$sql = "INSERT INTO chat_messages (student_id, sender, message, created_at)
        VALUES (?, 'admin', ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $student_id, $message);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Reminder sent successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send reminder."
    ]);
}

$stmt->close();
$conn->close();
?>

