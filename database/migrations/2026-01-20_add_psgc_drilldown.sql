-- Migration: Add PSGC drill-down support (Region -> Province -> City/Municipality)
-- Date: 2026-01-20
--
-- Adds:
-- 1) ref_psgc_city: reference table populated from docs/update/lib_psgc_2025.csv
-- 2) survey_responses PSGC code columns for standardized reporting/filtering

USE eteeap_survey;

-- Create PSGC reference table (denormalized for simple lookups)
CREATE TABLE IF NOT EXISTS ref_psgc_city (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_code INT UNSIGNED NOT NULL,
    region_name VARCHAR(20) NOT NULL,
    province_code INT UNSIGNED NOT NULL,
    province_name VARCHAR(255) NOT NULL,
    city_code INT UNSIGNED NOT NULL,
    city_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY ux_city_code (city_code),
    KEY idx_region_code (region_code),
    KEY idx_province_code (province_code),
    KEY idx_region_province (region_code, province_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add PSGC columns to survey_responses (idempotent)
SET @add_psgc_region_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN psgc_region_code INT UNSIGNED NULL AFTER office_assignment',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'psgc_region_code'
);
PREPARE stmt FROM @add_psgc_region_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_psgc_province_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN psgc_province_code INT UNSIGNED NULL AFTER psgc_region_code',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'psgc_province_code'
);
PREPARE stmt FROM @add_psgc_province_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_psgc_city_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD COLUMN psgc_city_code INT UNSIGNED NULL AFTER psgc_province_code',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND column_name = 'psgc_city_code'
);
PREPARE stmt FROM @add_psgc_city_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for PSGC filtering (idempotent)
SET @add_idx_psgc_region_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD INDEX idx_psgc_region_code (psgc_region_code)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND index_name = 'idx_psgc_region_code'
);
PREPARE stmt FROM @add_idx_psgc_region_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_idx_psgc_province_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD INDEX idx_psgc_province_code (psgc_province_code)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND index_name = 'idx_psgc_province_code'
);
PREPARE stmt FROM @add_idx_psgc_province_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_idx_psgc_city_code := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE survey_responses ADD INDEX idx_psgc_city_code (psgc_city_code)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'survey_responses'
      AND index_name = 'idx_psgc_city_code'
);
PREPARE stmt FROM @add_idx_psgc_city_code;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

