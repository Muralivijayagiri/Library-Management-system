<?php
session_start();
header("Content-Type: application/json");
include "../config/db.php";

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$roll = trim($_POST['roll'] ?? '');
$admin_password = $_POST['admin_password'] ?? '';

if (empty($roll) || empty($admin_password)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Get admin username from session
$admin_username = $_SESSION['admin_username'] ?? '';

if (empty($admin_username)) {
    echo json_encode(["status" => "error", "message" => "Admin session invalid"]);
    exit;
}

// Get admin password from database
$adminQuery = $conn->prepare("SELECT password FROM admin WHERE username = ?");
$adminQuery->bind_param("s", $admin_username);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();

if ($adminResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Admin not found"]);
    exit;
}

$admin = $adminResult->fetch_assoc();
$stored_password = $admin['password'];

// Verify admin password
// Try password_verify first (for password_hash), then fallback to SHA256 for backward compatibility
$password_valid = false;

// Try password_verify (for passwords stored with password_hash)
if (password_verify($admin_password, $stored_password)) {
    $password_valid = true;
} 
// Fallback to SHA256 (for existing passwords)
else if (hash('sha256', $admin_password) === $stored_password) {
    $password_valid = true;
}

if (!$password_valid) {
    echo json_encode(["status" => "error", "message" => "Incorrect admin password."]);
    exit;
}

// Check if student exists
$studentCheck = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
$studentCheck->bind_param("s", $roll);
$studentCheck->execute();
$studentResult = $studentCheck->get_result();

if ($studentResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

// Check if student has active borrowed books
$borrowCheck = $conn->prepare("SELECT id FROM borrow_records WHERE student_roll = ? AND status = 'borrowed'");
$borrowCheck->bind_param("s", $roll);
$borrowCheck->execute();
$borrowResult = $borrowCheck->get_result();

if ($borrowResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Cannot delete! Student has active borrowed books."]);
    exit;
}

// Get student profile image path before deletion
$studentData = $studentResult->fetch_assoc();
$studentId = $studentData['id'];

$imageQuery = $conn->prepare("SELECT profile_image FROM students WHERE id = ?");
$imageQuery->bind_param("i", $studentId);
$imageQuery->execute();
$imageResult = $imageQuery->get_result();
$studentImage = $imageResult->fetch_assoc();

// Delete student
$deleteStmt = $conn->prepare("DELETE FROM students WHERE roll_number = ?");
$deleteStmt->bind_param("s", $roll);

if ($deleteStmt->execute()) {
    // Delete profile image if exists
    if ($studentImage && !empty($studentImage['profile_image'])) {
        $imagePath = "../uploads/student_profiles/" . $studentImage['profile_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Student removed successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
}

$conn->close();
?>

