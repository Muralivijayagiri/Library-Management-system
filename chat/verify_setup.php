<?php
/**
 * Chat System Verification Script
 * Run this file in browser to verify chat system setup
 * URL: http://localhost/Library_Managment/chat/verify_setup.php
 */

session_start();
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Chat System Verification</h1>";
echo "<pre>";

// 1. Check database connection
echo "1. Checking database connection...\n";
include "../config/db.php";

if ($conn->connect_error) {
    echo "   ❌ FAILED: " . $conn->connect_error . "\n";
    exit;
} else {
    echo "   ✅ SUCCESS: Database connected\n";
}

// 2. Check if chat_messages table exists
echo "\n2. Checking if chat_messages table exists...\n";
$result = $conn->query("SHOW TABLES LIKE 'chat_messages'");

if ($result && $result->num_rows > 0) {
    echo "   ✅ SUCCESS: Table 'chat_messages' exists\n";
    
    // Check table structure
    $result = $conn->query("DESCRIBE chat_messages");
    echo "   Table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "     - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "   ❌ FAILED: Table 'chat_messages' does NOT exist\n";
    echo "   ⚠️  ACTION REQUIRED: Create the table using:\n";
    echo "      CREATE TABLE chat_messages (\n";
    echo "          id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "          student_id INT NOT NULL,\n";
    echo "          sender ENUM('student','admin') NOT NULL,\n";
    echo "          message TEXT NOT NULL,\n";
    echo "          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
    echo "      );\n";
}

// 3. Check file existence
echo "\n3. Checking API files...\n";
$files = [
    '../chat/fetch_messages.php' => 'fetch_messages.php',
    '../chat/send_message.php' => 'send_message.php'
];

foreach ($files as $path => $name) {
    if (file_exists($path)) {
        echo "   ✅ SUCCESS: {$name} exists\n";
    } else {
        echo "   ❌ FAILED: {$name} NOT found at {$path}\n";
    }
}

// 4. Check student session
echo "\n4. Checking student session...\n";
if (isset($_SESSION['student_logged_in'])) {
    echo "   ✅ SUCCESS: Student session active\n";
    echo "   Student ID: " . ($_SESSION['student_id'] ?? 'NOT SET') . "\n";
} else {
    echo "   ⚠️  INFO: No student session (this is normal if not logged in)\n";
}

// 5. Test database query
echo "\n5. Testing database query...\n";
try {
    $test_query = "SELECT COUNT(*) as count FROM chat_messages";
    $result = $conn->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   ✅ SUCCESS: Query executed successfully\n";
        echo "   Current messages in table: " . $row['count'] . "\n";
    } else {
        echo "   ❌ FAILED: Query error - " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Exception - " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Verification Complete!\n";
echo "</pre>";

$conn->close();
?>

