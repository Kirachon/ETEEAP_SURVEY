-- ============================================
-- ETEEAP Survey Application - Seed Data
-- Sample data for testing and development
-- ============================================

USE eteeap_survey;

-- ============================================
-- Default Admin User
-- Password: password (CHANGE IN PRODUCTION!)
-- ============================================
INSERT INTO admin_users (username, email, password_hash, full_name) VALUES
('admin', 'admin@dswd.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');
-- Note: This hash is for 'password' - CHANGE IN PRODUCTION!

-- ============================================
-- Lookup: DSWD Programs
-- ============================================
INSERT INTO lookup_programs (code, name, sort_order) VALUES
('4ps', 'Pantawid Pamilyang Pilipino Program (4Ps)', 1),
('slp', 'Sustainable Livelihood Program (SLP)', 2),
('kalahi', 'KALAHI-CIDSS', 3),
('aics', 'Assistance to Individuals in Crisis Situation (AICS)', 4),
('centenarians', 'Social Pension for Indigent Senior Citizens', 5),
('pwd', 'Persons with Disabilities Program', 6),
('ip', 'Indigenous Peoples Program', 7),
('solo_parent', 'Solo Parent Program', 8),
('children', 'Programs and Services for Children', 9),
('youth', 'Programs and Services for Youth', 10),
('women', 'Programs and Services for Women', 11),
('disaster', 'Disaster Response and Management', 12),
('residential', 'Residential Care Facilities', 13),
('adoption', 'Adoption and Foster Care', 14),
('other', 'Other Programs', 99);

-- ============================================
-- Lookup: Social Work Tasks
-- ============================================
INSERT INTO lookup_sw_tasks (code, name, sort_order) VALUES
('case_management', 'Case Management', 1),
('counseling', 'Counseling and Guidance', 2),
('assessment', 'Psychosocial Assessment', 3),
('home_visit', 'Home Visits', 4),
('group_work', 'Group Work Facilitation', 5),
('community_org', 'Community Organizing', 6),
('referral', 'Referral and Coordination', 7),
('monitoring', 'Program Monitoring', 8),
('documentation', 'Case Documentation', 9),
('crisis_intervention', 'Crisis Intervention', 10),
('advocacy', 'Advocacy and Policy Development', 11),
('training', 'Training and Capacity Building', 12),
('research', 'Research and Data Analysis', 13),
('admin', 'Administrative Functions', 14),
('other', 'Other Tasks', 99);

-- ============================================
-- Lookup: DSWD Academy Courses
-- ============================================
INSERT INTO lookup_dswd_courses (code, name, sort_order) VALUES
('swb', 'Social Welfare and Development Basic Course', 1),
('case_mgmt_basic', 'Case Management Basic Course', 2),
('case_mgmt_adv', 'Case Management Advanced Course', 3),
('counseling_basic', 'Basic Counseling Skills', 4),
('crisis', 'Crisis Intervention Training', 5),
('disaster', 'Disaster Response Training', 6),
('child_protection', 'Child Protection Training', 7),
('vawc', 'VAWC Response Training', 8),
('pwd_services', 'PWD Services Training', 9),
('senior_citizens', 'Senior Citizens Program Training', 10),
('4ps_operations', '4Ps Operations Training', 11),
('slp_operations', 'SLP Operations Training', 12),
('leadership', 'Leadership and Management Course', 13),
('ethics', 'Ethics in Social Work', 14),
('other', 'Other Courses', 99);

-- ============================================
-- Sample Survey Responses (for testing)
-- ============================================
INSERT INTO survey_responses (
    session_id, consent_given, current_step, completed_at,
    last_name, first_name, middle_name, ext_name, sex, age_range, email, phone,
    office_type, specific_office, current_position, employment_status,
    years_dswd, years_swd_sector,
    performs_sw_tasks,
    highest_education, undergrad_course,
    availed_dswd_training,
    eteeap_awareness, eteeap_interest, will_apply
) VALUES
-- Sample 1: Completed response
('sess_sample_001', TRUE, 8, NOW(),
 'Maria Santos', 'female', '30-39', 'maria.santos@dswd.gov.ph', '09171234567',
 'field_office', 'DSWD Field Office NCR', 'Social Welfare Officer II', 'permanent',
 '6-10', '11-20',
 TRUE,
 'bachelors', 'Bachelor of Arts in Psychology',
 TRUE,
 TRUE, 'very_interested', 'yes'),

-- Sample 2: Completed response with extension
('sess_sample_002', TRUE, 8, NOW(),
 'Dela Cruz', 'Juan', 'Manuel', 'Jr.', 'male', '40-49', 'juan.delacruz@dswd.gov.ph', '09181234567',
 'central_office', 'DSWD Central Office - PDPB', 'Project Development Officer III', 'permanent',
 '16+', '21+',
 TRUE,
 'masters', 'Master of Science in Social Work',
 TRUE,
 TRUE, 'interested', 'maybe'),

-- Sample 3: In-progress response
('sess_sample_003', TRUE, 4, NULL,
 'Reyes', 'Ana', 'Bautista', NULL, 'female', '20-29', 'ana.reyes@dswd.gov.ph', '09191234567',
 'field_office', 'DSWD Field Office Region IV-A', 'Social Welfare Assistant', 'contractual',
 'less_than_2', 'less_than_5',
 NULL,
 'some_college', NULL,
 NULL,
 NULL, NULL, NULL),

-- Sample 4: Declined consent
('sess_sample_004', FALSE, 1, NOW(),
 NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
 NULL, NULL, NULL, NULL,
 NULL, NULL,
 NULL,
 NULL, NULL,
 NULL,
 NULL, NULL, NULL);

-- Sample multi-value data for completed responses
-- Response 1: Program assignments
INSERT INTO response_program_assignments (response_id, program) VALUES
(1, '4ps'),
(1, 'aics'),
(1, 'children');

-- Response 1: SW Tasks
INSERT INTO response_sw_tasks (response_id, task) VALUES
(1, 'case_management'),
(1, 'counseling'),
(1, 'home_visit'),
(1, 'documentation');

-- Response 1: Expertise areas
INSERT INTO response_expertise_areas (response_id, area) VALUES
(1, 'Family and Child Welfare'),
(1, 'Crisis Intervention'),
(1, 'Community Development');

-- Response 1: DSWD Courses
INSERT INTO response_dswd_courses (response_id, course) VALUES
(1, 'swb'),
(1, 'case_mgmt_basic'),
(1, 'child_protection');

-- Response 1: Motivations
INSERT INTO response_motivations (response_id, motivation) VALUES
(1, 'Professional growth and career advancement'),
(1, 'Recognition of work experience'),
(1, 'Eligibility for promotion');

-- Response 1: Barriers
INSERT INTO response_barriers (response_id, barrier) VALUES
(1, 'Work schedule conflicts'),
(1, 'Financial constraints');

-- Response 2: Program assignments
INSERT INTO response_program_assignments (response_id, program) VALUES
(2, 'slp'),
(2, 'kalahi');

-- Response 2: SW Tasks
INSERT INTO response_sw_tasks (response_id, task) VALUES
(2, 'community_org'),
(2, 'training'),
(2, 'advocacy'),
(2, 'research');

-- Response 2: Motivations
INSERT INTO response_motivations (response_id, motivation) VALUES
(2, 'To obtain a formal social work degree'),
(2, 'To enhance professional competence');
