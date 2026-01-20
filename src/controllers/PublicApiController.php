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
    private static bool $psgcSeedAttempted = false;

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
     * PSGC Regions endpoint
     * GET /api/psgc/regions
     */
    public function psgcRegions(): void
    {
        try {
            $this->ensurePsgcSeededIfEnabled();

            $rows = dbFetchAll(
                "SELECT DISTINCT region_code, region_name
                 FROM ref_psgc_city
                 ORDER BY region_name ASC"
            );

            if (empty($rows)) {
                $rows = $this->psgcRegionsFromCsv();
            }

            $results = array_map(static function (array $r) {
                $code = (string) ((int) ($r['region_code'] ?? 0));
                $name = (string) ($r['region_name'] ?? '');
                return ['value' => $code, 'text' => $name];
            }, $rows);

            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results),
            ]);
        } catch (Throwable $e) {
            // Fallback to CSV response if DB access fails (e.g., missing table/migrations)
            try {
                $rows = $this->psgcRegionsFromCsv();
                $results = array_map(static function (array $r) {
                    $code = (string) ((int) ($r['region_code'] ?? 0));
                    $name = (string) ($r['region_name'] ?? '');
                    return ['value' => $code, 'text' => $name];
                }, $rows);

                $this->jsonResponse([
                    'success' => true,
                    'data' => $results,
                    'total' => count($results),
                ]);
            } catch (Throwable $ignored) {
                $this->jsonError('Failed to fetch PSGC regions', 500);
            }
        }
    }

    /**
     * PSGC Provinces endpoint
     * GET /api/psgc/provinces?region_code=<int>&q=<search>&limit=<n>
     */
    public function psgcProvinces(): void
    {
        try {
            $this->ensurePsgcSeededIfEnabled();

            $regionCode = (int) ($_GET['region_code'] ?? 0);
            if ($regionCode <= 0) {
                $this->jsonError('region_code is required', 400);
            }

            $searchTerm = trim($_GET['q'] ?? '');
            $limit = min((int) ($_GET['limit'] ?? 200), 200);
            if ($limit < 1) {
                $limit = 200;
            }

            $where = "region_code = :region_code";
            $params = ['region_code' => $regionCode];
            if ($searchTerm !== '') {
                $where .= " AND province_name LIKE :q";
                $params['q'] = '%' . $searchTerm . '%';
            }

            $rows = dbFetchAll(
                "SELECT DISTINCT province_code, province_name
                 FROM ref_psgc_city
                 WHERE {$where}
                 ORDER BY province_name ASC
                 LIMIT {$limit}",
                $params
            );

            if (empty($rows)) {
                $rows = $this->psgcProvincesFromCsv($regionCode, $searchTerm, $limit);
            }

            $results = array_map(static function (array $r) {
                $code = (string) ((int) ($r['province_code'] ?? 0));
                $name = (string) ($r['province_name'] ?? '');
                return ['value' => $code, 'text' => $name];
            }, $rows);

            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results),
            ]);
        } catch (Throwable $e) {
            try {
                $regionCode = (int) ($_GET['region_code'] ?? 0);
                if ($regionCode <= 0) {
                    $this->jsonError('region_code is required', 400);
                }
                $searchTerm = trim($_GET['q'] ?? '');
                $limit = min((int) ($_GET['limit'] ?? 200), 200);
                if ($limit < 1) $limit = 200;

                $rows = $this->psgcProvincesFromCsv($regionCode, $searchTerm, $limit);
                $results = array_map(static function (array $r) {
                    $code = (string) ((int) ($r['province_code'] ?? 0));
                    $name = (string) ($r['province_name'] ?? '');
                    return ['value' => $code, 'text' => $name];
                }, $rows);

                $this->jsonResponse([
                    'success' => true,
                    'data' => $results,
                    'total' => count($results),
                ]);
            } catch (Throwable $ignored) {
                $this->jsonError('Failed to fetch PSGC provinces', 500);
            }
        }
    }

    /**
     * PSGC Cities/Municipalities endpoint
     * GET /api/psgc/cities?province_code=<int>&q=<search>&limit=<n>
     */
    public function psgcCities(): void
    {
        try {
            $this->ensurePsgcSeededIfEnabled();

            $provinceCode = (int) ($_GET['province_code'] ?? 0);
            if ($provinceCode <= 0) {
                $this->jsonError('province_code is required', 400);
            }

            $searchTerm = trim($_GET['q'] ?? '');
            $limit = min((int) ($_GET['limit'] ?? 200), 200);
            if ($limit < 1) {
                $limit = 200;
            }

            $where = "province_code = :province_code";
            $params = ['province_code' => $provinceCode];
            if ($searchTerm !== '') {
                $where .= " AND city_name LIKE :q";
                $params['q'] = '%' . $searchTerm . '%';
            }

            $rows = dbFetchAll(
                "SELECT city_code, city_name
                 FROM ref_psgc_city
                 WHERE {$where}
                 ORDER BY city_name ASC
                 LIMIT {$limit}",
                $params
            );

            if (empty($rows)) {
                $rows = $this->psgcCitiesFromCsv($provinceCode, $searchTerm, $limit);
            }

            $results = array_map(static function (array $r) {
                $code = (string) ((int) ($r['city_code'] ?? 0));
                $name = (string) ($r['city_name'] ?? '');
                return ['value' => $code, 'text' => $name];
            }, $rows);

            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results),
            ]);
        } catch (Throwable $e) {
            try {
                $provinceCode = (int) ($_GET['province_code'] ?? 0);
                if ($provinceCode <= 0) {
                    $this->jsonError('province_code is required', 400);
                }
                $searchTerm = trim($_GET['q'] ?? '');
                $limit = min((int) ($_GET['limit'] ?? 200), 200);
                if ($limit < 1) $limit = 200;

                $rows = $this->psgcCitiesFromCsv($provinceCode, $searchTerm, $limit);
                $results = array_map(static function (array $r) {
                    $code = (string) ((int) ($r['city_code'] ?? 0));
                    $name = (string) ($r['city_name'] ?? '');
                    return ['value' => $code, 'text' => $name];
                }, $rows);

                $this->jsonResponse([
                    'success' => true,
                    'data' => $results,
                    'total' => count($results),
                ]);
            } catch (Throwable $ignored) {
                $this->jsonError('Failed to fetch PSGC cities', 500);
            }
        }
    }

    private function ensurePsgcSeededIfEnabled(): void
    {
        if (self::$psgcSeedAttempted) {
            return;
        }
        self::$psgcSeedAttempted = true;

        // If table already has rows, do nothing.
        try {
            $row = dbFetchOne("SELECT COUNT(*) AS c FROM ref_psgc_city");
            $count = (int) ($row['c'] ?? 0);
            if ($count > 0) {
                return;
            }
        } catch (Throwable $e) {
            // DB/table may not exist; let callers fallback to CSV.
            return;
        }

        // Default behavior:
        // - development: auto-import if table is empty
        // - production: require explicit opt-in via PSGC_AUTO_IMPORT=1
        $raw = getenv('PSGC_AUTO_IMPORT');
        $flag = null;
        if (is_string($raw) && $raw !== '') {
            $flag = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        $autoImport = ($flag !== null) ? $flag : (!function_exists('isProduction') || !isProduction());

        if (!$autoImport) {
            return;
        }

        if (!defined('SRC_PATH')) {
            return;
        }
        require_once SRC_PATH . '/services/PsgcImportService.php';

        // This is safe reference data; importing once makes dropdowns + reports work without manual admin steps.
        // Use a best-effort DB lock to avoid concurrent imports if multiple requests hit at once.
        $lockName = 'psgc_import';
        $lockAcquired = false;
        try {
            $lockRow = dbFetchOne("SELECT GET_LOCK(:name, 30) AS locked", ['name' => $lockName]);
            $lockAcquired = ((int) ($lockRow['locked'] ?? 0)) === 1;
        } catch (Throwable $e) {
            // If GET_LOCK isn't available (or errors), skip auto-import.
            // Users can still import via Admin -> Import -> Import PSGC.
            return;
        }

        if (!$lockAcquired) {
            return;
        }

        try {
            PsgcImportService::importFromCsv();
        } finally {
            try {
                dbFetchOne("SELECT RELEASE_LOCK(:name) AS released", ['name' => $lockName]);
            } catch (Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * CSV fallback helpers (used when DB is empty/missing).
     *
     * @return array<int, array{region_code:int,region_name:string}>
     */
    private function psgcRegionsFromCsv(): array
    {
        $rows = $this->loadPsgcCsv();
        $regions = [];
        foreach ($rows as $r) {
            $rc = (int) ($r['region_code'] ?? 0);
            $rn = (string) ($r['region_name'] ?? '');
            if ($rc <= 0 || $rn === '') continue;
            $regions[$rc] = $rn;
        }
        asort($regions, SORT_STRING | SORT_FLAG_CASE);
        $out = [];
        foreach ($regions as $code => $name) {
            $out[] = ['region_code' => (int) $code, 'region_name' => (string) $name];
        }
        return $out;
    }

    /**
     * @return array<int, array{province_code:int,province_name:string}>
     */
    private function psgcProvincesFromCsv(int $regionCode, string $q, int $limit): array
    {
        $qLower = strtolower($q);
        $rows = $this->loadPsgcCsv();
        $provinces = [];
        foreach ($rows as $r) {
            if ((int) ($r['region_code'] ?? 0) !== $regionCode) continue;
            $pc = (int) ($r['province_code'] ?? 0);
            $pn = (string) ($r['province_name'] ?? '');
            if ($pc <= 0 || $pn === '') continue;
            if ($qLower !== '' && strpos(strtolower($pn), $qLower) === false) continue;
            $provinces[$pc] = $pn;
        }
        asort($provinces, SORT_STRING | SORT_FLAG_CASE);
        $out = [];
        foreach ($provinces as $code => $name) {
            $out[] = ['province_code' => (int) $code, 'province_name' => (string) $name];
            if (count($out) >= $limit) break;
        }
        return $out;
    }

    /**
     * @return array<int, array{city_code:int,city_name:string}>
     */
    private function psgcCitiesFromCsv(int $provinceCode, string $q, int $limit): array
    {
        $qLower = strtolower($q);
        $rows = $this->loadPsgcCsv();
        $cities = [];
        foreach ($rows as $r) {
            if ((int) ($r['province_code'] ?? 0) !== $provinceCode) continue;
            $cc = (int) ($r['city_code'] ?? 0);
            $cn = (string) ($r['city_name'] ?? '');
            if ($cc <= 0 || $cn === '') continue;
            if ($qLower !== '' && strpos(strtolower($cn), $qLower) === false) continue;
            $cities[$cc] = $cn;
        }
        asort($cities, SORT_STRING | SORT_FLAG_CASE);
        $out = [];
        foreach ($cities as $code => $name) {
            $out[] = ['city_code' => (int) $code, 'city_name' => (string) $name];
            if (count($out) >= $limit) break;
        }
        return $out;
    }

    /**
     * Load PSGC CSV into memory.
     *
     * @return array<int, array{region_code:int,region_name:string,province_code:int,province_name:string,city_code:int,city_name:string}>
     */
    private function loadPsgcCsv(): array
    {
        $path = APP_ROOT . '/docs/update/lib_psgc_2025.csv';
        if (!file_exists($path)) {
            return [];
        }
        $size = @filesize($path);
        if (is_int($size) && $size > (10 * 1024 * 1024)) {
            // Guardrail: avoid loading unexpectedly large files into memory.
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle, 0, ',');
        if (!is_array($header) || empty($header)) {
            fclose($handle);
            return [];
        }

        $header = array_map(static fn($h) => trim((string) $h), $header);
        $index = array_flip($header);

        $required = ['region_code','region_name','province_code','province_name','city_code','city_name'];
        foreach ($required as $col) {
            if (!array_key_exists($col, $index)) {
                fclose($handle);
                return [];
            }
        }

        $toUtf8 = static function (string $value): string {
            $value = trim($value);
            if ($value === '') return '';
            if (function_exists('mb_check_encoding') && @mb_check_encoding($value, 'UTF-8')) {
                return $value;
            }
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
            if (is_string($converted) && $converted !== '') return $converted;
            $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);
            if (is_string($converted) && $converted !== '') return $converted;
            return preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', $value) ?? '';
        };

        $rows = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = [
                'region_code' => (int) trim((string) ($row[$index['region_code']] ?? '0')),
                'region_name' => $toUtf8((string) ($row[$index['region_name']] ?? '')),
                'province_code' => (int) trim((string) ($row[$index['province_code']] ?? '0')),
                'province_name' => $toUtf8((string) ($row[$index['province_name']] ?? '')),
                'city_code' => (int) trim((string) ($row[$index['city_code']] ?? '0')),
                'city_name' => $toUtf8((string) ($row[$index['city_name']] ?? '')),
            ];
        }

        fclose($handle);
        return $rows;
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
