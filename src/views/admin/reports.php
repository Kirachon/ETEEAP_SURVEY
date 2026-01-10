<?php
/**
 * Admin Reports Page
 * 
 * Dynamic Dashboard with Premium "Wow" Aesthetics along with 14 report types.
 * Features: Glassmorphism, Gradient Accents, Micro-animations, and Dynamic Interactivity.
 */

// Get report metadata from service
$reports = ReportGenerator::REPORTS;
$reportMeta = ReportGenerator::REPORT_META;
?>

<!-- Custom Animations & Styles -->
<style nonce="<?= cspNonceEscaped() ?>">
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.6);
    }
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Input & Select Focus Glow */
    .focus-glow:focus {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
</style>

<!-- Page Header with Ambient Dynamic Background -->
<div class="mb-10 relative group">
    <!-- Decorative Ambient Glows -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl pointer-events-none mix-blend-multiply animate-blob"></div>
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-purple-400/20 rounded-full blur-3xl pointer-events-none mix-blend-multiply animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none mix-blend-multiply animate-blob animation-delay-4000"></div>

    <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-6 z-10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                <span class="text-xs font-bold tracking-widest text-slate-500 uppercase">Analytics & Intelligence</span>
            </div>
            <h2 class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-slate-800 to-slate-600 font-display tracking-tight leading-[1.1] drop-shadow-sm">
                Reports Dashboard
            </h2>
            <p class="text-lg text-slate-500 mt-3 max-w-2xl font-medium leading-relaxed">
                Unlock actionable insights from ETEEAP survey data with real-time analytics.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-5 py-2.5 bg-white/60 backdrop-blur-md rounded-full border border-white/60 shadow-lg shadow-slate-200/50 flex items-center gap-2.5 transition-all hover:scale-105 hover:bg-white/80 cursor-default">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-black text-slate-700 uppercase tracking-widest">Live System</span>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Container -->
<div class="space-y-8 animate-fade-in-up">
    
    <!-- Controls Panel: Glassmorphism + Gradients -->
    <div class="glass-panel rounded-[2rem] shadow-2xl p-8 relative overflow-hidden transition-all duration-500 hover:shadow-blue-900/5">
        <!-- Subtle Top Gradient Border -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 opacity-80"></div>
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end relative z-10">
            <!-- Report Selector (Main Focus) -->
            <div class="lg:col-span-4 space-y-2 group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Select Analysis Report</label>
                <div class="relative">
                    <select id="reportSelector" class="w-full pl-5 pr-10 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:ring-0 focus:border-blue-500 focus-glow transition-all cursor-pointer hover:bg-white hover:shadow-md appearance-none">
                        <?php foreach ($reports as $reportType): 
                            $meta = $reportMeta[$reportType] ?? ['name' => $reportType];
                        ?>
                        <option value="<?= htmlspecialchars($reportType) ?>"><?= htmlspecialchars($meta['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            
            <!-- Global Filters Array -->
            <div class="lg:col-span-8">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <?php 
                    $filtersDefs = [
                        'filterSex' => 'Sex',
                        'filterAge' => 'Age Range',
                        'filterOffice' => 'Office',
                        'filterEmployment' => 'Employment',
                        'filterEducation' => 'Education'
                    ];
                    foreach ($filtersDefs as $id => $label): ?>
                    <div class="space-y-1.5 group">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-500 transition-colors"><?= $label ?></label>
                        <div class="relative">
                            <select id="<?= $id ?>" class="w-full pl-3 pr-8 py-2.5 bg-white/50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 focus:ring-0 focus:border-indigo-500 focus-glow transition-all hover:bg-white hover:shadow-sm appearance-none">
                                <option value="">All</option>
                                <?php if($id === 'filterSex'): ?>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="prefer_not_to_say">Prefer not to say</option>
                                <?php elseif($id === 'filterAge'): ?>
                                    <option value="20-29">20-29</option>
                                    <option value="30-39">30-39</option>
                                    <option value="40-49">40-49</option>
                                    <option value="50-59">50-59</option>
                                    <option value="60+">60+</option>
                                <?php elseif($id === 'filterOffice'): ?>
                                    <option value="central_office">Central Office</option>
                                    <option value="field_office">Field Office</option>
                                    <option value="attached_agency">Attached Agency</option>
                                <?php elseif($id === 'filterEmployment'): ?>
                                    <option value="permanent">Permanent</option>
                                    <option value="cos">COS</option>
                                    <option value="jo">JO</option>
                                    <option value="others">Others</option>
                                <?php elseif($id === 'filterEducation'): ?>
                                    <option value="high_school">High School</option>
                                    <option value="some_college">Some College</option>
                                    <option value="bachelors">Bachelor's</option>
                                    <option value="masters">Master's</option>
                                    <option value="doctoral">Doctoral</option>
                                <?php endif; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Actions & Active State -->
        <div class="flex flex-wrap items-center justify-between gap-4 mt-8 pt-6 border-t border-slate-100/60">
            <div class="flex items-center gap-4">
                <button type="button" id="clearFilters" class="group flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-400 hover:text-red-500 transition-colors">
                    <span class="w-5 h-5 rounded-lg bg-slate-100 group-hover:bg-red-50 flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </span>
                    Reset Filters
                </button>
                <div class="h-4 w-px bg-slate-200"></div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider">Active:</span>
                    <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 font-semibold" id="activeFiltersText">Default View</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="#" id="exportCsv" class="group relative px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 hover:border-slate-300 hover:shadow-lg hover:-translate-y-0.5 transition-all active:scale-95 flex items-center gap-2 overflow-hidden">
                    <span class="absolute right-0 top-0 bottom-0 w-1 bg-slate-200 group-hover:bg-slate-300 transition-colors"></span>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Export CSV</span>
                </a>
                
                <button type="button" id="refreshReport" class="group relative px-8 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-slate-900/20 hover:shadow-xl hover:shadow-slate-900/30 hover:-translate-y-0.5 transition-all active:scale-95 flex items-center gap-3 overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    <svg class="w-4 h-4 animate-spin-slow group-hover:animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="relative">Refresh Data</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- KPI Strip: Dynamic Cards -->
    <div id="kpiStrip" class="hidden animate-fade-in-up animation-delay-100">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5" id="kpiCards">
            <!-- KPI cards injected via JS -->
        </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up animation-delay-200">
        
        <!-- Visual Panel (Charts) -->
        <div class="lg:col-span-5 flex flex-col h-full">
            <div class="glass-card rounded-[2.5rem] p-8 h-full relative group">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg" id="chartTitle">Visual Analysis</h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Interactive data visualization</p>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    </div>
                </div>
                
                <div class="relative h-[400px] w-full flex items-center justify-center">
                    <canvas id="reportChart"></canvas>
                    
                     <!-- Loading Overlay -->
                    <div id="chartLoading" class="hidden absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center rounded-3xl transition-opacity duration-300 z-20">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest animate-pulse">Analyzing...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Panel (Tables) -->
        <div class="lg:col-span-7 flex flex-col h-full">
            <div class="glass-card rounded-[2.5rem] p-8 min-h-[500px]" id="tablesPanel">
                <div class="flex items-center justify-between mb-6">
                     <h3 class="font-bold text-slate-800 text-lg">Detailed Breakdown</h3>
                     <div class="flex gap-2">
                         <span class="w-2 h-2 rounded-full bg-red-400"></span>
                         <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                         <span class="w-2 h-2 rounded-full bg-green-400"></span>
                     </div>
                </div>
                
                <div id="tablesLoading" class="hidden h-full flex items-center justify-center py-20">
                     <div class="flex flex-col items-center gap-4">
                        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Fetching Data...</span>
                    </div>
                </div>
                
                <div id="tablesContent" class="space-y-8 custom-scrollbar overflow-y-auto max-h-[600px] pr-2">
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <p class="text-slate-400 font-medium">Select a report and click "Refresh Data" to begin.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Metadata -->
    <div class="glass-panel rounded-2xl px-8 py-5 flex flex-wrap items-center justify-between gap-6 text-xs animate-fade-in-up animation-delay-400">
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-blue-50 rounded-md text-blue-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div>
                    <span class="block font-black text-slate-400 uppercase tracking-widest text-[9px] mb-0.5">Source</span>
                    <span class="font-bold text-slate-700">ETEEAP Database</span>
                </div>
            </div>
            
            <div class="h-8 w-px bg-slate-200"></div>
            
             <div class="flex items-center gap-2">
                <div class="p-1.5 bg-emerald-50 rounded-md text-emerald-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <span class="block font-black text-slate-400 uppercase tracking-widest text-[9px] mb-0.5">Status</span>
                    <span class="font-bold text-slate-700">Live Production</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <span class="font-bold text-slate-400">Generated:</span>
            <span class="font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded text-[10px]"><?= date('Y-m-d H:i:s') ?></span>
        </div>
    </div>
</div>

<script nonce="<?= cspNonceEscaped() ?>">
(function() {
    // DOM Elements - Using distinct variable naming for clarity
    const ui = {
        selector: document.getElementById('reportSelector'),
        refreshBtn: document.getElementById('refreshReport'),
        exportBtn: document.getElementById('exportCsv'),
        clearBtn: document.getElementById('clearFilters'),
        kpiStrip: document.getElementById('kpiStrip'),
        kpiCards: document.getElementById('kpiCards'),
        tablesContent: document.getElementById('tablesContent'),
        tablesLoading: document.getElementById('tablesLoading'),
        chartCanvas: document.getElementById('reportChart'),
        chartTitle: document.getElementById('chartTitle'),
        chartLoading: document.getElementById('chartLoading'),
        activeFiltersText: document.getElementById('activeFiltersText'),
        filters: {
            sex: document.getElementById('filterSex'),
            age_range: document.getElementById('filterAge'),
            office_type: document.getElementById('filterOffice'),
            employment_status: document.getElementById('filterEmployment'),
            highest_education: document.getElementById('filterEducation')
        }
    };
    
    // Metadata from backend
    const reportMeta = <?= json_encode($reportMeta) ?>;
    let currentChart = null;
    
    // Premium Color Palettes for Charts/UI
    const theme = {
        colors: ['#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#06b6d4', '#6366f1'],
        gradients: [
            ['#3b82f6', '#1e40af'], // Blue
            ['#8b5cf6', '#5b21b6'], // Violet
            ['#ec4899', '#9d174d'], // Pink
            ['#f43f5e', '#9f1239'], // Rose
            ['#f59e0b', '#92400e'], // Amber
            ['#10b981', '#065f46'], // Emerald
            ['#06b6d4', '#155e75'], // Cyan
        ]
    };

    /**
     * Data Management
     */
    function getFiltersValues() {
        const result = {};
        for (const [key, el] of Object.entries(ui.filters)) {
            if (el.value) result[key] = el.value;
        }
        return result;
    }
    
    function updateActiveState() {
        const active = [];
        for (const [key, el] of Object.entries(ui.filters)) {
            if (el.value) {
                const label = el.options[el.selectedIndex].text;
                active.push(label);
            }
        }
        
        if (active.length > 0) {
            ui.activeFiltersText.textContent = active.join(' • ');
            ui.activeFiltersText.className = "px-3 py-1 rounded-lg bg-blue-100 text-blue-700 font-bold border border-blue-200 shadow-sm";
        } else {
            ui.activeFiltersText.textContent = "Default View";
            ui.activeFiltersText.className = "px-3 py-1 rounded-lg bg-slate-100 text-slate-500 font-bold border border-slate-200";
        }
        
        // Update URL for standard export
        const type = ui.selector.value;
        const params = new URLSearchParams({ type: type, ...getFiltersValues() });
        ui.exportBtn.href = '<?= appUrl('/admin/reports/export/') ?>' + encodeURIComponent(type) + '?' + params.toString();
    }
    
    /**
     * Chart Rendering with Verified Chart.js Availability
     */
    function renderChart(data, type) {
        if (!ui.chartCanvas) return;
        
        const ctx = ui.chartCanvas.getContext('2d');
        if (currentChart) {
            currentChart.destroy();
            currentChart = null;
        }

        // 1. Priority: Explicit Chart Data from Backend
        if (data.chart && data.chart.labels && data.chart.datasets) {
            ui.chartTitle.textContent = data.chart.title || 'Visual Analysis';
            
            // Enhance datasets with gradients if they don't have colors
            const enhancedDatasets = data.chart.datasets.map((ds, i) => {
                if (!ds.backgroundColor) {
                    if (data.chart.type === 'doughnut' || data.chart.type === 'pie') {
                        ds.backgroundColor = data.chart.labels.map((_, j) => {
                            const g = theme.gradients[j % theme.gradients.length];
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, g[0]);
                            gradient.addColorStop(1, g[1]);
                            return gradient;
                        });
                    } else {
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, '#3b82f6');
                        gradient.addColorStop(1, '#1d4ed8');
                        ds.backgroundColor = gradient;
                    }
                }
                // Ensure nice border radius for bars
                if (data.chart.type === 'bar' && !ds.borderRadius) {
                    ds.borderRadius = 8;
                }
                return ds;
            });

            currentChart = new Chart(ctx, {
                type: data.chart.type || 'bar',
                data: {
                    labels: data.chart.labels,
                    datasets: enhancedDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1000, easing: 'easeOutQuart' },
                    layout: { padding: 20 },
                    plugins: {
                        legend: {
                            display: ['doughnut', 'pie'].includes(data.chart.type),
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                font: { family: 'Inter', weight: '600', size: 11 },
                                color: '#64748b'
                            }
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1e293b',
                            bodyColor: '#334155',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: true
                        }
                    },
                    scales: ['doughnut', 'pie'].includes(data.chart.type) ? {
                        x: { display: false }, y: { display: false }
                    } : {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: { font: { size: 10, weight: '500' }, color: '#94a3b8' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: '600' }, color: '#64748b', maxRotation: 45, minRotation: 0 }
                        }
                    },
                    cutout: data.chart.type === 'doughnut' ? '75%' : 0
                }
            });
            return;
        }
        
        // 2. Fallback: Heuristic Data Extraction from First Section
        if (!data.sections || data.sections.length === 0 || !data.sections[0].rows || data.sections[0].rows.length === 0) {
            ui.chartTitle.textContent = "No Data Available";
            return;
        }
        
        const first = data.sections[0];
        ui.chartTitle.textContent = first.title || 'Visual Analysis';
        
        const labels = first.rows.map(r => r[0]);
        // Attempt to find the first numeric column after the label
        // This makes it more robust than just assuming index 1
        let valueIndex = 1;
        // Check first row to find a numeric value
        const testRow = first.rows[0];
        if (testRow) {
             for (let i = 1; i < testRow.length; i++) {
                 const val = parseFloat(String(testRow[i]).replace(/[^0-9.-]/g, ''));
                 if (!isNaN(val)) {
                     valueIndex = i;
                     break;
                 }
             }
        }

        const values = first.rows.map(r => parseFloat(String(r[valueIndex]).replace(/[^0-9.-]/g, '')) || 0);
        
        // SMART CHART TYPE: Doughnut ≤5, Horizontal Bar 6+
        const itemCount = labels.length;
        const chartType = itemCount <= 5 ? 'doughnut' : 'bar';
        const useHorizontal = itemCount > 5;
        
        // DYNAMIC HEIGHT for horizontal bars
        const chartContainer = ui.chartCanvas.parentElement;
        if (useHorizontal) {
            chartContainer.style.height = Math.max(350, itemCount * 38) + 'px';
        } else {
            chartContainer.style.height = '400px';
        }
        
        // Truncate labels, full in tooltip
        const truncate = (s, max = 30) => s && s.length > max ? s.substring(0, max) + '…' : s;
        const displayLabels = labels.map(l => truncate(l));
        
        // helper for gradients
        const createGradient = (color1, color2) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        };
        
        let bgColors;
        if (chartType === 'doughnut') {
            bgColors = values.map((_, i) => {
                const g = theme.gradients[i % theme.gradients.length];
                return createGradient(g[0], g[1]);
            });
        } else {
            bgColors = createGradient('#3b82f6', '#1d4ed8'); // Primary Blue Gradient
        }

        currentChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: displayLabels,
                datasets: [{
                    label: first.title,
                    data: values,
                    backgroundColor: bgColors,
                    borderWidth: 0,
                    borderRadius: chartType === 'bar' ? 6 : 0,
                    hoverOffset: 20,
                    barThickness: useHorizontal ? 22 : undefined,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                layout: { padding: 20 },
                plugins: {
                    legend: {
                        display: chartType === 'doughnut',
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'rectRounded',
                            padding: 20,
                            font: { family: 'Inter', weight: '600', size: 11 },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1e293b',
                        bodyColor: '#334155',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        boxPadding: 6,
                        displayColors: true,
                        callbacks: {
                            title: (items) => labels[items[0].dataIndex] || '',
                            labelTextColor: () => '#334155'
                        }
                    }
                },
                scales: chartType === 'bar' ? (useHorizontal ? {
                    // HORIZONTAL BAR
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#475569', padding: 8 },
                        afterFit: (axis) => { axis.width = 160; }
                    }
                } : {
                    // VERTICAL BAR
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#64748b', maxRotation: 45 }
                    }
                }) : { x: { display: false }, y: { display: false } },
                indexAxis: useHorizontal ? 'y' : 'x',
                cutout: chartType === 'doughnut' ? '70%' : 0
            }
        });
    }

    /**
     * Core Fetch Logic
     */
    async function loadData() {
        const type = ui.selector.value;
        const params = new URLSearchParams({ type: type, ...getFiltersValues() });
        
        // UI Loading States
        ui.tablesLoading.classList.remove('hidden');
        ui.chartLoading.classList.remove('hidden');
        ui.tablesContent.innerHTML = '';
        ui.kpiStrip.classList.add('hidden');
        ui.kpiCards.innerHTML = '';
        
        updateActiveState();

        try {
            const res = await fetch('<?= appUrl('/admin/reports/generate') ?>?' + params.toString());
            const json = await res.json();
            
            // Remove Loaders
            ui.tablesLoading.classList.add('hidden');
            ui.chartLoading.classList.add('hidden');
            
            if (json.success) {
                renderKPIs(json.data.summary);
                renderTables(json.data.sections);
                renderChart(json.data, type);
            } else {
                showError(json.error || 'Failed to load report data');
            }
        } catch (err) {
            ui.tablesLoading.classList.add('hidden');
            ui.chartLoading.classList.add('hidden');
            showError('Connection error. Please check your network.');
            console.error(err);
        }
    }
    
    function renderKPIs(summary) {
        if (!summary || Object.keys(summary).length === 0) return;
        
        ui.kpiStrip.classList.remove('hidden');
        let html = '';
        
        let i = 0;
        for (const [key, value] of Object.entries(summary)) {
            const label = key.replace(/_/g, ' ');
            const color = theme.colors[i % theme.colors.length];
            
            html += `
                <div class="bg-white/80 backdrop-blur-md rounded-2xl p-5 border border-white/50 shadow-lg shadow-blue-900/5 group hover:-translate-y-1 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1" style="background: ${color}"></div>
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-gradient-to-br from-white/0 to-slate-100/50 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    
                    <div class="relative z-10">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate mb-2" title="${label}">${label}</p>
                        <p class="text-3xl font-black text-slate-800 tracking-tight">${value}</p>
                    </div>
                </div>
            `;
            i++;
        }
        ui.kpiCards.innerHTML = html;
    }
    
    function renderTables(sections) {
        if (!sections || sections.length === 0) {
            ui.tablesContent.innerHTML = `<div class="text-center py-10 text-slate-400">No detailed records found.</div>`;
            return;
        }
        
        let html = '';
        sections.forEach((sec, idx) => {
            const accent = theme.colors[idx % theme.colors.length];
            
            let headers = '';
            if (sec.headers) {
                headers = sec.headers.map(h => 
                    `<th class="px-5 py-3 text-left text-[9px] font-black text-slate-500 uppercase tracking-widest">${h}</th>`
                ).join('');
            }
            
            let rows = '';
            (sec.rows || []).forEach((row) => {
                const cells = row.map((cell, cIdx) => {
                    const style = cIdx === 0 ? 'font-bold text-slate-800' : 'text-slate-600 font-medium';
                    return `<td class="px-5 py-3 ${style}">${cell}</td>`;
                }).join('');
                rows += `<tr class="hover:bg-blue-50/50 transition-colors border-b border-transparent hover:border-blue-100">${cells}</tr>`;
            });
            
            html += `
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-8 last:mb-0 group hover:shadow-md transition-shadow">
                    <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                        <div class="w-2 h-6 rounded-full" style="background: ${accent}"></div>
                        <h4 class="font-bold text-slate-800 text-sm">${sec.title}</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50/50 border-b border-slate-100"><tr>${headers}</tr></thead>
                            <tbody class="divide-y divide-slate-50">${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        });
        
        ui.tablesContent.innerHTML = html;
    }
    
    function showError(msg) {
        ui.tablesContent.innerHTML = `
            <div class="p-6 bg-red-50 rounded-2xl border border-red-100 flex items-center gap-4 text-red-600">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="font-bold text-sm">${msg}</span>
            </div>
        `;
    }

    // Event Binding
    ui.selector.addEventListener('change', loadData);
    ui.refreshBtn.addEventListener('click', loadData);
    ui.clearBtn.addEventListener('click', () => {
        for (const el of Object.values(ui.filters)) el.value = '';
        loadData();
    });
    
    // Auto-refresh when filters change
    Object.values(ui.filters).forEach(el => el.addEventListener('change', loadData));
    
    // Initial Load
    loadData();

})();
</script>
