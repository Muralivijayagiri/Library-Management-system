<?php
session_start();
header('Content-Type: application/json');

// Validate admin session
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

include "../config/db.php";

// Get admin username from session
$admin_username = $_SESSION['admin_username'] ?? null;

if (!$admin_username) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

// Fetch admin ID from database using username
$stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$admin = $result->fetch_assoc();
$admin_id = $admin['id'];
$stmt->close();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

// Get POST parameters
$student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate input
if (empty($student_id) || empty($message)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

// Validate student_id is numeric
if (!is_numeric($student_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

$student_id = (int)$student_id;

// Prepare and execute insert query
$stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, receiver_id, message, timestamp, seen_status) VALUES (?, 'admin', ?, ?, NOW(), 0)");
$stmt->bind_param("iis", $admin_id, $student_id, $message);

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

