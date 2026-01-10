<?php
/**
 * ETEEAP Survey Application - Report Generator Service
 * 
 * Generates all 14 report types for the Admin Reports page.
 * Based on the Dynamic Report Dashboard Blueprint.
 */

class ReportGenerator
{
    /** Report type constants - 14 reports per blueprint */
    const REPORT_EXECUTIVE_SUMMARY = 'executive_summary';
    const REPORT_WORKFORCE_DEMOGRAPHICS = 'workforce_demographics';
    const REPORT_EMPLOYMENT_PROFILE = 'employment_profile';
    const REPORT_POSITION_ROLE = 'position_role';
    const REPORT_WORK_EXPERIENCE = 'work_experience';
    const REPORT_EDUCATIONAL_ATTAINMENT = 'educational_attainment';
    const REPORT_SOCIAL_WORK_EXPOSURE = 'social_work_exposure';
    const REPORT_TRAINING_CAPACITY = 'training_capacity';
    const REPORT_ETEEAP_AWARENESS = 'eteeap_awareness';
    const REPORT_APPLICATION_READINESS = 'application_readiness';
    const REPORT_MOTIVATION_ANALYSIS = 'motivation_analysis';
    const REPORT_BARRIER_ANALYSIS = 'barrier_analysis';
    const REPORT_PROGRAM_READINESS = 'program_readiness';
    const REPORT_ORGANIZATIONAL_READINESS = 'organizational_readiness';
    
    /** All reports in display order */
    const REPORTS = [
        self::REPORT_EXECUTIVE_SUMMARY,
        self::REPORT_WORKFORCE_DEMOGRAPHICS,
        self::REPORT_EMPLOYMENT_PROFILE,
        self::REPORT_POSITION_ROLE,
        self::REPORT_WORK_EXPERIENCE,
        self::REPORT_EDUCATIONAL_ATTAINMENT,
        self::REPORT_SOCIAL_WORK_EXPOSURE,
        self::REPORT_TRAINING_CAPACITY,
        self::REPORT_ETEEAP_AWARENESS,
        self::REPORT_APPLICATION_READINESS,
        self::REPORT_MOTIVATION_ANALYSIS,
        self::REPORT_BARRIER_ANALYSIS,
        self::REPORT_PROGRAM_READINESS,
        self::REPORT_ORGANIZATIONAL_READINESS,
    ];
    
