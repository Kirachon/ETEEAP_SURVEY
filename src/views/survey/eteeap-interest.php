<?php
/**
 * Step 8: ETEEAP Interest
 */

$currentStep = $currentStep ?? 8;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="bg-slate-50 pt-8 pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 8: ETEEAP INTEREST
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your awareness and interest in ETEEAP – BS Social Work.
            </p>
        </div>

        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Awareness -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">23. Awareness of ETEEAP</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <?php
                            $awarenessOptions = [
                                'aware' => 'Aware',
                                'not_aware' => 'Not aware',
                            ];
                            foreach ($awarenessOptions as $value => $label):
                                $isChecked = ($savedData['eteeap_awareness'] ?? null) === ($value === 'aware');
                            ?>
                            <label class="group relative flex items-center justify-center p-6 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-lg font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                                <input type="radio" name="eteeap_awareness" value="<?= htmlspecialchars($value) ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['eteeap_awareness'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['eteeap_awareness'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Interest -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">24. Interest in ETEEAP – BS Social Work</h3>
                        </div>

                        <div class="space-y-3">
                            <?php
                            $interestOptions = [
                                'very_interested' => 'Very interested',
                                'interested' => 'Interested',
                                'somewhat_interested' => 'Somewhat interested',
                                'not_interested' => 'Not interested',
                            ];
                            foreach ($interestOptions as $value => $label):
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                                <input type="radio" name="eteeap_interest" value="<?= htmlspecialchars($value) ?>" <?= ($savedData['eteeap_interest'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['eteeap_interest'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['eteeap_interest'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Motivation -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden lg:col-span-2">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider break-words">25. Motivation for Enrolling</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start md:self-auto">Checkboxes – Check all that apply</span>
                        </div>

                        <?php
                        $motivationOptions = [
                            'PRC Social Worker eligibility',
                            'Career progression / promotion',
                            'Professionalization',
                            'Alignment with current work',
                            'Personal growth',
                        ];
                        $selectedMotivations = $savedData['motivations'] ?? [];
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 auto-rows-fr">
                            <?php foreach ($motivationOptions as $label): ?>
                            <label class="group relative flex items-start justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-snug break-words"><?= htmlspecialchars($label) ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue mt-0.5">
                                    <input type="checkbox" name="motivations[]" value="<?= htmlspecialchars($label) ?>"
                                        <?= in_array($label, $selectedMotivations, true) ? 'checked' : '' ?>
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Barriers -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden lg:col-span-2">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider break-words">26. Possible Barriers</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start md:self-auto">Checkboxes – Check all that apply</span>
                        </div>

                        <?php
                        $barrierOptions = [
                            'Tuition / fees',
                            'Time / workload',
                            'Family responsibilities',
                            'Internet / connectivity',
                            'Lack of management support',
                        ];
                        $selectedBarriers = $savedData['barriers'] ?? [];
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 auto-rows-fr">
                            <?php foreach ($barrierOptions as $label): ?>
                            <label class="group relative flex items-start justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-snug break-words"><?= htmlspecialchars($label) ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue mt-0.5">
                                    <input type="checkbox" name="barriers[]" value="<?= htmlspecialchars($label) ?>"
                                        <?= in_array($label, $selectedBarriers, true) ? 'checked' : '' ?>
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Final Apply Question -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden lg:col-span-2">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">27. If offered, will you apply for ETEEAP – BSSW?</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <?php
                            $applyOptions = [
                                'yes' => 'Yes',
                                'maybe' => 'Maybe',
                                'no' => 'No',
                            ];
                            foreach ($applyOptions as $value => $label):
                            ?>
                            <label class="group relative flex items-center justify-center p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                                <input type="radio" name="will_apply" value="<?= htmlspecialchars($value) ?>" <?= ($savedData['will_apply'] ?? '') === $value ? 'checked' : '' ?> class="sr-only">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['will_apply'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['will_apply'][0]) ?></p>
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
                    class="flex-1 group relative flex items-center justify-center gap-3 bg-green-600 hover:bg-green-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-green-900/20 transition-all active:scale-[0.98]">
                    <span>Submit Survey</span>
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
