<?php
/**
 * Step 4: Work Experience
 */

$currentStep = $currentStep ?? 4;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Work Experience
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your years of service and professional background in social welfare.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Bento Card: Years in DSWD -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Years in DSWD <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="space-y-3">
                            <?php 
                            $yearsDswdOptions = [
                                'less_than_2' => 'Less than 2 years',
                                '2-5' => '2-5 years',
                                '6-10' => '6-10 years',
                                '11-15' => '11-15 years',
                                '16+' => '16 years and above'
                            ];
                            foreach ($yearsDswdOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="years_dswd" value="<?= $value ?>" <?= ($savedData['years_dswd'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['years_dswd'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['years_dswd'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Years in SWD Sector -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">SWD Sector <span class="text-red-500">*</span></h3>
                        </div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-8 pl-13">Include work in NGOs, LGUs, etc.</p>

                        <div class="space-y-3">
                            <?php 
                            $yearsSectorOptions = [
                                'less_than_5' => 'Less than 5 years',
                                '5-10' => '5-10 years',
                                '11-20' => '11-20 years',
                                '21+' => '21 years and above'
                            ];
                            foreach ($yearsSectorOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="years_swd_sector" value="<?= $value ?>" <?= ($savedData['years_swd_sector'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['years_swd_sector'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['years_swd_sector'][0]) ?></p>
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
