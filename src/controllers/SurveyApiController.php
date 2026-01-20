<?php
/**
 * ETEEAP Survey Application - Survey API Controller
 *
 * Public JSON endpoint for survey submission (CSRF protected).
 */

class SurveyApiController
{
    public function __construct()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function submit(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonError('Method not allowed', 405);
        }

        // CSRF validation (supports form POST and X-CSRF-Token header)
        csrfProtect();

        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody ?: '', true);

        if (!is_array($payload)) {
            $this->jsonError('Invalid JSON request body', 400);
        }

        // Consent is required for submission.
        if (($payload['consent_given'] ?? null) !== true) {
            $this->jsonError('Consent is required to submit the survey', 400);
        }

        $flat = $this->flattenPayload($payload);

        $errors = [];
        $sanitized = [];

        // Reuse existing validators (same rules as the multi-step form)
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

        if (!empty($errors)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 400);
        }

        $email = $sanitized['email'] ?? '';
        $lastName = $sanitized['last_name'] ?? '';
        $firstName = $sanitized['first_name'] ?? '';
        $middleName = $sanitized['middle_name'] ?? null;
        $extName = $sanitized['ext_name'] ?? null;

        if (
            !empty($email) &&
            !empty($lastName) &&
            !empty($firstName) &&
            isNameEmailAlreadyUsed($email, $lastName, $firstName, $middleName, $extName)
        ) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Duplicate submission',
                'errors' => [
                    'email' => ['A survey response with the same name and email already exists.'],
                ],
            ], 409);
        }

        $sessionId = sanitizeString($payload['session_id'] ?? '');
        if ($sessionId === '') {
            $sessionId = bin2hex(random_bytes(32));
        }

        $willApply = $sanitized['will_apply'] ?? null;
        $willNotApplyReason = $sanitized['will_not_apply_reason'] ?? null;
        if ($willApply !== 'no') {
            $willNotApplyReason = null;
        }

        try {
            dbBeginTransaction();

            $responseId = dbInsert(
                "INSERT INTO survey_responses (
                    session_id, consent_given, current_step, completed_at,
                    last_name, first_name, middle_name, ext_name, sex, age_range, email, phone,
                    office_type, office_assignment, psgc_region_code, psgc_province_code, psgc_city_code, office_bureau, attached_agency, field_office_unit, specific_office, current_position, employment_status,
                    years_dswd, years_swd_sector,
                    performs_sw_tasks,
                    highest_education, undergrad_course, diploma_course, graduate_course,
                    availed_dswd_training,
                    eteeap_awareness, eteeap_interest, will_apply, will_not_apply_reason, additional_comments
                ) VALUES (
                    :session_id, :consent_given, :current_step, NOW(),
                    :last_name, :first_name, :middle_name, :ext_name, :sex, :age_range, :email, :phone,
                    :office_type, :office_assignment, :psgc_region_code, :psgc_province_code, :psgc_city_code, :office_bureau, :attached_agency, :field_office_unit, :specific_office, :current_position, :employment_status,
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
                    'last_name' => $lastName ?: null,
                    'first_name' => $firstName ?: null,
                    'middle_name' => $middleName ?: null,
                    'ext_name' => $extName ?: null,
                    'sex' => $sanitized['sex'] ?? null,
                    'age_range' => $sanitized['age_range'] ?? null,
                    'email' => $sanitized['email'] ?? null,
                    'phone' => $sanitized['phone'] ?? null,
                    'office_type' => $sanitized['office_type'] ?? null,
                    'office_assignment' => $sanitized['office_assignment'] ?? null,
                    'psgc_region_code' => $sanitized['psgc_region_code'] ?? null,
                    'psgc_province_code' => $sanitized['psgc_province_code'] ?? null,
                    'psgc_city_code' => $sanitized['psgc_city_code'] ?? null,
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

            $this->insertMultiValues($responseId, 'response_program_assignments', 'program', $sanitized['program_assignments'] ?? []);
            $this->insertMultiValues($responseId, 'response_sw_tasks', 'task', $sanitized['sw_tasks'] ?? []);
            $this->insertMultiValues($responseId, 'response_expertise_areas', 'area', $sanitized['expertise_areas'] ?? []);
            $this->insertMultiValues($responseId, 'response_dswd_courses', 'course', $sanitized['dswd_courses'] ?? []);
            $this->insertMultiValues($responseId, 'response_motivations', 'motivation', $sanitized['motivations'] ?? []);
            $this->insertMultiValues($responseId, 'response_barriers', 'barrier', $sanitized['barriers'] ?? []);

            dbCommit();

            csrfRegenerateToken();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Survey submitted successfully',
                'response_id' => $responseId,
            ], 201);
        } catch (PDOException $e) {
            dbRollback();

            // Duplicate key error (MySQL error code 1062)
            $driverCode = null;
            if (isset($e->errorInfo) && is_array($e->errorInfo)) {
                $driverCode = $e->errorInfo[1] ?? null;
            }

            if ($driverCode === 1062 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Duplicate submission',
                    'errors' => [
                        'email' => ['A survey response with the same name and email already exists.'],
                    ],
                ], 409);
            }

            error_log('Survey API submission failed: ' . $e->getMessage());
            $this->jsonError('Failed to submit survey', 500);
        } catch (Exception $e) {
            dbRollback();
            error_log('Survey API submission failed: ' . $e->getMessage());
            $this->jsonError('Failed to submit survey', 500);
        }
    }

    private function flattenPayload(array $payload): array
    {
        $basic = is_array($payload['basic_info'] ?? null) ? $payload['basic_info'] : [];
        $office = is_array($payload['office_data'] ?? null) ? $payload['office_data'] : [];
        $work = is_array($payload['work_experience'] ?? null) ? $payload['work_experience'] : [];
        $competencies = is_array($payload['competencies'] ?? null) ? $payload['competencies'] : [];
        $education = is_array($payload['education'] ?? null) ? $payload['education'] : [];
        $training = is_array($payload['dswd_training'] ?? null) ? $payload['dswd_training'] : [];
        $interest = is_array($payload['eteeap_interest'] ?? null) ? $payload['eteeap_interest'] : [];

        return [
            // Basic info
            'last_name' => $basic['last_name'] ?? null,
            'first_name' => $basic['first_name'] ?? null,
            'middle_name' => $basic['middle_name'] ?? null,
            'ext_name' => $basic['ext_name'] ?? null,
            'sex' => $basic['sex'] ?? null,
            'age_range' => $basic['age_range'] ?? null,
            'email' => $basic['email'] ?? null,
            'phone' => $basic['phone'] ?? null,

            // Office
            'office_type' => $office['office_type'] ?? null,
            'office_assignment' => $office['office_assignment'] ?? null,
            'office_bureau' => $office['office_bureau'] ?? null,
            'attached_agency' => $office['attached_agency'] ?? null,
            'field_office_unit' => $office['field_office_unit'] ?? null,
            'specific_office' => $office['specific_office'] ?? null,
            'current_position' => $office['current_position'] ?? null,
            'employment_status' => $office['employment_status'] ?? null,
            'program_assignments' => $office['program_assignments'] ?? [],

            // Work
            'years_dswd' => $work['years_dswd'] ?? null,
            'years_swd_sector' => $work['years_swd_sector'] ?? null,

            // Competencies
            'performs_sw_tasks' => $competencies['performs_sw_tasks'] ?? null,
            'sw_tasks' => $competencies['sw_tasks'] ?? [],
            'expertise_areas' => $competencies['expertise_areas'] ?? [],

            // Education
            'highest_education' => $education['highest_education'] ?? null,
            'undergrad_course' => $education['undergrad_course'] ?? null,
            'diploma_course' => $education['diploma_course'] ?? null,
            'graduate_course' => $education['graduate_course'] ?? null,

            // Training
            'availed_dswd_training' => ($training['availed_dswd_training'] ?? null) === true ? 'yes' : ((($training['availed_dswd_training'] ?? null) === false) ? 'no' : null),
            'dswd_courses' => $training['courses'] ?? [],

            // Interest
            'eteeap_awareness' => ($interest['eteeap_awareness'] ?? null) === true ? 'aware' : ((($interest['eteeap_awareness'] ?? null) === false) ? 'not_aware' : null),
            'eteeap_interest' => $interest['interest_level'] ?? null,
            'motivations' => $interest['motivations'] ?? [],
            'barriers' => $interest['barriers'] ?? [],
            'will_apply' => $interest['will_apply'] ?? null,
            'will_not_apply_reason' => $interest['will_not_apply_reason'] ?? ($payload['will_not_apply_reason'] ?? null),
            'additional_comments' => $interest['additional_comments'] ?? null,
        ];
    }

    private function insertMultiValues(int $responseId, string $table, string $column, array $values): void
    {
        if (empty($values)) {
            return;
        }

        // Basic injection hardening for dynamic identifiers.
        if (!isAllowedTable($table) || !isSafeIdentifier($column)) {
            throw new InvalidArgumentException('Unsafe target table/column.');
        }

        $sql = "INSERT INTO {$table} (response_id, {$column}) VALUES (:response_id, :value)";
        foreach ($values as $value) {
            $value = sanitizeString($value);
            if ($value !== '') {
                dbExecute($sql, [
                    'response_id' => $responseId,
                    'value' => $value,
                ]);
            }
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function jsonError(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