    /** Report metadata */
    const REPORT_META = [
        self::REPORT_EXECUTIVE_SUMMARY => [
            'name' => 'Executive Summary',
            'description' => 'Key metrics funnel from awareness to application intent',
            'hasKPIs' => true,
            'chartTypes' => ['funnel', 'stacked_bar', 'stacked_100']
        ],
        self::REPORT_WORKFORCE_DEMOGRAPHICS => [
            'name' => 'Workforce Demographics',
            'description' => 'Age range and sex distribution of respondents',
            'hasKPIs' => false,
            'chartTypes' => ['stacked_bar', 'bar']
        ],
        self::REPORT_EMPLOYMENT_PROFILE => [
            'name' => 'Employment Profile',
            'description' => 'Office type and employment status breakdown',
            'hasKPIs' => false,
            'chartTypes' => ['grouped_bar', 'bar']
        ],
        self::REPORT_POSITION_ROLE => [
            'name' => 'Position & Role Distribution',
            'description' => 'Current positions and task assignments',
            'hasKPIs' => false,
            'chartTypes' => ['horizontal_bar', 'horizontal_bar']
        ],
        self::REPORT_WORK_EXPERIENCE => [
            'name' => 'Work Experience Profile',
            'description' => 'Total and SW-related years of experience',
            'hasKPIs' => true,
            'chartTypes' => ['bar', 'bar']
        ],
        self::REPORT_EDUCATIONAL_ATTAINMENT => [
            'name' => 'Educational Attainment',
            'description' => 'Highest education level and degree distribution',
            'hasKPIs' => false,
            'chartTypes' => ['doughnut', 'bar']
        ],
        self::REPORT_SOCIAL_WORK_EXPOSURE => [
            'name' => 'Social Work Exposure',
            'description' => 'SW-related experiences and expertise areas',
            'hasKPIs' => false,
            'chartTypes' => ['horizontal_bar']
        ],
        self::REPORT_TRAINING_CAPACITY => [
            'name' => 'Training & Capacity Building',
            'description' => 'DSWD Academy training participation and courses',
            'hasKPIs' => true,
            'chartTypes' => ['stacked_bar', 'bar']
        ],
        self::REPORT_ETEEAP_AWARENESS => [
            'name' => 'ETEEAP Awareness & Interest',
            'description' => 'Program awareness and interest level distribution',
            'hasKPIs' => true,
            'chartTypes' => ['bar', 'bar']
        ],
        self::REPORT_APPLICATION_READINESS => [
            'name' => 'Application Readiness',
            'description' => 'Intent to apply for ETEEAP-BSSW',
            'hasKPIs' => true,
            'chartTypes' => ['bar']
        ],
        self::REPORT_MOTIVATION_ANALYSIS => [
            'name' => 'Motivation Analysis',
            'description' => 'Top reasons for enrolling in ETEEAP-BSSW',
            'hasKPIs' => false,
            'chartTypes' => ['horizontal_bar']
        ],
        self::REPORT_BARRIER_ANALYSIS => [
            'name' => 'Barrier Analysis',
            'description' => 'Top obstacles to program participation',
            'hasKPIs' => false,
            'chartTypes' => ['horizontal_bar']
        ],
        self::REPORT_PROGRAM_READINESS => [
            'name' => 'Program Readiness Snapshot',
            'description' => 'Readiness indicators for ETEEAP eligibility',
            'hasKPIs' => true,
            'chartTypes' => ['stacked_100', 'bar']
        ],
        self::REPORT_ORGANIZATIONAL_READINESS => [
            'name' => 'Organizational Readiness Overview',
            'description' => 'Readiness by office/region with experience and intent',
            'hasKPIs' => false,
            'chartTypes' => ['grouped_bar', 'bar']
        ],
    ];
    
    /**
     * Generate a report by type
     */
    public function generate(string $type, array $filters = []): array
    {
        $method = 'report' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
        
        if (!method_exists($this, $method)) {
            throw new Exception("Unknown report type: {$type}");
        }
        
        return $this->$method($filters);
    }
    
