-- Migration: Add OTP challenges table (email OTP / MFA)
-- Date: 2026-01-17
--
-- This table supports:
-- - Survey respondent email verification (OTP)
-- - Admin login second factor (OTP)

USE eteeap_survey;

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

