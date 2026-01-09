<?php
/**
 * Step 4: Work Experience
 */

$currentStep = $currentStep ?? 4;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="bg-slate-50 pt-8 pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 4: WORK EXPERIENCE
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your work experience.
            </p>
        </div>

        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">13. Total Years of Work Experience</h3>
                        </div>

                        <div class="space-y-3">
                            <?php
                            $totalYearsOptions = [
                                'lt5' => '<5',
                                '5-10' => '5–10',
                                '11-15' => '11–15',
                                '15+' => '15+',
                            ];
                            foreach ($totalYearsOptions as $value => $label):
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                                <input type="radio" name="years_dswd" value="<?= htmlspecialchars($value) ?>" <?= ($savedData['years_dswd'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['years_dswd'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['years_dswd'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">14. Years of Social Work–Related Experience</h3>
                        </div>

                        <div class="space-y-3">
                            <?php
                            $swYearsOptions = [
                                'lt5' => '<5',
                                '5-10' => '5–10',
                                '11-15' => '11–15',
                                '15+' => '15+',
                            ];
                            foreach ($swYearsOptions as $value => $label):
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                                <input type="radio" name="years_swd_sector" value="<?= htmlspecialchars($value) ?>" <?= ($savedData['years_swd_sector'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['years_swd_sector'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['years_swd_sector'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">15. Current Tasks / Functions</h3>
                        </div>
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Checkboxes – Check all that apply</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 auto-rows-fr">
                        <?php
                        $taskOptions = [
                            'Case management / casework',
                            'Community organizing / development',
                            'Program implementation (4Ps, SLP, AICS, etc.)',
                            'Disaster response / humanitarian assistance',
                            'Psychosocial support services',
                            'Monitoring & evaluation / reporting',
                            'Policy / standards / research work',
                            'Administrative / support functions',
                            'Supervision / team leadership',
                            'Others',
                        ];
                        $selectedTasks = $savedData['sw_tasks'] ?? [];
                        foreach ($taskOptions as $label):
                            $value = $label === 'Others' ? 'Other' : $label;
                        ?>
                        <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                            <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-tight"><?= htmlspecialchars($label) ?></span>
                            <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                <input type="checkbox" name="sw_tasks[]" value="<?= htmlspecialchars($value) ?>"
                                    <?= in_array($value, $selectedTasks, true) ? 'checked' : '' ?>
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['sw_tasks'])): ?>
                        <p class="mt-6 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['sw_tasks'][0]) ?></p>
                    <?php endif; ?>

                    <div class="mt-6">
                        <label for="sw_tasks_other" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">If Others, please specify</label>
                        <input type="text" name="sw_tasks_other" id="sw_tasks_other" value="<?= htmlspecialchars($savedData['sw_tasks_other'] ?? '') ?>"
                               class="mt-2 w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                               placeholder="Others: __________">
                        <?php if (isset($errors['sw_tasks_other'])): ?>
                            <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['sw_tasks_other'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Enhanced Navigation Bar (Inline) -->
            <div class="mt-12 p-6 md:p-8 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 flex items-center justify-between gap-4">
                <a href="<?= appUrl('/survey/step/' . ($currentStep - 1)) ?>"
                    class="group flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-4 px-8 rounded-2xl transition-all active:scale-[0.98]">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                    </svg>
                    <span class="hidden sm:inline">Back</span>
                </a>

                <button type="submit"
                    class="flex-1 group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                    <span>Save & Continue</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