    /**
     * Export report as CSV
     */
    public function exportCsv(string $type, array $filters = []): void
    {
        $data = $this->generate($type, $filters);
        $meta = self::REPORT_META[$type] ?? ['name' => $type];
        
        $filename = strtolower(str_replace(' ', '_', $meta['name'])) . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Write report title
        fputcsv($output, [$meta['name']]);
        fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
        fputcsv($output, []);
        
        // Write data based on report structure
        if (!empty($data['rows'])) {
            // Table format
            if (!empty($data['headers'])) {
                fputcsv($output, $data['headers']);
            }
            foreach ($data['rows'] as $row) {
                fputcsv($output, $row);
            }
        } elseif (!empty($data['sections'])) {
            // Multiple sections format
            foreach ($data['sections'] as $section) {
                fputcsv($output, [$section['title']]);
                if (!empty($section['headers'])) {
                    fputcsv($output, $section['headers']);
                }
                foreach ($section['rows'] ?? [] as $row) {
                    fputcsv($output, $row);
                }
                fputcsv($output, []);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get base query for completed responses with optional filters
     * Uses whitelist validation for safety (ENUM values only)
     */
    private function baseWhere(array $filters = []): string
    {
        $where = "consent_given = 1 AND completed_at IS NOT NULL";
        
        // Valid ENUM values from database schema
        $validSex = ['male', 'female', 'prefer_not_to_say'];
        $validAge = ['20-29', '30-39', '40-49', '50-59', '60+'];
        $validOffice = ['central_office', 'field_office', 'attached_agency'];
        $validEmployment = ['permanent', 'cos', 'jo', 'others'];
        $validEducation = ['high_school', 'some_college', 'bachelors', 'masters', 'doctoral'];
        
        // Apply filters with whitelist validation
        if (!empty($filters['sex']) && in_array($filters['sex'], $validSex, true)) {
            $where .= " AND sex = '{$filters['sex']}'";
        }
        if (!empty($filters['age_range']) && in_array($filters['age_range'], $validAge, true)) {
            $where .= " AND age_range = '{$filters['age_range']}'";
        }
        if (!empty($filters['office_type']) && in_array($filters['office_type'], $validOffice, true)) {
            $where .= " AND office_type = '{$filters['office_type']}'";
        }
        if (!empty($filters['employment_status']) && in_array($filters['employment_status'], $validEmployment, true)) {
            $where .= " AND employment_status = '{$filters['employment_status']}'";
        }
        if (!empty($filters['highest_education']) && in_array($filters['highest_education'], $validEducation, true)) {
            $where .= " AND highest_education = '{$filters['highest_education']}'";
        }
        
        return $where;
    }
    
    // -------------------------------------------------------------------------
    // REPORT 1: EXECUTIVE SUMMARY
    // -------------------------------------------------------------------------
    
    private function reportExecutiveSummary(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        if ($total == 0) {
            return ['summary' => [], 'sections' => []];
        }
        
        // KPIs
        $withSwExp = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND years_swd_sector != 'lt5'")['count'];
        $aware = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND eteeap_awareness = 1")['count'];
        $interested = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND eteeap_interest IN ('very_interested', 'interested', 'somewhat_interested')")['count'];
        $willApply = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND will_apply = 'yes'")['count'];
        
        // Funnel data
        $funnel = [
            ['Stage', 'Count'],
            ['Total Respondents', $total],
            ['ETEEAP Aware', $aware],
            ['Interested', $interested],
            ['Will Apply', $willApply],
        ];
        
        // By Office Type x Employment Status
        $byOfficeStatus = dbFetchAll(
            "SELECT office_type, employment_status, COUNT(*) as count
             FROM survey_responses WHERE {$where}
             GROUP BY office_type, employment_status
             ORDER BY office_type, employment_status"
        );
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'with_sw_experience' => round($withSwExp * 100 / $total, 1) . '%',
                'eteeap_aware' => round($aware * 100 / $total, 1) . '%',
                'interested' => round($interested * 100 / $total, 1) . '%',
                'will_apply' => round($willApply * 100 / $total, 1) . '%',
            ],
            'funnel' => $funnel,
            'sections' => [
                [
                    'title' => 'Conversion Funnel',
                    'headers' => ['Stage', 'Count', 'Percentage'],
                    'rows' => [
                        ['Total Respondents', $total, '100%'],
                        ['ETEEAP Aware', $aware, round($aware * 100 / $total, 1) . '%'],
                        ['Interested (Any Level)', $interested, round($interested * 100 / $total, 1) . '%'],
                        ['Will Apply (Yes)', $willApply, round($willApply * 100 / $total, 1) . '%'],
                    ]
                ],
                [
                    'title' => 'Office Type × Employment Status',
                    'headers' => ['Office Type', 'Employment Status', 'Count'],
                    'rows' => array_map(fn($r) => [$r['office_type'] ?? 'N/A', $r['employment_status'] ?? 'N/A', $r['count']], $byOfficeStatus)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 2: WORKFORCE DEMOGRAPHICS
    // -------------------------------------------------------------------------
    
    private function reportWorkforceDemographics(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Age Range x Sex (for table)
        $byAgeSex = dbFetchAll(
            "SELECT age_range, sex, COUNT(*) as count
             FROM survey_responses WHERE {$where}
             GROUP BY age_range, sex
             ORDER BY FIELD(age_range, '20-29', '30-39', '40-49', '50-59', '60+'), sex"
        );
        
        // Age Distribution (for Chart)
        $byAge = dbFetchAll(
            "SELECT age_range as label, COUNT(*) as count
             FROM survey_responses WHERE {$where}
             GROUP BY age_range 
             ORDER BY FIELD(age_range, '20-29', '30-39', '40-49', '50-59', '60+')"
        );
        
        // Sex Distribution (for secondary table)
        $bySex = dbFetchAll(
            "SELECT sex as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY sex ORDER BY count DESC"
        );
        
        return [
            'chart' => [
                'type' => 'bar',
                'labels' => array_column($byAge, 'label'),
                'datasets' => [
                    [
                        'label' => 'Respondents by Age Group',
                        'data' => array_column($byAge, 'count'),
                        'backgroundColor' => '#3b82f6' // Blue
                    ]
                ]
            ],
            'sections' => [
                [
                    'title' => 'Age Range × Sex Distribution',
                    'headers' => ['Age Range', 'Sex', 'Count'],
                    'rows' => array_map(fn($r) => [$r['age_range'] ?? 'N/A', $r['sex'] ?? 'N/A', $r['count']], $byAgeSex)
                ],
                [
                    'title' => 'Sex Distribution',
                    'headers' => ['Sex', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [ucfirst($r['label'] ?? 'N/A'), $r['count'], $r['percentage'] . '%'], $bySex)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 3: EMPLOYMENT PROFILE
    // -------------------------------------------------------------------------
    
    private function reportEmploymentProfile(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // Office Type x Employment Status (for table)
        $byOfficeStatus = dbFetchAll(
            "SELECT office_type, employment_status, COUNT(*) as count
             FROM survey_responses WHERE {$where}
             GROUP BY office_type, employment_status
             ORDER BY office_type, employment_status"
        );
        
        // Employment Status Distribution (for Chart)
        $byStatus = dbFetchAll(
            "SELECT employment_status as label, COUNT(*) as count
             FROM survey_responses WHERE {$where}
             GROUP BY employment_status ORDER BY count DESC"
        );
        
        // By Office Assignment (for secondary table)
        $byOffice = dbFetchAll(
            "SELECT office_assignment as label, COUNT(*) as count
             FROM survey_responses WHERE {$where} AND office_assignment IS NOT NULL
             GROUP BY office_assignment ORDER BY count DESC LIMIT 15"
        );
        
        return [
            'chart' => [
                'type' => 'doughnut',
                'labels' => array_map('ucfirst', array_column($byStatus, 'label')),
                'datasets' => [
                    [
                        'label' => 'Employment Status',
                        'data' => array_column($byStatus, 'count')
                    ]
                ]
            ],
            'sections' => [
                [
                    'title' => 'Office Type × Employment Status',
                    'headers' => ['Office Type', 'Employment Status', 'Count'],
                    'rows' => array_map(fn($r) => [$r['office_type'] ?? 'N/A', $r['employment_status'] ?? 'N/A', $r['count']], $byOfficeStatus)
                ],
                [
                    'title' => 'Top 15 Office Assignments',
                    'headers' => ['Office/Region', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byOffice)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 4: POSITION & ROLE DISTRIBUTION
    // -------------------------------------------------------------------------
    
    private function reportPositionRole(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // By Position
        $byPosition = dbFetchAll(
            "SELECT current_position as label, COUNT(*) as count
             FROM survey_responses WHERE {$where} AND current_position IS NOT NULL
             GROUP BY current_position ORDER BY count DESC LIMIT 20"
        );
        
        // By SW Tasks
        $byTasks = dbFetchAll(
            "SELECT task as label, COUNT(*) as count
             FROM response_sw_tasks rst
             JOIN survey_responses sr ON rst.response_id = sr.id
             WHERE {$where}
             GROUP BY task ORDER BY count DESC LIMIT 15"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Top 20 Positions/Designations',
                    'headers' => ['Position', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byPosition)
                ],
                [
                    'title' => 'Top 15 Tasks/Functions Performed',
                    'headers' => ['Task', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byTasks)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 5: WORK EXPERIENCE PROFILE
    // -------------------------------------------------------------------------
    
    private function reportWorkExperience(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Total Work Experience
        $byTotalYears = dbFetchAll(
            "SELECT years_dswd as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY years_dswd ORDER BY FIELD(years_dswd, 'lt5', '5-10', '11-15', '15+')"
        );
        
        // SW-Related Experience
        $bySwYears = dbFetchAll(
            "SELECT years_swd_sector as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY years_swd_sector ORDER BY FIELD(years_swd_sector, 'lt5', '5-10', '11-15', '15+')"
        );
        
        // Summary stats
        $with5PlusTotal = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND years_dswd IN ('5-10', '11-15', '15+')")['count'];
        $with5PlusSw = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND years_swd_sector IN ('5-10', '11-15', '15+')")['count'];
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'with_5plus_total_exp' => $with5PlusTotal,
                'with_5plus_sw_exp' => $with5PlusSw,
            ],
            'sections' => [
                [
                    'title' => 'Total Years of Work Experience',
                    'headers' => ['Years', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$this->formatYearsLabel($r['label']), $r['count'], $r['percentage'] . '%'], $byTotalYears)
                ],
                [
                    'title' => 'Years of SW-Related Experience',
                    'headers' => ['Years', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$this->formatYearsLabel($r['label']), $r['count'], $r['percentage'] . '%'], $bySwYears)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 6: EDUCATIONAL ATTAINMENT
    // -------------------------------------------------------------------------
    
    private function reportEducationalAttainment(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Highest Education
        $byEducation = dbFetchAll(
            "SELECT highest_education as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY highest_education ORDER BY FIELD(highest_education, 'high_school', 'some_college', 'bachelors', 'masters', 'doctoral')"
        );
        
        // Undergraduate Courses
        $byUndergrad = dbFetchAll(
            "SELECT undergrad_course as label, COUNT(*) as count
             FROM survey_responses WHERE {$where} AND undergrad_course IS NOT NULL
             GROUP BY undergrad_course ORDER BY count DESC LIMIT 10"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Highest Education Level',
                    'headers' => ['Level', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$this->formatEducationLabel($r['label']), $r['count'], $r['percentage'] . '%'], $byEducation)
                ],
                [
                    'title' => 'Top 10 Undergraduate Courses',
                    'headers' => ['Course/Degree', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byUndergrad)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 7: SOCIAL WORK EXPOSURE
    // -------------------------------------------------------------------------
    
    private function reportSocialWorkExposure(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // SW Experiences/Expertise Areas
        $byExpertise = dbFetchAll(
            "SELECT area as label, COUNT(*) as count
             FROM response_expertise_areas rea
             JOIN survey_responses sr ON rea.response_id = sr.id
             WHERE {$where}
             GROUP BY area ORDER BY count DESC"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Social Work-Related Experiences',
                    'headers' => ['Experience/Expertise Area', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byExpertise)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 8: TRAINING & CAPACITY BUILDING
    // -------------------------------------------------------------------------
    
    private function reportTrainingCapacity(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Availed Training
        $availedTraining = dbFetchAll(
            "SELECT CASE WHEN availed_dswd_training = 1 THEN 'Yes' ELSE 'No' END as label, 
                    COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY availed_dswd_training"
        );
        
        // Courses Taken
        $byCourses = dbFetchAll(
            "SELECT course as label, COUNT(*) as count
             FROM response_dswd_courses rdc
             JOIN survey_responses sr ON rdc.response_id = sr.id
             WHERE {$where}
             GROUP BY course ORDER BY count DESC LIMIT 15"
        );
        
        $trainedCount = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND availed_dswd_training = 1")['count'];
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'trained' => $trainedCount,
                'trained_percentage' => round($trainedCount * 100 / max($total, 1), 1) . '%',
            ],
            'sections' => [
                [
                    'title' => 'DSWD Training Participation',
                    'headers' => ['Availed Training', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count'], $r['percentage'] . '%'], $availedTraining)
                ],
                [
                    'title' => 'Top 15 DSWD Courses Taken',
                    'headers' => ['Course', 'Count'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byCourses)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 9: ETEEAP AWARENESS & INTEREST
    // -------------------------------------------------------------------------
    
    private function reportEteeapAwareness(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Awareness
        $byAwareness = dbFetchAll(
            "SELECT CASE WHEN eteeap_awareness = 1 THEN 'Yes' ELSE 'No' END as label,
                    COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY eteeap_awareness"
        );
        
        // Interest Level
        $byInterest = dbFetchAll(
            "SELECT eteeap_interest as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where}
             GROUP BY eteeap_interest ORDER BY FIELD(eteeap_interest, 'very_interested', 'interested', 'somewhat_interested', 'not_interested')"
        );
        
        $awareCount = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND eteeap_awareness = 1")['count'];
        $interestedCount = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND eteeap_interest IN ('very_interested', 'interested')")['count'];
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'aware' => $awareCount,
                'aware_percentage' => round($awareCount * 100 / max($total, 1), 1) . '%',
                'high_interest' => $interestedCount,
            ],
            'sections' => [
                [
                    'title' => 'ETEEAP Awareness',
                    'headers' => ['Aware', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count'], $r['percentage'] . '%'], $byAwareness)
                ],
                [
                    'title' => 'Interest Level',
                    'headers' => ['Interest Level', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [$this->formatInterestLabel($r['label']), $r['count'], $r['percentage'] . '%'], $byInterest)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 10: APPLICATION READINESS
    // -------------------------------------------------------------------------
    
    private function reportApplicationReadiness(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Will Apply
        $byApply = dbFetchAll(
            "SELECT will_apply as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where} AND will_apply IS NOT NULL
             GROUP BY will_apply ORDER BY FIELD(will_apply, 'yes', 'no')"
        );
        
        $yesCount = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND will_apply = 'yes'")['count'];
        $noCount = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND will_apply = 'no'")['count'];
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'will_apply_yes' => $yesCount,
                'will_apply_no' => $noCount,
                'application_rate' => round($yesCount * 100 / max($total, 1), 1) . '%',
            ],
            'sections' => [
                [
                    'title' => 'Application Intent',
                    'headers' => ['Will Apply', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [ucfirst($r['label'] ?? 'N/A'), $r['count'], $r['percentage'] . '%'], $byApply)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 11: MOTIVATION ANALYSIS
    // -------------------------------------------------------------------------
    
    private function reportMotivationAnalysis(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // Motivations
        $byMotivation = dbFetchAll(
            "SELECT motivation as label, COUNT(*) as count
             FROM response_motivations rm
             JOIN survey_responses sr ON rm.response_id = sr.id
             WHERE {$where}
             GROUP BY motivation ORDER BY count DESC"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Motivations for ETEEAP-BSSW Enrollment',
                    'headers' => ['Motivation', 'Frequency'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byMotivation)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 12: BARRIER ANALYSIS
    // -------------------------------------------------------------------------
    
    private function reportBarrierAnalysis(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // Barriers
        $byBarrier = dbFetchAll(
            "SELECT barrier as label, COUNT(*) as count
             FROM response_barriers rb
             JOIN survey_responses sr ON rb.response_id = sr.id
             WHERE {$where}
             GROUP BY barrier ORDER BY count DESC"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Barriers to ETEEAP Participation',
                    'headers' => ['Barrier', 'Frequency'],
                    'rows' => array_map(fn($r) => [$r['label'], $r['count']], $byBarrier)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 13: PROGRAM READINESS SNAPSHOT
    // -------------------------------------------------------------------------
    
    private function reportProgramReadiness(array $filters): array
    {
        $where = $this->baseWhere($filters);
        $total = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where}")['count'];
        
        // Readiness indicators
        $with5PlusSw = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND years_swd_sector IN ('5-10', '11-15', '15+')")['count'];
        $withDegree = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND highest_education IN ('bachelors', 'masters', 'doctoral')")['count'];
        $trained = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND availed_dswd_training = 1")['count'];
        $willApply = dbFetchOne("SELECT COUNT(*) as count FROM survey_responses WHERE {$where} AND will_apply = 'yes'")['count'];
        
        // By Will Apply
        $byApply = dbFetchAll(
            "SELECT will_apply as label, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / {$total}, 1) as percentage
             FROM survey_responses WHERE {$where} AND will_apply IS NOT NULL
             GROUP BY will_apply ORDER BY FIELD(will_apply, 'yes', 'no')"
        );
        
        return [
            'summary' => [
                'total_respondents' => $total,
                'sw_experience_5plus' => round($with5PlusSw * 100 / max($total, 1), 1) . '%',
                'with_degree' => round($withDegree * 100 / max($total, 1), 1) . '%',
                'dswd_trained' => round($trained * 100 / max($total, 1), 1) . '%',
                'will_apply' => round($willApply * 100 / max($total, 1), 1) . '%',
            ],
            'sections' => [
                [
                    'title' => 'Readiness Indicators',
                    'headers' => ['Indicator', 'Count', '% Meeting'],
                    'rows' => [
                        ['5+ Years SW Experience', $with5PlusSw, round($with5PlusSw * 100 / max($total, 1), 1) . '%'],
                        ['Bachelor\'s Degree or Higher', $withDegree, round($withDegree * 100 / max($total, 1), 1) . '%'],
                        ['DSWD Training Completed', $trained, round($trained * 100 / max($total, 1), 1) . '%'],
                        ['Will Apply (Yes)', $willApply, round($willApply * 100 / max($total, 1), 1) . '%'],
                    ]
                ],
                [
                    'title' => 'Application Intent Distribution',
                    'headers' => ['Will Apply', 'Count', 'Percentage'],
                    'rows' => array_map(fn($r) => [ucfirst($r['label'] ?? 'N/A'), $r['count'], $r['percentage'] . '%'], $byApply)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // REPORT 14: ORGANIZATIONAL READINESS OVERVIEW
    // -------------------------------------------------------------------------
    
    private function reportOrganizationalReadiness(array $filters): array
    {
        $where = $this->baseWhere($filters);
        
        // By Office with SW Experience and Will Apply
        $byOffice = dbFetchAll(
            "SELECT 
                office_assignment as office,
                COUNT(*) as total,
                SUM(CASE WHEN years_swd_sector IN ('5-10', '11-15', '15+') THEN 1 ELSE 0 END) as with_sw_exp,
                SUM(CASE WHEN will_apply = 'yes' THEN 1 ELSE 0 END) as will_apply_yes
             FROM survey_responses WHERE {$where} AND office_assignment IS NOT NULL
             GROUP BY office_assignment
             ORDER BY total DESC LIMIT 15"
        );
        
        return [
            'sections' => [
                [
                    'title' => 'Top 15 Offices - Readiness Overview',
                    'headers' => ['Office/Region', 'Total', '% With SW Exp', '% Will Apply'],
                    'rows' => array_map(fn($r) => [
                        $r['office'],
                        $r['total'],
                        round($r['with_sw_exp'] * 100 / max($r['total'], 1), 1) . '%',
                        round($r['will_apply_yes'] * 100 / max($r['total'], 1), 1) . '%'
                    ], $byOffice)
                ]
            ]
        ];
    }
    
    // -------------------------------------------------------------------------
    // HELPER METHODS
    // -------------------------------------------------------------------------
    
    private function formatYearsLabel(?string $label): string
    {
        $map = [
            'lt5' => 'Less than 5 years',
            '5-10' => '5-10 years',
            '11-15' => '11-15 years',
            '15+' => '15+ years',
        ];
        return $map[$label] ?? ($label ?? 'N/A');
    }
    
    private function formatEducationLabel(?string $label): string
    {
        $map = [
            'high_school' => 'High School',
            'some_college' => 'Some College',
            'bachelors' => 'Bachelor\'s Degree',
            'masters' => 'Master\'s Degree',
            'doctoral' => 'Doctoral Degree',
        ];
        return $map[$label] ?? ($label ?? 'N/A');
    }
    
    private function formatInterestLabel(?string $label): string
    {
        $map = [
            'very_interested' => 'Very Interested',
            'interested' => 'Interested',
            'somewhat_interested' => 'Somewhat Interested',
            'not_interested' => 'Not Interested',
        ];
        return $map[$label] ?? ($label ?? 'N/A');
    }
}
