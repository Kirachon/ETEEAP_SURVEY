<?php
/**
 * Step 1: Data Privacy Consent
 * 
 * Adapted from stitch_consent_form/consent_form/code.html
 */

// Get view data
$currentStep = $currentStep ?? 1;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$csrfToken = $csrfToken ?? csrfGetToken();
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <!-- Main Content Area -->
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Text -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Data Privacy Consent
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Your privacy is our priority. Please review our data protection policies before beginning the survey.
            </p>
        </div>
        
        <!-- Bento Card: Consent Terms -->
        <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden relative">
            
            <!-- Visual Header inside Card -->
            <div class="relative py-8 px-6 lg:px-10 bg-gradient-to-br from-dswd-blue to-primary-600 overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
                <div class="relative flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shadow-inner border border-white/30 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-black text-white text-lg tracking-tight">Terms of Agreement</span>
                        <span class="block text-xs font-bold text-blue-100 uppercase tracking-widest mt-1 opacity-80">Legal Document</span>
                    </div>
                </div>
            </div>
            
            <!-- Terms Content -->
            <div class="p-6 lg:p-10 text-sm lg:text-base leading-relaxed text-slate-700 max-h-[450px] overflow-y-auto bg-white custom-scrollbar">
                <div class="space-y-6">
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100/50">
                        <p class="font-bold text-dswd-dark leading-snug">
                            To proceed with the ETEEAP Survey, we need to collect and process your personal data in accordance with Republic Act No. 10173, also known as the Data Privacy Act of 2012.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-black text-dswd-dark flex items-center gap-2">
                            <span class="w-6 h-6 bg-dswd-blue/10 text-dswd-blue rounded-full flex items-center justify-center text-[10px]">01</span>
                            Collection of Data
                        </h3>
                        <p class="text-justify pl-8">
                            We collect personal information such as your name, contact details, educational background, and employment history. This data is essential for understanding the interest and eligibility of DSWD personnel for the ETEEAP-BS Social Work Program.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-black text-dswd-dark flex items-center gap-2">
                            <span class="w-6 h-6 bg-dswd-blue/10 text-dswd-blue rounded-full flex items-center justify-center text-[10px]">02</span>
                            Use of Information
                        </h3>
                        <p class="text-justify pl-8">
                            Your data will be used solely for the purpose of survey analysis and program planning. It will not be shared with third parties without your explicit consent, except where required by law.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-black text-dswd-dark flex items-center gap-2">
                            <span class="w-6 h-6 bg-dswd-blue/10 text-dswd-blue rounded-full flex items-center justify-center text-[10px]">03</span>
                            Data Security
                        </h3>
                        <p class="text-justify pl-8">
                            We implement appropriate organizational, physical, and technical security measures to protect your personal data against unauthorized access, loss, or disclosure.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-black text-dswd-dark flex items-center gap-2">
                            <span class="w-6 h-6 bg-dswd-blue/10 text-dswd-blue rounded-full flex items-center justify-center text-[10px]">04</span>
                            Your Rights
                        </h3>
                        <p class="text-justify pl-8">
                            You have the right to access, correct, or request the deletion of your personal data at any time. By consenting, you acknowledge these rights and agree to our processing methods.
                        </p>
                    </div>
                </div>
                
                <p class="mt-8 text-xs text-slate-400 font-medium italic border-t border-slate-100 pt-4">
                    Document Version: 1.2 • Last updated: <?= date('F j, Y') ?>
                </p>
            </div>
        </div>
        </div>
        
        <!-- Error Display -->
        <?php if (!empty($errors)): ?>
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2 text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">Please select an option below.</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sticky Footer Actions -->
<div class="fixed bottom-0 left-0 right-0 z-40 glass border-t border-slate-200/50 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto p-6 md:p-8">
        <form method="POST" action="<?= appUrl('/survey/consent') ?>" class="flex flex-col gap-4">
            <?= csrfInputField() ?>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Agree Button -->
                <button type="submit" name="consent" value="yes" class="flex-1 group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                    <span>Agree & Start Survey</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
                
                <!-- Disagree Button -->
                <button type="submit" name="consent" value="no" class="group flex items-center justify-center gap-2 bg-slate-50 hover:bg-red-50 text-slate-500 hover:text-red-600 font-bold py-4 px-8 rounded-2xl border border-slate-200 hover:border-red-100 transition-all active:scale-[0.98]">
                    <span>Exit</span>
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">
                    Securely processed by DSWD ETEEAP Infrastructure
                </p>
            </div>
        </form>
    </div>
</div>
