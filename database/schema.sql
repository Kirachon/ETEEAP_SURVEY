-- ============================================
-- ETEEAP Survey Application - Database Schema
-- MySQL 8.x Compatible
-- Consolidated baseline schema (includes migrations up to 2026-01-12)
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS eteeap_survey
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE eteeap_survey;

-- ============================================
-- Core Survey Responses Table
-- ============================================
CREATE TABLE IF NOT EXISTS survey_responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    consent_given BOOLEAN DEFAULT FALSE,
    current_step TINYINT UNSIGNED DEFAULT 1,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Section 2: Basic Information
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    ext_name VARCHAR(20) NULL COMMENT 'Jr., Sr., III, IV, etc.',
    sex ENUM('male', 'female', 'prefer_not_to_say') NULL,
    age_range ENUM('20-29', '30-39', '40-49', '50-59', '60+') NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    
    -- Section 3: Office & Employment Data
    office_type ENUM('central_office', 'field_office', 'attached_agency') NULL,
    office_assignment VARCHAR(50) NULL,
    psgc_region_code INT UNSIGNED NULL,
    psgc_province_code INT UNSIGNED NULL,
    psgc_city_code INT UNSIGNED NULL,
    office_bureau VARCHAR(255) NULL,
    attached_agency VARCHAR(255) NULL,
    field_office_unit VARCHAR(255) NULL,
    specific_office VARCHAR(255) NULL,
    current_position VARCHAR(255) NULL,
    employment_status ENUM('permanent', 'cos', 'jo', 'others') NULL,
    
    -- Section 4: Work Experience
    years_dswd ENUM('lt5', '5-10', '11-15', '15+') NULL,
    years_swd_sector ENUM('lt5', '5-10', '11-15', '15+') NULL,
    
    -- Section 5: Competencies (single-value fields)
    performs_sw_tasks BOOLEAN NULL,
    
    -- Section 6: Educational Background
    highest_education ENUM('high_school', 'some_college', 'bachelors', 'masters', 'doctoral') NULL,
    undergrad_course VARCHAR(255) NULL,
    diploma_course VARCHAR(255) NULL,
    graduate_course VARCHAR(255) NULL,
    
    -- Section 7: DSWD Academy Courses
    availed_dswd_training BOOLEAN NULL,
    
    -- Section 8: ETEEAP Interest
    eteeap_awareness BOOLEAN NULL,
    eteeap_interest ENUM('very_interested', 'interested', 'somewhat_interested', 'not_interested') NULL,
    will_apply ENUM('yes', 'no') NULL COMMENT 'Will apply for ETEEAP-BSSW (Maybe option removed 2026-01-09)',
    will_not_apply_reason TEXT NULL COMMENT 'Reason for selecting No in Q27 (required field)',
    additional_comments TEXT NULL,
    
    -- Indexes for reporting queries
    INDEX idx_session (session_id),
    INDEX idx_created (created_at),
    INDEX idx_office_type (office_type),
    INDEX idx_psgc_region_code (psgc_region_code),
    INDEX idx_psgc_province_code (psgc_province_code),
    INDEX idx_psgc_city_code (psgc_city_code),
    INDEX idx_interest (eteeap_interest),
    INDEX idx_completed (completed_at),
    INDEX idx_age_range (age_range),
    INDEX idx_sex (sex),
    INDEX idx_will_not_apply_reason (will_not_apply_reason(100)),
    UNIQUE INDEX idx_unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PSGC Reference Data (Region -> Province -> City/Municipality)
-- ============================================
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

-- ============================================
-- Multi-Value Tables (Checkbox Fields)
-- ============================================

-- Section 3: Program Assignments (checkboxes)
CREATE TABLE IF NOT EXISTS response_program_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    program VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_program (program)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section 5: Social Work Tasks Performed (checkboxes)
CREATE TABLE IF NOT EXISTS response_sw_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    task VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_task (task)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section 5: Areas of Expertise (checkboxes)
CREATE TABLE IF NOT EXISTS response_expertise_areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    area VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_area (area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section 7: DSWD Academy Courses Taken (checkboxes)
CREATE TABLE IF NOT EXISTS response_dswd_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    course VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_course (course)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section 8: Motivations for ETEEAP (checkboxes)
CREATE TABLE IF NOT EXISTS response_motivations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    motivation VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_motivation (motivation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section 8: Barriers to ETEEAP (checkboxes)
CREATE TABLE IF NOT EXISTS response_barriers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    barrier VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_barrier (barrier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- OTP Challenges (Email OTP / MFA)
-- ============================================
CREATE TABLE IF NOT EXISTS otp_challenges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purpose VARCHAR(40) NOT NULL,
    email VARCHAR(255) NOT NULL,
    code_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts INT UNSIGNED NOT NULL DEFAULT 5,
    last_sent_at DATETIME NULL,
    consumed_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    survey_session_id VARCHAR(64) NULL,
    admin_user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_purpose (purpose),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at),
    INDEX idx_consumed_at (consumed_at),
    INDEX idx_survey_session (survey_session_id),
    INDEX idx_admin_user (admin_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Admin Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Lookup Tables for Dropdown/Checkbox Options
-- (Optional - can be used for dynamic options)
-- ============================================

-- Programs available for assignment
CREATE TABLE IF NOT EXISTS lookup_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Work Tasks
CREATE TABLE IF NOT EXISTS lookup_sw_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DSWD Academy Courses
CREATE TABLE IF NOT EXISTS lookup_dswd_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
