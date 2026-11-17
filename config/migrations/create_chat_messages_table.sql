-- Migration: Create chat_messages table for Student ↔ Admin chat system
-- Date: 2024
-- Description: Creates the chat_messages table to store chat messages between students and admins

USE library_management;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    sender ENUM('student','admin') NOT NULL,
    message TEXT NOT NULL,
    seen_status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

