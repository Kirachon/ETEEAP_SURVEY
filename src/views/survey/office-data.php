<?php
/**
 * Step 3: Office & Employment Data
 */

$currentStep = $currentStep ?? 3;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Office & Employment
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your current workplace and position within the department.
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
                            <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider">Type of Office <span class="text-red-500">*</span></h3>
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

                <!-- Bento Card: Specific Office -->
                <div class="lg:col-span-7 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10 h-full flex flex-col justify-center">
                        <div class="space-y-3">
                            <label for="specific_office" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                Specific Office/Unit <span class="text-red-500">*</span>
                            </label>
                            <textarea id="specific_office" name="specific_office" rows="3" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none resize-none" 
                                placeholder="e.g. DSWD Field Office NCR - Social Welfare Division" required><?= htmlspecialchars($savedData['specific_office'] ?? '') ?></textarea>
                            <?php if (isset($errors['specific_office'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['specific_office'][0]) ?></p>
                            <?php endif; ?>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2">Specify your bureau, service, or division</p>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Program Assignments -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Program Assignment(s)</h3>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest self-start sm:self-auto">Select all that apply</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php 
                            $programOptions = [
                                '4ps' => 'Pantawid Pamilya (4Ps)',
                                'slp' => 'Sustainable Livelihood (SLP)',
                                'kalahi' => 'KALAHI-CIDSS',
                                'aics' => 'Crisis Intervention (AICS)',
                                'centenarians' => 'Social Pension',
                                'pwd' => 'Programs for PWDs',
                                'children' => 'Programs for Children',
                                'women' => 'Programs for Women',
                                'disaster' => 'Disaster Response',
                                'other' => 'Other Programs'
                            ];
                            $selectedPrograms = $savedData['program_assignments'] ?? [];
                            foreach ($programOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center gap-4 p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <div class="relative flex items-center justify-center">
                                    <input type="checkbox" name="program_assignments[]" value="<?= $value ?>" 
                                        <?= in_array($value, $selectedPrograms) ? 'checked' : '' ?> 
                                        class="w-6 h-6 rounded-lg border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0 transition-all">
                                </div>
                                <span class="text-sm font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors leading-tight"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Current Position -->
                <div class="lg:col-span-7 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="space-y-4">
                            <label for="current_position" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                Current Position <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="current_position" id="current_position" value="<?= htmlspecialchars($savedData['current_position'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Social Welfare Officer II" required>
                            <?php if (isset($errors['current_position'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['current_position'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Employment Status -->
                <div class="lg:col-span-5 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">Employment Status <span class="text-red-500">*</span></h3>
                        <div class="space-y-2">
                            <?php 
                            $statusOptions = [
                                'permanent' => 'Permanent',
                                'coterminous' => 'Coterminous',
                                'contractual' => 'Contractual',
                                'cos_moa' => 'COS / MOA'
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
            </div>
        </form>
    </div>
</div>

<!-- Floating Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 z-40 glass border-t border-slate-200/50 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto p-6 md:p-8 flex items-center justify-between gap-4">
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
