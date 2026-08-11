<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

// Get distinct student IDs who have messages in chat_messages table
$query = "SELECT DISTINCT student_id 
          FROM chat_messages 
          ORDER BY student_id";

$result = $conn->query($query);

$students_with_messages = [];

while ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    
    // Get student details
    $student_query = "SELECT name, roll_number FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        
        // Get last message from chat_messages table
        $last_msg_query = "SELECT message, created_at 
                          FROM chat_messages 
                          WHERE student_id = ?
                          ORDER BY created_at DESC 
                          LIMIT 1";
        $last_msg_stmt = $conn->prepare($last_msg_query);
        $last_msg_stmt->bind_param("i", $student_id);
        $last_msg_stmt->execute();
        $last_msg_result = $last_msg_stmt->get_result();
        
        $last_message = '';
        $last_timestamp = '';
        if ($last_msg_result->num_rows > 0) {
            $last_msg = $last_msg_result->fetch_assoc();
            $last_message = $last_msg['message'];
            $last_timestamp = $last_msg['created_at'];
        }
        $last_msg_stmt->close();
        
        // Count unread messages (student messages that admin hasn't seen yet)
        $unread_query = "SELECT COUNT(*) as unread_count 
                        FROM chat_messages 
                        WHERE sender = 'student' 
                          AND student_id = ? 
                          AND seen_status = 0";
        $unread_stmt = $conn->prepare($unread_query);
        $unread_stmt->bind_param("i", $student_id);
        $unread_stmt->execute();
        $unread_result = $unread_stmt->get_result();
        $unread_data = $unread_result->fetch_assoc();
        $unread_count = (int)$unread_data['unread_count'];
        $unread_stmt->close();
        
        $students_with_messages[] = [
            'id' => $student_id,
            'name' => $student['name'],
            'roll_number' => $student['roll_number'],
            'last_message' => $last_message,
            'last_timestamp' => $last_timestamp,
            'unread_count' => $unread_count
        ];
        
        $student_stmt->close();
    }
}

$conn->close();

// Sort by last timestamp (most recent first)
usort($students_with_messages, function($a, $b) {
    if (empty($a['last_timestamp'])) return 1;
    if (empty($b['last_timestamp'])) return -1;
    return strtotime($b['last_timestamp']) - strtotime($a['last_timestamp']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Inbox - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f3f6ff 0%, #e8edff 100%);
            display: flex;
            color: #1a1a2e;
        }

        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
        }

        .topbar h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .chat-inbox-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .chat-inbox-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .chat-inbox-header h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chat-inbox-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        #adminChatList {
            background: #f8f9ff;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            padding: 20px;
            height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .chat-list-item {
            background: #ffffff;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .chat-list-item:hover {
            border-color: #b3c0ed;
            box-shadow: 0 4px 12px rgba(179, 192, 237, 0.2);
            transform: translateY(-2px);
        }

        .chat-list-item:last-child {
            margin-bottom: 0;
        }

        .chat-list-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .chat-list-item-student {
            font-weight: 600;
            font-size: 16px;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            flex: 1;
        }

        .chat-list-item-preview {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        .chat-list-item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-size: 12px;
            color: #999;
        }

        .chat-list-item-roll {
            font-weight: 500;
        }

        .chat-list-item-time {
            color: #999;
        }

        .unread-badge {
            background: #E53935;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: auto;
            font-weight: 600;
        }

        .chat-list-item-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .no-chats-message {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="content">

        <div class="topbar">
            <h2>Chat Inbox</h2>
        </div>

        <div class="chat-inbox-container">
            <div class="chat-inbox-header">
                <h3>Chat Inbox</h3>
                <p>View and reply to student chats.</p>
            </div>

            <div id="adminChatList">
                <?php if (empty($students_with_messages)): ?>
                    <div class="no-chats-message">
                        No chat conversations yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($students_with_messages as $student): ?>
                        <a href="chat_view.php?student_id=<?= htmlspecialchars($student['id'], ENT_QUOTES) ?>" class="chat-list-item-link">
                            <div class="chat-list-item">
                                <div class="chat-list-item-header">
                                    <div class="chat-list-item-student">
                                        <?= htmlspecialchars($student['name'], ENT_QUOTES) ?>
                                        <span class="unread-badge" id="unread_badge_<?= $student['id'] ?>" style="display: <?= $student['unread_count'] > 0 ? 'block' : 'none' ?>;"><?= $student['unread_count'] ?></span>
                                    </div>
                                </div>
                                <div class="chat-list-item-preview">
                                    <?php 
                                    $preview = htmlspecialchars($student['last_message'], ENT_QUOTES);
                                    if (strlen($preview) > 40) {
                                        $preview = substr($preview, 0, 40) . '…';
                                    }
                                    echo $preview ?: 'No messages';
                                    ?>
                                </div>
                                <div class="chat-list-item-meta">
                                    <span class="chat-list-item-roll">Roll: <?= htmlspecialchars($student['roll_number'], ENT_QUOTES) ?></span>
                                    <?php if ($student['last_timestamp']): ?>
                                        <span class="chat-list-item-time">
                                            <?php
                                            $timestamp = new DateTime($student['last_timestamp']);
                                            $now = new DateTime();
                                            $diff = $now->diff($timestamp);
                                            
                                            if ($diff->days == 0) {
                                                echo $timestamp->format('H:i');
                                            } else if ($diff->days == 1) {
                                                echo 'Yesterday';
                                            } else if ($diff->days < 7) {
                                                echo $timestamp->format('D');
                                            } else {
                                                echo $timestamp->format('M d');
                                            }
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        // Function to check and update unread message badges
        function updateUnreadBadges() {
            fetch('../chat/check_unread_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // First, hide all badges
                    document.querySelectorAll('.unread-badge').forEach(badge => {
                        badge.style.display = 'none';
                    });

                    // Then show and update badges for students with unread messages
                    data.data.forEach(item => {
                        const badge = document.getElementById('unread_badge_' + item.student_id);
                        if (badge) {
                            if (item.unread > 0) {
                                badge.textContent = item.unread;
                                badge.style.display = 'block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    });
                } else {
                    console.error('Error fetching unread messages:', data.message);
                }
            })
            .catch(error => {
                console.error('Network error checking unread messages:', error);
            });
        }

        // Check unread messages when page loads
        updateUnreadBadges();

        // Auto-refresh unread badges every 3 seconds
        setInterval(updateUnreadBadges, 3000);
    </script>

</body>
</html>
