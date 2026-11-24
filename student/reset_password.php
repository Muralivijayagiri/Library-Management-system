<?php
session_start();
header("Content-Type: application/json");

// Verify student session
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    echo json_encode(["status" => "error", "message" => "Session expired. Please login again."]);
    exit;
}

// Get student ID from session
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(["status" => "error", "message" => "Invalid session."]);
    exit;
}

// Include database connection
include "../config/db.php";

// Get POST data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Validate input
if (empty($current_password) || empty($new_password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
    exit;
}

// Fetch student's current password hash from database
$stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student not found."]);
    exit;
}

$student = $result->fetch_assoc();
$stored_password_hash = $student['password'];

// Verify current password
// Try password_verify() first (for password_hash/bcrypt)
$password_match = false;

if (isset($stored_password_hash) && password_verify($current_password, $stored_password_hash)) {
    $password_match = true;
} else {
    // Fallback: compare SHA256 hash (for SHA2 in database)
    if (isset($stored_password_hash)) {
        $current_password_hash = hash('sha256', $current_password);
        if (hash_equals($stored_password_hash, $current_password_hash)) {
            $password_match = true;
        }
    }
}

if (!$password_match) {
    echo json_encode(["status" => "error", "message" => "Incorrect current password."]);
    exit;
}

// Hash the new password with bcrypt. Login supports both bcrypt and the
// legacy SHA-256 fallback, so resets should always upgrade to bcrypt.
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in database
$update_stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_password_hash, $student_id);

if ($update_stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password. Please try again."]);
}

$update_stmt->close();
$conn->close();
?>

