<?php
/**
 * ETEEAP Survey Application - Admin Controller
 * 
 * Handles admin dashboard and response management.
 */

class AdminController
{
    public function __construct()
    {
        // Require authentication for all admin routes
        AuthController::requireAuth();
    }
    
    /**
     * Dashboard with statistics and charts
     */
    public function dashboard(): void
    {
        // Get total counts
        $stats = $this->getDashboardStats();
        
        // Get recent responses
        $recentResponses = dbFetchAll(
            "SELECT id, last_name, first_name, middle_name, ext_name, email, office_type, eteeap_interest, created_at 
             FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             ORDER BY created_at DESC 
             LIMIT 5"
        );
        
        $this->render('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'recentResponses' => $recentResponses,
            'adminUser' => sessionGet('admin_user')
        ]);
    }
    
    /**
     * List all responses with pagination
     */
    public function responses(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $allowedPerPage = [10, 20, 50, 100];
        $perPage = (int) ($_GET['per_page'] ?? PAGINATION_PER_PAGE);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = PAGINATION_PER_PAGE;
        }

        $offset = ($page - 1) * $perPage;

        $q = trim((string) ($_GET['q'] ?? ''));
        $officeType = trim((string) ($_GET['office_type'] ?? ''));
        $employmentStatus = trim((string) ($_GET['employment_status'] ?? ''));
        $sort = trim((string) ($_GET['sort'] ?? 'newest'));
        if (!in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }
        $orderBy = ($sort === 'oldest')
            ? 'created_at ASC, id ASC'
            : 'created_at DESC, id DESC';

        $where = [];
        $params = [];

        // Always show only completed responses
        $where[] = 'consent_given = 1';
        $where[] = 'completed_at IS NOT NULL';

        if ($officeType !== '') {
            $where[] = 'office_type = :office_type';
            $params['office_type'] = $officeType;
        }

        if ($employmentStatus !== '') {
            $where[] = 'employment_status = :employment_status';
            $params['employment_status'] = $employmentStatus;
        }

        if ($q !== '') {
            // If the user searches an ID (e.g. "123" or "#123"), prioritize exact match.
            $qNorm = ltrim($q, "# \t\r\n");
            if ($qNorm !== '' && ctype_digit($qNorm)) {
                $where[] = 'id = :id';
                $params['id'] = (int) $qNorm;
            } else {
                $where[] = '('
                    // MySQL PDO with emulated prepares disabled does not support reusing the same named
                    // parameter multiple times. Use distinct placeholders.
                    . 'LOWER(TRIM(last_name)) LIKE :q_last'
                    . ' OR LOWER(TRIM(first_name)) LIKE :q_first'
                    . ' OR LOWER(TRIM(middle_name)) LIKE :q_middle'
                    . ' OR LOWER(TRIM(ext_name)) LIKE :q_ext'
                    . ' OR LOWER(TRIM(email)) LIKE :q_email'
                    . ')';

                $needle = '%' . strtolower($q) . '%';
                $params['q_last'] = $needle;
                $params['q_first'] = $needle;
                $params['q_middle'] = $needle;
                $params['q_ext'] = $needle;
                $params['q_email'] = $needle;
            }
        }

        $whereSql = implode(' AND ', $where);

        // Get total count for pagination (respect filters)
        $totalRow = dbFetchOne(
            "SELECT COUNT(*) as count FROM survey_responses WHERE {$whereSql}",
            $params
        );
        $total = (int) ($totalRow['count'] ?? 0);

        $totalPages = (int) ceil(max($total, 1) / $perPage);
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        // Fetch paginated rows
        $responses = dbFetchAll(
            "SELECT id, last_name, first_name, middle_name, ext_name, email, sex, age_range, office_type, employment_status,
                    eteeap_interest, will_apply, created_at
             FROM survey_responses
             WHERE {$whereSql}
             ORDER BY {$orderBy}
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
        
        $this->render('admin/responses', [
            'pageTitle' => 'Survey Responses',
            'currentPage' => 'responses',
            'responses' => $responses,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'allowedPerPage' => $allowedPerPage,
            'filters' => [
                'q' => $q,
                'office_type' => $officeType,
                'employment_status' => $employmentStatus,
                'sort' => $sort,
            ],
            'adminUser' => sessionGet('admin_user')
        ]);
    }
    
    /**
     * View single response details
     * 
     * @param int|string $id
     */
    public function viewResponse($id): void
    {
        $id = (int) $id;
        
        // Get main response
        $response = dbFetchOne(
            "SELECT * FROM survey_responses WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$response) {
            flashSet('error', 'Response not found.');
            redirect(appUrl('/admin/responses'));
        }
        
        // Get multi-value data
        $multiValues = [];
        
        $multiValues['sw_tasks'] = dbFetchAll(
            "SELECT task FROM response_sw_tasks WHERE response_id = :id",
            ['id' => $id]
        );
        
        $multiValues['expertise_areas'] = dbFetchAll(
            "SELECT area FROM response_expertise_areas WHERE response_id = :id",
            ['id' => $id]
        );
        
        $multiValues['dswd_courses'] = dbFetchAll(
            "SELECT course FROM response_dswd_courses WHERE response_id = :id",
            ['id' => $id]
        );
        
        $multiValues['motivations'] = dbFetchAll(
            "SELECT motivation FROM response_motivations WHERE response_id = :id",
            ['id' => $id]
        );
        
        $multiValues['barriers'] = dbFetchAll(
            "SELECT barrier FROM response_barriers WHERE response_id = :id",
            ['id' => $id]
        );
        
        $this->render('admin/response-detail', [
            'pageTitle' => 'Response #' . $id,
            'currentPage' => 'responses',
            'response' => $response,
            'multiValues' => $multiValues,
            'adminUser' => sessionGet('admin_user')
        ]);
    }
    
    /**
     * Export responses to CSV
     */
    public function exportCsv(): void
    {
        // Get all completed responses
        $responses = dbFetchAll(
            "SELECT * FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             ORDER BY created_at DESC"
        );

        // Attach multi-value fields in bulk (avoid N+1 queries)
        $ids = array_values(array_filter(
            array_map(static fn($r) => (int) ($r['id'] ?? 0), $responses),
            static fn(int $id) => $id > 0
        ));
        $multi = $this->fetchMultiValuesByResponseIds($ids);

        foreach ($responses as &$response) {
            $id = (int) ($response['id'] ?? 0);
            $response['sw_tasks'] = $multi['sw_tasks'][$id] ?? [];
            $response['expertise_areas'] = $multi['expertise_areas'][$id] ?? [];
            $response['dswd_courses'] = $multi['dswd_courses'][$id] ?? [];
            $response['motivations'] = $multi['motivations'][$id] ?? [];
            $response['barriers'] = $multi['barriers'][$id] ?? [];
        }
        
        // Use export helper
        exportSurveyToCsv($responses, 'eteeap_survey_export_' . date('Y-m-d_His'));
    }

    /**
     * CSV import page (admin).
     */
    public function importCsvForm(): void
    {
        $this->render('admin/import', [
            'pageTitle' => 'Import CSV',
            'currentPage' => 'import',
            'adminUser' => sessionGet('admin_user'),
        ]);
    }

    /**
     * Handle CSV import upload (admin).
     */
    public function importCsvUpload(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(appUrl('/admin/import/csv'));
        }

        csrfProtect();

        require_once SRC_PATH . '/services/CsvImportService.php';

        $strictHeaders = filter_var($_POST['strict_headers'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $strictHeaders = $strictHeaders ?? true;
        $atomic = filter_var($_POST['atomic'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $atomic = $atomic ?? true;

        $file = $_FILES['csv_file'] ?? [];
        $result = CsvImportService::importFromUploadedFile($file, [
            'strict_headers' => $strictHeaders,
            'atomic' => $atomic,
        ]);

        if (($result['success'] ?? false) === true) {
            flashSet('success', 'Imported ' . (int) ($result['inserted'] ?? 0) . ' row(s).');
        } else {
            $rolledBack = ($result['atomic'] ?? false) && ($result['rolled_back'] ?? false);
            $msg = $rolledBack
                ? 'Import failed (atomic import rolled back).'
                : 'Import finished with errors.';
            flashSet('error', $msg);
        }

        $this->render('admin/import', [
            'pageTitle' => 'Import CSV',
            'currentPage' => 'import',
            'adminUser' => sessionGet('admin_user'),
            'importResult' => $result,
            'importOptions' => [
                'strict_headers' => $strictHeaders,
                'atomic' => $atomic,
            ],
        ]);
    }

    /**
     * Download the CSV import template.
     */
    public function exportImportTemplate(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(appUrl('/admin/import/csv'));
        }

        csrfProtect();
        require_once SRC_PATH . '/services/CsvImportService.php';
        CsvImportService::exportTemplateCsv();
    }

    /**
     * Reports dashboard page
     */
    public function reports(): void
    {
        require_once SRC_PATH . '/services/ReportGenerator.php';

        $this->render('admin/reports', [
            'pageTitle' => 'Reports Dashboard',
            'currentPage' => 'reports',
            'adminUser' => sessionGet('admin_user'),
        ]);
    }

    /**
     * Generate a specific report (AJAX)
     */
    public function generateReport(): void
    {
        // SECURITY: Validate CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrfProtect();
        }
        
        header('Content-Type: application/json; charset=utf-8');

        $type = $_POST['type'] ?? $_GET['type'] ?? '';

        $filters = [];
        $filterKeys = ['sex', 'age_range', 'office_type', 'employment_status', 'highest_education'];
        foreach ($filterKeys as $key) {
            $value = $_POST[$key] ?? $_GET[$key] ?? '';
            if (is_string($value) && $value !== '') {
                $filters[$key] = $value;
            }
        }

        if ($type === '') {
            echo json_encode(['success' => false, 'error' => 'Report type is required']);
            return;
        }

        try {
            require_once SRC_PATH . '/services/ReportGenerator.php';
            $generator = new ReportGenerator();
            $data = $generator->generate($type, $filters);

            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Export report as CSV
     *
     * @param string $type Report type
     */
    public function exportReport(string $type): void
    {
        $filters = [];
        $filterKeys = ['sex', 'age_range', 'office_type', 'employment_status', 'highest_education'];
        foreach ($filterKeys as $key) {
            $value = $_GET[$key] ?? '';
            if (is_string($value) && $value !== '') {
                $filters[$key] = $value;
            }
        }

        try {
            require_once SRC_PATH . '/services/ReportGenerator.php';
            $generator = new ReportGenerator();
            $generator->exportCsv($type, $filters);
        } catch (Throwable $e) {
            flashSet('error', 'Export failed: ' . $e->getMessage());
            redirect(appUrl('/admin/reports'));
        }
    }

    /**
     * Fetch checkbox/multi-value fields for a set of survey response IDs in bulk.
     *
     * @param int[] $ids
     * @return array<string, array<int, array>>
     */
    private function fetchMultiValuesByResponseIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id) => $id > 0)));
        if (empty($ids)) {
            return $this->emptyMultiValues();
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }
        if (empty($params) || count($placeholders) !== count($params) || count($params) !== count($ids)) {
            return $this->emptyMultiValues();
        }
        $in = implode(',', $placeholders);

        $grouped = static function (array $rows, string $valueKey): array {
            $out = [];
            foreach ($rows as $row) {
                $rid = (int) ($row['response_id'] ?? 0);
                if ($rid <= 0) {
                    continue;
                }
                $out[$rid][] = $row[$valueKey] ?? '';
            }
            return $out;
        };

        return [
            'sw_tasks' => $grouped(
                dbFetchAll("SELECT response_id, task FROM response_sw_tasks WHERE response_id IN ($in)", $params),
                'task'
            ),
            'expertise_areas' => $grouped(
                dbFetchAll("SELECT response_id, area FROM response_expertise_areas WHERE response_id IN ($in)", $params),
                'area'
            ),
            'dswd_courses' => $grouped(
                dbFetchAll("SELECT response_id, course FROM response_dswd_courses WHERE response_id IN ($in)", $params),
                'course'
            ),
            'motivations' => $grouped(
                dbFetchAll("SELECT response_id, motivation FROM response_motivations WHERE response_id IN ($in)", $params),
                'motivation'
            ),
            'barriers' => $grouped(
                dbFetchAll("SELECT response_id, barrier FROM response_barriers WHERE response_id IN ($in)", $params),
                'barrier'
            ),
        ];
    }

    /**
     * @return array<string, array<int, array>>
     */
    private function emptyMultiValues(): array
    {
        return [
            'sw_tasks' => [],
            'expertise_areas' => [],
            'dswd_courses' => [],
            'motivations' => [],
            'barriers' => [],
        ];
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    private function getDashboardStats(): array
    {
        $stats = [];
        
        // Raw counts for rates
        $totalSessions = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses")['count'] ?: 1;
        $consentGiven = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1")['count'];
        $completed = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL")['count'];
        
        // Rates
        $stats['consent_rate'] = ($consentGiven / $totalSessions) * 100;
        $stats['completion_rate'] = $consentGiven > 0 ? ($completed / $consentGiven) * 100 : 0;
        
        // Total completed responses
        $stats['total_responses'] = $completed;
        
        // This week responses
        $stats['week_responses'] = dbFetchOne(
            "SELECT COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )['count'];
        
        // Interest breakdown
        $stats['very_interested'] = dbFetchOne(
            "SELECT COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL AND eteeap_interest = 'very_interested'"
        )['count'];
        
        $stats['will_apply'] = dbFetchOne(
            "SELECT COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL AND will_apply = 'yes'"
        )['count'];
        
        // Office type breakdown
        $stats['by_office'] = dbFetchAll(
            "SELECT office_type, COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             GROUP BY office_type"
        );
        
        // Age range breakdown
        $stats['by_age'] = dbFetchAll(
            "SELECT age_range, COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             GROUP BY age_range ORDER BY FIELD(age_range, '20-29', '30-39', '40-49', '50-59', '60+')"
        );
        
        // Interest level breakdown
        $stats['by_interest'] = dbFetchAll(
            "SELECT eteeap_interest, COUNT(*) as count FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             GROUP BY eteeap_interest"
        );
        
        return $stats;
    }
    
    /**
     * Render a view with admin layout
     * 
     * @param string $view
     * @param array $data
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        
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
        
        // Include admin layout
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
