-- Migration: Adjust duplicate submission rule to name+email (allow same names)
-- Date: 2026-01-09
--
-- This replaces the old unique email constraint with a composite unique key:
--   (email, last_name, first_name, middle_name, ext_name)
-- Notes:
-- - Because email is nullable, multiple rows with NULL email remain allowed.
-- - Run during a maintenance window if the table is large.

USE eteeap_survey;

-- Drop old unique index on email if present
SET @drop_email_unique := (
  SELECT IF(
    COUNT(*) > 0,
    'ALTER TABLE survey_responses DROP INDEX idx_unique_email',
    'SELECT 1'
  )
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'survey_responses'
    AND index_name = 'idx_unique_email'
);
PREPARE stmt FROM @drop_email_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add composite unique identity index if missing
SET @add_identity_unique := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE survey_responses ADD UNIQUE INDEX idx_unique_identity (email, last_name, first_name, middle_name, ext_name)',
    'SELECT 1'
  )
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'survey_responses'
    AND index_name = 'idx_unique_identity'
);
PREPARE stmt FROM @add_identity_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
