-- Migration: Add fields for extended office assignment details
-- Date: 2026-01-12
--
-- Adds columns to store:
-- 1. office_bureau: For Central Office selections (OBS list)
-- 2. attached_agency: For Attached Agency selections

USE eteeap_survey;

-- Add office_bureau column
SET @add_office_bureau := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN office_bureau VARCHAR(255) NULL AFTER office_assignment',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'office_bureau'
);
PREPARE stmt FROM @add_office_bureau;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add attached_agency column
SET @add_attached_agency := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN attached_agency VARCHAR(255) NULL AFTER office_bureau',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'attached_agency'
);
PREPARE stmt FROM @add_attached_agency;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
