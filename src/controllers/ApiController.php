<?php

class ApiController extends AuthController
{
    public function __construct()
    {
        // Require authentication for these endpoints
        self::requireAuth();
    }

    /**
     * Get CHED programs list for Tom Select
     */
    public function chedPrograms()
    {
        $this->serveCsvData(APP_ROOT . '/docs/update/ched_list.csv');
    }

    /**
     * Get OBS (Office/Bureau/Service) list for Tom Select
     */
    public function obs()
    {
        $this->serveCsvData(APP_ROOT . '/docs/update/obs.csv');
    }

    /**
     * Get Attached Agencies list for Tom Select
     */
    public function attachedAgencies()
    {
        $this->serveCsvData(APP_ROOT . '/docs/update/attached_agency.csv');
    }

    /**
     * Get Positions list for Tom Select (existing functionality replacement or new)
     * Note: If positions.csv exists, we serve it.
     */
    public function positions()
    {
        $this->serveCsvData(APP_ROOT . '/docs/update/positions.csv');
    }

    /**
     * Dashboard Summary Stats (API Version)
     */
    public function summary()
    {
        try {
            // Total Responses
            $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE completed_at IS NOT NULL")['count'];
            
            // Consent Rate
            $sessions = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses")['count'] ?: 1;
            $consented = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE consent_given = 1")['count'];
            $consentRate = ($consented / $sessions) * 100;

            // Completion Rate
            $completionRate = ($total / ($consented ?: 1)) * 100;

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_responses' => $total,
                    'consent_rate' => round($consentRate, 1),
                    'completion_rate' => round($completionRate, 1)
                ]
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Demographics Charts Data
     */
    public function demographics()
    {
        try {
            // Sex Distribution
            $sex = dbFetchAll("
                SELECT sex as label, COUNT(*) as value 
                FROM survey_responses 
                WHERE completed_at IS NOT NULL 
                GROUP BY sex
            ");

            // Office Type
            $office = dbFetchAll("
                SELECT office_type as label, COUNT(*) as value 
                FROM survey_responses 
                WHERE completed_at IS NOT NULL 
                GROUP BY office_type
            ");

            // Age Distribution
            $age = dbFetchAll("
                SELECT age_range as label, COUNT(*) as value 
                FROM survey_responses 
                WHERE completed_at IS NOT NULL 
                GROUP BY age_range
                ORDER BY FIELD(age_range, '20-29', '30-39', '40-49', '50-59', '60+')
            ");

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'sex_distribution' => $sex,
                    'office_type_distribution' => $office,
                    'age_distribution' => $age
                ]
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Interest & Motivations Data
     */
    public function interest()
    {
        try {
            // Interest Levels
            $interest = dbFetchAll("
                SELECT eteeap_interest as label, COUNT(*) as value 
                FROM survey_responses 
                WHERE completed_at IS NOT NULL 
                GROUP BY eteeap_interest
            ");

            // Top Motivations (from junction/child table)
            // Adjust table name if strictly normalized, but usually 'response_motivations'
            $motivations = dbFetchAll("
                SELECT motivation as label, COUNT(*) as value 
                FROM response_motivations 
                GROUP BY motivation 
                ORDER BY value DESC 
                LIMIT 5
            ");

            // Top Barriers
            $barriers = dbFetchAll("
                SELECT barrier as label, COUNT(*) as value 
                FROM response_barriers 
                GROUP BY barrier 
                ORDER BY value DESC 
                LIMIT 5
            ");

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'interest_levels' => $interest,
                    'top_motivations' => $motivations,
                    'top_barriers' => $barriers
                ]
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Timeline Data (Last 30 Days)
     */
    public function timeline()
    {
        try {
            $timeline = dbFetchAll("
                SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM survey_responses 
                WHERE completed_at IS NOT NULL 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");

            $this->jsonResponse([
                'success' => true,
                'data' => $timeline
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Helper to output JSON
     */
    private function jsonResponse($data)
    {
        ob_clean(); // Clear any previous output (warnings, notices)
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Helper to read CSV and output JSON
     * Assumes CSV has no header or single column of data.
     * If multiple columns, it takes the first one as value and label.
     */
    private function serveCsvData($path)
    {
        header('Content-Type: application/json');

        if (!file_exists($path)) {
            echo json_encode([]);
            return;
        }

        $data = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Skip empty rows
                if (empty($row) || empty($row[0])) {
                    continue;
                }
                
                $value = trim($row[0]);
                // Basic cleaning
                if ($value === '') continue;

                $data[] = [
                    'value' => $value,
                    'text' => $value
                ];
            }
            fclose($handle);
        }

        echo json_encode($data);
        exit;
    }
}
