<?php
/**
 * Admin Dashboard View
 */

$stats = $stats ?? [];
$recentResponses = $recentResponses ?? [];
?>

<div class="space-y-10">
    
    <!-- Stats Cards: Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
        
        <!-- Total Responses -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-600 mb-5 group-hover:bg-slate-900 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Total Completed</p>
                <p class="text-4xl font-black text-slate-900 font-display"><?= number_format($stats['total_responses'] ?? 0) ?></p>
            </div>
        </div>
        
        <!-- Completion Rate -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50/50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-5 group-hover:bg-dswd-blue group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Completion Rate</p>
                <div class="flex items-baseline space-x-1">
                    <p class="text-4xl font-black text-blue-600 font-display"><?= number_format($stats['completion_rate'] ?? 0, 1) ?></p>
                    <span class="text-xl font-black text-blue-600/60 font-display">%</span>
                </div>
            </div>
        </div>
        
        <!-- Consent Rate -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50/50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-5 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Consent Rate</p>
                <div class="flex items-baseline space-x-1">
                    <p class="text-4xl font-black text-indigo-600 font-display"><?= number_format($stats['consent_rate'] ?? 0, 1) ?></p>
                    <span class="text-xl font-black text-indigo-600/60 font-display">%</span>
                </div>
            </div>
        </div>
        
        <!-- This Week -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50/50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 mb-5 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">This Week</p>
                <p class="text-4xl font-black text-green-600 font-display"><?= number_format($stats['week_responses'] ?? 0) ?></p>
            </div>
        </div>
        
        <!-- Very Interested -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50/50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-5 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Very Interested</p>
                <p class="text-4xl font-black text-purple-600 font-display"><?= number_format($stats['very_interested'] ?? 0) ?></p>
            </div>
        </div>
        
        <!-- Will Apply -->
        <div class="card-premium p-6 group hover:shadow-premium transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50/50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-5 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Will Apply</p>
                <p class="text-4xl font-black text-amber-600 font-display"><?= number_format($stats['will_apply'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Demographics Overview: Balanced Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Age Distribution: Primary Visual (Col Span 8) -->
        <div class="lg:col-span-8 card-premium p-8 h-full flex flex-col justify-between">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-slate-900 font-display">Age Distribution</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Breakdown by age brackets</p>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="h-[280px] relative">
                <canvas id="ageChart"></canvas>
            </div>
        </div>

        <!-- Sex Distribution: Secondary Visual (Col Span 4) -->
        <div class="lg:col-span-4 card-premium p-8 h-full flex flex-col items-center">
            <div class="mb-8 w-full">
                <h3 class="text-lg font-black text-slate-900 font-display text-center">Sex Distribution</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mt-1">Gender demographics</p>
            </div>
            <div class="h-[280px] w-full relative">
                <canvas id="sexChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Analytics Row: Three Balanced Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Interests Level -->
        <div class="card-premium p-8 flex flex-col items-center">
            <div class="mb-8 w-full">
                <h3 class="text-lg font-black text-slate-900 font-display text-center">Interest Levels</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mt-1">Program sentiment</p>
            </div>
            <div class="h-[220px] w-full relative">
                <canvas id="interestChart"></canvas>
            </div>
        </div>

        <!-- Top Motivations -->
        <div class="card-premium p-8">
            <div class="mb-8">
                <h3 class="text-lg font-black text-slate-900 font-display">Top Motivations</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Drivers for interest</p>
            </div>
            <div class="h-[220px] relative">
                <canvas id="motivationsChart"></canvas>
            </div>
        </div>

        <!-- Key Challenges -->
        <div class="card-premium p-8">
            <div class="mb-8">
                <h3 class="text-lg font-black text-slate-900 font-display">Key Challenges</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Identified barriers</p>
            </div>
            <div class="h-[220px] relative">
                <canvas id="barriersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Office & Operational Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Office Types: Left (Col Span 4) -->
        <div class="lg:col-span-4 card-premium p-8 flex flex-col items-center">
            <div class="mb-8 w-full text-center">
                <h3 class="text-lg font-black text-slate-900 font-display">Office Types</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Work environment breakdown</p>
            </div>
            <div class="h-[220px] w-full relative">
                <canvas id="officeChart"></canvas>
            </div>
        </div>

        <!-- Recent Responses: Right (Col Span 8) -->
        <div class="lg:col-span-8 card-premium overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900 font-display">Recent Activity</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Latest submissions</p>
                </div>
                <a href="<?= appUrl('/admin/responses') ?>" class="inline-flex items-center px-4 py-2 bg-slate-50 hover:bg-slate-900 text-slate-600 hover:text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-300 group">
                    Full List 
                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Name</th>
                            <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Interest</th>
                            <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach (array_slice($recentResponses, 0, 4) as $response): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 font-black text-[10px] group-hover:bg-slate-900 group-hover:text-white transition-all">
                                        <?= strtoupper($response['first_name'][0] . $response['last_name'][0]) ?>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900 truncate max-w-[150px]">
                                        <?= htmlspecialchars(strtoupper($response['last_name'])) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <?php
                                $interestStyles = match($response['eteeap_interest']) {
                                    'very_interested' => ['text-emerald-600', 'Very Interested'],
                                    'interested' => ['text-blue-600', 'Interested'],
                                    'somewhat_interested' => ['text-amber-600', 'Somewhat'],
                                    default => ['text-slate-400', 'Not Specified']
                                };
                                ?>
                                <span class="text-[10px] font-black uppercase tracking-widest <?= $interestStyles[0] ?>">
                                    <?= $interestStyles[1] ?>
                                </span>
                            </td>
                            <td class="px-8 py-4 text-[10px] text-slate-400 font-medium font-mono"><?= date('M d', strtotime($response['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Full Table Section (Optional/Bottom) -->
    <div class="card-premium group hover:shadow-premium transition-all duration-500 relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-slate-50/50 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
        <div class="relative p-12 text-center">
            <div class="w-16 h-16 bg-slate-900 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-slate-900/10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 font-display uppercase tracking-tight">Comprehensive Data Management</h3>
            <p class="text-slate-500 max-w-lg mx-auto mb-8 font-medium">Access full survey results, detailed respondent profiles, and advanced filtering options in the dedicated response center.</p>
            <a href="<?= appUrl('/admin/responses') ?>" class="inline-flex items-center px-8 py-4 bg-slate-100 hover:bg-slate-900 text-slate-900 hover:text-white font-black uppercase tracking-widest text-xs rounded-2xl transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1">
                Explore Full Response Database
                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Scripts: Premium Styling -->
<script nonce="<?= cspNonceEscaped() ?>">   
document.addEventListener('DOMContentLoaded', function() {
    // Premium Chart.js Defaults
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 12;
    Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold', size: 12 };
    
    // Chart instances
    let interestChart, officeChart, ageChart, sexChart, motivationsChart, barriersChart;
    
    const formatLabel = (str) => {
        if (!str) return 'Unknown';

        const map = {
            // Sex
            male: 'Male',
            female: 'Female',
            prefer_not_to_say: 'Prefer not to say',

            // Age ranges (use en dash)
            '20-29': '20–29',
            '30-39': '30–39',
            '40-49': '40–49',
            '50-59': '50–59',
            '60+': '60+',

            // Office type
            central_office: 'Central Office',
            field_office: 'Field Office',
            attached_agency: 'Attached Agency',

            // Employment status
            permanent: 'Permanent',
            cos: 'COS',
            jo: 'JO',
            others: 'Others',

            // Work experience buckets
            lt5: '<5',
            '5-10': '5–10',
            '11-15': '11–15',
            '15+': '15+',
        };

        if (map[str]) return map[str];
        return String(str).split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    };
    
    // Sophisticated Color Palette
    const colors = {
        primary: '#003087',
        secondary: '#2563eb',
        accent1: '#6366f1',
        accent2: '#8b5cf6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        slate: ['#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0']
    };

    const chartPalettes = {
        gender: ['#3b82f6', '#ec4899', '#f43f5e'],
        interest: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        office: ['#003087', '#2563eb', '#60a5fa', '#93c5fd']
    };

    // Build same-origin URLs regardless of APP_URL config (avoids cookie/session mismatches like 127.0.0.1 vs localhost).
    const basePath = <?= json_encode((function () {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = is_string($scriptName) ? dirname($scriptName) : '';
        $dir = str_replace('\\', '/', $dir);
        if ($dir === '/' || $dir === '.' || $dir === '\\') return '';
        return rtrim($dir, '/');
    })()) ?>;
    const apiUrl = (path) => `${basePath}${path}`;
    
    function showLoading(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (canvas) canvas.parentElement.classList.add('animate-pulse', 'opacity-50');
    }
    
    function hideLoading(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (canvas) canvas.parentElement.classList.remove('animate-pulse', 'opacity-50');
    }
    
    // Fetch and render Demographics data (Sex, Office, Age)
    async function loadDemographics() {
        showLoading('sexChart');
        showLoading('officeChart');
        showLoading('ageChart');
        
        try {
            const response = await fetch(apiUrl('/api/stats/demographics'));
            const text = await response.text();
            let result;

            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON:', text);
                alert('Server Error (Demographics): ' + text.substring(0, 500));
                return;
            }
            
            if (result.success) {
                const data = result.data;
                
                // Sex Distribution
                if (data.sex_distribution) {
                    const sexLabels = data.sex_distribution.map(s => formatLabel(s.label));
                    const sexValues = data.sex_distribution.map(s => parseInt(s.value));
                    
                    sexChart = new Chart(document.getElementById('sexChart'), {
                        type: 'doughnut',
                        data: {
                            labels: sexLabels,
                            datasets: [{
                                data: sexValues,
                                backgroundColor: chartPalettes.gender,
                                hoverOffset: 15,
                                borderWidth: 0,
                                weight: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: { 
                                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold' } } }
                            }
                        }
                    });
                }
                
                // Office distribution
                if (data.office_type_distribution) {
                    const labels = data.office_type_distribution.map(o => formatLabel(o.label));
                    const values = data.office_type_distribution.map(o => parseInt(o.value));
                    
                    officeChart = new Chart(document.getElementById('officeChart'), {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: chartPalettes.office,
                                hoverOffset: 15,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold' } } }
                            }
                        }
                    });
                }
                
                // Age Distribution
                if (data.age_distribution) {
                    const labels = data.age_distribution.map(a => a.label || 'Unknown');
                    const values = data.age_distribution.map(a => parseInt(a.value));
                    
                    ageChart = new Chart(document.getElementById('ageChart'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Respondents',
                                data: values,
                                backgroundColor: colors.secondary,
                                borderRadius: 12,
                                barThickness: 40
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                y: { 
                                    beginAtZero: true, 
                                    grid: { display: true, color: '#f1f5f9', drawBorder: false },
                                    ticks: { stepSize: 1, padding: 10 }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { padding: 10 }
                                }
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load demographics charts:', error);
            alert('Error loading demographics: ' + error.message);
        }
        
        hideLoading('sexChart');
        hideLoading('officeChart');
        hideLoading('ageChart');
    }
    
    // Fetch and render Interest data (Interest Levels, Motivations, Barriers)
    async function loadInterestData() {
        showLoading('interestChart');
        showLoading('motivationsChart');
        showLoading('barriersChart');
        
        try {
            const response = await fetch(apiUrl('/api/stats/interest'));
            const text = await response.text();
            let result;

            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON:', text);
                alert('Server Error (Interest): ' + text.substring(0, 500));
                return;
            }
            
            if (result.success) {
                const data = result.data;
                
                // Interest Level
                if (data.interest_levels) {
                    const labels = data.interest_levels.map(i => formatLabel(i.label));
                    const values = data.interest_levels.map(i => parseInt(i.value));
                    
                    interestChart = new Chart(document.getElementById('interestChart'), {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: chartPalettes.interest,
                                hoverOffset: 15,
                                borderWidth: 0,
                                weight: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: { 
                                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold' } } }
                            }
                        }
                    });
                }
                
                // Top Motivations
                if (data.top_motivations) {
                    const labels = data.top_motivations.map(m => formatLabel(m.label));
                    const values = data.top_motivations.map(m => parseInt(m.value));
                    
                    motivationsChart = new Chart(document.getElementById('motivationsChart'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Selections',
                                data: values,
                                backgroundColor: colors.success,
                                borderRadius: 8,
                                barThickness: 16
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                x: { 
                                    beginAtZero: true, 
                                    grid: { display: true, color: '#f1f5f9', drawBorder: false },
                                    ticks: { stepSize: 1, padding: 10 }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { padding: 10 }
                                }
                            }
                        }
                    });
                }
                
                // Top Barriers
                if (data.top_barriers) {
                    const labels = data.top_barriers.map(b => formatLabel(b.label));
                    const values = data.top_barriers.map(b => parseInt(b.value));
                    
                    barriersChart = new Chart(document.getElementById('barriersChart'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Selections',
                                data: values,
                                backgroundColor: colors.danger,
                                borderRadius: 8,
                                barThickness: 16
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                x: { 
                                    beginAtZero: true, 
                                    grid: { display: true, color: '#f1f5f9', drawBorder: false },
                                    ticks: { stepSize: 1, padding: 10 }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { padding: 10 }
                                }
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load interest charts:', error);
            alert('Error loading interest data: ' + error.message);
        }
        
        hideLoading('interestChart');
        hideLoading('motivationsChart');
        hideLoading('barriersChart');
    }
    
    // Load all charts
    async function loadAllCharts() {
        await Promise.all([
            loadDemographics(),
            loadInterestData()
        ]);
    }
    
    // Initial load
    loadAllCharts();
    
    window.refreshDashboardCharts = loadAllCharts;
});
</script>

