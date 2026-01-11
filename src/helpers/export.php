<?php
/**
 * ETEEAP Survey Application - Export Helper
 * 
 * CSV and data export functions.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Prevent CSV/Excel formula injection by prefixing values that could be interpreted as formulas.
 *
 * Excel (and some other spreadsheet software) may treat cells beginning with =, +, -, @ as formulas.
 * This also applies when the first non-whitespace character is one of those symbols.
 *
 * @param string $value
 * @return string
 */
function csvEscapeFormula(string $value): string
{
    $trimmed = ltrim($value);
    if ($trimmed === '') {
        return $value;
    }

    $first = $trimmed[0];
    if (in_array($first, ['=', '+', '-', '@'], true)) {
        return "'" . $value;
    }

    return $value;
}

/**
 * Sanitize Unicode characters to ASCII equivalents for maximum Excel compatibility.
 * 
 * Replaces smart quotes, en-dashes, em-dashes, and other problematic Unicode
 * characters with their ASCII equivalents to prevent encoding display issues.
 *
 * @param string $value
 * @return string
 */
function sanitizeForCsv(string $value): string
{
    // Map of Unicode characters to ASCII equivalents
    $replacements = [
        // Quotes
        "'" => "'",   // Right single quote (U+2019)
        "'" => "'",   // Left single quote (U+2018)
        '"' => '"',   // Left double quote (U+201C)
        '"' => '"',   // Right double quote (U+201D)
        
        // Dashes
        '–' => '-',   // En-dash (U+2013)
        '—' => '-',   // Em-dash (U+2014)
        
        // Spaces
        "\xC2\xA0" => ' ',   // Non-breaking space (UTF-8)
        
        // Other common problematic characters
        '…' => '...',  // Ellipsis (U+2026)
        '•' => '*',    // Bullet (U+2022)
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $value);
}

/**
 * Export data to CSV and send as download
 * 
 * @param array $data Array of associative arrays (rows)
 * @param string $filename Filename without extension
 * @param array $headers Optional custom headers (keys)
 */
function exportToCsv(array $data, string $filename = 'export', array $headers = []): void
{
    if (empty($data)) {
        http_response_code(204);
        return;
    }
    
    // Generate filename with timestamp
    $filename = sprintf(
        '%s_%s.csv',
        $filename,
        date(EXPORT_DATETIME_FORMAT)
    );
    
    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Determine headers
    if (empty($headers)) {
        $headers = array_keys($data[0]);
    }
    
    // Write headers
    fputcsv($output, $headers, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);
    
    // Write data rows
    foreach ($data as $row) {
        $csvRow = [];
        foreach ($headers as $header) {
            $value = $row[$header] ?? '';
            
            // Handle arrays (multi-value fields)
            if (is_array($value)) {
                $value = implode('; ', $value);
            }
            
            // Handle booleans
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            // Prevent CSV/Excel formula injection on string output
            if (is_string($value)) {
                $value = csvEscapeFormula($value);
            }
            
            $csvRow[] = $value;
        }
        fputcsv($output, $csvRow, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);
    }
    
    fclose($output);
    exit;
}

/**
 * Enum mappings for consistent human-readable exports.
 *
 * @return array<string, array<string, string>>
 */
function getSurveyEnumMappings(): array
{
    return [
        'sex' => [
            'male' => 'Male',
            'female' => 'Female',
            'prefer_not_to_say' => 'Prefer not to say'
        ],
        'age_range' => [
            '20-29' => '20-29',
            '30-39' => '30-39',
            '40-49' => '40-49',
            '50-59' => '50-59',
            '60+' => '60+'
        ],
        'office_type' => [
            'central_office' => 'Central Office',
            'field_office' => 'Field Office',
            'attached_agency' => 'Attached Agency'
        ],
        'employment_status' => [
            'permanent' => 'Permanent',
            'cos' => 'COS',
            'jo' => 'JO',
            'others' => 'Others'
        ],
        'years_dswd' => [
            'lt5' => 'Less than 5 years',
            '5-10' => '5 to 10 years',
            '11-15' => '11 to 15 years',
            '15+' => 'More than 15 years'
        ],
        'years_swd_sector' => [
            'lt5' => 'Less than 5 years',
            '5-10' => '5 to 10 years',
            '11-15' => '11 to 15 years',
            '15+' => 'More than 15 years'
        ],
        'highest_education' => [
            'high_school' => 'High School',
            'some_college' => 'Some College',
            'bachelors' => "Bachelor's",
            'masters' => "Master's",
            'doctoral' => 'Doctoral Units / Degree'
        ],
        'eteeap_interest' => [
            'very_interested' => 'Very interested',
            'interested' => 'Interested',
            'somewhat_interested' => 'Somewhat interested',
            'not_interested' => 'Not interested'
        ],
        'will_apply' => [
            'yes' => 'Yes',
            'no' => 'No'
        ]
    ];
}

/**
 * Format survey response for export
 * 
 * @param array $response Raw database response
 * @param array $multiValues Multi-value fields (programs, tasks, etc.)
 * @return array Formatted for export
 */
