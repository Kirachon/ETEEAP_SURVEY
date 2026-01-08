<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                DSWD Academy Courses
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your specialized training and professional development with the DSWD Academy.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="flex flex-col gap-8">
                <!-- Bento Card: Availed Training -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Availed training? <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php 
                            $availedOptions = [
                                'yes' => ['label' => 'Yes, I have', 'desc' => 'I have completed academy courses'],
                                'no' => ['label' => 'No, not yet', 'desc' => 'I haven\'t taken any courses yet']
                            ];
                            foreach ($availedOptions as $value => $option): 
                            ?>
                            <label class="group relative flex flex-col p-6 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $option['label'] ?></span>
                                    <input type="radio" name="availed_dswd_training" value="<?= $value ?>" 
                                        <?= ($savedData['availed_dswd_training'] ?? '') === $value ? 'checked' : '' ?> 
                                        onchange="toggleCoursesSection(this.value === 'yes')"
                                        class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue">
                                </div>
                                <span class="text-sm font-bold text-slate-400 group-has-[:checked]:text-slate-500 transition-colors uppercase tracking-tight"><?= $option['desc'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['availed_dswd_training'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['availed_dswd_training'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Bento Card: DSWD Courses (Conditional) -->
                <div id="coursesSection" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden transition-all duration-500 <?= ($savedData['availed_dswd_training'] ?? '') !== 'yes' ? 'hidden' : '' ?>">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Completed Courses <span class="text-red-500">*</span></h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Select all that apply</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $courseOptions = [
                                'swb' => 'Social Welfare and Development Basic Course',
                                'case_mgmt_basic' => 'Case Management Basic Course',
                                'case_mgmt_adv' => 'Case Management Advanced Course',
                                'counseling_basic' => 'Basic Counseling Skills',
                                'crisis' => 'Crisis Intervention Training',
                                'disaster' => 'Disaster Response Training',
                                'child_protection' => 'Child Protection Training',
                                'vawc' => 'VAWC Response Training',
                                'pwd_services' => 'PWD Services Training',
                                'senior_citizens' => 'Senior Citizens Program Training',
                                '4ps_operations' => '4Ps Operations Training',
                                'slp_operations' => 'SLP Operations Training',
                                'leadership' => 'Leadership and Management Course',
                                'other' => 'Other Courses'
                            ];
                            $selectedCourses = $savedData['dswd_courses'] ?? [];
                            foreach ($courseOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-tight"><?= $label ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                    <input type="checkbox" name="dswd_courses[]" value="<?= $value ?>" 
                                        <?= in_array($value, $selectedCourses) ? 'checked' : '' ?> 
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['dswd_courses'])): ?>
                            <p class="mt-6 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['dswd_courses'][0]) ?></p>
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

<script>
function toggleCoursesSection(show) {
    const section = document.getElementById('coursesSection');
    if (show) {
        section.classList.remove('hidden');
        // Add a small delay for enter animation if needed
        setTimeout(() => {
            section.classList.add('animate-fade-in');
        }, 10);
    } else {
        section.classList.add('hidden');
    }
}
</script>
