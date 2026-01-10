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
        
        // Remove duplicates and sort
        $courses = array_unique($courses);
        sort($courses, SORT_STRING | SORT_FLAG_CASE);
        
        $this->coursesCache = $courses;
        return $courses;
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
