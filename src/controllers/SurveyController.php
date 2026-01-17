<?php
/**
 * ETEEAP Survey Application - Survey Controller
 * 
 * Handles multi-step survey form logic.
 */

class SurveyController
{
    /**
     * Survey landing page - redirect to consent
     */
    public function index(): void
    {
        redirect(appUrl('/survey/consent'));
    }
    
    /**
     * Show a specific survey step
     * 
     * @param int|string $step
     */
    public function showStep($step = 1): void
    {
        $step = (int) $step;
        
        // Validate step number
        if ($step < 1 || $step > SURVEY_TOTAL_STEPS) {
            redirect(appUrl('/survey/consent'));
        }
        
        // Get survey session
        $survey = getSurveySession();
        
        // Check consent for steps > 1
        if ($step > 1 && !$survey['consent_given']) {
            flashSet('error', 'Please provide consent before continuing.');
            redirect(appUrl('/survey/consent'));
        }
        
        // Prevent skipping steps
        $maxAllowedStep = $survey['current_step'];
        if ($step > $maxAllowedStep) {
            redirect(appUrl('/survey/step/' . $maxAllowedStep));
        }

        // Email OTP gate: require verified email before step 3+
        if ($step > 2) {
            $emailVerified = ($survey['email_verified'] ?? false) === true;
            if (!$emailVerified) {
                redirect(appUrl('/survey/verify-email'));
            }

            $allData = getAllSurveyData();
            $email = strtolower(trim((string) ($allData['email'] ?? '')));
            $verifiedEmail = strtolower(trim((string) ($survey['verified_email'] ?? '')));
            if ($email === '' || $verifiedEmail === '' || $email !== $verifiedEmail) {
                // Email changed or missing; require re-verification.
                updateSurveySession([
                    'email_verified' => false,
                    'verified_email' => null,
                    'email_pending' => $email !== '' ? $email : null,
                ]);
                redirect(appUrl('/survey/verify-email'));
            }
        }
        
        // Get previously saved data for this step
        $savedData = getSurveyStepData($step);
        
        // Prepare view data
        $viewData = [
            'currentStep' => $step,
            'totalSteps' => SURVEY_TOTAL_STEPS,
            'stepName' => SURVEY_STEP_NAMES[$step] ?? 'Survey',
            'savedData' => $savedData,
            'errors' => flashGet('validation_errors', []),
            'csrfToken' => csrfGetToken()
        ];
        
        // Load step view
        $stepViews = [
            1 => 'consent',
            2 => 'basic-info',
            3 => 'office-data',
            4 => 'work-experience',
            5 => 'competencies',
            6 => 'education',
            7 => 'dswd-courses',
            8 => 'eteeap-interest'
        ];
        
        $viewFile = $stepViews[$step] ?? 'consent';
        $this->render('survey/' . $viewFile, $viewData);
    }
    
