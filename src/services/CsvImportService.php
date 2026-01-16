<?php
/**
 * ETEEAP Survey Application - CSV Import Service
 *
 * Handles admin CSV template export and CSV imports into the database.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

class CsvImportService
{
    /**
     * Multi-value fields are encoded as `value1; value2; value3` in a single CSV cell.
     */
    private const MULTI_VALUE_SPLIT_REGEX = '/\s*;\s*/';

    /**
     * Max rows to process per import (excluding header).
     * Prevents huge uploads from exhausting memory/time.
     */
    private const DEFAULT_MAX_ROWS = 5000;

    /**
     * Maximum CSV file size (bytes).
     */
    private const DEFAULT_MAX_FILE_BYTES = 5242880; // 5 MiB

    /**
     * Maximum CSV line length (bytes) to protect memory usage.
     */
    private const DEFAULT_MAX_LINE_BYTES = 200000;

    /**
     * Template headers (human-readable), aligned with export labels where possible.
     *
     * @return string[]
     */
    public static function getTemplateHeaders(): array
    {
        return [
            'Consent Given',

            // Basic Information
            'Last Name',
            'First Name',
            'Middle Name',
            'Extension Name',
            'Sex',
            'Age Range',
            'Email',
            'Phone',

            // Office & Employment
            'Office Type',
            'Field Office (Region) Assignment',
            'Central Office Bureau / Service / Office',
            'Attached Agency',
            'Field Office Unit / Program Assignment',
            'Division / Section / Unit (Optional)',
            'Program Assignments',
            'Current Position / Designation',
            'Employment Status',

            // Work Experience
            'Total Years of Work Experience',
            'Years of Social Work-Related Experience',
            'Current Tasks / Functions',
            'Current Tasks / Functions (Other)',
            'Social Work-Related Experiences',
            'Social Work-Related Experiences (Other)',

            // Education
            'Highest Education',
            'Undergraduate Course / Degree',
            'Diploma Course',
            'Graduate Course / Degree',

            // DSWD Academy
            'Availed DSWD Training',
            'DSWD Courses Taken',

            // ETEEAP Interest
            'ETEEAP Awareness',
            'ETEEAP Interest Level',
            'Motivations',
            'Barriers',
            'Will Apply',
            'Reason for Not Applying',
            'Additional Comments',
        ];
    }

    /**
     * Stream the template as CSV download.
     */
    public static function exportTemplateCsv(): void
    {
        $filename = sprintf(
            'eteeap_survey_import_template_%s.csv',
            date(EXPORT_DATETIME_FORMAT)
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

        $headers = self::getTemplateHeaders();
        $safeHeaders = array_map(static function ($v) {
            $v = sanitizeForCsv((string) $v);
            return csvEscapeFormula($v);
        }, $headers);
        fputcsv($output, $safeHeaders, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);

        $example = self::getTemplateExampleRow();
        $row = [];
        foreach ($headers as $label) {
            $v = (string) ($example[$label] ?? '');
            $v = sanitizeForCsv($v);
            $v = csvEscapeFormula($v);
            $row[] = $v;
        }
        fputcsv($output, $row, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);

        fclose($output);
        exit;
    }

    /**
     * Import CSV rows into the database.
     *
     * @param array $file The $_FILES entry
     * @param array{strict_headers?:bool, atomic?:bool, max_rows?:int} $options
     * @return array{success:bool, inserted:int, failed:int, total:int, atomic:bool, rolled_back?:bool, warnings:string[], errors:array<int, array{row:int, message:string}>}
     */
    public static function importFromUploadedFile(array $file, array $options = []): array
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $msg = self::uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE));
            return self::resultError($msg);
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp)) {
            return self::resultError('Upload failed: temporary file not found.');
        }

        if (function_exists('is_uploaded_file') && !is_uploaded_file($tmp)) {
            return self::resultError('Invalid upload source.');
        }

        $originalName = (string) ($file['name'] ?? '');
        if ($originalName !== '' && !preg_match('/\\.csv$/i', $originalName)) {
            return self::resultError('Invalid file extension. Please upload a .csv file.');
        }

        $allowedMimes = [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
        ];

        $mime = (string) ($file['type'] ?? '');
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = @finfo_file($finfo, $tmp);
                if (is_string($detected) && $detected !== '') {
                    $mime = $detected;
                }
                @finfo_close($finfo);
            }
        }
        if ($mime !== '' && !in_array($mime, $allowedMimes, true)) {
            return self::resultError('Invalid file type. Please upload a CSV file.');
        }

        $maxBytes = (int) ($options['max_bytes'] ?? self::DEFAULT_MAX_FILE_BYTES);
        if ($maxBytes < 1) {
            $maxBytes = self::DEFAULT_MAX_FILE_BYTES;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            $size = (int) (filesize($tmp) ?: 0);
        }
        if ($size > 0 && $size > $maxBytes) {
            return self::resultError('The uploaded CSV is too large.');
        }

        return self::importFromCsvPath($tmp, $options);
    }

    /**
     * @param array{strict_headers?:bool, atomic?:bool, max_rows?:int} $options
     * @return array{success:bool, inserted:int, failed:int, total:int, atomic:bool, rolled_back?:bool, warnings:string[], errors:array<int, array{row:int, message:string}>}
     */
    public static function importFromCsvPath(string $path, array $options = []): array
    {
        $strictHeaders = (bool) ($options['strict_headers'] ?? true);
        $atomic = (bool) ($options['atomic'] ?? true);
        $maxRows = (int) ($options['max_rows'] ?? self::DEFAULT_MAX_ROWS);
        if ($maxRows < 1) {
            $maxRows = self::DEFAULT_MAX_ROWS;
        }
        $maxLineBytes = (int) ($options['max_line_bytes'] ?? self::DEFAULT_MAX_LINE_BYTES);
        if ($maxLineBytes < 1) {
            $maxLineBytes = self::DEFAULT_MAX_LINE_BYTES;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return self::resultError('Unable to read the CSV file.');
        }

        $warnings = [];
        $errors = [];
        $inserted = 0;
        $failed = 0;
        $processed = 0;

        try {
            $headerRow = fgetcsv($handle, $maxLineBytes, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);
            if (!is_array($headerRow) || empty($headerRow)) {
                return self::resultError('CSV header row is missing or invalid.');
            }

            $headerRow = self::normalizeHeaderRow($headerRow);

            $headerToField = self::getHeaderToFieldMap();
            $colToField = [];
            $unknownHeaders = [];

            foreach ($headerRow as $i => $header) {
                $norm = self::normalizeHeader($header);
                if ($norm === '') {
                    continue;
                }
                if (isset($headerToField[$norm])) {
                    $colToField[$i] = $headerToField[$norm];
                } else {
                    $unknownHeaders[] = $header;
                }
            }

            if ($strictHeaders && !empty($unknownHeaders)) {
                return self::resultError(
                    'Unknown header(s): ' . implode(', ', array_slice($unknownHeaders, 0, 15))
                    . (count($unknownHeaders) > 15 ? '…' : '')
                );
            }

            $required = self::getRequiredFields();
            $presentFields = array_values(array_unique(array_values($colToField)));
            $missingFields = array_values(array_diff($required, $presentFields));
            if (!empty($missingFields)) {
                return self::resultError(
                    'Missing required column(s): ' . implode(', ', array_map([self::class, 'fieldToPreferredLabel'], $missingFields))
                );
            }

            $seenEmails = [];
            $rowNumber = 1; // header = 1

            if ($atomic) {
                dbBeginTransaction();
            }

            while (($row = fgetcsv($handle, $maxLineBytes, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE)) !== false) {
                $rowNumber++;
                if ($row === [null] || $row === false) {
                    continue;
                }

                $processed++;
                if ($processed > $maxRows) {
                    $warnings[] = "Stopped after {$maxRows} row(s) (limit reached).";
                    break;
                }

                // Treat empty rows as skippable.
                $isEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string) $cell) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) {
                    continue;
                }

                if (!$atomic) {
                    if (getDbConnection()->inTransaction()) {
                        dbRollback();
                    }
                    dbBeginTransaction();
                }

                try {
                    $flat = self::rowToFlat($row, $colToField);
                    $flat = self::normalizeFlatValues($flat);

                    // Consent must be explicitly Yes/true.
                    if (!self::parseTruthy($flat['consent_given'] ?? '')) {
                        throw new InvalidArgumentException('Consent Given must be Yes.');
                    }

                    $email = strtolower(trim((string) ($flat['email'] ?? '')));
                    if ($email !== '') {
                        if (isset($seenEmails[$email])) {
                            throw new InvalidArgumentException('Duplicate email within the CSV file.');
                        }
                        $seenEmails[$email] = true;
                    }

                    [$sanitized, $validationErrors] = self::validateFlat($flat);
                    if (!empty($validationErrors)) {
                        throw new InvalidArgumentException($validationErrors);
                    }

                    self::insertResponse($sanitized, $flat);
                    $inserted++;

                    if (!$atomic) {
                        dbCommit();
                    }
                } catch (Throwable $e) {
                    $failed++;
                    if (!$atomic) {
                        dbRollback();
                    }

                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => self::stringifyRowError($e),
                    ];
                }
            }

            if ($atomic) {
                if (!empty($errors)) {
                    dbRollback();
                    return [
                        'success' => false,
                        'inserted' => 0,
                        'failed' => $failed,
                        'total' => $inserted + $failed,
                        'atomic' => true,
                        'rolled_back' => true,
                        'warnings' => $warnings,
                        'errors' => $errors,
                    ];
                }
                dbCommit();
            }

            return [
                'success' => $inserted > 0 && empty($errors),
                'inserted' => $inserted,
                'failed' => $failed,
                'total' => $inserted + $failed,
                'atomic' => $atomic,
                'warnings' => $warnings,
                'errors' => $errors,
            ];
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array<string, string> normalized header => field
     */
    private static function getHeaderToFieldMap(): array
    {
        // Start with export labels.
        $map = getExportHeaders();

        // Extend with import-only columns.
        $map['Program Assignments'] = 'program_assignments';
        $map['Current Tasks / Functions (Other)'] = 'sw_tasks_other';
        $map['Social Work-Related Experiences (Other)'] = 'expertise_areas_other';
        $map['Additional Comments'] = 'additional_comments';

        $out = [];
        foreach ($map as $label => $field) {
            $out[self::normalizeHeader($label)] = $field;
            $out[self::normalizeHeader($field)] = $field; // allow DB-field style headers
        }

        // Back-compat: allow a couple of common variants.
        $out[self::normalizeHeader('DSWD Courses Taken')] = 'dswd_courses';
        $out[self::normalizeHeader('DSWD Courses')] = 'dswd_courses';

        return $out;
    }

    /**
     * @return string[]
     */
    private static function getRequiredFields(): array
    {
        return [
            'consent_given',

            // Basic
            'last_name',
            'first_name',
            'sex',
            'age_range',
            'email',

            // Office & employment
            'office_type',
            'current_position',
            'employment_status',

            // Work
            'years_dswd',
            'years_swd_sector',
            'sw_tasks',

            // Education
            'highest_education',

            // Interest
            'eteeap_awareness',
            'eteeap_interest',
            'motivations',
            'barriers',
            'will_apply',
        ];
    }

    private static function fieldToPreferredLabel(string $field): string
    {
        $reverse = [];

        foreach (getExportHeaders() as $label => $f) {
            $reverse[$f] = $label;
        }
        $reverse['program_assignments'] = 'Program Assignments';
        $reverse['sw_tasks_other'] = 'Current Tasks / Functions (Other)';
        $reverse['expertise_areas_other'] = 'Social Work-Related Experiences (Other)';
        $reverse['additional_comments'] = 'Additional Comments';

        return $reverse[$field] ?? $field;
    }

    /**
     * @param string[] $headerRow
     * @return string[]
     */
    private static function normalizeHeaderRow(array $headerRow): array
    {
        $out = [];
        foreach ($headerRow as $i => $header) {
            $header = (string) $header;
            if ($i === 0) {
                // Strip UTF-8 BOM from first header if present.
                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            }
            $out[] = trim($header);
        }
        return $out;
    }

    private static function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;
        return strtolower($header);
    }

    /**
     * @param array<int, string> $row
     * @param array<int, string> $colToField
     * @return array<string, mixed>
     */
    private static function rowToFlat(array $row, array $colToField): array
    {
        $flat = [];
        foreach ($colToField as $col => $field) {
            $value = $row[$col] ?? '';
            $flat[$field] = is_string($value) ? trim($value) : $value;
        }

        // Normalize multi-value fields from strings.
        foreach (['program_assignments', 'sw_tasks', 'expertise_areas', 'dswd_courses', 'motivations', 'barriers'] as $field) {
            if (!array_key_exists($field, $flat)) {
                continue;
            }
            $v = $flat[$field];
            if (is_string($v)) {
                $flat[$field] = self::splitMultiValueCell($v);
            }
        }

        return $flat;
    }

    /**
     * Map human-readable labels (from export) back to internal enum codes expected by validators.
     *
     * @param array<string, mixed> $flat
     * @return array<string, mixed>
     */
    private static function normalizeFlatValues(array $flat): array
    {
        $enum = getSurveyEnumMappings();
        $reverse = [];
        foreach ($enum as $field => $mapping) {
            $reverse[$field] = [];
            foreach ($mapping as $code => $label) {
                $reverse[$field][mb_strtolower(trim($label), 'UTF-8')] = $code;
            }
        }

        $mapEnum = static function (string $field, $value) use ($reverse): ?string {
            if ($value === null) {
                return null;
            }
            $raw = trim((string) $value);
            if ($raw === '') {
                return null;
            }

            $rawLower = mb_strtolower($raw, 'UTF-8');
            if (isset($reverse[$field]) && isset($reverse[$field][$rawLower])) {
                return $reverse[$field][$rawLower];
            }

            // Accept internal code directly.
            return $raw;
        };

        foreach (['sex', 'office_type', 'employment_status', 'years_dswd', 'years_swd_sector', 'highest_education', 'eteeap_interest', 'will_apply'] as $field) {
            if (array_key_exists($field, $flat)) {
                $flat[$field] = $mapEnum($field, $flat[$field]);
            }
        }

        // Booleans / special enums.
        if (array_key_exists('availed_dswd_training', $flat)) {
            $flat['availed_dswd_training'] = self::parseYesNo($flat['availed_dswd_training']);
        }

        if (array_key_exists('eteeap_awareness', $flat)) {
            $awareness = trim((string) ($flat['eteeap_awareness'] ?? ''));
            if ($awareness !== '') {
                $awarenessLower = strtolower($awareness);
                if (in_array($awarenessLower, ['aware', 'not_aware'], true)) {
                    $flat['eteeap_awareness'] = $awarenessLower;
                } elseif (self::parseTruthy($awareness)) {
                    $flat['eteeap_awareness'] = 'aware';
                } elseif (self::parseFalsy($awareness)) {
                    $flat['eteeap_awareness'] = 'not_aware';
                }
            }
        }

        return $flat;
    }

    /**
     * @param array<string, mixed> $flat
     * @return array{0:array<string,mixed>,1:string} [sanitized, errorMessage]
     */
    private static function validateFlat(array $flat): array
    {
        $errors = [];
        $sanitized = [];

        $basic = validateStepBasicInfo($flat);
        $office = validateStepOfficeData($flat);
        $work = validateStepWorkExperience($flat);
        $competencies = validateStepCompetencies($flat);
        $education = validateStepEducation($flat);
        $training = validateStepDswdCourses($flat);
        $interest = validateStepEteeapInterest($flat);

        foreach ([$basic, $office, $work, $competencies, $education, $training, $interest] as $result) {
            if (!$result->isValid) {
                $errors = array_merge_recursive($errors, $result->errors);
            }
            $sanitized = array_merge($sanitized, $result->sanitized);
        }

        // Ensure additional_comments is available (not validated elsewhere).
        if (isset($flat['additional_comments'])) {
            $comment = sanitizeString($flat['additional_comments']);
            $sanitized['additional_comments'] = $comment !== '' ? $comment : null;
        } else {
            $sanitized['additional_comments'] = null;
        }

        if (empty($errors)) {
            return [$sanitized, ''];
        }

        // Build compact error string.
        $parts = [];
        foreach ($errors as $field => $messages) {
            if (!is_array($messages) || empty($messages)) {
                continue;
            }
            $parts[] = self::fieldToPreferredLabel((string) $field) . ': ' . (string) $messages[0];
            if (count($parts) >= 6) {
                $parts[] = '…';
                break;
            }
        }

        return [$sanitized, implode(' | ', $parts)];
    }

    /**
     * Insert a validated response and its multi-value fields.
     *
     * @param array<string, mixed> $sanitized
     * @param array<string, mixed> $flat Original flat (for a couple of optional non-validated fields)
     */
    private static function insertResponse(array $sanitized, array $flat): int
    {
        $sessionId = bin2hex(random_bytes(32));

        $willApply = $sanitized['will_apply'] ?? null;
        $willNotApplyReason = $sanitized['will_not_apply_reason'] ?? null;
        if ($willApply !== 'no') {
            $willNotApplyReason = null;
        }

        $responseId = dbInsert(
            "INSERT INTO survey_responses (
                session_id, consent_given, current_step, completed_at,
                last_name, first_name, middle_name, ext_name, sex, age_range, email, phone,
                office_type, office_assignment, office_bureau, attached_agency, field_office_unit, specific_office, current_position, employment_status,
                years_dswd, years_swd_sector,
                performs_sw_tasks,
                highest_education, undergrad_course, diploma_course, graduate_course,
                availed_dswd_training,
                eteeap_awareness, eteeap_interest, will_apply, will_not_apply_reason, additional_comments
            ) VALUES (
                :session_id, :consent_given, :current_step, NOW(),
                :last_name, :first_name, :middle_name, :ext_name, :sex, :age_range, :email, :phone,
                :office_type, :office_assignment, :office_bureau, :attached_agency, :field_office_unit, :specific_office, :current_position, :employment_status,
                :years_dswd, :years_swd_sector,
                :performs_sw_tasks,
                :highest_education, :undergrad_course, :diploma_course, :graduate_course,
                :availed_dswd_training,
                :eteeap_awareness, :eteeap_interest, :will_apply, :will_not_apply_reason, :additional_comments
            )",
            [
                'session_id' => $sessionId,
                'consent_given' => true,
                'current_step' => SURVEY_TOTAL_STEPS,
                'last_name' => $sanitized['last_name'] ?? null,
                'first_name' => $sanitized['first_name'] ?? null,
                'middle_name' => $sanitized['middle_name'] ?? null,
                'ext_name' => $sanitized['ext_name'] ?? null,
                'sex' => $sanitized['sex'] ?? null,
                'age_range' => $sanitized['age_range'] ?? null,
                'email' => $sanitized['email'] ?? null,
                'phone' => $sanitized['phone'] ?? null,
                'office_type' => $sanitized['office_type'] ?? null,
                'office_assignment' => $sanitized['office_assignment'] ?? null,
                'office_bureau' => $sanitized['office_bureau'] ?? null,
                'attached_agency' => $sanitized['attached_agency'] ?? null,
                'field_office_unit' => $sanitized['field_office_unit'] ?? null,
                'specific_office' => $sanitized['specific_office'] ?? null,
                'current_position' => $sanitized['current_position'] ?? null,
                'employment_status' => $sanitized['employment_status'] ?? null,
                'years_dswd' => $sanitized['years_dswd'] ?? null,
                'years_swd_sector' => $sanitized['years_swd_sector'] ?? null,
                'performs_sw_tasks' => isset($sanitized['performs_sw_tasks']) ? ($sanitized['performs_sw_tasks'] ? 1 : 0) : null,
                'highest_education' => $sanitized['highest_education'] ?? null,
                'undergrad_course' => $sanitized['undergrad_course'] ?? null,
                'diploma_course' => $sanitized['diploma_course'] ?? null,
                'graduate_course' => $sanitized['graduate_course'] ?? null,
                'availed_dswd_training' => isset($sanitized['availed_dswd_training']) ? ($sanitized['availed_dswd_training'] ? 1 : 0) : null,
                'eteeap_awareness' => isset($sanitized['eteeap_awareness']) ? ($sanitized['eteeap_awareness'] ? 1 : 0) : null,
                'eteeap_interest' => $sanitized['eteeap_interest'] ?? null,
                'will_apply' => $willApply,
                'will_not_apply_reason' => $willNotApplyReason,
                'additional_comments' => $sanitized['additional_comments'] ?? null,
            ]
        );

        self::insertMultiValues($responseId, 'response_program_assignments', 'program', $sanitized['program_assignments'] ?? ($flat['program_assignments'] ?? []));
        self::insertMultiValues($responseId, 'response_sw_tasks', 'task', $sanitized['sw_tasks'] ?? ($flat['sw_tasks'] ?? []));
        self::insertMultiValues($responseId, 'response_expertise_areas', 'area', $sanitized['expertise_areas'] ?? ($flat['expertise_areas'] ?? []));
        self::insertMultiValues($responseId, 'response_dswd_courses', 'course', $sanitized['dswd_courses'] ?? ($flat['dswd_courses'] ?? []));
        self::insertMultiValues($responseId, 'response_motivations', 'motivation', $sanitized['motivations'] ?? ($flat['motivations'] ?? []));
        self::insertMultiValues($responseId, 'response_barriers', 'barrier', $sanitized['barriers'] ?? ($flat['barriers'] ?? []));

        return $responseId;
    }

    private static function insertMultiValues(int $responseId, string $table, string $column, array $values): void
    {
        if (empty($values)) {
            return;
        }

        $sqlByTable = [
            'response_program_assignments' => [
                'column' => 'program',
                'sql' => 'INSERT INTO response_program_assignments (response_id, program) VALUES (:response_id, :value)',
            ],
            'response_sw_tasks' => [
                'column' => 'task',
                'sql' => 'INSERT INTO response_sw_tasks (response_id, task) VALUES (:response_id, :value)',
            ],
            'response_expertise_areas' => [
                'column' => 'area',
                'sql' => 'INSERT INTO response_expertise_areas (response_id, area) VALUES (:response_id, :value)',
            ],
            'response_dswd_courses' => [
                'column' => 'course',
                'sql' => 'INSERT INTO response_dswd_courses (response_id, course) VALUES (:response_id, :value)',
            ],
            'response_motivations' => [
                'column' => 'motivation',
                'sql' => 'INSERT INTO response_motivations (response_id, motivation) VALUES (:response_id, :value)',
            ],
            'response_barriers' => [
                'column' => 'barrier',
                'sql' => 'INSERT INTO response_barriers (response_id, barrier) VALUES (:response_id, :value)',
            ],
        ];

        $allowedTables = array_keys($sqlByTable);
        if (!in_array($table, $allowedTables, true)) {
            throw new InvalidArgumentException('Invalid table/column combination.');
        }

        $entry = $sqlByTable[$table] ?? null;
        if ($entry === null) {
            throw new InvalidArgumentException('Invalid table/column combination.');
        }
        if (($entry['column'] ?? '') !== $column) {
            throw new InvalidArgumentException('Invalid table/column combination.');
        }

        $sql = (string) ($entry['sql'] ?? '');
        if ($sql === '') {
            throw new InvalidArgumentException('Invalid table/column combination.');
        }
        foreach ($values as $value) {
            $value = sanitizeString($value);
            if ($value === '') {
                continue;
            }
            dbExecute($sql, [
                'response_id' => $responseId,
                'value' => $value,
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private static function getTemplateExampleRow(): array
    {
        return [
            'Consent Given' => 'Yes',
            'Last Name' => 'SANTOS',
            'First Name' => 'JUAN',
            'Middle Name' => 'D',
            'Extension Name' => '',
            'Sex' => 'Male',
            'Age Range' => '30-39',
            'Email' => 'juan.santos@example.com',
            'Phone' => '09171234567',
            'Office Type' => 'Field Office',
            'Field Office (Region) Assignment' => 'NCR',
            'Central Office Bureau / Service / Office' => '',
            'Attached Agency' => '',
            'Field Office Unit / Program Assignment' => 'SLP',
            'Division / Section / Unit (Optional)' => '',
            'Program Assignments' => '4Ps; SLP',
            'Current Position / Designation' => 'SOCIAL WELFARE OFFICER I',
            'Employment Status' => 'Permanent',
            'Total Years of Work Experience' => '5 to 10 years',
            'Years of Social Work-Related Experience' => 'Less than 5 years',
            'Current Tasks / Functions' => 'Case management / casework; Monitoring & evaluation / reporting',
            'Current Tasks / Functions (Other)' => '',
            'Social Work-Related Experiences' => 'Psychosocial support services',
            'Social Work-Related Experiences (Other)' => '',
            'Highest Education' => "Bachelor's",
            'Undergraduate Course / Degree' => 'BS SOCIAL WORK',
            'Diploma Course' => '',
            'Graduate Course / Degree' => '',
            'Availed DSWD Training' => 'Yes',
            'DSWD Courses Taken' => 'Case Management; Community Organizing',
            'ETEEAP Awareness' => 'Yes',
            'ETEEAP Interest Level' => 'Interested',
            'Motivations' => 'Career advancement; Professional growth',
            'Barriers' => 'Time constraints',
            'Will Apply' => 'Yes',
            'Reason for Not Applying' => '',
            'Additional Comments' => '',
        ];
    }

    private static function splitMultiValueCell(string $cell): array
    {
        $cell = trim($cell);
        if ($cell === '') {
            return [];
        }

        $parts = preg_split(self::MULTI_VALUE_SPLIT_REGEX, $cell) ?: [];
        $parts = array_map('sanitizeString', $parts);
        $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));
        $parts = array_values(array_unique($parts));
        return $parts;
    }

    private static function parseYesNo($value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        if (self::parseTruthy($v)) {
            return 'yes';
        }
        if (self::parseFalsy($v)) {
            return 'no';
        }
        $lower = strtolower($v);
        if (in_array($lower, ['yes', 'no'], true)) {
            return $lower;
        }
        return null;
    }

    private static function parseTruthy($value): bool
    {
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'yes', 'y'], true);
    }

    private static function parseFalsy($value): bool
    {
        $v = strtolower(trim((string) $value));
        return in_array($v, ['0', 'false', 'no', 'n'], true);
    }

    private static function stringifyRowError(Throwable $e): string
    {
        if ($e instanceof PDOException) {
            $driverCode = null;
            if (isset($e->errorInfo) && is_array($e->errorInfo)) {
                $driverCode = $e->errorInfo[1] ?? null;
            }
            if ($driverCode === 1062 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return 'Duplicate entry (likely duplicate email).';
            }
        }

        $msg = trim($e->getMessage());
        return $msg !== '' ? $msg : 'Unknown error';
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
            UPLOAD_ERR_PARTIAL => 'The file upload was incomplete.',
            UPLOAD_ERR_NO_FILE => 'Please select a CSV file to upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload failed: missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Upload failed: cannot write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload failed: blocked by a PHP extension.',
            default => 'Upload failed.',
        };
    }

    /**
     * @return array{success:bool, inserted:int, failed:int, total:int, atomic:bool, warnings:string[], errors:array<int, array{row:int, message:string}>}
     */
    private static function resultError(string $message): array
    {
        return [
            'success' => false,
            'inserted' => 0,
            'failed' => 0,
            'total' => 0,
            'atomic' => true,
            'warnings' => [],
            'errors' => [
                ['row' => 0, 'message' => $message],
            ],
        ];
    }
}
