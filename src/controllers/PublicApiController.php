<?php
/**
 * ETEEAP Survey Application - Public API Controller
 * 
 * Provides JSON endpoints for survey form data (no authentication required).
 */

class PublicApiController
{
    private array $positionsCache = [];
    private array $coursesCache = [];

    public function __construct()
    {
        // Set JSON content type
        header('Content-Type: application/json; charset=utf-8');
    }
    
    /**
     * Positions endpoint - Returns government positions from CSV
     * GET /api/positions?q=search_term&limit=50
     */
    public function positions(): void
    {
        try {
            $searchTerm = trim($_GET['q'] ?? '');
            $limit = min((int)($_GET['limit'] ?? 50), 100);
            
            // Load positions from CSV
            $positions = $this->loadPositions();
            
            // Filter by search term if provided
            if ($searchTerm !== '') {
                $searchLower = strtolower($searchTerm);
                $positions = array_filter($positions, function($pos) use ($searchLower) {
                    return strpos(strtolower($pos), $searchLower) !== false;
                });
            }
            
            // Limit results
            $positions = array_slice(array_values($positions), 0, $limit);
            
            // Format for Tom Select
            $results = array_map(function($pos) {
                return ['value' => $pos, 'text' => $pos];
            }, $positions);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch positions', 500);
        }
    }
    
    /**
     * Courses endpoint - Returns DSWD Academy courses from CSV
     * GET /api/courses?q=search_term
     */
    public function courses(): void
    {
        try {
            $searchTerm = trim($_GET['q'] ?? '');
            
            // Load courses from CSV
            $courses = $this->loadCourses();
            
            // Filter by search term if provided
            if ($searchTerm !== '') {
                $searchLower = strtolower($searchTerm);
                $courses = array_filter($courses, function($course) use ($searchLower) {
                    return strpos(strtolower($course), $searchLower) !== false;
                });
            }
            
            // Format for multi-select
            $results = array_map(function($course) {
                return ['value' => $course, 'text' => $course];
            }, array_values($courses));
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch courses', 500);
        }
    }
    
    /**
     * Load positions from CSV file
     */
    private function loadPositions(): array
    {
        if (!empty($this->positionsCache)) {
            return $this->positionsCache;
        }
        
        $csvPath = APP_ROOT . '/docs/update/positions.csv';
        
        if (!file_exists($csvPath)) {
            return [];
        }
        
        $positions = [];
        $handle = fopen($csvPath, 'r');
        
        if ($handle !== false) {
            while (($line = fgets($handle)) !== false) {
                $position = trim($line);
                if ($position !== '') {
                    // Fix known typo
                    $position = str_replace('Administrative Assistat', 'Administrative Assistant', $position);
                    $positions[] = $position;
                }
            }
            fclose($handle);
        }
        
        // Remove duplicates and sort
        $positions = array_unique($positions);
        sort($positions, SORT_STRING | SORT_FLAG_CASE);
        
        $this->positionsCache = $positions;
        return $positions;
    }
    
    /**
     * Load courses from CSV file
     */
    private function loadCourses(): array
    {
        if (!empty($this->coursesCache)) {
            return $this->coursesCache;
        }
        
        $csvPath = APP_ROOT . '/docs/update/academy_course.csv';
        
        if (!file_exists($csvPath)) {
            return [];
        }
        
        $courses = [];
        $handle = fopen($csvPath, 'r');
        
        if ($handle !== false) {
            while (($line = fgets($handle)) !== false) {
                $course = trim($line);
                if ($course !== '') {
                    $courses[] = $course;
                }
            }
            fclose($handle);
        }
        
        sort($courses, SORT_STRING | SORT_FLAG_CASE);
        
        $this->coursesCache = $courses;
        return $courses;
    }

    /**
     * OBS (Office/Bureau/Service) endpoint
     * GET /api/obs?q=search_term
     */
    public function obs(): void
    {
        try {
            $searchTerm = trim($_GET['q'] ?? '');
            $obsList = $this->loadCsvData(APP_ROOT . '/docs/update/obs.csv');
            
            if ($searchTerm !== '') {
                $searchLower = strtolower($searchTerm);
                $obsList = array_filter($obsList, function($item) use ($searchLower) {
                    return strpos(strtolower($item), $searchLower) !== false;
                });
            }
            
            $results = array_map(function($item) {
                return ['value' => $item, 'text' => $item];
            }, array_values($obsList));
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch OBS data', 500);
        }
    }

    /**
     * Attached Agencies endpoint
     * GET /api/attached-agencies?q=search_term
     */
    public function attachedAgencies(): void
    {
        try {
            $searchTerm = trim($_GET['q'] ?? '');
            $agencies = $this->loadCsvData(APP_ROOT . '/docs/update/attached_agency.csv');
            
            if ($searchTerm !== '') {
                $searchLower = strtolower($searchTerm);
                $agencies = array_filter($agencies, function($item) use ($searchLower) {
                    return strpos(strtolower($item), $searchLower) !== false;
                });
            }
            
            $results = array_map(function($item) {
                return ['value' => $item, 'text' => $item];
            }, array_values($agencies));
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch agencies', 500);
        }
    }

    /**
     * CHED Programs endpoint
     * GET /api/ched-programs?q=search_term
     */
    public function chedPrograms(): void
    {
        try {
            $searchTerm = trim($_GET['q'] ?? '');
            $programs = $this->loadCsvData(APP_ROOT . '/docs/update/ched_list.csv');
            
            if ($searchTerm !== '') {
                $searchLower = strtolower($searchTerm);
                $programs = array_filter($programs, function($item) use ($searchLower) {
                    return strpos(strtolower($item), $searchLower) !== false;
                });
            }
            
            $results = array_map(function($item) {
                return ['value' => $item, 'text' => $item];
            }, array_values($programs));
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
        } catch (Exception $e) {
            $this->jsonError('Failed to fetch programs', 500);
        }
    }

    /**
     * Helper to load simple list from CSV
     */
    private function loadCsvData(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $data = [];
        $handle = fopen($path, 'r');
        if ($handle !== false) {
            while (($line = fgets($handle)) !== false) {
                $item = trim($line);
                if ($item !== '') {
                    $data[] = $item;
                }
            }
            fclose($handle);
        }
        
        $data = array_unique($data);
        sort($data, SORT_STRING | SORT_FLAG_CASE);
        return $data;
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
