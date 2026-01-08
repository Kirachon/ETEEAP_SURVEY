-- Migration: Align survey fields to docs/Nationwide_Interest_Survey.md
-- Date: 2026-01-08
--
-- Notes:
-- - This migration updates enum values; run during a maintenance window.
-- - It includes best-effort remapping of existing values to the new buckets.

USE eteeap_survey;

-- Add office assignment field (dropdown in SECTION 3)
SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE survey_responses ADD COLUMN office_assignment VARCHAR(50) NULL AFTER office_type',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'survey_responses'
    AND column_name = 'office_assignment'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Remap old employment status values to new set
-- Convert to VARCHAR first so we can safely remap old enum values to new enum values.
ALTER TABLE survey_responses
  MODIFY employment_status VARCHAR(20) NULL;

UPDATE survey_responses
  SET employment_status = CASE employment_status
    WHEN 'cos_moa' THEN 'cos'
    WHEN 'contractual' THEN 'jo'
    WHEN 'coterminous' THEN 'others'
    WHEN 'permanent' THEN 'permanent'
    WHEN 'cos' THEN 'cos'
    WHEN 'jo' THEN 'jo'
    WHEN 'others' THEN 'others'
    ELSE NULL
  END
WHERE employment_status IS NOT NULL;

-- Remap old work experience buckets to new buckets
-- Convert to VARCHAR first so we can safely remap old enum values to new enum values.
ALTER TABLE survey_responses
  MODIFY years_dswd VARCHAR(20) NULL,
  MODIFY years_swd_sector VARCHAR(20) NULL;

UPDATE survey_responses
  SET years_dswd = CASE years_dswd
    WHEN 'less_than_2' THEN 'lt5'
    WHEN '2-5' THEN 'lt5'
    WHEN '6-10' THEN '5-10'
    WHEN '11-15' THEN '11-15'
    WHEN '16+' THEN '15+'
    WHEN 'lt5' THEN 'lt5'
    WHEN '5-10' THEN '5-10'
    WHEN '11-15' THEN '11-15'
    WHEN '15+' THEN '15+'
    ELSE NULL
  END
WHERE years_dswd IS NOT NULL;

UPDATE survey_responses
  SET years_swd_sector = CASE years_swd_sector
    WHEN 'less_than_5' THEN 'lt5'
    WHEN '5-10' THEN '5-10'
    WHEN '11-20' THEN '15+'
    WHEN '21+' THEN '15+'
    WHEN 'lt5' THEN 'lt5'
    WHEN '11-15' THEN '11-15'
    WHEN '15+' THEN '15+'
    ELSE NULL
  END
WHERE years_swd_sector IS NOT NULL;

-- Apply new enum definitions
ALTER TABLE survey_responses
  MODIFY employment_status ENUM('permanent', 'cos', 'jo', 'others') NULL,
  MODIFY years_dswd ENUM('lt5', '5-10', '11-15', '15+') NULL,
  MODIFY years_swd_sector ENUM('lt5', '5-10', '11-15', '15+') NULL;
