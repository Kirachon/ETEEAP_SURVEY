<?php
/**
 * Admin Responses List View
 */

$responses = $responses ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$perPage = $perPage ?? PAGINATION_PER_PAGE;
$allowedPerPage = $allowedPerPage ?? [10, 20, 50, 100];
$filters = $filters ?? ['q' => '', 'office_type' => '', 'employment_status' => '', 'sort' => 'newest'];
$currentSort = in_array(($filters['sort'] ?? ''), ['newest', 'oldest'], true) ? ($filters['sort'] ?? 'newest') : 'newest';

$buildQuery = static function (array $overrides = []) use ($filters, $perPage, $currentSort): string {
    $params = [
        'q' => $filters['q'] ?? '',
        'office_type' => $filters['office_type'] ?? '',
        'employment_status' => $filters['employment_status'] ?? '',
        'sort' => $currentSort,
        'per_page' => $perPage,
    ];
    foreach ($overrides as $k => $v) {
        $params[$k] = $v;
    }

    // Drop empty params for cleaner URLs
    $params = array_filter($params, static fn($v) => $v !== null && $v !== '');
    return '?' . http_build_query($params);
};
?>

<div class="space-y-6">
    
    <!-- Header with Export -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">All Responses</h2>
            <p class="text-sm text-gray-500"><?= number_format($total) ?> total responses</p>
        </div>
        <a href="<?= appUrl('/admin/export/csv') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Search + Filters -->
    <form method="GET" action="<?= appUrl('/admin/responses') ?>" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-5">
                <label for="q" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                    placeholder="Search by name, email, or #ID"
                    class="w-full h-10 px-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="office_type" class="block text-xs font-medium text-gray-600 mb-1">Office Type</label>
                <select id="office_type" name="office_type" class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <?php
                    $officeOptions = [
                        '' => 'All',
                        'central_office' => 'Central Office',
                        'field_office' => 'Field Office',
                        'attached_agency' => 'Attached Agency',
                    ];
                    foreach ($officeOptions as $val => $label):
                        $selected = (($filters['office_type'] ?? '') === $val) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="employment_status" class="block text-xs font-medium text-gray-600 mb-1">Employment</label>
                <select id="employment_status" name="employment_status" class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <?php
                    $employmentOptions = [
                        '' => 'All',
                        'permanent' => 'Permanent',
                        'cos' => 'COS',
                        'jo' => 'JO',
                        'others' => 'Others',
                    ];
                    foreach ($employmentOptions as $val => $label):
                        $selected = (($filters['employment_status'] ?? '') === $val) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-1">
                <label for="sort" class="block text-xs font-medium text-gray-600 mb-1">Sort</label>
                <select id="sort" name="sort" class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <?php
                    $sortOptions = [
                        'newest' => 'Newest',
                        'oldest' => 'Oldest',
                    ];
                    foreach ($sortOptions as $val => $label):
                        $selected = ($currentSort === $val) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-1">
                <label for="per_page" class="block text-xs font-medium text-gray-600 mb-1">Per page</label>
                <select id="per_page" name="per_page" class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <?php foreach ($allowedPerPage as $n): ?>
                        <option value="<?= (int) $n ?>" <?= ((int) $perPage === (int) $n) ? 'selected' : '' ?>><?= (int) $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-1 flex gap-2">
                <button type="submit" class="flex-1 h-10 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Apply
                </button>
                <a href="<?= appUrl('/admin/responses') ?>" class="h-10 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium flex items-center justify-center">
                    Clear
                </a>
            </div>
        </div>
    </form>
    
    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Sex</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Office</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Will Apply</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($responses as $response): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">#<?= $response['id'] ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <?= htmlspecialchars(strtoupper($response['last_name'])) ?>, 
                            <?= htmlspecialchars($response['first_name']) ?> 
                            <?= $response['middle_name'] ? htmlspecialchars($response['middle_name'][0] . '.') : '' ?>
                            <?= $response['ext_name'] ? htmlspecialchars($response['ext_name']) : '' ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($response['email']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600 hidden lg:table-cell"><?= ucfirst(str_replace('_', ' ', $response['sex'] ?? '')) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600 hidden lg:table-cell"><?= $response['age_range'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= ucwords(str_replace('_', ' ', $response['office_type'] ?? '')) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $interestClass = match($response['eteeap_interest']) {
                                'very_interested' => 'bg-green-100 text-green-800',
                                'interested' => 'bg-blue-100 text-blue-800',
                                'somewhat_interested' => 'bg-amber-100 text-amber-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $interestLabel = ucwords(str_replace('_', ' ', $response['eteeap_interest'] ?? ''));
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $interestClass ?>"><?= $interestLabel ?></span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <?php
                            $applyClass = match($response['will_apply']) {
                                'yes' => 'bg-green-100 text-green-800',
                                'no' => 'bg-slate-100 text-slate-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $applyClass ?>"><?= ucfirst($response['will_apply'] ?? '') ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= date('M j, Y', strtotime($response['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <a href="<?= appUrl('/admin/responses/' . $response['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($responses)): ?>
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">No responses found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing <?= (($page - 1) * $perPage) + 1 ?> to <?= min($page * $perPage, $total) ?> of <?= $total ?> results
            </p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?= $buildQuery(['page' => 1]) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">First</a>
                    <a href="<?= $buildQuery(['page' => $page - 1]) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="<?= $buildQuery(['page' => $i]) ?>" class="px-3 py-1 text-sm border <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50' ?> rounded"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $buildQuery(['page' => $page + 1]) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Next</a>
                    <a href="<?= $buildQuery(['page' => $totalPages]) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Last</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
