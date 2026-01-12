<?php
/**
 * Step 3: Office & Employment Data
 */

$currentStep = $currentStep ?? 3;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 3: OFFICE & EMPLOYMENT
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your office and employment details.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Bento Card: Office Type -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">8. Office Assignment *</h3>
                        </div>

                        <?php 
                        $officeTypes = [
                            'central_office' => 'Central Office (CO)',
                            'field_office' => 'Field Office (FO)',
                            'attached_agency' => 'Attached Agency'
                        ]; 
                        ?>

                        <div class="space-y-3">
                            <?php foreach ($officeTypes as $value => $label): ?>
                                <label class="relative flex items-center p-4 rounded-xl border-2 transition-all cursor-pointer group hover:bg-slate-50
                                    <?= ($savedData['office_type'] ?? '') === $value ? 'border-dswd-blue bg-blue-50/50' : 'border-slate-100' ?>">
                                    <input type="radio" name="office_type" value="<?= $value ?>" 
                                        class="peer w-5 h-5 text-dswd-blue focus:ring-dswd-blue border-gray-300"
                                        <?= ($savedData['office_type'] ?? '') === $value ? 'checked' : '' ?>
                                        required>
                                    <span class="ml-3 font-bold text-slate-700 peer-checked:text-dswd-blue"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['office_type'])): ?>
                            <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['office_type'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Specific Office/Unit/Bureau -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 h-full flex flex-col justify-center">
                        
                        <!-- 10. Field Office Assignment (Only for FO) -->
                        <div id="field_office_container" class="hidden space-y-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">9. Region Assignment *</h3>
                            </div>
                            
                            <div class="relative">
                                <select name="office_assignment" id="office_assignment" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none appearance-none cursor-pointer">
                                    <option value="" disabled selected>Select Region</option>
                                    <?php 
                                    $regions = ['I', 'II', 'III', 'IV-A', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'NCR', 'CAR', 'XIII', 'MIMAROPA', 'NIR', 'BARMM'];
                                    foreach ($regions as $region): 
                                    ?>
                                        <option value="<?= $region ?>" <?= ($savedData['office_assignment'] ?? '') === $region ? 'selected' : '' ?>>
                                            Field Office <?= $region ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <?php if (isset($errors['office_assignment'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['office_assignment'][0]) ?></p>
                            <?php endif; ?>

                            <!-- 10. Office Field / Unit / Program Assignment (For FO) -->
                            <div class="pt-4">
                                <div class="flex items-center gap-3 mb-2">
                                     <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">10. Office Field / Unit / Program Assignment *</h3>
                                </div>
                                <input type="text" name="field_office_unit" id="field_office_unit" 
                                    value="<?= htmlspecialchars($savedData['field_office_unit'] ?? '') ?>" 
                                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                    placeholder="e.g. Pantawid Pamilyang Pilipino Program / CIS / DRMD">
                                <?php if (isset($errors['field_office_unit'])): ?>
                                    <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['field_office_unit'][0]) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- 10.a Central Office Bureau (Only for CO) - Dynamic Tom Select -->
                        <div id="central_office_container" class="hidden space-y-4">
                            <div class="flex items-center gap-3 mb-2">
                                 <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">10.a Bureau / Service / Office *</h3>
                            </div>
                            <select
                                name="office_bureau"
                                id="office_bureau"
                                data-tom-select="obs"
                                data-tom-url="<?= htmlspecialchars(appUrl('/api/obs')) ?>"
                                placeholder="Select or type Bureau/Service..."
                                class="w-full"
                            >
                                 <option value="">Select Bureau/Service...</option>
                                 <?php if (!empty($savedData['office_bureau'] ?? '')): ?>
                                    <option value="<?= htmlspecialchars($savedData['office_bureau']) ?>" selected>
                                        <?= htmlspecialchars($savedData['office_bureau']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($errors['office_bureau'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['office_bureau'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- 10.b Attached Agency (Only for Attached Agency) - Dynamic Tom Select -->
                        <div id="attached_agency_container" class="hidden space-y-4">
                            <div class="flex items-center gap-3 mb-2">
                                 <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">10.b Attached Agency *</h3>
                            </div>
                             <select
                                name="attached_agency"
                                id="attached_agency"
                                data-tom-select="agency"
                                data-tom-url="<?= htmlspecialchars(appUrl('/api/attached-agencies')) ?>"
                                placeholder="Select Attached Agency..."
                                class="w-full"
                            >
                                 <option value="">Select Agency...</option>
                                 <?php if (!empty($savedData['attached_agency'] ?? '')): ?>
                                    <option value="<?= htmlspecialchars($savedData['attached_agency']) ?>" selected>
                                        <?= htmlspecialchars($savedData['attached_agency']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($errors['attached_agency'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['attached_agency'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Bento Card: Specific Office/Unit & Position -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                     <div class="p-8 md:p-10 grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- 11. Specific Division/Unit (Optional) -->
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">11. Division / Section / Unit (Optional)</h3>
                            </div>
                            <input type="text" name="specific_office" id="specific_office" 
                                value="<?= htmlspecialchars($savedData['specific_office'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g., HRMD Division / Accounting Section">
                            <?php if (isset($errors['specific_office'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['specific_office'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- 12. Current Position (Required) -->
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .85.672 1.515 1.5 1.5s1.5-.665 1.5-1.5V3"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">12. Current Position / Designation *</h3>
                            </div>
                            <div class="relative">
                               <!-- Tom Select: searchable dropdown (loads from /api/positions) -->
                                <select
                                    name="current_position"
                                    id="current_position"
                                    data-tom-select="positions"
                                    data-tom-url="<?= htmlspecialchars(appUrl('/api/positions')) ?>"
                                    placeholder="Start typing your position (e.g., Social Welfare Officer II)"
                                    class="w-full"
                                    required
                                >
                                    <option value="">Select or type a position</option>
                                    <?php if (!empty($savedData['current_position'] ?? '')): ?>
                                        <option value="<?= htmlspecialchars($savedData['current_position']) ?>" selected>
                                            <?= htmlspecialchars($savedData['current_position']) ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <p class="mt-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                    You can also type if your position is not listed.
                                </p>
                                <?php if (isset($errors['current_position'])): ?>
                                    <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['current_position'][0]) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Bento Card: Employment Status -->
             <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">13. Employment Status *</h3>
                    </div>

                    <?php 
                    $statuses = [
                        'permanent' => 'Permanent',
                        'cos' => 'Contract of Service (COS)',
                        'jo' => 'Job Order (JO)',
                        'others' => 'Others'
                    ]; 
                    ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($statuses as $value => $label): ?>
                            <label class="relative flex items-center p-4 rounded-xl border-2 transition-all cursor-pointer group hover:bg-slate-50
                                <?= ($savedData['employment_status'] ?? '') === $value ? 'border-dswd-blue bg-blue-50/50' : 'border-slate-100' ?>">
                                <input type="radio" name="employment_status" value="<?= $value ?>" 
                                    class="peer w-5 h-5 text-dswd-blue focus:ring-dswd-blue border-gray-300"
                                    <?= ($savedData['employment_status'] ?? '') === $value ? 'checked' : '' ?>
                                    required>
                                <span class="ml-3 font-bold text-slate-700 peer-checked:text-dswd-blue"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['employment_status'])): ?>
                        <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['employment_status'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Navigation Bar -->
        <div class="mt-12 p-6 md:p-8 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 flex items-center justify-between gap-4">
            <!-- Back Button -->
            <a href="<?= appUrl('/survey/step/' . ($currentStep - 1)) ?>" 
                class="group flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-4 px-8 rounded-2xl transition-all active:scale-[0.98]">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                </svg>
                <span class="hidden sm:inline">Back</span>
            </a>

            <!-- Submit/Next Button -->
            <button type="submit" form="surveyForm" 
                class="flex-1 group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                <span>Save & Continue</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script nonce="<?= cspNonceEscaped() ?>">
document.addEventListener('DOMContentLoaded', () => {
    // Determine initial state
    const officeTypeRadios = document.querySelectorAll('input[name="office_type"]');
    const fieldOfficeDiv = document.getElementById('field_office_container');
    const centralOfficeDiv = document.getElementById('central_office_container');
    const attachedAgencyDiv = document.getElementById('attached_agency_container');

    function updateOfficeFields(type) {
        // Reset/Hide all first
        fieldOfficeDiv.classList.add('hidden');
        centralOfficeDiv.classList.add('hidden');
        attachedAgencyDiv.classList.add('hidden');

        if (type === 'field_office') {
            fieldOfficeDiv.classList.remove('hidden');
        } else if (type === 'central_office') {
            centralOfficeDiv.classList.remove('hidden');
        } else if (type === 'attached_agency') {
            attachedAgencyDiv.classList.remove('hidden');
        }
    }

    // Attach listener
    officeTypeRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            updateOfficeFields(e.target.value);
        });
        // Set initial
        if (radio.checked) {
            updateOfficeFields(radio.value);
        }
    });

    // Initialize Tom Selects
    // Check if we already have TomSelect loaded
    const initTomSelect = (el, url, create = false) => {
        if (!window.TomSelect) return;
        if (el.tomselect) return; // Already init

        new TomSelect(el, {
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            create: create,
            maxItems: 1,
            render: {
                option: function(data, escape) {
                    return '<div>' + escape(String(data.text ?? '')) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(String(data.text ?? '')) + '</div>';
                }
            },
            load: function(query, callback) {
                const fetchUrl = new URL(url, window.location.origin);
                fetchUrl.searchParams.append('q', query);
                
                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(json => {
                        // API returns { success: true, data: [...], total: ... }
                        // Tom Select expects an array
                        if (json.success && Array.isArray(json.data)) {
                            callback(json.data);
                        } else {
                            callback();
                        }
                    }).catch(()=>{
                        callback();
                    });
            }
        });
    };

    document.querySelectorAll('[data-tom-select]').forEach(el => {
        const type = el.getAttribute('data-tom-select');
        const url = el.getAttribute('data-tom-url');
        // Allow creation only for 'positions', strict for others
        const allowCreate = type === 'positions';
        initTomSelect(el, url, allowCreate);
    });
});
</script>
