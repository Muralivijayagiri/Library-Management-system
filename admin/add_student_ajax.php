<?php
session_start();
header("Content-Type: application/json");
include "../config/db.php";
include "../config/config.php";

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$roll_number = trim($_POST['roll_number'] ?? '');
$branch = trim($_POST['branch'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';

// Validate required fields
if (empty($name) || empty($roll_number) || empty($branch) || empty($email) || empty($mobile) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All required fields must be filled"]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit;
}

// Check if roll_number already exists
$checkRoll = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
$checkRoll->bind_param("s", $roll_number);
$checkRoll->execute();
$rollResult = $checkRoll->get_result();

if ($rollResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Student already exists"]);
    exit;
}

// Check if email already exists
$checkEmail = $conn->prepare("SELECT id FROM students WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$emailResult = $checkEmail->get_result();

if ($emailResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Student already exists"]);
    exit;
}

// Handle profile image upload
$profile_image = null;
if (!empty($_FILES['profile_image']['name'])) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['profile_image']['type'];
    $file_size = $_FILES['profile_image']['size'];
    
    // Validate file type
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(["status" => "error", "message" => "Invalid file type. Only images are allowed."]);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "File size too large. Maximum size is 5MB."]);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($STUDENT_PROFILES_PATH)) {
        mkdir($STUDENT_PROFILES_PATH, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $profile_image = time() . "_" . uniqid() . "." . $file_extension;
    $upload_path = $STUDENT_PROFILES_PATH . $profile_image;
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload profile image"]);
        exit;
    }
}

// Hash password using password_hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert student into database
$stmt = $conn->prepare("INSERT INTO students (name, roll_number, branch, email, mobile, password, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $roll_number, $branch, $email, $mobile, $hashed_password, $profile_image);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Student added successfully"
    ]);
} else {
    // If insert failed and we uploaded an image, delete it
    if ($profile_image && file_exists($STUDENT_PROFILES_PATH . $profile_image)) {
        unlink($STUDENT_PROFILES_PATH . $profile_image);
    }
    
    error_log("add_student_ajax: " . $conn->error);
    echo json_encode([
        "status" => "error",
        "message" => "A database error occurred."
    ]);
}

$conn->close();
?>

