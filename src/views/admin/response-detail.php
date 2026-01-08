<?php
/**
 * Admin Response Detail View
 */

$response = $response ?? [];
$multiValues = $multiValues ?? [];

// Helper to format field labels
function formatLabel($key) {
    return ucwords(str_replace('_', ' ', $key));
}

// Helper to format boolean
function formatBool($val) {
    return $val ? 'Yes' : 'No';
}
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="<?= appUrl('/admin/responses') ?>" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    <?= htmlspecialchars(strtoupper($response['last_name'])) ?>, 
                    <?= htmlspecialchars($response['first_name']) ?> 
                    <?= $response['middle_name'] ? htmlspecialchars($response['middle_name']) : '' ?>
                    <?= $response['ext_name'] ? htmlspecialchars($response['ext_name']) : '' ?>
                </h2>
                <p class="text-sm text-gray-500">Response #<?= $response['id'] ?> • Submitted <?= date('F j, Y \a\t g:i A', strtotime($response['created_at'])) ?></p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">Basic Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Last Name</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars(strtoupper($response['last_name'])) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">First Name</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['first_name']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Middle Name</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['middle_name'] ?: 'N/A') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Extension Name</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['ext_name'] ?: 'N/A') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Sex</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['sex']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Age Range</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= $response['age_range'] ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['email']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Phone</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['phone'] ?: 'N/A') ?></dd>
                </div>
            </dl>
        </div>
        
        <!-- Office & Employment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">Office & Employment</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Office Type</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['office_type']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Specific Office</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['specific_office']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Current Position</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['current_position']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Employment Status</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['employment_status']) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 mb-2">Program Assignments</dt>
                    <dd class="flex flex-wrap gap-1">
                        <?php 
                        $programs = array_column($multiValues['program_assignments'] ?? [], 'program');
                        if (empty($programs)): ?>
                            <span class="text-sm text-gray-400">None</span>
                        <?php else: ?>
                            <?php foreach ($programs as $program): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"><?= htmlspecialchars(formatLabel($program)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>
        
        <!-- Work Experience -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">Work Experience</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Years in DSWD</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['years_dswd']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Years in SWD Sector</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['years_swd_sector']) ?></dd>
                </div>
            </dl>
        </div>
        
        <!-- Social Work Competencies -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">Social Work Competencies</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Performs SW Tasks</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatBool($response['performs_sw_tasks']) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 mb-2">SW Tasks</dt>
                    <dd class="flex flex-wrap gap-1">
                        <?php 
                        $tasks = array_column($multiValues['sw_tasks'] ?? [], 'task');
                        if (empty($tasks)): ?>
                            <span class="text-sm text-gray-400">None</span>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded"><?= htmlspecialchars(formatLabel($task)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 mb-2">Expertise Areas</dt>
                    <dd class="flex flex-wrap gap-1">
                        <?php 
                        $areas = array_column($multiValues['expertise_areas'] ?? [], 'area');
                        if (empty($areas)): ?>
                            <span class="text-sm text-gray-400">None</span>
                        <?php else: ?>
                            <?php foreach ($areas as $area): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded"><?= htmlspecialchars(formatLabel($area)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>
        
        <!-- Educational Background -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">Educational Background</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Highest Education</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatLabel($response['highest_education']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Undergraduate Course</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['undergrad_course'] ?: 'N/A') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Diploma Course</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['diploma_course'] ?: 'N/A') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Graduate Course</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($response['graduate_course'] ?: 'N/A') ?></dd>
                </div>
            </dl>
        </div>
        
        <!-- DSWD Academy -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">DSWD Academy Courses</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Availed Training</dt>
                    <dd class="text-sm font-medium text-gray-900"><?= formatBool($response['availed_dswd_training']) ?></dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 mb-2">Courses Completed</dt>
                    <dd class="flex flex-wrap gap-1">
                        <?php 
                        $courses = array_column($multiValues['dswd_courses'] ?? [], 'course');
                        if (empty($courses)): ?>
                            <span class="text-sm text-gray-400">None</span>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded"><?= htmlspecialchars(formatLabel($course)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    
    <!-- ETEEAP Interest (Full Width) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-md font-semibold text-gray-900 mb-4 pb-2 border-b">ETEEAP Interest</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <dt class="text-sm text-gray-500">Aware of ETEEAP</dt>
                <dd class="text-lg font-semibold text-gray-900 mt-1"><?= formatBool($response['eteeap_awareness']) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Interest Level</dt>
                <dd class="mt-1">
                    <?php
                    $interestClass = match($response['eteeap_interest']) {
                        'very_interested' => 'bg-green-100 text-green-800',
                        'interested' => 'bg-blue-100 text-blue-800',
                        'somewhat_interested' => 'bg-amber-100 text-amber-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    ?>
                    <span class="px-3 py-1 text-sm rounded-full <?= $interestClass ?>"><?= formatLabel($response['eteeap_interest']) ?></span>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Will Apply</dt>
                <dd class="mt-1">
                    <?php
                    $applyClass = match($response['will_apply']) {
                        'yes' => 'bg-green-100 text-green-800',
                        'maybe' => 'bg-amber-100 text-amber-800',
                        default => 'bg-red-100 text-red-800'
                    };
                    ?>
                    <span class="px-3 py-1 text-sm rounded-full <?= $applyClass ?>"><?= ucfirst($response['will_apply']) ?></span>
                </dd>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <dt class="text-sm text-gray-500 mb-2">Motivations</dt>
                <dd class="flex flex-wrap gap-1">
                    <?php 
                    $motivations = array_column($multiValues['motivations'] ?? [], 'motivation');
                    if (empty($motivations)): ?>
                        <span class="text-sm text-gray-400">None specified</span>
                    <?php else: ?>
                        <?php foreach ($motivations as $m): ?>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded"><?= htmlspecialchars(formatLabel($m)) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500 mb-2">Barriers</dt>
                <dd class="flex flex-wrap gap-1">
                    <?php 
                    $barriers = array_column($multiValues['barriers'] ?? [], 'barrier');
                    if (empty($barriers)): ?>
                        <span class="text-sm text-gray-400">None specified</span>
                    <?php else: ?>
                        <?php foreach ($barriers as $b): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded"><?= htmlspecialchars(formatLabel($b)) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </dd>
            </div>
        </div>
        
        <?php if (!empty($response['additional_comments'])): ?>
        <div class="mt-6">
            <dt class="text-sm text-gray-500 mb-2">Additional Comments</dt>
            <dd class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700"><?= nl2br(htmlspecialchars($response['additional_comments'])) ?></dd>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Metadata -->
    <div class="bg-gray-100 rounded-lg p-4 text-xs text-gray-500">
        <div class="flex flex-wrap gap-6">
            <span>Session ID: <?= htmlspecialchars($response['session_id'] ?? 'N/A') ?></span>
            <span>IP: <?= htmlspecialchars($response['ip_address'] ?? 'N/A') ?></span>
            <span>Created: <?= date('Y-m-d H:i:s', strtotime($response['created_at'])) ?></span>
            <span>Completed: <?= $response['completed_at'] ? date('Y-m-d H:i:s', strtotime($response['completed_at'])) : 'N/A' ?></span>
        </div>
    </div>
</div>
