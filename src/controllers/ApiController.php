<?php
/**
 * ETEEAP Survey Application - API Controller
 * 
 * Provides JSON endpoints for dashboard charts and statistics.
 * All endpoints require admin authentication.
 */

class ApiController
{
    public function __construct()
    {
        // Require authentication for all API routes
        AuthController::requireAuth();
        
        // Set JSON content type
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Summary endpoint - Overall statistics used by dashboards
     * GET /api/stats/summary
     */
    public function summary(): void
    {
        try {
            // Raw counts for rates
            $totalSessions = (int) (dbFetchOne("SELECT COUNT(*) as count FROM survey_responses")['count'] ?? 0);
            $consentGiven = (int) (dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1")['count'] ?? 0);
            $completed = (int) (dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL")['count'] ?? 0);

            $denomSessions = max($totalSessions, 1);
            $denomConsent = max($consentGiven, 1);

            $data = [
                'total_responses' => $completed,
                'consent_rate' => ($consentGiven / $denomSessions) * 100,
                'completion_rate' => ($completed / $denomConsent) * 100,
                'week_responses' => (int) (dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses
                     WHERE consent_given = 1 AND completed_at IS NOT NULL
                       AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                )['count'] ?? 0),
                'very_interested' => (int) (dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses
                     WHERE consent_given = 1 AND completed_at IS NOT NULL AND eteeap_interest = 'very_interested'"
                )['count'] ?? 0),
                'will_apply' => (int) (dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses
                     WHERE consent_given = 1 AND completed_at IS NOT NULL AND will_apply = 'yes'"
                )['count'] ?? 0),
            ];

            $this->jsonResponse(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch summary data', 500);
        }
    }
    
    /**
     * Demographics endpoint - Age, Sex, Office Type distribution
     * GET /api/stats/demographics
     */
    public function demographics(): void
    {
        try {
            $data = [];
            
            // Age distribution
            $data['age_distribution'] = dbFetchAll(
                "SELECT age_range as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND age_range IS NOT NULL
                 GROUP BY age_range 
                 ORDER BY FIELD(age_range, '20-29', '30-39', '40-49', '50-59', '60+')"
            );
            
            // Sex distribution
            $data['sex_distribution'] = dbFetchAll(
                "SELECT sex as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND sex IS NOT NULL
                 GROUP BY sex"
            );
            
            // Office type distribution
            $data['office_type_distribution'] = dbFetchAll(
                "SELECT office_type as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND office_type IS NOT NULL
                 GROUP BY office_type"
            );
            
            // Employment status distribution
            $data['employment_status_distribution'] = dbFetchAll(
                "SELECT employment_status as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND employment_status IS NOT NULL
                 GROUP BY employment_status"
            );
            
            // Years in DSWD distribution
            $data['years_dswd_distribution'] = dbFetchAll(
                "SELECT years_dswd as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND years_dswd IS NOT NULL
                 GROUP BY years_dswd 
                 ORDER BY FIELD(years_dswd, 'lt5', '5-10', '11-15', '15+')"
            );
            
            // Education level distribution
            $data['education_distribution'] = dbFetchAll(
                "SELECT highest_education as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND highest_education IS NOT NULL
                 GROUP BY highest_education 
                 ORDER BY FIELD(highest_education, 'high_school', 'some_college', 'bachelors', 'masters', 'doctoral')"
            );
            
            $this->jsonResponse(['success' => true, 'data' => $data]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch demographics data', 500);
        }
    }
    
    /**
     * Interest stats endpoint - ETEEAP interest levels and intentions
     * GET /api/stats/interest
     */
    public function interest(): void
    {
        try {
            $data = [];
            
            // Interest level distribution
            $data['interest_levels'] = dbFetchAll(
                "SELECT eteeap_interest as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND eteeap_interest IS NOT NULL
                 GROUP BY eteeap_interest 
                 ORDER BY FIELD(eteeap_interest, 'very_interested', 'interested', 'somewhat_interested', 'not_interested')"
            );
            
            // Will apply distribution
            $data['will_apply'] = dbFetchAll(
                "SELECT will_apply as label, COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL AND will_apply IS NOT NULL
                 GROUP BY will_apply 
                 ORDER BY FIELD(will_apply, 'yes', 'no')"
            );
            
            // ETEEAP awareness
            $data['eteeap_awareness'] = dbFetchAll(
                "SELECT 
                    CASE WHEN eteeap_awareness = 1 THEN 'aware' ELSE 'not_aware' END as label, 
                    COUNT(*) as value 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL
                 GROUP BY eteeap_awareness"
            );
            
            // Top motivations (from response_motivations table)
            $data['top_motivations'] = dbFetchAll(
                "SELECT motivation as label, COUNT(*) as value 
                 FROM response_motivations rm
                 JOIN survey_responses sr ON rm.response_id = sr.id
                 WHERE sr.consent_given = 1 AND sr.completed_at IS NOT NULL
                 GROUP BY motivation 
                 ORDER BY value DESC 
                 LIMIT 10"
            );
            
            // Top barriers (from response_barriers table)
            $data['top_barriers'] = dbFetchAll(
                "SELECT barrier as label, COUNT(*) as value 
                 FROM response_barriers rb
                 JOIN survey_responses sr ON rb.response_id = sr.id
                 WHERE sr.consent_given = 1 AND sr.completed_at IS NOT NULL
                 GROUP BY barrier 
                 ORDER BY value DESC 
                 LIMIT 10"
            );
            
            // Summary stats
            $data['summary'] = [
                'total_responses' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL"
                )['count'],
                'very_interested_count' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL AND eteeap_interest = 'very_interested'"
                )['count'],
                'will_apply_yes_count' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL AND will_apply = 'yes'"
                )['count'],
                'aware_of_eteeap_count' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1 AND completed_at IS NOT NULL AND eteeap_awareness = 1"
                )['count']
            ];
            
            $this->jsonResponse(['success' => true, 'data' => $data]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch interest data', 500);
        }
    }
    
    /**
     * Timeline endpoint - Response trends over time
     * GET /api/stats/timeline
     */
    public function timeline(): void
    {
        try {
            $data = [];
            
            // Daily responses for the last 30 days
            $data['daily'] = dbFetchAll(
                "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL 
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) 
                 ORDER BY date ASC"
            );
            
            // Weekly responses for the last 12 weeks
            $data['weekly'] = dbFetchAll(
                "SELECT YEARWEEK(created_at, 1) as week, COUNT(*) as count 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL 
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
                 GROUP BY YEARWEEK(created_at, 1) 
                 ORDER BY week ASC"
            );
            
            // Monthly responses (all time)
            $data['monthly'] = dbFetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                 FROM survey_responses 
                 WHERE consent_given = 1 AND completed_at IS NOT NULL
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                 ORDER BY month ASC"
            );
            
            // Current period stats
            $data['current_stats'] = [
                'today' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses 
                     WHERE consent_given = 1 AND completed_at IS NOT NULL AND DATE(created_at) = CURDATE()"
                )['count'],
                'this_week' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses 
                     WHERE consent_given = 1 AND completed_at IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                )['count'],
                'this_month' => dbFetchOne(
                    "SELECT COUNT(*) as count FROM survey_responses 
                     WHERE consent_given = 1 AND completed_at IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                )['count']
            ];
            
            $this->jsonResponse(['success' => true, 'data' => $data]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch timeline data', 500);
        }
    }
    
    /**
     * Send JSON success response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send JSON error response
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
