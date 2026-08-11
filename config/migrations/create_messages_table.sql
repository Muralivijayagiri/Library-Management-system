-- Migration: Create messages table for Student ↔ Admin chat system
-- Date: 2024
-- Description: Creates the messages table to store chat messages between students and admins

USE library_management;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('student', 'admin') NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    seen_status TINYINT(1) DEFAULT 0
);

