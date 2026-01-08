<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Social Work Competencies
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Highlight your technical expertise and the social work interventions you perform daily.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="flex flex-col gap-8">
                <!-- Bento Card: Performs SW Tasks -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Perform SW Tasks? <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php 
                            $taskQuestOptions = [
                                'yes' => ['label' => 'Yes, I do', 'desc' => 'I perform social work interventions'],
                                'no' => ['label' => 'No, I don\'t', 'desc' => 'My role is purely administrative']
                            ];
                            foreach ($taskQuestOptions as $value => $option): 
                            ?>
                            <label class="group relative flex flex-col p-6 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $option['label'] ?></span>
                                    <input type="radio" name="performs_sw_tasks" value="<?= $value ?>" 
                                        <?= ($savedData['performs_sw_tasks'] ?? '') === $value ? 'checked' : '' ?> 
                                        onchange="toggleTasksSection(this.value === 'yes')"
                                        class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue">
                                </div>
                                <span class="text-sm font-bold text-slate-400 group-has-[:checked]:text-slate-500 transition-colors uppercase tracking-tight"><?= $option['desc'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['performs_sw_tasks'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['performs_sw_tasks'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Bento Card: SW Tasks (Conditional) -->
                <div id="tasksSection" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden transition-all duration-500 <?= ($savedData['performs_sw_tasks'] ?? '') !== 'yes' ? 'hidden' : '' ?>">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Intervensions Performed <span class="text-red-500">*</span></h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Select all that apply</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $taskOptions = [
                                'case_management' => 'Case Management',
                                'counseling' => 'Counseling and Guidance',
                                'assessment' => 'Psychosocial Assessment',
                                'home_visit' => 'Home Visits',
                                'group_work' => 'Group Work Facilitation',
                                'community_org' => 'Community Organizing',
                                'referral' => 'Referral and Coordination',
                                'monitoring' => 'Program Monitoring',
                                'documentation' => 'Case Documentation',
                                'crisis_intervention' => 'Crisis Intervention',
                                'advocacy' => 'Advocacy and Policy Development',
                                'training' => 'Training and Capacity Building',
                                'other' => 'Other Interventions'
                            ];
                            $selectedTasks = $savedData['sw_tasks'] ?? [];
                            foreach ($taskOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-tight"><?= $label ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                    <input type="checkbox" name="sw_tasks[]" value="<?= $value ?>" 
                                        <?= in_array($value, $selectedTasks) ? 'checked' : '' ?> 
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
                    </div>
                </div>

                <!-- Bento Card: Expertise Areas -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Areas of Expertise</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Optional</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $expertiseOptions = [
                                'family_child' => 'Family and Child Welfare',
                                'youth' => 'Youth Development',
                                'women' => 'Women\'s Rights and Gender',
                                'senior' => 'Senior Citizens Services',
                                'pwd' => 'Persons with Disabilities',
                                'ip' => 'Indigenous Peoples',
                                'disaster' => 'Disaster Response',
                                'community' => 'Community Development',
                                'crisis' => 'Crisis Intervention',
                                'livelihood' => 'Livelihood/Economic Empowerment'
                            ];
                            $selectedAreas = $savedData['expertise_areas'] ?? [];
                            foreach ($expertiseOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors mr-3 leading-tight"><?= $label ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                    <input type="checkbox" name="expertise_areas[]" value="<?= $value ?>" 
                                        <?= in_array($value, $selectedAreas) ? 'checked' : '' ?> 
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
function toggleTasksSection(show) {
    const section = document.getElementById('tasksSection');
    if (show) {
        section.classList.remove('hidden');
        setTimeout(() => {
            section.classList.add('animate-fade-in');
        }, 10);
    } else {
        section.classList.add('hidden');
    }
}
</script>
