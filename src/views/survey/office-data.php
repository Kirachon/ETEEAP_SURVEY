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
                <div class="lg:col-span-5 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider">8. Office Type</h3>
                        </div>
                        <div class="space-y-3">
                            <?php 
                            $officeOptions = [
                                'central_office' => 'Central Office',
                                'field_office' => 'Field Office',
                                'attached_agency' => 'Attached Agency'
                            ];
                            foreach ($officeOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="office_type" value="<?= $value ?>" <?= ($savedData['office_type'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['office_type'])): ?>
                            <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['office_type'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Office / Field Office Assignment -->
                <div class="lg:col-span-7 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10 h-full flex flex-col justify-center">
                        <div class="space-y-3">
                            <label for="office_assignment" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                9. Office / Field Office Assignment
                            </label>
                            <select id="office_assignment" name="office_assignment"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none">
                                <?php
                                $assignmentOptions = [
                                    '' => 'Select an option',
                                    'I' => 'I',
                                    'II' => 'II',
                                    'III' => 'III',
                                    'IV-A' => 'IV-A',
                                    'V' => 'V',
                                    'VI' => 'VI',
                                    'VII' => 'VII',
                                    'VIII' => 'VIII',
                                    'IX' => 'IX',
                                    'X' => 'X',
                                    'XI' => 'XI',
                                    'XII' => 'XII',
                                    'NCR' => 'NCR',
                                    'CAR' => 'CAR',
                                    'XIII' => 'XIII',
                                    'MIMAROPA' => 'MIMAROPA',
                                    'NIR' => 'NIR',
                                    'BARMM' => 'BARMM',
                                ];
                                foreach ($assignmentOptions as $value => $label):
                                ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= ($savedData['office_assignment'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['office_assignment'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['office_assignment'][0]) ?></p>
                            <?php endif; ?>
                            <p id="officeAssignmentHelp" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2 hidden">
                                Disabled for Central Office / Attached Agency
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Office Field / Unit / Program Assignment -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="space-y-3">
                            <label for="specific_office" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                10. Office Field / Unit / Program Assignment
                            </label>
                            <input type="text" name="specific_office" id="specific_office" value="<?= htmlspecialchars($savedData['specific_office'] ?? '') ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="e.g., 4Ps, SLP, AICS, Disaster Response, Protective Services, Admin, HR, Finance">
                            <?php if (isset($errors['specific_office'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['specific_office'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Employment Status -->
                <div class="lg:col-span-5 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">11. Employment Status</h3>
                        <div class="space-y-2">
                            <?php 
                            $statusOptions = [
                                'permanent' => 'Permanent',
                                'cos' => 'COS',
                                'jo' => 'JO',
                                'others' => 'Others'
                            ];
                            foreach ($statusOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-sm font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="employment_status" value="<?= $value ?>" <?= ($savedData['employment_status'] ?? '') === $value ? 'checked' : '' ?> class="w-4 h-4 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['employment_status'])): ?>
                            <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['employment_status'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Current Position -->
                <div class="lg:col-span-7 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="space-y-4">
                            <label for="current_position" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                12. Current Position / Designation
                            </label>

                            <!-- Tom Select: searchable dropdown (loads from /api/positions) -->
                            <select
                                name="current_position"
                                id="current_position"
                                data-tom-select="positions"
                                data-tom-url="<?= htmlspecialchars(appUrl('/api/positions')) ?>"
                                placeholder="Start typing your position (e.g., Social Welfare Officer II)"
                                class="w-full"
                            >
                                <option value="">Select or type a position</option>
                                <?php if (!empty($savedData['current_position'] ?? '')): ?>
                                    <option value="<?= htmlspecialchars($savedData['current_position']) ?>" selected>
                                        <?= htmlspecialchars($savedData['current_position']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                Searchable list (supports thousands of entries). You can also type if your position is not listed.
                            </p>
                            <?php if (isset($errors['current_position'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['current_position'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
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
