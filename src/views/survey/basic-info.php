<?php
/**
 * Step 2: Basic Information
 */

$currentStep = $currentStep ?? 2;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];

$extNameOptions = [
    '' => 'None',
    'Jr.' => 'Jr.',
    'Sr.' => 'Sr.',
    'II' => 'II',
    'III' => 'III',
    'IV' => 'IV',
    'V' => 'V',
    'VI' => 'VI',
];
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto px-6 w-full animate-fade-in">
        
        <!-- Header Section -->
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 2: BASIC INFORMATION
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Please provide your basic information.
            </p>
        </div>
        
        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>
            
            <!-- Bento Card: Full Name -->
            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Full Name</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label for="last_name" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">2a. Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($savedData['last_name'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Dela Cruz" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['last_name'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- First Name -->
                        <div class="space-y-2">
                            <label for="first_name" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">2b. First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($savedData['first_name'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Juan" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['first_name'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Middle Name -->
                        <div class="space-y-2">
                            <label for="middle_name" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">2c. Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" value="<?= htmlspecialchars($savedData['middle_name'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold uppercase focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="e.g. Santos">
                            <?php if (isset($errors['middle_name'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['middle_name'][0]) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Ext Name -->
                        <div class="space-y-2">
                            <label for="ext_name" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">2d. Extension Name (e.g., Jr., Sr., III)</label>
                            <select name="ext_name" id="ext_name"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none">
                                <?php foreach ($extNameOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= ($savedData['ext_name'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['ext_name'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['ext_name'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Bento Card: Sex -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">3. Sex</h3>
                        <div class="space-y-3">
                            <?php 
                            $sexOptions = [
                                'male' => ['label' => 'Male', 'icon' => 'M'],
                                'female' => ['label' => 'Female', 'icon' => 'F'],
                                'prefer_not_to_say' => ['label' => 'Prefer not to say', 'icon' => '—']
                            ];
                            foreach ($sexOptions as $value => $option): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-400 group-has-[:checked]:text-dswd-blue group-has-[:checked]:border-dswd-blue transition-colors">
                                        <?= $option['icon'] ?>
                                    </div>
                                    <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $option['label'] ?></span>
                                </div>
                                <input type="radio" name="sex" value="<?= $value ?>" <?= ($savedData['sex'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['sex'])): ?>
                            <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['sex'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bento Card: Age Range -->
                <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                    <div class="p-8">
                        <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">4. Age Range</h3>
                        <div class="space-y-3">
                            <?php 
                            $ageOptions = [
                                '20-29' => '20–29',
                                '30-39' => '30–39',
                                '40-49' => '40–49',
                                '50-59' => '50–59',
                                '60+' => '60+'
                            ];
                            foreach ($ageOptions as $value => $label): 
                            ?>
                            <label class="group relative flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                <span class="text-base font-bold text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= $label ?></span>
                                <input type="radio" name="age_range" value="<?= $value ?>" <?= ($savedData['age_range'] ?? '') === $value ? 'checked' : '' ?> class="w-5 h-5 border-slate-300 text-dswd-blue focus:ring-dswd-blue">
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['age_range'])): ?>
                            <p class="mt-3 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['age_range'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Bento Card: Contact Details -->
            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Contact Details</h3>
                    </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">5. Email Address</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($savedData['email'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="juan.delacruz@dswd.gov.ph">
                            <?php if (isset($errors['email'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['email'][0]) ?></p>
                            <?php endif; ?>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2">Email validation applies if provided</p>
                        </div>

                        <!-- Mobile / Phone Number -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label for="phone" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">6. Mobile / Phone Number</label>
                                <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full uppercase tracking-widest">Optional</span>
                            </div>
                            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($savedData['phone'] ?? '') ?>" 
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none" 
                                placeholder="09XX XXX XXXX">
                            <?php if (isset($errors['phone'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['phone'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('surveyForm');
    if (!form) return;

    const normalizeSpaces = (value) => value.replace(/\s+/g, ' ').trim();

    form.addEventListener('submit', () => {
        // Names: trim + collapse spaces + uppercase
        for (const id of ['last_name', 'first_name', 'middle_name']) {
            const el = document.getElementById(id);
            if (!el || typeof el.value !== 'string') continue;
            const normalized = normalizeSpaces(el.value);
            el.value = normalized ? normalized.toUpperCase() : '';
        }

        // Email: trim only (do not uppercase)
        const email = document.getElementById('email');
        if (email && typeof email.value === 'string') {
            email.value = email.value.trim();
        }

        // Phone: trim only
        const phone = document.getElementById('phone');
        if (phone && typeof phone.value === 'string') {
            phone.value = phone.value.trim();
        }
    });
})();
</script>

<!-- Floating Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 z-40 glass border-t border-slate-200/50 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto p-6 md:p-8 flex items-center justify-between gap-4">
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
