<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                ETEEAP Interest
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your interest in the ETEEAP-BS Social Work Program and how we can support your journey.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Bento Card: ETEEAP Awareness -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Are you aware of ETEEAP? <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <?php 
                            $awarenessOptions = ['yes' => 'Yes', 'no' => 'No'];
                            foreach ($awarenessOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-center p-6 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-lg font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="eteeap_awareness" value="<?= $value ?>" <?= ($savedData['eteeap_awareness'] ?? '') === $value ? 'checked' : '' ?> class="sr-only">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['eteeap_awareness'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['eteeap_awareness'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Interest Level -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Interest Level <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="space-y-3">
                            <?php 
                            $interestOptions = [
                                'very_interested' => 'Very Interested',
                                'interested' => 'Interested',
                                'somewhat_interested' => 'Somewhat Interested',
                                'not_interested' => 'Not Interested'
                            ];
                            foreach ($interestOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="eteeap_interest" value="<?= $value ?>" <?= ($savedData['eteeap_interest'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['eteeap_interest'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['eteeap_interest'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Motivations -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden lg:col-span-2">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">What motivates you?</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Select all that apply</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $motivationOptions = [
                                'career' => 'Professional growth and advancement',
                                'recognition' => 'Recognition of work experience',
                                'promotion' => 'Eligibility for promotion',
                                'formal_degree' => 'To obtain a formal SW degree',
                                'competence' => 'To enhance professional competence',
                                'requirements' => 'To meet licensure requirements',
                                'personal' => 'Personal fulfillment',
                                'salary' => 'Salary increase opportunities'
                            ];
                            $selectedMotivations = $savedData['motivations'] ?? [];
                            foreach ($motivationOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                    <input type="checkbox" name="motivations[]" value="<?= $value ?>" <?= in_array($value, $selectedMotivations) ? 'checked' : '' ?> class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Barriers -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden lg:col-span-2">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Potential Barriers</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Select all that apply</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php 
                            $barrierOptions = [
                                'time' => 'Work schedule conflicts',
                                'financial' => 'Financial constraints',
                                'family' => 'Family responsibilities',
                                'location' => 'Distance/Location',
                                'info' => 'Lack of information',
                                'support' => 'Lack of org support',
                                'age' => 'Age-related concerns',
                                'health' => 'Health concerns',
                                'none' => 'No barriers'
                            ];
                            $selectedBarriers = $savedData['barriers'] ?? [];
                            foreach ($barrierOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <div class="flex-shrink-0 relative w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-dswd-blue transition-colors group-has-[:checked]:bg-dswd-blue group-has-[:checked]:border-dswd-blue">
                                    <input type="checkbox" name="barriers[]" value="<?= $value ?>" <?= in_array($value, $selectedBarriers) ? 'checked' : '' ?> class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <svg class="w-4 h-4 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Will Apply -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Intention to Apply <span class="text-red-500">*</span></h3>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <?php 
                            $applyOptions = ['yes' => 'Yes, absolutely', 'maybe' => 'I\'m considering it', 'no' => 'No, not at this time'];
                            foreach ($applyOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="will_apply" value="<?= $value ?>" <?= ($savedData['will_apply'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue focus:ring-offset-0">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['will_apply'])): ?>
                            <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['will_apply'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Additional Comments -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                </div>
                                <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Feedback</h3>
                            </div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-2 py-1 bg-slate-100 rounded">Optional</span>
                        </div>

                        <textarea 
                            id="additional_comments" 
                            name="additional_comments" 
                            rows="5"
                            placeholder="Share your thoughts or suggestions here..."
                            class="w-full p-5 rounded-2xl bg-slate-50 border border-slate-200 text-dswd-dark text-base font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all resize-none"
                        ><?= htmlspecialchars($savedData['additional_comments'] ?? '') ?></textarea>
                        <?php if (isset($errors['additional_comments'])): ?>
                            <p class="mt-2 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['additional_comments'][0]) ?></p>
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

        <!-- Submit Button -->
        <button type="submit" form="surveyForm" 
            class="flex-1 group relative flex items-center justify-center gap-3 bg-green-600 hover:bg-green-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-green-900/20 transition-all active:scale-[0.98]">
            <span>Submit Survey</span>
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </button>
    </div>
</div>
