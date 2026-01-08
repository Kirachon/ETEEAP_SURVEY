<?php
/**
 * ETEEAP Survey Application - Validation Helper
 * 
 * Server-side validation functions for survey forms.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Validation result container
 */
class ValidationResult
{
    public bool $isValid = true;
    public array $errors = [];
    public array $sanitized = [];
    
    public function addError(string $field, string $message): void
    {
        $this->isValid = false;
        $this->errors[$field][] = $message;
    }
    
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && count($this->errors[$field]) > 0;
    }
}

/**
 * Sanitize a string input
 * 
 * @param mixed $value
 * @return string
 */
function sanitizeString($value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email input
 * 
 * @param mixed $value
 * @return string
 */
function sanitizeEmail($value): string
{
    if ($value === null) {
        return '';
    }
    return filter_var(trim((string) $value), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitize phone number (keep only digits, +, -, space)
 * 
 * @param mixed $value
 * @return string
 */
function sanitizePhone($value): string
{
    if ($value === null) {
        return '';
    }
    return preg_replace('/[^0-9+\-\s]/', '', trim((string) $value));
}

/**
 * Validate required field
 * 
 * @param mixed $value
 * @return bool
 */
function validateRequired($value): bool
{
    if (is_array($value)) {
        return count($value) > 0;
    }
    return trim((string) $value) !== '';
}

/**
 * Validate email format
 * 
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate email has a legitimate domain
 * Accepts: gmail, yahoo, outlook, hotmail, gov.ph, edu.ph, and other common providers
 * 
 * @param string $email
 * @return bool
 */
function validateEmailDomain(string $email): bool
{
    if (empty($email)) {
        return false;
    }
    
    // Extract domain from email
    $parts = explode('@', strtolower($email));
    if (count($parts) !== 2) {
        return false;
    }
    
    $domain = $parts[1];
    
    // Allowed domain patterns
    $allowedDomains = [
        // Major email providers
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'yahoo.com.ph',
        'yahoo.co.uk',
        'outlook.com',
        'outlook.ph',
        'hotmail.com',
        'live.com',
        'msn.com',
        'icloud.com',
        'me.com',
        'aol.com',
        'protonmail.com',
        'proton.me',
        'zoho.com',
        'mail.com',
        'ymail.com',
        'rocketmail.com',
        
        // Philippine government domains
        'dswd.gov.ph',
        'gov.ph',
        
        // Educational domains (Philippines)
        'edu.ph',
        'up.edu.ph',
        'ust.edu.ph',
        'ateneo.edu',
        'dlsu.edu.ph',
        'admu.edu.ph'
    ];
    
    // Check exact match
    if (in_array($domain, $allowedDomains)) {
        return true;
    }
    
    // Check wildcard patterns (*.gov.ph, *.edu.ph)
    $wildcardPatterns = [
        '.gov.ph',   // All government domains
        '.edu.ph',   // All Philippine educational domains
        '.edu',      // All educational domains
        '.ac.ph',    // Academic institutions
    ];
    
    foreach ($wildcardPatterns as $pattern) {
        if (str_ends_with($domain, $pattern)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Validate value is in allowed list (enum)
 * 
 * @param mixed $value
 * @param array $allowed
 * @return bool
 */
function validateInList($value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

/**
 * Validate minimum string length
 * 
 * @param string $value
 * @param int $min
 * @return bool
 */
function validateMinLength(string $value, int $min): bool
{
    return mb_strlen($value) >= $min;
}

/**
 * Validate maximum string length
 * 
 * @param string $value
 * @param int $max
 * @return bool
 */
function validateMaxLength(string $value, int $max): bool
{
    return mb_strlen($value) <= $max;
}

/**
 * Validate phone number format (Philippine)
 * 
 * @param string $phone
 * @return bool
 */
function validatePhone(string $phone): bool
{
    // Allow: 09XXXXXXXXX, +639XXXXXXXXX, (02) XXXX-XXXX, etc.
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    return preg_match('/^(\+63|0)?[0-9]{10,11}$/', $cleaned) === 1;
}

/**
 * Validate checkbox array (at least one selected)
 * 
 * @param mixed $value
 * @return bool
 */
function validateCheckboxRequired($value): bool
{
    return is_array($value) && count($value) > 0;
}

/**
 * Validate all checkbox values are in allowed list
 * 
 * @param array $values
 * @param array $allowed
 * @return bool
 */
function validateCheckboxValues(array $values, array $allowed): bool
{
    foreach ($values as $value) {
        if (!in_array($value, $allowed, true)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if email has already been used for a completed survey
 * 
 * @param string $email
 * @return bool
 */
function isEmailAlreadyUsed(string $email): bool
{
    if (empty($email)) {
        return false;
    }
    
    $result = dbFetchOne(
        "SELECT COUNT(*) as count FROM survey_responses 
         WHERE email = :email AND consent_given = 1 AND completed_at IS NOT NULL",
        ['email' => strtolower(trim($email))]
    );
    
    return ($result['count'] ?? 0) > 0;
}

/**
 * Check if name has already been used for a completed survey
 * Uses case-insensitive comparison with trimmed/normalized name
 * 
 * @param string $fullName
 * @return bool
 */
function isNameAlreadyUsed(string $lastName, string $firstName, ?string $middleName = null, ?string $extName = null): bool
{
    if (empty($lastName) || empty($firstName)) {
        return false;
    }
    
    $params = [
        'ln' => strtolower(trim($lastName)),
        'fn' => strtolower(trim($firstName))
    ];
    
    $where = "LOWER(TRIM(last_name)) = :ln AND LOWER(TRIM(first_name)) = :fn";
    
    if ($middleName !== null) {
        $where .= " AND LOWER(TRIM(middle_name)) = :mn";
        $params['mn'] = strtolower(trim($middleName));
    } else {
        $where .= " AND middle_name IS NULL";
    }
    
    if ($extName !== null) {
        $where .= " AND LOWER(TRIM(ext_name)) = :ext";
        $params['ext'] = strtolower(trim($extName));
    } else {
        $where .= " AND ext_name IS NULL";
    }
    
    $result = dbFetchOne(
        "SELECT COUNT(*) as count FROM survey_responses 
         WHERE $where 
         AND consent_given = 1 
         AND completed_at IS NOT NULL",
        $params
    );
    
    return ($result['count'] ?? 0) > 0;
}

// ============================================
// Survey Step Validators
// ============================================

/**
 * Validate Step 1: Consent
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepConsent(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    $consent = $data['consent'] ?? null;
    if (!validateInList($consent, ['yes', 'no'])) {
        $result->addError('consent', 'Please select whether you consent or not.');
    }
    
    $result->sanitized['consent_given'] = $consent === 'yes';
    
    return $result;
}

/**
 * Validate Step 2: Basic Information
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepBasicInfo(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Last Name (required)
    $lastName = sanitizeString($data['last_name'] ?? '');
    if (!validateRequired($lastName)) {
        $result->addError('last_name', 'Last name is required.');
    } elseif (!validateMinLength($lastName, 2)) {
        $result->addError('last_name', 'Last name must be at least 2 characters.');
    } elseif (!validateMaxLength($lastName, 100)) {
        $result->addError('last_name', 'Last name must not exceed 100 characters.');
    }
    $result->sanitized['last_name'] = $lastName;
    
    // First Name (required)
    $firstName = sanitizeString($data['first_name'] ?? '');
    if (!validateRequired($firstName)) {
        $result->addError('first_name', 'First name is required.');
    } elseif (!validateMinLength($firstName, 2)) {
        $result->addError('first_name', 'First name must be at least 2 characters.');
    } elseif (!validateMaxLength($firstName, 100)) {
        $result->addError('first_name', 'First name must not exceed 100 characters.');
    }
    $result->sanitized['first_name'] = $firstName;
    
    // Middle Name (optional)
    $middleName = sanitizeString($data['middle_name'] ?? '');
    if ($middleName !== '' && !validateMaxLength($middleName, 100)) {
        $result->addError('middle_name', 'Middle name must not exceed 100 characters.');
    }
    $result->sanitized['middle_name'] = $middleName !== '' ? $middleName : null;
    
    // Extension Name (optional)
    $extName = sanitizeString($data['ext_name'] ?? '');
    if ($extName !== '' && !validateMaxLength($extName, 20)) {
        $result->addError('ext_name', 'Extension name must not exceed 20 characters.');
    }
    $result->sanitized['ext_name'] = $extName !== '' ? $extName : null;
    
    // Check for duplicate name (combined check)
    if ($lastName && $firstName) {
        if (isNameAlreadyUsed($lastName, $firstName, $middleName, $extName)) {
            $result->addError('last_name', 'A survey response with this name already exists. Each person may only submit one response.');
        }
    }
    
    // Sex (required)
    $sex = $data['sex'] ?? null;
    $allowedSex = ['male', 'female', 'prefer_not_to_say'];
    if (!validateInList($sex, $allowedSex)) {
        $result->addError('sex', 'Please select your sex.');
    }
    $result->sanitized['sex'] = $sex;
    
    // Age range (required)
    $ageRange = $data['age_range'] ?? null;
    $allowedAge = ['20-29', '30-39', '40-49', '50-59', '60+'];
    if (!validateInList($ageRange, $allowedAge)) {
        $result->addError('age_range', 'Please select your age range.');
    }
    $result->sanitized['age_range'] = $ageRange;
    
    // Email (required, valid format, legitimate domain, unique)
    $email = sanitizeEmail($data['email'] ?? '');
    if (!validateRequired($email)) {
        $result->addError('email', 'Email address is required.');
    } elseif (!validateEmail($email)) {
        $result->addError('email', 'Please enter a valid email address.');
    } elseif (!validateEmailDomain($email)) {
        $result->addError('email', 'Please use a valid email provider (e.g., Gmail, Yahoo, Outlook, or your official government/educational email).');
    } elseif (isEmailAlreadyUsed($email)) {
        $result->addError('email', 'This email address has already been used to submit a survey. Each person may only submit one response.');
    }
    $result->sanitized['email'] = $email;
    
    // Phone (optional but validate format if provided)
    $phone = sanitizePhone($data['phone'] ?? '');
    if ($phone !== '' && !validatePhone($phone)) {
        $result->addError('phone', 'Please enter a valid phone number.');
    }
    $result->sanitized['phone'] = $phone;
    
    return $result;
}


/**
 * Validate Step 3: Office & Employment Data
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepOfficeData(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Office type (required)
    $officeType = $data['office_type'] ?? null;
    $allowedOffice = ['central_office', 'field_office', 'attached_agency'];
    if (!validateInList($officeType, $allowedOffice)) {
        $result->addError('office_type', 'Please select your office type.');
    }
    $result->sanitized['office_type'] = $officeType;
    
    // Specific office (required)
    $specificOffice = sanitizeString($data['specific_office'] ?? '');
    if (!validateRequired($specificOffice)) {
        $result->addError('specific_office', 'Please specify your office/unit.');
    }
    $result->sanitized['specific_office'] = $specificOffice;
    
    // Program assignments (optional, multi-value)
    $programs = $data['program_assignments'] ?? [];
    if (!is_array($programs)) {
        $programs = [];
    }
    $result->sanitized['program_assignments'] = array_map('sanitizeString', $programs);
    
    // Current position (required)
    $position = sanitizeString($data['current_position'] ?? '');
    if (!validateRequired($position)) {
        $result->addError('current_position', 'Please enter your current position.');
    }
    $result->sanitized['current_position'] = $position;
    
    // Employment status (required)
    $status = $data['employment_status'] ?? null;
    $allowedStatus = ['permanent', 'coterminous', 'contractual', 'cos_moa'];
    if (!validateInList($status, $allowedStatus)) {
        $result->addError('employment_status', 'Please select your employment status.');
    }
    $result->sanitized['employment_status'] = $status;
    
    return $result;
}

/**
 * Validate Step 4: Work Experience
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepWorkExperience(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Years in DSWD (required)
    $yearsDswd = $data['years_dswd'] ?? null;
    $allowedYears = ['less_than_2', '2-5', '6-10', '11-15', '16+'];
    if (!validateInList($yearsDswd, $allowedYears)) {
        $result->addError('years_dswd', 'Please select your years of service in DSWD.');
    }
    $result->sanitized['years_dswd'] = $yearsDswd;
    
    // Years in SWD sector (required)
    $yearsSector = $data['years_swd_sector'] ?? null;
    $allowedSector = ['less_than_5', '5-10', '11-20', '21+'];
    if (!validateInList($yearsSector, $allowedSector)) {
        $result->addError('years_swd_sector', 'Please select your years in SWD sector.');
    }
    $result->sanitized['years_swd_sector'] = $yearsSector;
    
    return $result;
}

/**
 * Validate Step 5: Social Work Competencies
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepCompetencies(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Performs SW tasks (required)
    $performsTasks = $data['performs_sw_tasks'] ?? null;
    if (!validateInList($performsTasks, ['yes', 'no'])) {
        $result->addError('performs_sw_tasks', 'Please indicate if you perform social work tasks.');
    }
    $result->sanitized['performs_sw_tasks'] = $performsTasks === 'yes';
    
    // SW tasks (conditional - required if performs_sw_tasks is yes)
    $tasks = $data['sw_tasks'] ?? [];
    if (!is_array($tasks)) {
        $tasks = [];
    }
    if ($performsTasks === 'yes' && count($tasks) === 0) {
        $result->addError('sw_tasks', 'Please select at least one task.');
    }
    $result->sanitized['sw_tasks'] = array_map('sanitizeString', $tasks);
    
    // Expertise areas (optional)
    $areas = $data['expertise_areas'] ?? [];
    if (!is_array($areas)) {
        $areas = [];
    }
    $result->sanitized['expertise_areas'] = array_map('sanitizeString', $areas);
    
    return $result;
}

/**
 * Validate Step 6: Educational Background
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepEducation(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Highest education (required)
    $highest = $data['highest_education'] ?? null;
    $allowedEdu = ['high_school', 'some_college', 'bachelors', 'masters', 'doctoral'];
    if (!validateInList($highest, $allowedEdu)) {
        $result->addError('highest_education', 'Please select your highest educational attainment.');
    }
    $result->sanitized['highest_education'] = $highest;
    
    // Undergrad course (optional)
    $undergrad = sanitizeString($data['undergrad_course'] ?? '');
    $result->sanitized['undergrad_course'] = $undergrad;
    
    // Diploma course (optional)
    $diploma = sanitizeString($data['diploma_course'] ?? '');
    $result->sanitized['diploma_course'] = $diploma;
    
    // Graduate course (optional)
    $graduate = sanitizeString($data['graduate_course'] ?? '');
    $result->sanitized['graduate_course'] = $graduate;
    
    return $result;
}

/**
 * Validate Step 7: DSWD Academy Courses
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepDswdCourses(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // Availed training (required)
    $availed = $data['availed_dswd_training'] ?? null;
    if (!validateInList($availed, ['yes', 'no'])) {
        $result->addError('availed_dswd_training', 'Please indicate if you have availed DSWD Academy training.');
    }
    $result->sanitized['availed_dswd_training'] = $availed === 'yes';
    
    // Courses taken (conditional)
    $courses = $data['dswd_courses'] ?? [];
    if (!is_array($courses)) {
        $courses = [];
    }
    if ($availed === 'yes' && count($courses) === 0) {
        $result->addError('dswd_courses', 'Please select at least one course.');
    }
    $result->sanitized['dswd_courses'] = array_map('sanitizeString', $courses);
    
    return $result;
}

/**
 * Validate Step 8: ETEEAP Interest
 * 
 * @param array $data POST data
 * @return ValidationResult
 */
function validateStepEteeapInterest(array $data): ValidationResult
{
    $result = new ValidationResult();
    
    // ETEEAP awareness (required)
    $awareness = $data['eteeap_awareness'] ?? null;
    if (!validateInList($awareness, ['yes', 'no'])) {
        $result->addError('eteeap_awareness', 'Please indicate if you are aware of ETEEAP.');
    }
    $result->sanitized['eteeap_awareness'] = $awareness === 'yes';
    
    // Interest level (required)
    $interest = $data['eteeap_interest'] ?? null;
    $allowedInterest = ['very_interested', 'interested', 'somewhat_interested', 'not_interested'];
    if (!validateInList($interest, $allowedInterest)) {
        $result->addError('eteeap_interest', 'Please select your level of interest.');
    }
    $result->sanitized['eteeap_interest'] = $interest;
    
    // Motivations (optional)
    $motivations = $data['motivations'] ?? [];
    if (!is_array($motivations)) {
        $motivations = [];
    }
    $result->sanitized['motivations'] = array_map('sanitizeString', $motivations);
    
    // Barriers (optional)
    $barriers = $data['barriers'] ?? [];
    if (!is_array($barriers)) {
        $barriers = [];
    }
    $result->sanitized['barriers'] = array_map('sanitizeString', $barriers);
    
    // Will apply (required)
    $willApply = $data['will_apply'] ?? null;
    if (!validateInList($willApply, ['yes', 'maybe', 'no'])) {
        $result->addError('will_apply', 'Please indicate if you intend to apply.');
    }
    $result->sanitized['will_apply'] = $willApply;
    
    // Additional comments (optional)
    $comments = sanitizeString($data['additional_comments'] ?? '');
    if (!validateMaxLength($comments, 2000)) {
        $result->addError('additional_comments', 'Comments must not exceed 2000 characters.');
    }
    $result->sanitized['additional_comments'] = $comments;
    
    return $result;
}

/**
 * Get the appropriate validator for a step
 * 
 * @param int $step
 * @return callable|null
 */
function getStepValidator(int $step): ?callable
{
    $validators = [
        1 => 'validateStepConsent',
        2 => 'validateStepBasicInfo',
        3 => 'validateStepOfficeData',
        4 => 'validateStepWorkExperience',
        5 => 'validateStepCompetencies',
        6 => 'validateStepEducation',
        7 => 'validateStepDswdCourses',
        8 => 'validateStepEteeapInterest'
    ];
    
    return $validators[$step] ?? null;
}
