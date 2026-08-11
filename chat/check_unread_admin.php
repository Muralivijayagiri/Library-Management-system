<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

include "../config/db.php";

// Verify admin session
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

// Query unread messages grouped by student_id
$query = "SELECT student_id,
                 COUNT(*) AS unread
          FROM chat_messages
          WHERE sender = 'student'
            AND seen_status = 0
          GROUP BY student_id";

$result = $conn->query($query);

$unread_data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unread_data[] = [
            'student_id' => (int)$row['student_id'],
            'unread' => (int)$row['unread']
        ];
    }
}

$conn->close();

// Return JSON response
echo json_encode([
    "status" => "success",
    "data" => $unread_data
]);
?>

