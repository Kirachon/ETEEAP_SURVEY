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

    $string = (string) $value;
    // Remove null bytes
    $string = str_replace("\0", '', $string);
    // Trim whitespace
    $string = trim($string);
    // Remove control characters (except common whitespace)
    $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', $string);

    return $string;
}

/**
 * Normalize whitespace (collapse internal whitespace + trim)
 */
function normalizeWhitespace(string $value): string
{
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value ?? '');
}

/**
 * Normalize a free-text field to uppercase + clean whitespace.
 * Intended only for non-code text inputs (names, office/unit, position, etc.).
 *
 * @param mixed $value
 */
function normalizeUpperText($value): string
{
    $clean = sanitizeString($value);
    $clean = normalizeWhitespace($clean);
    if ($clean === '') {
        return '';
    }

    return mb_strtoupper($clean, 'UTF-8');
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
 * Check for duplicate response
 * 
 * STRICT MODE: Checks ONLY email address.
 * 
 * @param PDO $pdo
 * @param array $identityData ['email']
 * @return bool True if duplicate exists
 */
function checkDuplicateResponse(PDO $pdo, array $identityData): bool
{
    $email = $identityData['email'] ?? null;
    
    // Email is required by validation, but just in case
    if (empty($email)) {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM survey_responses 
        WHERE email = :email
    ");
    
    $stmt->execute(['email' => $email]);
    
    return $stmt->fetchColumn() > 0;
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
 * Check if a completed survey already exists for the same person identity (name + email)
 *
 * This is intentionally stricter than checking email alone to avoid blocking different people
 * who share an email address, and to avoid false-positives for common names.
 */
function isNameEmailAlreadyUsed(string $email, string $lastName, string $firstName, ?string $middleName = null, ?string $extName = null): bool
{
    $email = strtolower(trim($email));
    $lastName = strtolower(trim($lastName));
    $firstName = strtolower(trim($firstName));
    $middleName = $middleName !== null ? strtolower(trim($middleName)) : null;
    $extName = $extName !== null ? strtolower(trim($extName)) : null;

    if ($email === '' || $lastName === '' || $firstName === '') {
        return false;
    }

    $result = dbFetchOne(
        "SELECT COUNT(*) as count
         FROM survey_responses
         WHERE LOWER(TRIM(email)) = :email
           AND LOWER(TRIM(last_name)) = :ln
           AND LOWER(TRIM(first_name)) = :fn
           AND LOWER(TRIM(middle_name)) <=> :mn
           AND LOWER(TRIM(ext_name)) <=> :ext
           AND consent_given = 1
           AND completed_at IS NOT NULL",
        [
            'email' => $email,
            'ln' => $lastName,
            'fn' => $firstName,
            'mn' => $middleName,
            'ext' => $extName,
        ]
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
    $lastName = normalizeUpperText($data['last_name'] ?? '');
    if (!validateRequired($lastName)) {
        $result->addError('last_name', 'Last name is required.');
    } elseif (!validateMinLength($lastName, 2)) {
        $result->addError('last_name', 'Last name must be at least 2 characters.');
    } elseif (!validateMaxLength($lastName, 100)) {
        $result->addError('last_name', 'Last name must not exceed 100 characters.');
    }
    $result->sanitized['last_name'] = $lastName;
    
    // First Name (required)
    $firstName = normalizeUpperText($data['first_name'] ?? '');
    if (!validateRequired($firstName)) {
        $result->addError('first_name', 'First name is required.');
    } elseif (!validateMinLength($firstName, 2)) {
        $result->addError('first_name', 'First name must be at least 2 characters.');
    } elseif (!validateMaxLength($firstName, 100)) {
        $result->addError('first_name', 'First name must not exceed 100 characters.');
    }
    $result->sanitized['first_name'] = $firstName;
    
    // Middle Name (optional)
    $middleName = normalizeUpperText($data['middle_name'] ?? '');
    if ($middleName !== '' && !validateMaxLength($middleName, 100)) {
        $result->addError('middle_name', 'Middle name must not exceed 100 characters.');
    }
    $result->sanitized['middle_name'] = $middleName !== '' ? $middleName : null;
    
    // Extension Name (optional)
    $extNameRaw = sanitizeString($data['ext_name'] ?? '');
    $extNameRaw = normalizeWhitespace($extNameRaw);
    $extName = $extNameRaw !== '' ? $extNameRaw : null;

    $allowedExtNames = [null, 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI'];
    if (!in_array($extName, $allowedExtNames, true)) {
        $result->addError('ext_name', 'Please select a valid extension name.');
    }
    $result->sanitized['ext_name'] = $extName;
    
    // Sex (optional)
    $sex = $data['sex'] ?? null;
    $allowedSex = ['male', 'female', 'prefer_not_to_say'];
    if ($sex !== null && $sex !== '' && !validateInList($sex, $allowedSex)) {
        $result->addError('sex', 'Please select a valid option.');
    }
    $result->sanitized['sex'] = ($sex !== null && $sex !== '') ? $sex : null;
    
    // Age range (optional)
    $ageRange = $data['age_range'] ?? null;
    $allowedAge = ['20-29', '30-39', '40-49', '50-59', '60+'];
    if ($ageRange !== null && $ageRange !== '' && !validateInList($ageRange, $allowedAge)) {
        $result->addError('age_range', 'Please select a valid option.');
    }
    $result->sanitized['age_range'] = ($ageRange !== null && $ageRange !== '') ? $ageRange : null;
    
    // Email (required)
    $email = sanitizeEmail($data['email'] ?? '');
    $email = $email !== '' ? strtolower($email) : '';
    if (!validateRequired($email)) {
        $result->addError('email', 'Email address is required.');
    } elseif (!validateEmail($email)) {
        $result->addError('email', 'Please enter a valid email address.');
    } elseif (!validateEmailDomain($email)) {
        $result->addError('email', 'Please use a valid email provider (e.g., Gmail, Yahoo, Outlook, or your official government/educational email).');
    } elseif (isNameEmailAlreadyUsed($email, $lastName, $firstName, $middleName !== '' ? $middleName : null, $extName !== '' ? $extName : null)) {
        $result->addError('email', 'A survey response with the same name and email already exists. Please use a different email or verify the name.');
    }
    $result->sanitized['email'] = $email !== '' ? $email : null;
    
    // Mobile / Phone Number (optional but validate format if provided)
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
    if (!validateRequired($officeType)) {
        $result->addError('office_type', 'Please select your office type.');
    } elseif (!validateInList($officeType, $allowedOffice)) {
        $result->addError('office_type', 'Please select a valid option.');
    }
    $result->sanitized['office_type'] = ($officeType !== null && $officeType !== '') ? $officeType : null;

     // Q12: Current Position (Required)
    $currentPosition = normalizeUpperText($data['current_position'] ?? '');
    if ($currentPosition === '') {
        $result->addError('current_position', 'Please select or type your current position.');
    }
    $result->sanitized['current_position'] = $currentPosition;

    // Q10: Field Office Assignment (conditional)
    $officeAssignment = sanitizeString($data['office_assignment'] ?? '');
    $allowedAssignments = [
        'I', 'II', 'III', 'IV-A', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 
        'NCR', 'CAR', 'XIII', 'MIMAROPA', 'NIR', 'BARMM',
    ];

    if ($officeType === 'field_office') {
        if ($officeAssignment === '') {
             $result->addError('office_assignment', 'Please select your Field Office assignment.');
        } elseif (!validateInList($officeAssignment, $allowedAssignments)) {
             $result->addError('office_assignment', 'Please select a valid Field Office.');
        }

        // PSGC drill-down (Region -> Province -> City/Municipality)
        $psgcRegionCode = (int) sanitizeString($data['psgc_region_code'] ?? '');
        $psgcProvinceCode = (int) sanitizeString($data['psgc_province_code'] ?? '');
        $psgcCityCode = (int) sanitizeString($data['psgc_city_code'] ?? '');

        if ($psgcRegionCode <= 0) {
            $result->addError('psgc_region_code', 'Please select your Region.');
        }
        if ($psgcProvinceCode <= 0) {
            $result->addError('psgc_province_code', 'Please select your Province.');
        }
        if ($psgcCityCode <= 0) {
            $result->addError('psgc_city_code', 'Please select your City / Municipality.');
        }

        // Validate PSGC hierarchy when all codes are present.
        // (Best-effort: if DB/table is unavailable, skip this check.)
        if ($psgcRegionCode > 0 && $psgcProvinceCode > 0 && $psgcCityCode > 0) {
            try {
                $ok = dbFetchOne(
                    "SELECT 1 FROM ref_psgc_city
                     WHERE city_code = :city_code
                       AND province_code = :province_code
                       AND region_code = :region_code
                     LIMIT 1",
                    [
                        'city_code' => $psgcCityCode,
                        'province_code' => $psgcProvinceCode,
                        'region_code' => $psgcRegionCode,
                    ]
                );
                if (!$ok) {
                    $result->addError('psgc_city_code', 'Selected location is invalid. Please re-select your Region/Province/City.');
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        $result->sanitized['psgc_region_code'] = $psgcRegionCode > 0 ? $psgcRegionCode : null;
        $result->sanitized['psgc_province_code'] = $psgcProvinceCode > 0 ? $psgcProvinceCode : null;
        $result->sanitized['psgc_city_code'] = $psgcCityCode > 0 ? $psgcCityCode : null;

        // Q10: Field Office Unit (Required)
        $fieldOfficeUnit = sanitizeString($data['field_office_unit'] ?? '');
        if ($fieldOfficeUnit === '') {
            $result->addError('field_office_unit', 'Please enter your Office Field / Unit / Program Assignment.');
        } elseif (!validateMaxLength($fieldOfficeUnit, 255)) {
            $result->addError('field_office_unit', 'Field Office Unit must not exceed 255 characters.');
        }
        $result->sanitized['field_office_unit'] = $fieldOfficeUnit;

    } else {
        $officeAssignment = null;
        $result->sanitized['field_office_unit'] = null;
        $result->sanitized['psgc_region_code'] = null;
        $result->sanitized['psgc_province_code'] = null;
        $result->sanitized['psgc_city_code'] = null;
    }
    $result->sanitized['office_assignment'] = $officeAssignment;
    
    // Q10.a: Central Office Bureau/Service (conditional) - New Field
    // Will come from 'office_bureau' input
    $officeBureau = sanitizeString($data['office_bureau'] ?? '');
    if ($officeType === 'central_office') {
         if ($officeBureau === '') {
             $result->addError('office_bureau', 'Please select your Bureau/Service/Office.');
         }
    } else {
        $officeBureau = null;
    }
    $result->sanitized['office_bureau'] = $officeBureau;

    // Q10.b: Attached Agency (conditional) - New Field
    // Will come from 'attached_agency' input
    $attachedAgency = sanitizeString($data['attached_agency'] ?? '');
    if ($officeType === 'attached_agency') {
        if ($attachedAgency === '') {
             $result->addError('attached_agency', 'Please select your Attached Agency.');
        }
    } else {
        $attachedAgency = null;
    }
    $result->sanitized['attached_agency'] = $attachedAgency;
    
    // Q8/Legacy specific_office handling:
    // We keep specific_office for backward compat or manual entry fallback if needed, 
    // but primarily we rely on the specific dropdowns above.
    $specificOffice = normalizeUpperText($data['specific_office'] ?? '');
    $result->sanitized['specific_office'] = $specificOffice;
    
    // Program assignments (optional checkbox list / multi-select)
    $programs = $data['program_assignments'] ?? [];
    if (!is_array($programs)) {
        $programs = [];
    }
    $programs = array_map('sanitizeString', $programs);
    $programs = array_values(array_filter($programs, static fn($v) => $v !== ''));
    $programs = array_values(array_unique($programs));
    if (count($programs) > 30) {
        $result->addError('program_assignments', 'Please limit selections to 30 programs or fewer.');
    }
    foreach ($programs as $program) {
        if (!validateMaxLength($program, 100)) {
            $result->addError('program_assignments', 'One or more program entries are too long (max 100 characters each).');
            break;
        }
    }
    $result->sanitized['program_assignments'] = $programs;
    
    // Current position / designation (optional)
    $position = normalizeUpperText($data['current_position'] ?? '');
    if ($position !== '' && !validateMaxLength($position, 255)) {
        $result->addError('current_position', 'This field must not exceed 255 characters.');
    }
    $result->sanitized['current_position'] = $position !== '' ? $position : null;
    
    // Employment status (required)
    $status = $data['employment_status'] ?? null;
    $allowedStatus = ['permanent', 'cos', 'jo', 'others'];
    if (!validateRequired($status)) {
        $result->addError('employment_status', 'Please select your employment status.');
    } elseif (!validateInList($status, $allowedStatus)) {
        $result->addError('employment_status', 'Please select a valid option.');
    }
    $result->sanitized['employment_status'] = ($status !== null && $status !== '') ? $status : null;
    
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
    
    // Q13. Total Years (Required)
    $yearsWork = $data['years_dswd'] ?? '';
    // Allow '0' as valid entry, but not empty string/null
    if ($yearsWork === '' || $yearsWork === null) {
        $result->addError('years_dswd', 'Please enter your total years of work experience.');
    } // Removed numeric check as these are radio buttons with string values like 'lt5', '5-10' now
    
    $result->sanitized['years_dswd'] = ($yearsWork !== '') ? $yearsWork : null;

    // Q14. Years as SW (Required)
    $yearsSw = $data['years_swd_sector'] ?? '';
    if ($yearsSw === '' || $yearsSw === null) {
         $result->addError('years_swd_sector', 'Please enter your years of experience as a Social Worker/Development Worker.');
    }
    $result->sanitized['years_swd_sector'] = ($yearsSw !== '') ? $yearsSw : null;

    // Q15. Tasks (Checkbox - Required)
    $swTasks = $data['sw_tasks'] ?? [];
    if (!is_array($swTasks)) {
        $swTasks = [];
    }
    if (empty($swTasks)) {
        $result->addError('sw_tasks', 'Please select at least one task/function performed.');
    }
    // (Existing allowed list check remains valid, just enforcing non-empty above)
    $allowedTasks = [
        'Case management / casework',
        'Community organizing / development',
        'Program implementation (4Ps, SLP, AICS, etc.)',
        'Disaster response / humanitarian assistance',
        'Psychosocial support services',
        'Monitoring & evaluation / reporting',
        'Policy / standards / research work',
        'Administrative / support functions',
        'Supervision / team leadership',
        'Other'
    ];
    
    if (!validateCheckboxValues($swTasks, $allowedTasks)) {
        $result->addError('sw_tasks', 'Invalid task selection.');
    }
    $result->sanitized['sw_tasks'] = $swTasks;

    // Q16. Others (Conditional Required)
    $swTasksOther = normalizeWhitespace($data['sw_tasks_other'] ?? '');
    if (in_array('Other', $swTasks) && $swTasksOther === '') {
        $result->addError('sw_tasks_other', 'Please specify your other tasks.');
    }
    $result->sanitized['sw_tasks_other'] = in_array('Other', $swTasks) ? $swTasksOther : '';

    // Derive performs_sw_tasks from whether any SW tasks were selected.
    // Keep NULL when the respondent didn't select anything.
    $result->sanitized['performs_sw_tasks'] = count($swTasks) > 0 ? true : null;
    
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

    // Social work–related experiences (optional checkboxes)
    $areas = $data['expertise_areas'] ?? [];
    if (!is_array($areas)) {
        $areas = [];
    }
    $areas = array_map('sanitizeString', $areas);

    $otherText = normalizeUpperText($data['expertise_areas_other'] ?? '');
    if (!in_array('Other', $areas, true) && $otherText !== '') {
        $result->addError('expertise_areas', 'Please select "Others" if you want to specify additional experiences.');
    }
    if (in_array('Other', $areas, true)) {
        if ($otherText === '') {
            $result->addError('expertise_areas_other', 'Please specify your "Others" entry.');
        } elseif (!validateMaxLength($otherText, 200)) {
            $result->addError('expertise_areas_other', 'Your "Others" entry must not exceed 200 characters.');
        } else {
            $areas = array_values(array_filter($areas, static fn($v) => $v !== 'Other'));
            $areas[] = 'Others: ' . $otherText;
        }
    }
    $result->sanitized['expertise_areas'] = $areas;
    
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
    
    // Highest educational attainment (required)
    $highest = $data['highest_education'] ?? null;
    $allowedEdu = ['high_school', 'some_college', 'bachelors', 'masters', 'doctoral'];
    if (!validateRequired($highest)) {
        $result->addError('highest_education', 'Please select your highest educational attainment.');
    } elseif (!validateInList($highest, $allowedEdu)) {
        $result->addError('highest_education', 'Please select a valid option.');
    }
    $result->sanitized['highest_education'] = ($highest !== null && $highest !== '') ? $highest : null;
    
    // Undergrad course (optional)
    $undergrad = normalizeUpperText($data['undergrad_course'] ?? '');
    if ($undergrad !== '' && !validateMaxLength($undergrad, 255)) {
        $result->addError('undergrad_course', 'This field must not exceed 255 characters.');
    }
    $result->sanitized['undergrad_course'] = $undergrad !== '' ? $undergrad : null;
    
    // Diploma course (optional)
    $diploma = normalizeUpperText($data['diploma_course'] ?? '');
    if ($diploma !== '' && !validateMaxLength($diploma, 255)) {
        $result->addError('diploma_course', 'This field must not exceed 255 characters.');
    }
    $result->sanitized['diploma_course'] = $diploma !== '' ? $diploma : null;
    
    // Graduate course (optional)
    $graduate = normalizeUpperText($data['graduate_course'] ?? '');
    if ($graduate !== '' && !validateMaxLength($graduate, 255)) {
        $result->addError('graduate_course', 'This field must not exceed 255 characters.');
    }
    $result->sanitized['graduate_course'] = $graduate !== '' ? $graduate : null;
    
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
    
    // Have you availed of any DSWD Academy trainings? (optional)
    $availed = $data['availed_dswd_training'] ?? null;
    if ($availed !== null && $availed !== '' && !validateInList($availed, ['yes', 'no'])) {
        $result->addError('availed_dswd_training', 'Please select a valid option.');
    }
    $result->sanitized['availed_dswd_training'] = ($availed === 'yes') ? true : (($availed === 'no') ? false : null);
    
    // If YES, indicate courses taken (optional)
    $courses = $data['dswd_courses'] ?? [];
    if (!is_array($courses)) {
        $courses = [];
    }
    $courses = array_map('sanitizeString', $courses);
    $courses = array_values(array_filter($courses, static fn($v) => $v !== ''));
    $courses = array_values(array_unique($courses));

    // Basic sanity checks: avoid giant payloads and keep entries within DB column size.
    if (count($courses) > 50) {
        $result->addError('dswd_courses', 'Please limit selections to 50 courses or fewer.');
    }
    foreach ($courses as $course) {
        if (!validateMaxLength($course, 255)) {
            $result->addError('dswd_courses', 'One or more course entries are too long (max 255 characters each).');
            break;
        }
    }

    $result->sanitized['dswd_courses'] = $courses;
    
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

    // Q23. Awareness of ETEEAP (Required)
    $awareness = $data['eteeap_awareness'] ?? '';
    if (!validateRequired($awareness)) {
        $result->addError('eteeap_awareness', 'Please indicate your awareness of ETEEAP.');
    } elseif (!validateInList($awareness, ['aware', 'not_aware'])) {
        $result->addError('eteeap_awareness', 'Please select a valid option.');
    }
    $result->sanitized['eteeap_awareness'] = ($awareness !== '') ? $awareness : null;

    // Q24. Interest in ETEEAP (Required)
    $interest = $data['eteeap_interest'] ?? '';
    if (!validateRequired($interest)) {
        $result->addError('eteeap_interest', 'Please indicate your level of interest.');
    } else {
        $allowedInterest = ['very_interested', 'interested', 'somewhat_interested', 'not_interested'];
        if (!in_array($interest, $allowedInterest)) {
             $result->addError('eteeap_interest', 'Please select a valid interest level.');
        }
    }
    $result->sanitized['eteeap_interest'] = ($interest !== '') ? $interest : null;

    // Q25. Motivations (Required)
    $motivations = $data['motivations'] ?? [];
    if (!is_array($motivations)) {
        $motivations = [];
    }
    // Filter out empty strings just in case
    $motivations = array_filter($motivations);
    
    if (empty($motivations)) {
        $result->addError('motivations', 'Please select at least one motivation.');
    } else {
        // Optional: specific allowed values check if strictness needed
        // $allowedMotivations = ['...'];
        // if (!validateCheckboxValues($motivations, $allowedMotivations)) ...
    }
    $result->sanitized['motivations'] = $motivations;

    // Q26. Barriers (Required)
    $barriers = $data['barriers'] ?? [];
    if (!is_array($barriers)) {
        $barriers = [];
    }
    $barriers = array_filter($barriers);
    
    if (empty($barriers)) {
         $result->addError('barriers', 'Please select at least one possible barrier.');
    }
    $result->sanitized['barriers'] = $barriers;

    // Q27. Will Apply (Required)
    $willApply = $data['will_apply'] ?? '';
    if (!validateRequired($willApply)) {
        $result->addError('will_apply', 'Please indicate whether you would apply for ETEEAP.');
    } elseif (!validateInList($willApply, ['yes', 'no'])) {
        $result->addError('will_apply', 'Please select a valid option.');
    }
    $result->sanitized['will_apply'] = ($willApply !== null && $willApply !== '') ? $willApply : null;
    
    // If "No" is selected, reason is REQUIRED
    $willNotApplyReason = sanitizeString($data['will_not_apply_reason'] ?? '');
    if ($willApply === 'no') {
        if (!validateRequired($willNotApplyReason)) {
            $result->addError('will_not_apply_reason', 'Please provide a reason why you would not apply.');
        } elseif (!validateMinLength($willNotApplyReason, 10)) {
            $result->addError('will_not_apply_reason', 'Please provide a more detailed reason (at least 10 characters).');
        } elseif (!validateMaxLength($willNotApplyReason, 500)) {
            $result->addError('will_not_apply_reason', 'Reason must not exceed 500 characters.');
        }
        $result->sanitized['will_not_apply_reason'] = $willNotApplyReason;
    } else {
        // Clear reason if Yes is selected
        $result->sanitized['will_not_apply_reason'] = null;
    }
    
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
