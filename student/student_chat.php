<?php
// TODO: Backend/chat API will be implemented in later steps
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Get student ID from session
$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Admin - Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: transparent;
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
            max-width: 800px;
            margin: 0 auto;
        }

        .chat-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .chat-header h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chat-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        #chatWindow {
            background: #f8f9ff;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            padding: 20px;
            height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .bubble-student {
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

        .bubble-admin {
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

        #chatInput {
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

        #chatInput:focus {
            outline: none;
            border-color: #b3c0ed;
            box-shadow: 0 0 0 4px rgba(179, 192, 237, 0.1);
        }

        #chatSend {
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

        #chatSend:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(98, 122, 254, 0.35);
        }

        #chatSend:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="content">

        <div class="topbar">
            <h2>Chat with Admin</h2>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h3>Chat with Admin</h3>
                <p>This will be the student → admin chat area (coming soon).</p>
            </div>

            <div id="chatWindow">
                <!-- Messages will be loaded here -->
            </div>

            <div class="chat-input-area">
                <input type="text" id="chatInput" placeholder="Type your message...">
                <button id="chatSend">Send</button>
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
            const chatWindow = document.getElementById('chatWindow');
            
            // Clear existing messages to avoid duplicates
            chatWindow.innerHTML = '';

            if (messages.length === 0) {
                chatWindow.innerHTML = '<div style="text-align: center; color: #999; padding: 20px;">No messages yet. Start the conversation!</div>';
                return;
            }

            messages.forEach(msg => {
                const bubble = document.createElement('div');
                bubble.className = msg.sender_type === 'student' ? 'bubble-student' : 'bubble-admin';
                
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
            const chatInput = document.getElementById('chatInput');
            const messageText = chatInput.value.trim();

            // If empty, do nothing
            if (messageText === '') {
                return;
            }

            // Send POST request to send_message.php
            const formData = new FormData();
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

        // Auto-refresh messages every 2 seconds
        setInterval(loadMessages, 2000);

        // Send button click event
        document.getElementById("chatSend").addEventListener("click", sendMessage);

        // Enter key press event
        document.getElementById("chatInput").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });
    </script>

</body>
</html>

