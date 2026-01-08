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
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = PAGINATION_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = dbFetchOne(
            "SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL"
        )['count'];
        
        // Get filtered responses
        $responses = dbFetchAll(
            "SELECT id, last_name, first_name, middle_name, ext_name, email, sex, age_range, office_type, employment_status, 
                    eteeap_interest, will_apply, created_at 
             FROM survey_responses 
             WHERE consent_given = 1 AND completed_at IS NOT NULL
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );
        
        $totalPages = ceil($total / $perPage);
        
        $this->render('admin/responses', [
            'pageTitle' => 'Survey Responses',
            'currentPage' => 'responses',
            'responses' => $responses,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
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
        
        $multiValues['program_assignments'] = dbFetchAll(
            "SELECT program FROM response_program_assignments WHERE response_id = :id",
            ['id' => $id]
        );
        
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
        
        // Get multi-value data for each response
        foreach ($responses as &$response) {
            $id = $response['id'];
            
            $response['program_assignments'] = array_column(
                dbFetchAll("SELECT program FROM response_program_assignments WHERE response_id = :id", ['id' => $id]),
                'program'
            );
            
            $response['sw_tasks'] = array_column(
                dbFetchAll("SELECT task FROM response_sw_tasks WHERE response_id = :id", ['id' => $id]),
                'task'
            );
            
            $response['expertise_areas'] = array_column(
                dbFetchAll("SELECT area FROM response_expertise_areas WHERE response_id = :id", ['id' => $id]),
                'area'
            );
            
            $response['dswd_courses'] = array_column(
                dbFetchAll("SELECT course FROM response_dswd_courses WHERE response_id = :id", ['id' => $id]),
                'course'
            );
            
            $response['motivations'] = array_column(
                dbFetchAll("SELECT motivation FROM response_motivations WHERE response_id = :id", ['id' => $id]),
                'motivation'
            );
            
            $response['barriers'] = array_column(
                dbFetchAll("SELECT barrier FROM response_barriers WHERE response_id = :id", ['id' => $id]),
                'barrier'
            );
        }
        
        // Use export helper
        exportSurveyToCsv($responses, 'eteeap_survey_export_' . date('Y-m-d_His'));
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