function formatResponseForExport(array $response, array $multiValues = []): array
{
    $enumMappings = getSurveyEnumMappings();
    
    $formatted = [];
    
    // Format each field
    foreach ($response as $key => $value) {
        // Skip internal fields
        if (in_array($key, ['id', 'session_id', 'current_step'])) {
            continue;
        }
        
        // Format dates (already in Asia/Manila)
        if (in_array($key, ['created_at', 'updated_at', 'completed_at']) && $value) {
            $formatted[$key] = $value;
            continue;
        }
        
        // Format booleans (nullable)
        if (in_array($key, ['consent_given', 'performs_sw_tasks', 'availed_dswd_training', 'eteeap_awareness'], true)) {
            if ($value === null || $value === '') {
                $formatted[$key] = '';
            } else {
                $formatted[$key] = ((int) $value === 1) ? 'Yes' : 'No';
            }
            continue;
        }
        
        // Format enums
        if (isset($enumMappings[$key]) && isset($enumMappings[$key][$value])) {
            $formatted[$key] = $enumMappings[$key][$value];
            continue;
        }
        
        $formatted[$key] = $value;
    }
    
    // Add multi-value fields
    foreach ($multiValues as $key => $values) {
        $formatted[$key] = is_array($values) ? implode('; ', $values) : $values;
    }
    
    return $formatted;
}

/**
 * Get export headers with human-readable labels
 * 
 * @return array
 */
function getExportHeaders(): array
{
    return [
        'ID' => 'id',
        'Consent Given' => 'consent_given',
        'Completed At' => 'completed_at',
        'Created At' => 'created_at',
        
        // Basic Information
        'Last Name' => 'last_name',
        'First Name' => 'first_name',
        'Middle Name' => 'middle_name',
        'Extension Name' => 'ext_name',
        'Sex' => 'sex',
        'Age Range' => 'age_range',
        'Email' => 'email',
        'Phone' => 'phone',
        
        // Office & Employment
        'Office Type' => 'office_type',
        'Office / Field Office Assignment' => 'office_assignment',
        'Office Field / Unit / Program Assignment' => 'specific_office',
        'Current Position / Designation' => 'current_position',
        'Employment Status' => 'employment_status',
        
        // Work Experience
        'Total Years of Work Experience' => 'years_dswd',
        'Years of Social Work-Related Experience' => 'years_swd_sector',
        
        // Work Experience / Social Work-Related Experience
        'Current Tasks / Functions' => 'sw_tasks',
        'Social Work-Related Experiences' => 'expertise_areas',
        
        // Education
        'Highest Education' => 'highest_education',
        'Undergraduate Course / Degree' => 'undergrad_course',
        'Diploma Course' => 'diploma_course',
        'Graduate Course / Degree' => 'graduate_course',
        
        // DSWD Academy
        'Availed DSWD Training' => 'availed_dswd_training',
        'DSWD Courses Taken' => 'dswd_courses',
        
        // ETEEAP Interest
        'ETEEAP Awareness' => 'eteeap_awareness',
        'ETEEAP Interest Level' => 'eteeap_interest',
        'Motivations' => 'motivations',
        'Barriers' => 'barriers',
        'Will Apply' => 'will_apply',
        'Reason for Not Applying' => 'will_not_apply_reason',
    ];
}

/**
 * Export survey responses to CSV
 * 
 * @param array $responses Array of survey response data with multi-value fields
 * @param string $filename Filename without extension
 */
function exportSurveyToCsv(array $responses, string $filename = 'survey_export'): void
{
    if (empty($responses)) {
        http_response_code(204);
        echo 'No data to export.';
        return;
    }

    $enumMappings = getSurveyEnumMappings();
    $boolFields = ['consent_given', 'performs_sw_tasks', 'availed_dswd_training', 'eteeap_awareness'];

    // Ensure filename has .csv extension
    if (!str_ends_with(strtolower($filename), '.csv')) {
        $filename .= '.csv';
    }

    // Set headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Get export headers mapping
    $headerMapping = getExportHeaders();
    $headers = array_keys($headerMapping);

    // Write headers
    fputcsv($output, $headers, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);

    // Stream rows (avoid building a second in-memory export array)
    foreach ($responses as $response) {
        $csvRow = [];

        foreach ($headerMapping as $label => $field) {
            $value = $response[$field] ?? '';

            // Format dates (already in Asia/Manila)
            // No conversion needed

            // Map enums to human-readable labels
            if ($value !== '' && $value !== null && isset($enumMappings[$field])) {
                $mapped = $enumMappings[$field][(string) $value] ?? null;
                if ($mapped !== null) {
                    $value = $mapped;
                }
            }

            // Handle arrays (multi-value fields)
            if (is_array($value)) {
                $value = implode('; ', $value);
            }

            // Handle booleans (nullable)
            if (in_array($field, $boolFields, true)) {
                if ($value === '' || $value === null) {
                    $value = '';
                } elseif (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } else {
                    $value = ((int) $value === 1) ? 'Yes' : 'No';
                }
            } elseif (is_bool($value) || $value === '1' || $value === '0') {
                // Legacy fallback
                $value = ((string) $value === '1' || $value === true) ? 'Yes' : 'No';
            }

            // Sanitize Unicode and prevent CSV/Excel formula injection on string output
            if (is_string($value)) {
                $value = sanitizeForCsv($value);
                $value = csvEscapeFormula($value);
            }

            $csvRow[] = $value;
        }

        fputcsv($output, $csvRow, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);
    }

    fclose($output);
    exit;
}
