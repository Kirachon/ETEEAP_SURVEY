<?php
/**
 * Admin CSV Import View
 */

$importResult = $importResult ?? null;
$importOptions = $importOptions ?? ['strict_headers' => true, 'atomic' => true];
$strictHeaders = (bool) ($importOptions['strict_headers'] ?? true);
$atomic = (bool) ($importOptions['atomic'] ?? true);
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Import CSV</h2>
            <p class="text-sm text-gray-500">Upload a CSV file and import rows into the database.</p>
        </div>
        <form method="POST" action="<?= appUrl('/admin/import/template') ?>">
            <?= csrfInputField() ?>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Template
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
        <div class="text-sm text-gray-700">
            <p class="font-semibold text-gray-900 mb-1">Formatting notes</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>Use the template headers exactly (or use database field names as headers).</li>
                <li>Multi-value fields should be entered in one cell separated by <span class="font-mono">;</span> (example: <span class="font-mono">A; B; C</span>).</li>
                <li>Enum fields accept either the template text (e.g., <span class="font-mono">Male</span>) or internal codes (e.g., <span class="font-mono">male</span>).</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="<?= appUrl('/admin/import/csv') ?>" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
        <?= csrfInputField() ?>

        <div>
            <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-1">CSV file</label>
            <input
                type="file"
                id="csv_file"
                name="csv_file"
                accept=".csv,text/csv"
                required
                class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200"
            >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50">
                <input type="checkbox" name="strict_headers" value="1" <?= $strictHeaders ? 'checked' : '' ?> class="mt-1">
                <span class="text-sm text-gray-700">
                    <span class="font-semibold text-gray-900">Strict headers</span><br>
                    Reject files with unknown column headers.
                </span>
            </label>

            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50">
                <input type="checkbox" name="atomic" value="1" <?= $atomic ? 'checked' : '' ?> class="mt-1">
                <span class="text-sm text-gray-700">
                    <span class="font-semibold text-gray-900">Atomic import</span><br>
                    If any row fails, roll back the whole import.
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                Import
            </button>
        </div>
    </form>

    <?php if (is_array($importResult)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                    Total: <?= (int) ($importResult['total'] ?? 0) ?>
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    Inserted: <?= (int) ($importResult['inserted'] ?? 0) ?>
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                    Failed: <?= (int) ($importResult['failed'] ?? 0) ?>
                </span>

                <?php if (!empty($importResult['rolled_back'])): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                        Rolled back (atomic)
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($importResult['warnings']) && is_array($importResult['warnings'])): ?>
                <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-sm text-yellow-900">
                    <p class="font-semibold mb-1">Warnings</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <?php foreach ($importResult['warnings'] as $w): ?>
                            <li><?= htmlspecialchars((string) $w, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($importResult['errors']) && is_array($importResult['errors'])): ?>
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-gray-900">Errors</p>
                    <div class="overflow-auto border border-gray-200 rounded-xl">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="text-left font-semibold px-4 py-3">Row</th>
                                    <th class="text-left font-semibold px-4 py-3">Message</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach (array_slice($importResult['errors'], 0, 200) as $err): ?>
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700 font-mono"><?= (int) ($err['row'] ?? 0) ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars((string) ($err['message'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (count($importResult['errors']) > 200): ?>
                        <p class="text-xs text-gray-500">Showing first 200 errors.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
