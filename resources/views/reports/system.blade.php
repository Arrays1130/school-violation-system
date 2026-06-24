<x-app-layout>
    @section('header', 'System Reports')

    <div class="space-y-6 max-w-[1400px] mx-auto pb-12">

        {{-- Sub-header background element --}}
        <div class="absolute top-0 left-0 w-full h-[40vh] bg-slate-50 -z-10 border-b border-slate-200/50"></div>

        {{-- Header --}}
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6 pt-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('reports.index') }}"
                   class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                    <i data-lucide="arrow-left" class="w-4.5 h-4.5"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-widest mb-2">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        System Overview
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Reports</h1>
                    <p class="text-slate-500 mt-1 font-medium">Overview statistics — total cases, status breakdown & department analysis.</p>
                </div>
            </div>
            <div class="text-sm px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm text-slate-500 font-bold shrink-0">
                As of {{ now()->format('F d, Y') }}
            </div>
        </div>

        {{-- ─── Stat Cards ─── --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
            @php
                $cards = [
                    ['label' => 'Total Cases',    'value' => $total,    'icon' => 'folder-open',  'gradient' => 'from-blue-500 to-indigo-600', 'color' => 'text-blue-500'],
                    ['label' => 'Pending',         'value' => $pending,  'icon' => 'clock',        'gradient' => 'from-amber-500 to-orange-600', 'color' => 'text-amber-500'],
                    ['label' => 'Hearing',         'value' => $hearing,  'icon' => 'gavel',        'gradient' => 'from-indigo-500 to-violet-600', 'color' => 'text-indigo-500'],
                    ['label' => 'Endorsed',        'value' => $endorsed, 'icon' => 'send',         'gradient' => 'from-rose-500 to-pink-600', 'color' => 'text-rose-500'],
                    ['label' => 'Closed',          'value' => $closed,   'icon' => 'check-circle', 'gradient' => 'from-emerald-500 to-teal-600', 'color' => 'text-emerald-500'],
                ];
            @endphp

            @foreach($cards as $card)
                <div class="group relative bg-white rounded-3xl p-6 ring-1 ring-slate-200/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between min-h-[140px]">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br {{ $card['gradient'] }} opacity-[0.08] group-hover:opacity-[0.15] rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                    
                    <div class="flex items-start justify-between relative z-10 mb-4">
                        <div class="p-3.5 rounded-2xl bg-slate-50 ring-1 ring-slate-100 {{ $card['color'] }} group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                            <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </div>
                    
                    <div class="relative z-10">
                        <p class="text-[13px] font-bold text-slate-500 uppercase tracking-widest mb-1">{{ $card['label'] }}</p>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter tabular-nums">{{ number_format($card['value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ─── Bento Grid Layout ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 1. Monthly Trend Chart (Spans 2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Comparative Monthly Cases</h3>
                        <p class="text-sm font-medium text-slate-500">Minor vs Major infractions for {{ $currentYear }}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex-1 min-h-0 w-full relative">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            {{-- 2. Status Donut (Spans 1 Col) --}}
            <div class="bg-white rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px]">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Status Distribution</h3>
                        <p class="text-sm font-medium text-slate-500">Lifecycle of current cases</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex-1 min-h-0 w-full flex items-center justify-center relative mt-4">
                    <canvas id="statusChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-30px]">
                        <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ $total }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total</p>
                    </div>
                </div>
            </div>

            {{-- 3. Department Breakdown (Spans 2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px] overflow-hidden">
                <div class="p-8 pb-4 flex items-center justify-between bg-white z-10 border-b border-slate-50 flex-none">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Cases by Department</h3>
                        <p class="text-sm font-medium text-slate-500">Volume per college</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto no-scrollbar p-8 pt-4">
                    @if($byDepartment->isEmpty())
                        <div class="h-full flex items-center justify-center">
                            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest">No data available</p>
                        </div>
                    @else
                        @php $maxDept = $byDepartment->max('total'); @endphp
                        <div class="space-y-6">
                            @foreach($byDepartment as $row)
                                @php $pct = $maxDept > 0 ? round(($row->total / $maxDept) * 100) : 0; @endphp
                                <div class="group">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-bold text-slate-800">{{ $row->department ?: 'Unassigned' }}</span>
                                        <span class="text-sm font-black text-slate-900">{{ $row->total }} <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">cases</span></span>
                                    </div>
                                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000 group-hover:bg-indigo-600"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. Top Violations (Spans 1 Col) --}}
            <div class="bg-white rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px] overflow-hidden">
                <div class="p-8 pb-4 flex items-center justify-between border-b border-slate-50 flex-none">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Top Violations</h3>
                        <p class="text-sm font-medium text-slate-500">Most frequent infractions</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto no-scrollbar p-6 pt-4 space-y-3">
                    @if($topViolations->isEmpty())
                        <div class="h-full flex items-center justify-center">
                            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest">No violations</p>
                        </div>
                    @else
                        @foreach($topViolations as $i => $v)
                            @php
                                $sevMap = [
                                    'Minor'    => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'Major'    => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'Critical' => 'bg-rose-50 text-rose-700 border-rose-100',
                                ];
                                $sc = $sevMap[$v->severity] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 transition-colors group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-black text-slate-400 shrink-0 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-slate-900 truncate">{{ $v->title }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $sc }} tracking-wider uppercase">{{ $v->severity }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-slate-800">{{ $v->total }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const monthlyMinorData = @json(array_values($monthlyMinorData));
            const monthlyMajorData = @json(array_values($monthlyMajorData));

            // Chart base options for premium look
            const chartBaseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        titleFont: { size: 13, weight: 'bold', family: "'Inter', sans-serif" },
                        bodyFont: { size: 13, weight: 'normal', family: "'Inter', sans-serif" },
                        padding: 12,
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        displayColors: true,
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        boxPadding: 6,
                    }
                }
            };

            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            
            // Create Gradients for Bar Chart
            const minorGradient = ctxMonthly.createLinearGradient(0, 400, 0, 0);
            minorGradient.addColorStop(0, '#818cf8'); // indigo-400
            minorGradient.addColorStop(1, '#4f46e5'); // indigo-600

            const majorGradient = ctxMonthly.createLinearGradient(0, 400, 0, 0);
            majorGradient.addColorStop(0, '#fb7185'); // rose-400
            majorGradient.addColorStop(1, '#e11d48'); // rose-600

            // Monthly bar chart (Stacked Minor vs Major)
            new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Minor Cases',
                            data: monthlyMinorData,
                            backgroundColor: minorGradient,
                            borderWidth: 0,
                            borderRadius: 6,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Major Cases',
                            data: monthlyMajorData,
                            backgroundColor: majorGradient,
                            borderWidth: 0,
                            borderRadius: 6,
                            barPercentage: 0.6,
                        }
                    ]
                },
                options: {
                    ...chartBaseOptions,
                    plugins: {
                        ...chartBaseOptions.plugins,
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#64748b',
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        x: { 
                            stacked: true, 
                            grid: { display: false }, 
                            ticks: { font: { size: 11, weight: 'bold', family: "'Inter', sans-serif" }, color: '#94a3b8' },
                            border: { display: false }
                        },
                        y: { 
                            stacked: true, 
                            beginAtZero: true, 
                            grid: { color: 'rgba(241, 245, 249, 0.5)', drawBorder: false, borderDash: [5, 5] }, 
                            ticks: { stepSize: 1, font: { size: 11, weight: 'bold', family: "'Inter', sans-serif" }, color: '#94a3b8' },
                            border: { display: false }
                        }
                    }
                }
            });

            // Status donut chart
            new Chart(document.getElementById('statusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending','Hearing','Endorsed','Closed'],
                    datasets: [{
                        data: [{{ $pending }}, {{ $hearing }}, {{ $endorsed }}, {{ $closed }}],
                        backgroundColor: ['#f59e0b', '#4f46e5', '#e11d48', '#10b981'], // amber, indigo, rose, emerald
                        borderWidth: 6,
                        borderColor: '#ffffff',
                        borderRadius: 8,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    ...chartBaseOptions,
                    cutout: '75%',
                    plugins: {
                        ...chartBaseOptions.plugins,
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#64748b',
                                padding: 20
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
