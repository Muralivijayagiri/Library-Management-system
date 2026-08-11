<?php
session_start();
header("Content-Type: application/json");
include "../config/db.php";

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Read POST values
$bookId = $_POST['book_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$bookId || !$action) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Validate action
if ($action !== "add" && $action !== "remove") {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit;
}

// Fetch current total_copies from books table
$bookQuery = "SELECT total_copies FROM books WHERE id = ?";
$bookStmt = $conn->prepare($bookQuery);
$bookStmt->bind_param("i", $bookId);
$bookStmt->execute();
$bookResult = $bookStmt->get_result();

if ($bookResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Book not found"]);
    exit;
}

$bookData = $bookResult->fetch_assoc();
$totalCopies = $bookData['total_copies'];

// Fetch borrowed_count
$borrowQuery = "SELECT COUNT(*) AS borrowed FROM borrow_records WHERE book_id = ? AND actual_return_date IS NULL";
$borrowStmt = $conn->prepare($borrowQuery);
$borrowStmt->bind_param("i", $bookId);
$borrowStmt->execute();
$borrowResult = $borrowStmt->get_result();
$borrowData = $borrowResult->fetch_assoc();
$borrowedCount = $borrowData['borrowed'] ?? 0;

// Handle action
if ($action === "remove") {
    // Check if we can reduce below borrowed count
    if ($totalCopies <= $borrowedCount) {
        echo json_encode([
            "success" => false,
            "message" => "Cannot reduce below borrowed count"
        ]);
        exit;
    }
    $totalCopies--;
} elseif ($action === "add") {
    $totalCopies++;
}

// Update books table with new total_copies
$updateQuery = "UPDATE books SET total_copies = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ii", $totalCopies, $bookId);

if (!$updateStmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database update failed"]);
    exit;
}

// Calculate new available
$available = $totalCopies - $borrowedCount;

// Return JSON response
echo json_encode([
    "success" => true,
    "total" => $totalCopies,
    "borrowed" => $borrowedCount,
    "available" => $available
]);
?>

