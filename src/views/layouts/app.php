<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'ETEEAP Survey for DSWD Personnel') ?>">
    <?= csrfMetaTag() ?>
    
    <title><?= htmlspecialchars($pageTitle ?? 'ETEEAP Survey') ?> | <?= APP_NAME ?></title>

    <!-- Compiled CSS (Tailwind build) -->
    <link rel="stylesheet" href="<?= assetUrl('app.css') ?>">

    <!-- App JS (form normalization + small UX helpers) -->
    <script src="<?= assetUrl('app.js') ?>" defer></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <?php if (isset($additionalHead)): ?>
        <?= $additionalHead ?>
    <?php endif; ?>
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    
    <!-- Main Container -->
    <div class="flex flex-col min-h-screen">
        
        <!-- Header -->
        <header class="sticky top-0 z-50 glass border-b border-gray-200/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 mr-[-8px]">
                    <!-- Logo & Title -->
                    <div class="flex items-center space-x-4">
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-dswd-blue to-blue-500 rounded-full blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative w-10 h-10 bg-dswd-blue rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-lg">E</span>
                            </div>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold tracking-tight text-gray-900 leading-none">ETEEAP Survey</h1>
                            <p class="text-[10px] uppercase tracking-widest font-semibold text-gray-500 mt-1">BS Social Work Program</p>
                        </div>
                    </div>
                    
                    <!-- Progress Indicator (for survey pages) -->
                    <?php if (isset($currentStep) && isset($totalSteps)): ?>
                    <div class="flex flex-col items-end space-y-1.5 min-w-[120px]">
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] font-bold text-dswd-blue uppercase tracking-wider">Step <?= $currentStep ?> of <?= $totalSteps ?></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                                <div class="h-1.5 rounded-full transition-all duration-500 <?= $i <= $currentStep ? 'w-4 bg-dswd-blue shadow-[0_0_8px_rgba(0,48,135,0.4)]' : 'w-1.5 bg-gray-200' ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <?php if (isset($currentStep) && isset($totalSteps)): ?>
            <div class="h-[2px] w-full bg-gray-100/50 overflow-hidden">
                <progress class="survey-progress" value="<?= (int) $currentStep ?>" max="<?= (int) $totalSteps ?>"></progress>
            </div>
            <?php endif; ?>
        </header>
        
        <!-- Main Content -->
        <main class="flex-grow">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>&copy; <?= date('Y') ?> DSWD</span>
                        <span class="hidden sm:inline">•</span>
                        <span class="hidden sm:inline">Data Privacy Act (RA 10173) Compliant</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        ETEEAP Survey v<?= APP_VERSION ?>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Flash Messages -->
    <?php if (flashHas('success')): ?>
    <div id="flash-success" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span><?= htmlspecialchars(flashGet('success')) ?></span>
    </div>
    <script nonce="<?= cspNonceEscaped() ?>">setTimeout(() => document.getElementById('flash-success')?.remove(), 5000);</script>
    <?php endif; ?>
    
    <?php if (flashHas('error')): ?>
    <div id="flash-error" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span><?= htmlspecialchars(flashGet('error')) ?></span>
    </div>
    <script nonce="<?= cspNonceEscaped() ?>">setTimeout(() => document.getElementById('flash-error')?.remove(), 5000);</script>
    <?php endif; ?>
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>
