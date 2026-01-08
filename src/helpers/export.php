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
            
            $csvRow[] = $value;
        }
        fputcsv($output, $csvRow, EXPORT_CSV_DELIMITER, EXPORT_CSV_ENCLOSURE);
    }
    
    fclose($output);
    exit;
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
    // Map database enum values to human-readable labels
    $enumMappings = [
        'sex' => [
            'male' => 'Male',
            'female' => 'Female',
            'prefer_not_to_say' => 'Prefer not to say'
        ],
        'age_range' => [
            '20-29' => '20-29 years',
            '30-39' => '30-39 years',
            '40-49' => '40-49 years',
            '50-59' => '50-59 years',
            '60+' => '60 years and above'
        ],
        'office_type' => [
            'central_office' => 'Central Office',
            'field_office' => 'Field Office',
            'attached_agency' => 'Attached Agency'
        ],
        'employment_status' => [
            'permanent' => 'Permanent',
            'coterminous' => 'Coterminous',
            'contractual' => 'Contractual',
            'cos_moa' => 'COS/MOA'
        ],
        'years_dswd' => [
            'less_than_2' => 'Less than 2 years',
            '2-5' => '2-5 years',
            '6-10' => '6-10 years',
            '11-15' => '11-15 years',
            '16+' => '16 years and above'
        ],
        'years_swd_sector' => [
            'less_than_5' => 'Less than 5 years',
            '5-10' => '5-10 years',
            '11-20' => '11-20 years',
            '21+' => '21 years and above'
        ],
        'highest_education' => [
            'high_school' => 'High School Graduate',
            'some_college' => 'Some College',
            'bachelors' => 'Bachelor\'s Degree',
            'masters' => 'Master\'s Degree',
            'doctoral' => 'Doctoral Degree'
        ],
        'eteeap_interest' => [
            'very_interested' => 'Very Interested',
            'interested' => 'Interested',
            'somewhat_interested' => 'Somewhat Interested',
            'not_interested' => 'Not Interested'
        ],
        'will_apply' => [
            'yes' => 'Yes',
            'maybe' => 'Maybe',
            'no' => 'No'
        ]
    ];
    
    $formatted = [];
    
    // Format each field
    foreach ($response as $key => $value) {
        // Skip internal fields
        if (in_array($key, ['id', 'session_id', 'current_step'])) {
            continue;
        }
        
        // Format dates
        if (in_array($key, ['created_at', 'updated_at', 'completed_at']) && $value) {
            $formatted[$key] = date('Y-m-d H:i:s', strtotime($value));
            continue;
        }
        
        // Format booleans
        if (in_array($key, ['consent_given', 'performs_sw_tasks', 'availed_dswd_training', 'eteeap_awareness'])) {
            $formatted[$key] = $value ? 'Yes' : 'No';
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
        'Specific Office' => 'specific_office',
        'Current Position' => 'current_position',
        'Employment Status' => 'employment_status',
        'Program Assignments' => 'program_assignments',
        
        // Work Experience
        'Years in DSWD' => 'years_dswd',
        'Years in SWD Sector' => 'years_swd_sector',
        
        // Competencies
        'Performs SW Tasks' => 'performs_sw_tasks',
        'SW Tasks Performed' => 'sw_tasks',
        'Expertise Areas' => 'expertise_areas',
        
        // Education
        'Highest Education' => 'highest_education',
        'Undergrad Course' => 'undergrad_course',
        'Diploma Course' => 'diploma_course',
        'Graduate Course' => 'graduate_course',
        
        // DSWD Academy
        'Availed DSWD Training' => 'availed_dswd_training',
        'DSWD Courses Taken' => 'dswd_courses',
        
        // ETEEAP Interest
        'ETEEAP Awareness' => 'eteeap_awareness',
        'ETEEAP Interest Level' => 'eteeap_interest',
        'Motivations' => 'motivations',
        'Barriers' => 'barriers',
        'Will Apply' => 'will_apply',
        'Additional Comments' => 'additional_comments'
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
    
    // Get export headers mapping
    $headerMapping = getExportHeaders();
    
    // Prepare data with human-readable headers
    $exportData = [];
    
    foreach ($responses as $response) {
        $row = [];
        foreach ($headerMapping as $label => $field) {
            $value = $response[$field] ?? '';
            
            // Handle arrays (multi-value fields)
            if (is_array($value)) {
                $value = implode('; ', $value);
            }
            
            // Handle booleans
            if (is_bool($value) || $value === '1' || $value === '0') {
                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } else {
                    $value = $value === '1' ? 'Yes' : 'No';
                }
            }
            
            $row[$label] = $value;
        }
        $exportData[] = $row;
    }
    
    // Export to CSV
    exportToCsv($exportData, $filename, array_keys($headerMapping));
}
