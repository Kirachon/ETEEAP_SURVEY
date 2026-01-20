<?php
/**
 * PSGC Import Service
 *
 * Imports `docs/update/lib_psgc_2025.csv` into the DB reference table `ref_psgc_city`.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

class PsgcImportService
{
    public const DEFAULT_CSV_PATH = APP_ROOT . '/docs/update/lib_psgc_2025.csv';

    private static function normalizeUtf8(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        // Fast path: already valid UTF-8
        if (function_exists('mb_check_encoding') && @mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Common case: CSV saved as Windows-1252/ANSI on Windows.
        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        // Last resort: strip invalid bytes
        return preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', $value) ?? '';
    }

    /**
     * Import PSGC rows from CSV into ref_psgc_city.
     *
     * @return array{total:int, processed:int, inserted_or_updated:int, skipped:int}
     */
    public static function importFromCsv(?string $csvPath = null): array
    {
        $csvPath = $csvPath ?: self::DEFAULT_CSV_PATH;

        if (!is_string($csvPath) || $csvPath === '' || !file_exists($csvPath)) {
            throw new InvalidArgumentException('PSGC CSV file not found.');
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Failed to open PSGC CSV file.');
        }

        $header = fgetcsv($handle, 0, ',');
        if (!is_array($header) || empty($header)) {
            fclose($handle);
            throw new RuntimeException('PSGC CSV header is missing or invalid.');
        }

        $header = array_map(static fn($h) => trim((string) $h), $header);
        $index = array_flip($header);

        $required = [
            'region_code',
            'region_name',
            'province_code',
            'province_name',
            'city_code',
            'city_name',
        ];
        foreach ($required as $col) {
            if (!array_key_exists($col, $index)) {
                fclose($handle);
                throw new RuntimeException("PSGC CSV missing required column: {$col}");
            }
        }

        $total = 0;
        $processed = 0;
        $skipped = 0;
        $insertedOrUpdated = 0;

        dbBeginTransaction();
        try {
            $sql = "INSERT INTO ref_psgc_city (
                        region_code, region_name,
                        province_code, province_name,
                        city_code, city_name
                    ) VALUES (
                        :region_code, :region_name,
                        :province_code, :province_name,
                        :city_code, :city_name
                    )
                    ON DUPLICATE KEY UPDATE
                        region_code = VALUES(region_code),
                        region_name = VALUES(region_name),
                        province_code = VALUES(province_code),
                        province_name = VALUES(province_name),
                        city_name = VALUES(city_name),
                        updated_at = CURRENT_TIMESTAMP";

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $total++;

                $regionCode = (int) trim((string) ($row[$index['region_code']] ?? ''));
                $regionName = sanitizeString(self::normalizeUtf8((string) ($row[$index['region_name']] ?? '')));
                $provinceCode = (int) trim((string) ($row[$index['province_code']] ?? ''));
                $provinceName = sanitizeString(self::normalizeUtf8((string) ($row[$index['province_name']] ?? '')));
                $cityCode = (int) trim((string) ($row[$index['city_code']] ?? ''));
                $cityName = sanitizeString(self::normalizeUtf8((string) ($row[$index['city_name']] ?? '')));

                if ($regionCode <= 0 || $provinceCode <= 0 || $cityCode <= 0) {
                    $skipped++;
                    continue;
                }
                if ($regionName === '' || $provinceName === '' || $cityName === '') {
                    $skipped++;
                    continue;
                }

                dbExecute($sql, [
                    'region_code' => $regionCode,
                    'region_name' => $regionName,
                    'province_code' => $provinceCode,
                    'province_name' => $provinceName,
                    'city_code' => $cityCode,
                    'city_name' => $cityName,
                ]);

                $processed++;
                $insertedOrUpdated++;
            }

            fclose($handle);
            dbCommit();
        } catch (Throwable $e) {
            fclose($handle);
            dbRollback();
            throw $e;
        }

        return [
            'total' => $total,
            'processed' => $processed,
            'inserted_or_updated' => $insertedOrUpdated,
            'skipped' => $skipped,
        ];
    }
}
