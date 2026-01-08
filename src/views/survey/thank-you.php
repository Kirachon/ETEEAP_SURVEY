<?php
/**
 * Thank You / Declined Page
 * 
 * Shows different content based on $type ('success' or 'declined')
 */

$type = $type ?? 'success';
$isSuccess = $type === 'success';
?>

<div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 animate-fade-in">
    <div class="w-full max-w-xl mx-auto">
        
        <!-- Main Content Card -->
        <div class="bg-white rounded-[3rem] shadow-premium border border-slate-200/60 p-10 md:p-14 flex flex-col items-center text-center relative overflow-hidden group">
            <!-- Decorative Background Element -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-50 rounded-full blur-3xl opacity-50 group-hover:opacity-80 transition-opacity"></div>
            
            <!-- Status Icon -->
            <div class="mb-10 relative">
                <?php if ($isSuccess): ?>
                <!-- Success Icon -->
                <div class="absolute inset-0 bg-green-400/20 rounded-full scale-150 blur-2xl animate-pulse"></div>
                <div class="relative flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-50 to-green-100 rounded-[2rem] shadow-xl shadow-green-900/10 border border-green-200/50">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <?php else: ?>
                <!-- Declined Icon -->
                <div class="absolute inset-0 bg-slate-400/20 rounded-full scale-150 blur-2xl animate-pulse"></div>
                <div class="relative flex items-center justify-center w-24 h-24 bg-gradient-to-br from-slate-50 to-slate-100 rounded-[2rem] shadow-xl shadow-slate-900/10 border border-slate-200/50">
                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Headline -->
            <h2 class="text-3xl md:text-4xl font-black text-dswd-dark mb-6 tracking-tight leading-tight">
                <?= $isSuccess ? 'Response Received!' : 'Thank You' ?>
            </h2>
            
            <!-- Body Text -->
            <div class="max-w-md mx-auto">
                <?php if ($isSuccess): ?>
                <p class="text-slate-600 text-lg font-semibold leading-relaxed mb-4">
                    Your survey response has been successfully submitted. We appreciate your contribution to the ETEEAP-BS Social Work program.
                </p>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest leading-loose mb-10">
                    Your insights help DSWD support personnel growth.
                </p>
                <?php else: ?>
                <p class="text-slate-600 text-lg font-semibold leading-relaxed mb-10">
                    We understand your decision to decline consent. Your privacy is our priority. Thank you for visiting the ETEEAP survey portal.
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="w-full grid grid-cols-1 gap-4">
                <?php if ($isSuccess): ?>
                <a href="<?= appUrl('/') ?>" 
                    class="group flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-5 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Return to Homepage</span>
                </a>
                <?php else: ?>
                <a href="<?= appUrl('/survey/consent') ?>" 
                    class="group flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-5 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                    <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Take Survey Again</span>
                </a>
                <a href="<?= appUrl('/') ?>" 
                    class="group flex items-center justify-center gap-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-5 px-8 rounded-2xl transition-all active:scale-[0.98]">
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Homepage</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer / MetaText -->
        <div class="mt-12 text-center pointer-events-none">
            <p class="text-slate-400 text-xs font-black uppercase tracking-[0.2em] opacity-50">
                © <?= date('Y') ?> DSWD ETEEAP • SOCIAL WORK PROGRAM
            </p>
        </div>
    </div>
</div>
