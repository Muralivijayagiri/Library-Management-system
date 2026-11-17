<?php
/**
 * One-time script to create chat_messages table
 * Run this file once in browser: http://localhost/Library_Managment/chat/create_table.php
 * Then delete this file for security
 */

include "../config/db.php";

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Creating chat_messages Table</h1>";
echo "<pre>";

// Check if table already exists
$check_query = "SHOW TABLES LIKE 'chat_messages'";
$result = $conn->query($check_query);

if ($result && $result->num_rows > 0) {
    echo "⚠️  Table 'chat_messages' already exists.\n";
    echo "No action needed.\n";
} else {
    // Create the table
    $create_query = "CREATE TABLE chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        sender ENUM('student','admin') NOT NULL,
        message TEXT NOT NULL,
        seen_status TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_query)) {
        echo "✅ SUCCESS: Table 'chat_messages' created successfully!\n";
        echo "\nTable structure:\n";
        
        // Show table structure
        $desc_result = $conn->query("DESCRIBE chat_messages");
        while ($row = $desc_result->fetch_assoc()) {
            echo "  - {$row['Field']} ({$row['Type']})";
            if ($row['Null'] === 'NO') echo " NOT NULL";
            if ($row['Key'] === 'PRI') echo " PRIMARY KEY";
            if ($row['Extra'] === 'auto_increment') echo " AUTO_INCREMENT";
            if ($row['Default'] !== null) echo " DEFAULT {$row['Default']}";
            echo "\n";
        }
    } else {
        echo "❌ ERROR: Failed to create table.\n";
        echo "Error: " . $conn->error . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Done!\n";
echo "</pre>";

$conn->close();
?>

