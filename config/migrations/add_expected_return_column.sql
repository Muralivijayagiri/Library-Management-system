-- Migration: Add expected_return column to borrow_records table
-- Date: 2024
-- Description: Adds expected_return column if it doesn't exist, then backfills data from return_date

USE library_management;

-- Add expected_return column if it doesn't exist
ALTER TABLE borrow_records 
ADD COLUMN IF NOT EXISTS expected_return DATE DEFAULT NULL 
AFTER return_date;

-- Backfill expected_return from return_date for existing records
UPDATE borrow_records 
SET expected_return = return_date 
WHERE expected_return IS NULL 
   OR expected_return = '' 
   OR expected_return = '0000-00-00'
   OR expected_return = '-';

