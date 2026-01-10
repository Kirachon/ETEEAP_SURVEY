-- ============================================
-- Migration: Update Q27 Will Apply Field
-- Date: 2026-01-09
-- Description: Remove "Maybe" option and add reason field
-- ============================================

USE eteeap_survey;

-- Step 1: Add new field for reasons when selecting "No" (idempotent)
SET @add_reason_col := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE survey_responses ADD COLUMN will_not_apply_reason TEXT NULL COMMENT ''Reason for selecting No in Q27 (required field)'' AFTER will_apply',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'survey_responses'
    AND column_name = 'will_not_apply_reason'
);
PREPARE stmt FROM @add_reason_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Handle existing 'maybe' responses
-- Convert all 'maybe' to NULL (will be excluded from reporting)
-- Stakeholders can review these responses before migration if needed
UPDATE survey_responses 
SET will_apply = NULL 
WHERE will_apply = 'maybe';

-- Step 3: Modify enum to remove 'maybe' option
ALTER TABLE survey_responses 
MODIFY COLUMN will_apply ENUM('yes', 'no') NULL 
COMMENT 'Will apply for ETEEAP-BSSW (Maybe option removed 2026-01-09)';

-- Step 4: Add index for reporting performance (idempotent)
SET @add_reason_idx := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE survey_responses ADD INDEX idx_will_not_apply_reason (will_not_apply_reason(100))',
    'SELECT 1'
  )
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'survey_responses'
    AND index_name = 'idx_will_not_apply_reason'
);
PREPARE stmt FROM @add_reason_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Verification Queries
-- ============================================

-- Check current distribution
SELECT 
    will_apply,
    COUNT(*) as count,
    COUNT(will_not_apply_reason) as with_reason
FROM survey_responses
WHERE completed_at IS NOT NULL
GROUP BY will_apply;

-- Check for any orphaned data
SELECT COUNT(*) as orphaned_count
FROM survey_responses
WHERE will_apply IS NULL 
AND completed_at IS NOT NULL;

-- ============================================
-- Rollback (if needed)
-- ============================================
-- ALTER TABLE survey_responses 
-- DROP INDEX idx_will_not_apply_reason;
-- 
-- ALTER TABLE survey_responses 
-- MODIFY COLUMN will_apply ENUM('yes', 'maybe', 'no') NULL;
-- 
-- ALTER TABLE survey_responses 
-- DROP COLUMN will_not_apply_reason;
