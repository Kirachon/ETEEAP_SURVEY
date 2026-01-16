<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'ETEEAP Survey Admin Dashboard') ?>">
    <?= csrfMetaTag() ?>
    
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | <?= APP_NAME ?></title>

    <!-- Compiled CSS (Tailwind build) -->
    <link rel="stylesheet" href="<?= assetUrl('app.css') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="<?= assetUrl('vendor/chart.umd.min.js') ?>" nonce="<?= cspNonceEscaped() ?>"></script>
    
    <?php if (isset($additionalHead)): ?>
        <?= $additionalHead ?>
    <?php endif; ?>
    <!-- Failsafe styles for mobile layout if external CSS fails -->
    <style>
        @media (max-width: 1023px) {
            #desktopSidebar { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    
    <div class="flex min-h-screen">
        
        <!-- Sidebar -->
        <aside id="desktopSidebar" class="hidden lg:flex lg:flex-col lg:w-72 glass-sidebar text-white shadow-2xl z-20 flex-shrink-0">
            <!-- Logo area -->
            <div class="p-8 border-b border-white/5">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-primary-500 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-500"></div>
                        <div class="relative w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-2xl transform group-hover:scale-105 transition-transform duration-300">
                            <span class="text-dswd-blue font-black text-2xl font-display">E</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="font-black text-xl tracking-tight leading-none text-white font-display">ETEEAP</h1>
                        <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-blue-300/60 mt-2">DASHBOARD</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-6 space-y-8 overflow-y-auto">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.25em] font-black text-white/20 mb-6 px-4">Core Management</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?= appUrl('/admin/dashboard') ?>" 
                               class="group flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all duration-300 <?= ($currentPage ?? '') === 'dashboard' ? 'nav-item-active text-white' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' ?>">
                                <div class="p-2 rounded-xl transition-colors duration-300 <?= ($currentPage ?? '') === 'dashboard' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-slate-400 group-hover:bg-white/10 group-hover:text-white' ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold tracking-tight">Overview</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= appUrl('/admin/responses') ?>" 
                               class="group flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all duration-300 <?= ($currentPage ?? '') === 'responses' ? 'nav-item-active text-white' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' ?>">
                                <div class="p-2 rounded-xl transition-colors duration-300 <?= ($currentPage ?? '') === 'responses' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-slate-400 group-hover:bg-white/10 group-hover:text-white' ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold tracking-tight">Responses</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-[11px] uppercase tracking-[0.25em] font-black text-white/20 mb-6 px-4">Data Operations</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?= appUrl('/admin/reports') ?>" 
                               class="group flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all duration-300 <?= ($currentPage ?? '') === 'reports' ? 'nav-item-active text-white' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' ?>">
                                <div class="p-2 rounded-xl transition-colors duration-300 <?= ($currentPage ?? '') === 'reports' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-slate-400 group-hover:bg-white/10 group-hover:text-white' ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-7 0h8m-8 0a2 2 0 01-2-2V7a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2m-8 0v2"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold tracking-tight">Reports</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= appUrl('/admin/import/csv') ?>"
                               class="group flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all duration-300 <?= ($currentPage ?? '') === 'import' ? 'nav-item-active text-white' : 'text-slate-400/80 hover:bg-white/5 hover:text-white' ?>">
                                <div class="p-2 rounded-xl transition-colors duration-300 <?= ($currentPage ?? '') === 'import' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-slate-400 group-hover:bg-white/10 group-hover:text-white' ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold tracking-tight">Import Data</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= appUrl('/admin/export/csv') ?>" 
                               class="group flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-400/80 hover:bg-white/5 hover:text-white transition-all duration-300">
                                <div class="p-2 rounded-xl bg-white/5 text-slate-400 group-hover:bg-white/10 group-hover:text-white transition-all duration-300">
                                    <svg class="w-5 h-5 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </div>
                                <span class="font-semibold tracking-tight">Export Data</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- User Section -->
            <div class="p-6 mt-auto">
                <div class="p-4 bg-white/5 rounded-3xl border border-white/5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-sm"><?= strtoupper(substr($adminUser['username'] ?? 'A', 0, 1)) ?></span>
                        </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-white leading-tight"><?= htmlspecialchars($adminUser['username'] ?? 'Admin') ?></span>
                        <span class="text-[10px] text-blue-400/60 font-medium">Administrator</span>
                    </div>
                </div>
                    <form method="POST" action="<?= appUrl('/admin/logout') ?>" class="inline">
                        <?= csrfInputField() ?>
                        <button type="submit"
                                class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all"
                                title="Sign Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            
            <!-- Top Navigation -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-40 flex items-center justify-between px-8">
                <div>
                    <h1 class="text-xl font-black text-slate-900 font-display flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-dswd-blue rounded-full"></span>
                        <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                    </h1>
                </div>
                
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center px-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl text-slate-500 text-sm font-medium font-mono" id="realtime-clock">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Loading time...</span>
                    </div>
                    
                    <!-- Mobile Menu Trigger -->
                    <button id="mobileMenuBtn" class="lg:hidden p-3 bg-slate-100 rounded-2xl text-slate-600 hover:bg-slate-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </header>


            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-8 pb-24 relative">
                <!-- Content Backdrop Accent -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl -z-10 animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-100/20 rounded-full blur-3xl -z-10 opacity-50"></div>
                
                <div class="max-w-[1600px] mx-auto">
                    <?php if (isset($content)): ?>
                        <?= $content ?>
                    <?php endif; ?>
                </div>
            </main>
            
            <!-- Page Footer (Sticky) -->
            <footer class="h-16 bg-white/95 backdrop-blur-sm border-t border-slate-200/60 px-8 flex items-center justify-between fixed bottom-0 left-0 lg:left-72 right-0 z-30">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">System Operational</span>
                </div>
                <div class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">
                    ETEEAP v<?= APP_VERSION ?> • DSWD &copy; <?= date('Y') ?> • Server: <?= date('Y-m-d H:i:s') ?>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Mobile Menu (off-canvas) - At document root for proper z-index -->
    <div id="mobileMenuBackdrop" class="lg:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-[90]"></div>
    <aside id="mobileMenu" class="lg:hidden fixed top-0 left-0 h-full w-80 max-w-[85vw] bg-slate-900 text-white shadow-2xl hidden z-[100]">
        <div class="p-6 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-2xl flex items-center justify-center">
                    <span class="text-dswd-blue font-black text-xl font-display">E</span>
                </div>
                <div>
                    <p class="font-black text-lg leading-none font-display">ETEEAP</p>
                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-blue-300/70 mt-2">MENU</p>
                </div>
            </div>
            <button id="mobileMenuCloseBtn" class="p-2 rounded-xl hover:bg-white/10 transition-colors" aria-label="Close menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <nav class="p-6 space-y-8 overflow-y-auto h-[calc(100%-72px)]">
            <div>
                <p class="text-[11px] uppercase tracking-[0.25em] font-black text-white/20 mb-4 px-2">Core</p>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= appUrl('/admin/dashboard') ?>" class="block px-4 py-3 rounded-2xl font-semibold <?= ($currentPage ?? '') === 'dashboard' ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">Overview</a>
                    </li>
                    <li>
                        <a href="<?= appUrl('/admin/responses') ?>" class="block px-4 py-3 rounded-2xl font-semibold <?= ($currentPage ?? '') === 'responses' ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">Responses</a>
                    </li>
                </ul>
            </div>

            <div>
                <p class="text-[11px] uppercase tracking-[0.25em] font-black text-white/20 mb-4 px-2">Data</p>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= appUrl('/admin/reports') ?>" class="block px-4 py-3 rounded-2xl font-semibold <?= ($currentPage ?? '') === 'reports' ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">Reports</a>
                    </li>
                    <li>
                        <a href="<?= appUrl('/admin/import/csv') ?>" class="block px-4 py-3 rounded-2xl font-semibold <?= ($currentPage ?? '') === 'import' ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">Import CSV</a>
                    </li>
                    <li>
                        <a href="<?= appUrl('/admin/export/csv') ?>" class="block px-4 py-3 rounded-2xl font-semibold text-slate-300 hover:bg-white/10 hover:text-white">Export CSV</a>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
    
    <?php if (flashHas('success')): ?>
    <div id="flash-success" class="fixed top-6 right-6 bg-white border-l-4 border-green-500 p-4 rounded-2xl shadow-premium animate-slide-in z-[100] flex items-center gap-4">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Success</p>
            <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars(flashGet('success')) ?></p>
        </div>
    </div>
    <script nonce="<?= cspNonceEscaped() ?>">setTimeout(() => document.getElementById('flash-success')?.remove(), 5000);</script>
    <?php endif; ?>

    <?php if (flashHas('error')): ?>
    <div id="flash-error" class="fixed top-6 right-6 bg-white border-l-4 border-red-500 p-4 rounded-2xl shadow-premium animate-slide-in z-[100] flex items-center gap-4">
        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </div>
        <div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Error</p>
            <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars(flashGet('error')) ?></p>
        </div>
    </div>
    <script nonce="<?= cspNonceEscaped() ?>">setTimeout(() => document.getElementById('flash-error')?.remove(), 5000);</script>
    <?php endif; ?>

    <script nonce="<?= cspNonceEscaped() ?>">
        // Mobile menu toggle
        const mobBtn = document.getElementById('mobileMenuBtn');
        const menu = document.getElementById('mobileMenu');
        const backdrop = document.getElementById('mobileMenuBackdrop');
        const closeBtn = document.getElementById('mobileMenuCloseBtn');

        function openMenu() {
            if (!menu || !backdrop) return;
            menu.classList.remove('hidden');
            backdrop.classList.remove('hidden');
        }

        function closeMenu() {
            if (!menu || !backdrop) return;
            menu.classList.add('hidden');
            backdrop.classList.add('hidden');
        }

        if (mobBtn) mobBtn.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (backdrop) backdrop.addEventListener('click', closeMenu);

        // Real-time clock (Asia/Manila)
        function updateClock() {
            const clockEl = document.querySelector('#realtime-clock span');
            if (clockEl) {
                const now = new Date();
                const options = { 
                    timeZone: 'Asia/Manila', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: true
                };
                clockEl.textContent = now.toLocaleString('en-US', options);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>
