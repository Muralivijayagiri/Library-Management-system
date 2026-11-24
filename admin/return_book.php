<?php
session_start();
header("Content-Type: application/json");
include "../config/db.php";

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$borrow_id = $_POST['borrow_id'] ?? null;
$admin_password = $_POST['password'] ?? null;

if (!$borrow_id || !$admin_password) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
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
else if (hash_equals($stored_password, hash('sha256', $admin_password))) {
    $password_valid = true;
}

if (!$password_valid) {
    echo json_encode(["status" => "error", "message" => "Incorrect admin password."]);
    exit;
}

$stmt = $conn->prepare("UPDATE borrow_records SET status='returned', actual_return_date=CURDATE() WHERE id=?");
$stmt->bind_param("i", $borrow_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}
?>

