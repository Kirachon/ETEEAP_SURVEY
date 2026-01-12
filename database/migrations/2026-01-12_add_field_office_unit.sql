-- Migration: Add field for Field Office Unit (Q10)
-- Date: 2026-01-12
--
-- Adds column to store:
-- 1. field_office_unit: For Field Office selections (Q10: Office Field / Unit / Program Assignment)

USE eteeap_survey;

-- Add field_office_unit column
SET @add_field_office_unit := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN field_office_unit VARCHAR(255) NULL AFTER office_assignment',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'field_office_unit'
);
PREPARE stmt FROM @add_field_office_unit;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
