# Changelog

## Unreleased

- Added email OTP verification for survey respondents (after Step 2) to prevent fake/typo emails.
- Added admin login OTP (MFA) step after password login.
- Added `otp_challenges` table and migration for OTP storage.

## v1.0.5 (2026-01-10)

- Added CSRF-protected public endpoint `POST /api/survey/submit` (JSON submission).
- Added admin stats endpoint `GET /api/stats/summary`.
- Wired `program_assignments` validation and derived `performs_sw_tasks`.
- Updated documentation and deployment guides.