    /**
     * Save a survey step and proceed
     * 
     * @param int|string $step
     */
    public function saveStep($step = 1): void
    {
        $step = (int) $step;
        
        // Validate CSRF token
        csrfProtect();
        
        // SECURITY: Enforce consent for all steps beyond consent page
        // This prevents users from bypassing consent by directly POSTing to later steps
        if ($step > 1) {
            $survey = getSurveySession();
            if (empty($survey['consent_given'])) {
                flashSet('error', 'Please provide consent before continuing.');
                redirect(appUrl('/survey/consent'));
                return;
            }
        }
        
        // Get POST data
        $data = $_POST;
        
        // Get step validator
        $validator = getStepValidator($step);
        
        if ($validator === null) {
            flashSet('error', 'Invalid step.');
            redirect(appUrl('/survey/step/' . $step));
        }
        
        // Validate
        $result = $validator($data);
        
        if (!$result->isValid) {
            // Store errors and redirect back
            flashSet('validation_errors', $result->errors);
            redirect(appUrl('/survey/step/' . $step));
        }
        
        // Handle consent step specially
        if ($step === 1) {
            if (!$result->sanitized['consent_given']) {
                // Declined consent
                clearSurveySession();
                redirect(appUrl('/survey/declined'));
            }
            
            // Consent given - regenerate session for security
            sessionRegenerate();
            updateSurveySession(['consent_given' => true]);
        }
        
        // Save step data
        saveSurveyStepData($step, $result->sanitized);

        // After Step 2, require email OTP verification before proceeding.
        if ($step === 2) {
            $survey = getSurveySession();
            $email = strtolower(trim((string) ($result->sanitized['email'] ?? '')));

            if ($email !== '') {
                $verifiedEmail = strtolower(trim((string) ($survey['verified_email'] ?? '')));
                $alreadyVerified = (($survey['email_verified'] ?? false) === true) && ($verifiedEmail === $email);

                if (!$alreadyVerified) {
                    try {
                        require_once SRC_PATH . '/services/OtpService.php';
                        OtpService::sendSurveyOtp(
                            $email,
                            (string) ($survey['session_id'] ?? ''),
                            (string) (getClientIp() ?? ''),
                            (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
                        );
                        flashSet('success', 'We sent a one-time code (OTP) to your email. Please enter it to continue.');
                    } catch (Throwable $e) {
                        $msg = trim(preg_replace('/\s+/', ' ', (string) $e->getMessage()) ?? '');
                        $msg = mb_substr($msg, 0, 220, 'UTF-8');

                        $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
                        @error_log('Survey OTP send failed: ' . $msg . ' | IP=' . (string) getClientIp(), 3, $logPath);

                        $hint = APP_DEBUG && $msg !== '' ? (' Details: ' . $msg) : '';
                        flashSet('error', 'Failed to send OTP. Please try again.' . $hint);
                        redirect(appUrl('/survey/step/2'));
                        return;
                    }

                    updateSurveySession([
                        'email_verified' => false,
                        'verified_email' => null,
                        'email_pending' => $email,
                    ]);

                    redirect(appUrl('/survey/verify-email'));
                    return;
                }
            }
        }
        
        // Determine next action
        if ($step >= SURVEY_TOTAL_STEPS) {
            // Final step - submit survey
            $this->submitSurvey();
        } else {
            // Proceed to next step
            redirect(appUrl('/survey/step/' . ($step + 1)));
        }
    }
    
    /**
     * Submit the complete survey to database
     */
    private function submitSurvey(): void
    {
        $survey = getSurveySession();
        $allData = getAllSurveyData();

        // Enforce email verification before submission
        $emailVerified = ($survey['email_verified'] ?? false) === true;
        $email = strtolower(trim((string) ($allData['email'] ?? '')));
        $verifiedEmail = strtolower(trim((string) ($survey['verified_email'] ?? '')));
        if (!$emailVerified || $email === '' || $verifiedEmail === '' || $email !== $verifiedEmail) {
            flashSet('error', 'Please verify your email address before submitting the survey.');
            redirect(appUrl('/survey/verify-email'));
            return;
        }
        
        // Final duplicate check before submission (safety net for race conditions)
        $email = $allData['email'] ?? '';
        $lastName = $allData['last_name'] ?? '';
        $firstName = $allData['first_name'] ?? '';
        $middleName = $allData['middle_name'] ?? null;
        $extName = $allData['ext_name'] ?? null;
        
        // Duplicate check: only block when BOTH name and email match an existing completed response.
        if (
            !empty($email) &&
            !empty($lastName) &&
            !empty($firstName) &&
            isNameEmailAlreadyUsed($email, $lastName, $firstName, $middleName, $extName)
        ) {
            flashSet('error', 'A survey response with the same name and email already exists. Please use a different email or verify the name.');
            redirect(appUrl('/survey/step/2'));
            return;
        }
        
        try {
            dbBeginTransaction();
            
            $willApply = $allData['will_apply'] ?? null;
            $willNotApplyReason = $allData['will_not_apply_reason'] ?? null;
            if ($willApply !== 'no') {
                $willNotApplyReason = null;
            }

            // Insert main response
            $responseId = dbInsert(
                "INSERT INTO survey_responses (
                    session_id, consent_given, current_step, 
                    created_at, completed_at,
                    last_name, first_name, middle_name, ext_name, sex, age_range, email, phone,
                    office_type, office_assignment, field_office_unit, office_bureau, attached_agency, specific_office, current_position, employment_status,
                    years_dswd, years_swd_sector,
                    performs_sw_tasks,
                    highest_education, undergrad_course, diploma_course, graduate_course,
                    availed_dswd_training,
                    eteeap_awareness, eteeap_interest, will_apply, will_not_apply_reason, additional_comments
                ) VALUES (
                    :session_id, :consent_given, :current_step, 
                    :created_at, :completed_at,
                    :last_name, :first_name, :middle_name, :ext_name, :sex, :age_range, :email, :phone,
                    :office_type, :office_assignment, :field_office_unit, :office_bureau, :attached_agency, :specific_office, :current_position, :employment_status,
                    :years_dswd, :years_swd_sector,
                    :performs_sw_tasks,
                    :highest_education, :undergrad_course, :diploma_course, :graduate_course,
                    :availed_dswd_training,
                    :eteeap_awareness, :eteeap_interest, :will_apply, :will_not_apply_reason, :additional_comments
                )",
                [
                    'session_id' => $survey['session_id'],
                    'consent_given' => true,
                    'current_step' => SURVEY_TOTAL_STEPS,
                    'created_at' => date('Y-m-d H:i:s'),
                    'completed_at' => date('Y-m-d H:i:s'),
                    'last_name' => $lastName ?: null,
                    'first_name' => $firstName ?: null,
                    'middle_name' => $middleName ?: null,
                    'ext_name' => $extName ?: null,
                    'sex' => $allData['sex'] ?? null,
                    'age_range' => $allData['age_range'] ?? null,
                    'email' => $allData['email'] ?? null,
                    'phone' => $allData['phone'] ?? null,
                    'office_type' => $allData['office_type'] ?? null,
                    'office_assignment' => $allData['office_assignment'] ?? null,
                    'field_office_unit' => $allData['field_office_unit'] ?? null,
                    'office_bureau' => $allData['office_bureau'] ?? null,
                    'attached_agency' => $allData['attached_agency'] ?? null,
                    'specific_office' => $allData['specific_office'] ?? null,
                    'current_position' => $allData['current_position'] ?? null,
                    'employment_status' => $allData['employment_status'] ?? null,
                    'years_dswd' => $allData['years_dswd'] ?? null,
                    'years_swd_sector' => $allData['years_swd_sector'] ?? null,
                    'performs_sw_tasks' => isset($allData['performs_sw_tasks']) ? ($allData['performs_sw_tasks'] ? 1 : 0) : null,
                    'highest_education' => $allData['highest_education'] ?? null,
                    'undergrad_course' => $allData['undergrad_course'] ?? null,
                    'diploma_course' => $allData['diploma_course'] ?? null,
                    'graduate_course' => $allData['graduate_course'] ?? null,
                    'availed_dswd_training' => isset($allData['availed_dswd_training']) ? ($allData['availed_dswd_training'] ? 1 : 0) : null,
                    'eteeap_awareness' => isset($allData['eteeap_awareness']) ? ($allData['eteeap_awareness'] ? 1 : 0) : null,
                    'eteeap_interest' => $allData['eteeap_interest'] ?? null,
                    'will_apply' => $willApply,
                    'will_not_apply_reason' => $willNotApplyReason,
                    'additional_comments' => $allData['additional_comments'] ?? null
                ]
            );
            
            // Insert multi-value fields
            $this->insertMultiValues($responseId, 'response_program_assignments', 'program', $allData['program_assignments'] ?? []);
            $this->insertMultiValues($responseId, 'response_sw_tasks', 'task', $allData['sw_tasks'] ?? []);
            $this->insertMultiValues($responseId, 'response_expertise_areas', 'area', $allData['expertise_areas'] ?? []);
            $this->insertMultiValues($responseId, 'response_dswd_courses', 'course', $allData['dswd_courses'] ?? []);
            $this->insertMultiValues($responseId, 'response_motivations', 'motivation', $allData['motivations'] ?? []);
            $this->insertMultiValues($responseId, 'response_barriers', 'barrier', $allData['barriers'] ?? []);
            
            dbCommit();
            
            // Clear session and redirect to thank you
            clearSurveySession();
            flashSet('success', 'Thank you for completing the survey!');
            redirect(appUrl('/survey/thank-you'));
            
        } catch (PDOException $e) {
            dbRollback();
            
            // Check for duplicate key error (MySQL error code 1062)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                error_log('Duplicate survey submission attempted for email: ' . ($allData['email'] ?? 'unknown'));
                flashSet('error', 'A survey response with the same name and email already exists. Please use a different email or verify the name.');
                redirect(appUrl('/survey/step/2'));
            } else {
                $rawMessage = $e->getMessage();
                error_log('Survey submission failed: ' . $rawMessage);

                // Prefer MySQL driver error codes (PDOException::errorInfo[1]) over message matching.
                $driverCode = null;
                if (isset($e->errorInfo) && is_array($e->errorInfo)) {
                    $driverCode = $e->errorInfo[1] ?? null;
                }

                $schemaRelatedDriverCodes = [
                    1054, // Unknown column
                    1265, // Data truncated
                    1366, // Incorrect value
                    1292, // Truncated incorrect value
                    1406, // Data too long for column
                ];

                $isSchemaOutOfDate =
                    ($driverCode !== null && in_array($driverCode, $schemaRelatedDriverCodes, true)) ||
                    strpos($rawMessage, 'Unknown column') !== false ||
                    strpos($rawMessage, 'Data truncated') !== false ||
                    strpos($rawMessage, 'Incorrect') !== false;

                if ($isSchemaOutOfDate) {
                    $msg = 'System update required. Please contact the administrator.';
                    if (defined('APP_DEBUG') && APP_DEBUG) {
                        $msg = 'Database schema is out of date. Apply `database/migrations/2026-01-08_nationwide_interest_survey.sql`. Details: ' . $rawMessage;
                    }
                    flashSet('error', $msg);
                } else {
                    $msg = 'There was a problem submitting your survey. Please try again.';
                    if (defined('APP_DEBUG') && APP_DEBUG) {
                        $msg .= ' Details: ' . $rawMessage;
                    }
                    flashSet('error', $msg);
                }
                redirect(appUrl('/survey/step/' . SURVEY_TOTAL_STEPS));
            }
        } catch (Exception $e) {
            dbRollback();
            error_log('Survey submission failed: ' . $e->getMessage());
            $msg = 'There was a problem submitting your survey. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $msg .= ' Details: ' . $e->getMessage();
            }
            flashSet('error', $msg);
            redirect(appUrl('/survey/step/' . SURVEY_TOTAL_STEPS));
        }
    }

    /**
     * Show email verification (OTP) page.
     */
    public function showVerifyEmail(): void
    {
        $survey = getSurveySession();

        if (empty($survey['consent_given'])) {
            flashSet('error', 'Please provide consent before continuing.');
            redirect(appUrl('/survey/consent'));
            return;
        }

        $allData = getAllSurveyData();
        $email = strtolower(trim((string) ($allData['email'] ?? '')));
        if ($email === '') {
            flashSet('error', 'Please enter your email address first.');
            redirect(appUrl('/survey/step/2'));
            return;
        }

        $this->render('survey/verify-email', [
            'pageTitle' => 'Verify Email',
            'email' => $email,
            'errors' => flashGet('validation_errors', []),
            'csrfToken' => csrfGetToken(),
        ]);
    }

    /**
     * Verify email OTP.
     */
    public function verifyEmail(): void
    {
        csrfProtect();

        $survey = getSurveySession();
        if (empty($survey['consent_given'])) {
            flashSet('error', 'Please provide consent before continuing.');
            redirect(appUrl('/survey/consent'));
            return;
        }

        $allData = getAllSurveyData();
        $email = strtolower(trim((string) ($allData['email'] ?? '')));
        if ($email === '') {
            flashSet('error', 'Please enter your email address first.');
            redirect(appUrl('/survey/step/2'));
            return;
        }

        $otp = sanitizeString($_POST['otp'] ?? '');
        if ($otp === '') {
            flashSet('error', 'OTP is required.');
            redirect(appUrl('/survey/verify-email'));
            return;
        }

        require_once SRC_PATH . '/services/OtpService.php';
        $ok = OtpService::verifySurveyOtp($email, (string) ($survey['session_id'] ?? ''), $otp);

        if (!$ok) {
            flashSet('error', 'Invalid or expired OTP. Please try again.');
            redirect(appUrl('/survey/verify-email'));
            return;
        }

        updateSurveySession([
            'email_verified' => true,
            'verified_email' => $email,
            'email_pending' => null,
        ]);

        flashSet('success', 'Email verified. You may now continue the survey.');
        redirect(appUrl('/survey/step/3'));
    }

    /**
     * Resend survey email OTP.
     */
    public function resendEmailOtp(): void
    {
        csrfProtect();

        $survey = getSurveySession();
        if (empty($survey['consent_given'])) {
            flashSet('error', 'Please provide consent before continuing.');
            redirect(appUrl('/survey/consent'));
            return;
        }

        $allData = getAllSurveyData();
        $email = strtolower(trim((string) ($allData['email'] ?? '')));
        if ($email === '') {
            flashSet('error', 'Please enter your email address first.');
            redirect(appUrl('/survey/step/2'));
            return;
        }

        try {
            require_once SRC_PATH . '/services/OtpService.php';
            OtpService::sendSurveyOtp(
                $email,
                (string) ($survey['session_id'] ?? ''),
                (string) (getClientIp() ?? ''),
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
            );
            flashSet('success', 'OTP sent. Please check your email.');
        } catch (Throwable $e) {
            $msg = trim(preg_replace('/\s+/', ' ', (string) $e->getMessage()) ?? '');
            $msg = mb_substr($msg, 0, 220, 'UTF-8');

            $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
            @error_log('Survey OTP resend failed: ' . $msg . ' | IP=' . (string) getClientIp(), 3, $logPath);

            $hint = APP_DEBUG && $msg !== '' ? (' Details: ' . $msg) : '';
            flashSet('error', 'Failed to resend OTP.' . $hint);
        }

        redirect(appUrl('/survey/verify-email'));
    }
    
    /**
     * Insert multi-value field data
     * 
     * @param int $responseId
     * @param string $table
     * @param string $column
     * @param array $values
     */
    private function insertMultiValues(int $responseId, string $table, string $column, array $values): void
    {
        if (empty($values)) {
            return;
        }
        
        $sql = "INSERT INTO {$table} (response_id, {$column}) VALUES (:response_id, :value)";
        
        foreach ($values as $value) {
            if (!empty($value)) {
                dbExecute($sql, [
                    'response_id' => $responseId,
                    'value' => $value
                ]);
            }
        }
    }
    
    /**
     * Thank you page (successful submission)
     */
    public function thankYou(): void
    {
        $this->render('survey/thank-you', [
            'pageTitle' => 'Thank You',
            'type' => 'success'
        ]);
    }
    
    /**
     * Declined consent page
     */
    public function declined(): void
    {
        $this->render('survey/thank-you', [
            'pageTitle' => 'Survey Declined',
            'type' => 'declined'
        ]);
    }
    
    /**
     * Render a view with layout
     * 
     * @param string $view
     * @param array $data
     */
    private function render(string $view, array $data = []): void
    {
        // Extract data to variables
        extract($data);
        
        // Set layout defaults
        $pageTitle = $pageTitle ?? ($stepName ?? 'Survey');
        $currentStep = $currentStep ?? null;
        $totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
        
        // Start output buffering
        ob_start();
        
        // Include view
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="p-8 text-red-600">View not found: ' . htmlspecialchars($view) . '</div>';
        }
        
        // Get content
        $content = ob_get_clean();
        
        // Include layout
        include VIEWS_PATH . '/layouts/app.php';
    }
}
