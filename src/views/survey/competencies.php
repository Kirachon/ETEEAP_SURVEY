<?php
/**
 * Step 5: Social Work–Related Experience
 */

$currentStep = $currentStep ?? 5;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="bg-slate-50 pt-8 pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 5: SOCIAL WORK–RELATED EXPERIENCE
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Please indicate your social work–related experiences.
            </p>
        </div>

        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>

            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">16. Social Work–Related Experiences</h3>
                        </div>
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Checkboxes – Check all that apply</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 auto-rows-fr">
                        <?php
                        $experienceOptions = [
                            'Case management (assessment, intervention, referral)',
                            'Child, women, elderly, PWD, IP casework',
                            'Community organizing / advocacy',
                            'Social protection program implementation',
                            'Disaster response & recovery',
                            'Psychosocial support services',
                            'Program planning / monitoring & evaluation',
                            'Supervision of social welfare staff',
                            'Training facilitation / capacity building',
                            'NGO / LGU / volunteer social welfare work',
                            'Others',
                        ];
                        $selected = $savedData['expertise_areas'] ?? [];
                        foreach ($experienceOptions as $label):
                            $value = $label === 'Others' ? 'Other' : $label;
                        ?>
                        <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                            <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-tight"><?= htmlspecialchars($label) ?></span>
                            <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                <input type="checkbox" name="expertise_areas[]" value="<?= htmlspecialchars($value) ?>"
                                    <?= in_array($value, $selected, true) ? 'checked' : '' ?>
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($errors['expertise_areas'])): ?>
                        <p class="mt-6 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['expertise_areas'][0]) ?></p>
                    <?php endif; ?>

                    <div class="mt-6">
                        <label for="expertise_areas_other" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">If Others, please specify</label>
                        <input type="text" name="expertise_areas_other" id="expertise_areas_other" value="<?= htmlspecialchars($savedData['expertise_areas_other'] ?? '') ?>"
                               class="mt-2 w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                               placeholder="Others: __________">
                        <?php if (isset($errors['expertise_areas_other'])): ?>
                            <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['expertise_areas_other'][0]) ?></p>
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
