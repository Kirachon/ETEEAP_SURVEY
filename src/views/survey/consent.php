<?php
/**
 * Step 1: Consent
 */

$currentStep = $currentStep ?? 1;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="bg-slate-50 pt-8 pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Nationwide Interest Survey on the ETEEAP–BS Social Work Program for DSWD Personnel
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                The Department of Social Welfare and Development (DSWD), through the DSWD Academy, continues to strengthen the professionalization of the social welfare and development (SWD) workforce by supporting pathways for formal academic recognition of competencies acquired through formal and informal capability development interventions and related years of public service.
            </p>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                As part of this commitment, the DSWD Academy is exploring the implementation of the <span class="font-bold">Expanded Tertiary Education Equivalency and Accreditation Program (ETEEAP)</span> leading to a <span class="font-bold">Bachelor of Science in Social Work (BSSW)</span>, in partnership with the Commission on Higher Education (CHED).
            </p>
        </div>

        <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
            <div class="relative py-8 px-6 lg:px-10 bg-gradient-to-br from-dswd-blue to-primary-600 overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-dot-grid"></div>
                <div class="relative flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shadow-inner border border-white/30 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-black text-white text-lg tracking-tight">SECTION 1: CONSENT</span>
                        <span class="block text-xs font-bold text-blue-100 uppercase tracking-widest mt-1 opacity-80">Multiple Choice – Required</span>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:p-10 bg-white">
                <p class="text-slate-700 text-sm lg:text-base leading-relaxed font-semibold">
                    I hereby consent to the collection, storage, and use of my personal and professional information for purposes of planning, assessing, and implementing the ETEEAP – BS Social Work program, in accordance with the Data Privacy Act of 2012 (RA 10173).
                </p>

                <form method="POST" action="<?= appUrl('/survey/consent') ?>" id="surveyForm" class="mt-8 space-y-4">
                    <?= csrfInputField() ?>

                    <div class="space-y-3">
                        <?php
                        $consentOptions = [
                            'yes' => 'Yes, I consent',
                            'no' => 'No, I do not consent (If selected, you will be directed to submit the form)',
                        ];
                        foreach ($consentOptions as $value => $label):
                        ?>
                        <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                            <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors pr-4"><?= htmlspecialchars($label) ?></span>
                            <input type="radio" name="consent" value="<?= $value ?>" <?= ($savedData['consent'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0" required>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($errors['consent'])): ?>
                        <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['consent'][0]) ?></p>
                    <?php endif; ?>

                    <!-- Enhanced Navigation Bar (Inline) -->
                    <div class="mt-12 p-6 md:p-8 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 flex items-center justify-between gap-4">
                        <a href="<?= appUrl('/') ?>"
                           class="group flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-4 px-8 rounded-2xl transition-all active:scale-[0.98]">
                            <span class="hidden sm:inline">Cancel</span>
                        </a>

                        <button type="submit"
                            class="flex-1 group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                            <span>Continue</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
