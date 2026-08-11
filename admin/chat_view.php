<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

// Get student_id from GET parameter
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if (!$student_id) {
    header("Location: chat_inbox.php");
    exit;
}

// Fetch student details
$stmt = $conn->prepare("SELECT name, roll_number FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: chat_inbox.php");
    exit;
}

$student = $result->fetch_assoc();
$student_name = $student['name'];
$student_roll = $student['roll_number'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= htmlspecialchars($student_name, ENT_QUOTES) ?> - Admin Dashboard</title>
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

        .chat-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .chat-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(179, 192, 237, 0.2);
        }

        .chat-header h3 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 600;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chat-header-info {
            display: flex;
            gap: 20px;
            align-items: center;
            font-size: 14px;
            color: #666;
        }

        .chat-header-status {
            color: #8fa0ff;
            font-weight: 500;
        }

        #adminChatWindow {
            background: #f5f5f5;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            padding: 20px;
            height: 450px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .bubble-admin {
            padding: 10px;
            margin: 8px;
            border-radius: 12px;
            max-width: 70%;
            text-align: right;
            margin-left: auto;
            background: #DCF8C6;
            color: #1a1a2e;
            word-wrap: break-word;
        }

        .bubble-student {
            padding: 10px;
            margin: 8px;
            border-radius: 12px;
            max-width: 70%;
            text-align: left;
            margin-right: auto;
            background: #E0E0E0;
            color: #1a1a2e;
            word-wrap: break-word;
        }

        .message-timestamp {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            opacity: 0.8;
        }

        .chat-input-area {
            display: flex;
            gap: 12px;
        }

        #adminChatInput {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #1a1a2e;
        }

        #adminChatInput:focus {
            outline: none;
            border-color: #b3c0ed;
            box-shadow: 0 0 0 4px rgba(179, 192, 237, 0.1);
        }

        #adminChatSend {
            padding: 14px 28px;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(98, 122, 254, 0.25);
        }

        #adminChatSend:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(98, 122, 254, 0.35);
        }

        #adminChatSend:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="content">

        <div class="topbar">
            <h2>Chat with Student</h2>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h3><?= htmlspecialchars($student_name, ENT_QUOTES) ?></h3>
                <div class="chat-header-info">
                    <span>Roll: <?= htmlspecialchars($student_roll, ENT_QUOTES) ?></span>
                    <span class="chat-header-status">Chat with Student</span>
                </div>
            </div>

            <div id="adminChatWindow">
                <!-- Messages will be loaded here -->
            </div>

            <div class="chat-input-area">
                <input type="text" id="adminChatInput" placeholder="Type your message...">
                <button id="adminChatSend">Send</button>
            </div>
        </div>

    </div>

    <script>
        // Get student ID from PHP
        const studentId = <?= json_encode($student_id) ?>;

        // Function to fetch and render messages
        function loadMessages() {
            const formData = new FormData();
            formData.append('student_id', studentId);

            fetch('../chat/fetch_messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderMessages(data.messages);
                } else {
                    console.error('Error loading messages:', data.message);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
            });
        }

        // Function to render messages
        function renderMessages(messages) {
            const chatWindow = document.getElementById('adminChatWindow');
            
            // Clear existing messages to avoid duplicates
            chatWindow.innerHTML = '';

            if (messages.length === 0) {
                chatWindow.innerHTML = '<div style="text-align: center; color: #999; padding: 20px;">No messages yet. Start the conversation!</div>';
                return;
            }

            messages.forEach(msg => {
                const bubble = document.createElement('div');
                // For admin view: admin messages are right-aligned (bubble-admin), student messages are left-aligned (bubble-student)
                bubble.className = msg.sender_type === 'admin' ? 'bubble-admin' : 'bubble-student';
                
                // Format timestamp
                const timestamp = new Date(msg.timestamp);
                const timeStr = timestamp.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });

                bubble.innerHTML = `
                    <div>${escapeHtml(msg.message)}</div>
                    <div class="message-timestamp">${timeStr}</div>
                `;

                chatWindow.appendChild(bubble);
            });

            // Auto-scroll to bottom
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Function to send message
        function sendMessage() {
            const chatInput = document.getElementById('adminChatInput');
            const messageText = chatInput.value.trim();

            // If empty, do nothing
            if (messageText === '') {
                return;
            }

            // Send POST request to chat/send_message.php
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('message', messageText);

            fetch('../chat/send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Clear the input field
                    chatInput.value = '';
                    
                    // Immediately reload messages
                    loadMessages();
                } else {
                    console.error('Error sending message:', data.message);
                    alert('Failed to send message: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                alert('Network error. Please try again.');
            });
        }

        // Load messages immediately on page load
        loadMessages();

        // Auto-refresh messages every 3 seconds
        setInterval(loadMessages, 3000);

        // Send button click event
        document.getElementById("adminChatSend").addEventListener("click", sendMessage);

        // Enter key press event
        document.getElementById("adminChatInput").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });
    </script>

</body>
</html>
