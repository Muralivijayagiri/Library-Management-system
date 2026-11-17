-- Migration: Add seen_status column to chat_messages table
-- Date: 2026
-- Description: Tracks whether chat messages have been read by the recipient

USE library_management;

ALTER TABLE chat_messages
ADD COLUMN IF NOT EXISTS seen_status TINYINT(1) DEFAULT 0 AFTER message;
