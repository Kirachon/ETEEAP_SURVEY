-- Migration: Enforce strict email uniqueness for duplicate checking
-- Date: 2026-01-12
--
-- This strictly enforces one response per email address.
-- It replaces the previous composite index (email + name parts).

USE eteeap_survey;

-- 1. Drop the old composite index 'idx_unique_identity' if it exists
SET @drop_identity_unique := (
    SELECT IF(
        COUNT(*) > 0,
        'ALTER TABLE survey_responses DROP INDEX idx_unique_identity',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND index_name = 'idx_unique_identity'
);
PREPARE stmt FROM @drop_identity_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Drop the original 'idx_unique_email' if it somehow still exists (cleanup)
SET @drop_old_email_unique := (
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
PREPARE stmt FROM @drop_old_email_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Add the new strict UNIQUE index on email
-- Note: Email is nullable in schema definition but required in form validation.
-- DB allows multiple NULLs, but our app logic enforces email presence.
SET @add_email_unique := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD UNIQUE INDEX idx_unique_email (email)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND index_name = 'idx_unique_email'
);
PREPARE stmt FROM @add_email_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
