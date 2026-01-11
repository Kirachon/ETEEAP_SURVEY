<?php
/**
 * Admin Responses List View
 */

$responses = $responses ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
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
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php 
                            try {
                                $date = new DateTime($response['created_at'], new DateTimeZone('UTC'));
                                $date->setTimezone(new DateTimeZone('Asia/Manila'));
                                echo $date->format('M j, Y');
                            } catch (Exception $e) {
                                echo date('M j, Y', strtotime($response['created_at']));
                            }
                            ?>
                        </td>
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
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>" class="px-3 py-1 text-sm border <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50' ?> rounded"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
