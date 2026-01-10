<?php
/**
 * Step 6: Educational Background
 */

$currentStep = $currentStep ?? 6;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 6: EDUCATIONAL BACKGROUND
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your educational background.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Bento Card: Highest Educational Attainment -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">17. Highest Educational Attainment <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php 
                            $eduOptions = [
                                'high_school' => 'High School',
                                'some_college' => 'Some College',
                                'bachelors' => 'Bachelor’s',
                                'masters' => 'Master’s',
                                'doctoral' => 'Doctoral Units / Degree'
                            ];
                            foreach ($eduOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="highest_education" value="<?= $value ?>" <?= ($savedData['highest_education'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['highest_education'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['highest_education'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Undergraduate Course -->
                <div class="lg:col-span-12 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label for="undergrad_course" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                    18. Undergraduate Course / Degree
                                </label>
                                <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">If applicable</span>
                            </div>
                            <input type="text" name="undergrad_course" id="undergrad_course" value="<?= htmlspecialchars($savedData['undergrad_course'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Bachelor of Arts in Psychology">
                            <?php if (isset($errors['undergrad_course'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['undergrad_course'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Diploma/Certificate -->
                <div class="lg:col-span-6 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label for="diploma_course" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                    19. Diploma Course (if any)
                                </label>
                                <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">Optional</span>
                            </div>
                            <input type="text" name="diploma_course" id="diploma_course" value="<?= htmlspecialchars($savedData['diploma_course'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Diploma in Social Work">
                            <?php if (isset($errors['diploma_course'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['diploma_course'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Graduate Course -->
                <div class="lg:col-span-6 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label for="graduate_course" class="block text-sm font-black text-dswd-dark uppercase tracking-wider">
                                    20. Graduate Course / Degree (if any)
                                </label>
                                <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">Optional</span>
                            </div>
                            <input type="text" name="graduate_course" id="graduate_course" value="<?= htmlspecialchars($savedData['graduate_course'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Master of Science in Social Work">
                            <?php if (isset($errors['graduate_course'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['graduate_course'][0]) ?></p>
                            <?php endif; ?>
                        </div>
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
