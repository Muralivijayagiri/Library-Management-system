-- Migration: Backfill expected_return column from return_date
-- Date: 2024
-- Description: Updates all existing borrow_records to set expected_return = return_date
--              for records where expected_return is NULL, empty, or '0000-00-00'

USE library_management;

UPDATE borrow_records 
SET expected_return = return_date 
WHERE expected_return IS NULL 
   OR expected_return = '' 
   OR expected_return = '0000-00-00'
   OR expected_return = '-';

