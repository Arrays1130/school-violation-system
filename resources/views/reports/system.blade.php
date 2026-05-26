<x-app-layout>
    @section('header', 'System Reports')

    <div class="space-y-6 max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-violet-950 p-8 shadow-xl border border-violet-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(139,92,246,0.18),_transparent_55%)]"></div>
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-violet-500/10 blur-3xl"></div>

            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('reports.index') }}"
                       class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all">
                        <i data-lucide="arrow-left" class="w-4.5 h-4.5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            System Overview
                        </div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">System Reports</h1>
                        <p class="text-violet-100/60 text-sm mt-1">Overview statistics — total cases, status breakdown & department analysis.</p>
                    </div>
                </div>
                <div class="text-sm text-white/50 font-medium shrink-0">
                    As of {{ now()->format('F d, Y') }}
                </div>
            </div>
        </div>

        {{-- ─── Stat Cards ─── --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

            @php
                $cards = [
                    ['label' => 'Total Cases',    'value' => $total,    'icon' => 'folder-open',  'color' => 'indigo'],
                    ['label' => 'Pending',         'value' => $pending,  'icon' => 'clock',        'color' => 'amber'],
                    ['label' => 'Hearing',         'value' => $hearing,  'icon' => 'gavel',        'color' => 'blue'],
                    ['label' => 'Endorsed',        'value' => $endorsed, 'icon' => 'send',         'color' => 'rose'],
                    ['label' => 'Closed',          'value' => $closed,   'icon' => 'check-circle', 'color' => 'emerald'],
                ];
                $palette = [
                    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'border' => 'border-indigo-100',  'num' => 'text-indigo-700'],
                    'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'border' => 'border-amber-100',   'num' => 'text-amber-700'],
                    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'border' => 'border-blue-100',    'num' => 'text-blue-700'],
                    'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-600',    'border' => 'border-rose-100',    'num' => 'text-rose-700'],
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'num' => 'text-emerald-700'],
                ];
            @endphp

            @foreach($cards as $card)
                @php $p = $palette[$card['color']]; @endphp
                <div class="bg-white border {{ $p['border'] }} rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl {{ $p['bg'] }} flex items-center justify-center">
                            <i data-lucide="{{ $card['icon'] }}" class="w-4.5 h-4.5 {{ $p['text'] }}"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold {{ $p['num'] }} tabular-nums">{{ number_format($card['value']) }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- ─── Two-column layout: Department Breakdown + Top Violations ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Department Breakdown --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Cases by Department</h2>
                </div>

                @if($byDepartment->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-8">No data available.</p>
                @else
                    @php $maxDept = $byDepartment->max('total'); @endphp
                    <div class="space-y-3">
                        @foreach($byDepartment as $row)
                            @php $pct = $maxDept > 0 ? round(($row->total / $maxDept) * 100) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                    <span class="truncate max-w-[70%]">{{ $row->department ?: 'Unassigned' }}</span>
                                    <span class="text-indigo-600">{{ $row->total }}</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-700"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Top Violations --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Top 5 Violations</h2>
                </div>

                @if($topViolations->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-8">No violations recorded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($topViolations as $i => $v)
                            @php
                                $sevMap = [
                                    'Minor'    => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'Major'    => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'Critical' => 'bg-red-50 text-red-700 border-red-100',
                                ];
                                $sc = $sevMap[$v->severity] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-xs font-extrabold text-gray-400 shrink-0">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-800 truncate">{{ $v->title }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ $v->code }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $sc }}">{{ $v->severity }}</span>
                                    </div>
                                </div>
                                <div class="text-lg font-extrabold text-gray-700 tabular-nums shrink-0">{{ $v->total }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Monthly Trend Chart ─── --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Monthly Cases — {{ $currentYear }}</h2>
            </div>
            <canvas id="monthlyChart" height="90"></canvas>
        </div>

        {{-- ─── Status Donut ─── --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-blue-600"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Status Distribution</h2>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-8">
                <div class="w-48 h-48 shrink-0">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="space-y-3 flex-1">
                    @php
                        $statusRows = [
                            ['label' => 'Pending',   'value' => $pending,  'color' => '#f59e0b'],
                            ['label' => 'Hearing',   'value' => $hearing,  'color' => '#3b82f6'],
                            ['label' => 'Endorsed',  'value' => $endorsed, 'color' => '#f43f5e'],
                            ['label' => 'Closed',    'value' => $closed,   'color' => '#10b981'],
                        ];
                    @endphp
                    @foreach($statusRows as $row)
                        @php $pct2 = $total > 0 ? round(($row['value'] / $total) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full shrink-0" style="background:{{ $row['color'] }}"></div>
                            <span class="text-sm font-semibold text-gray-700 w-24">{{ $row['label'] }}</span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width:{{ $pct2 }}%; background:{{ $row['color'] }}"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-600 tabular-nums w-8 text-right">{{ $row['value'] }}</span>
                            <span class="text-xs text-gray-400 w-8 text-right">({{ $pct2 }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const monthlyData = @json(array_values($monthlyData));

        // Monthly bar chart
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Cases',
                    data: monthlyData,
                    backgroundColor: 'rgba(99,102,241,0.15)',
                    borderColor: 'rgba(99,102,241,0.9)',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
                }
            }
        });

        // Status donut chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending','Hearing','Endorsed','Closed'],
                datasets: [{
                    data: [{{ $pending }}, {{ $hearing }}, {{ $endorsed }}, {{ $closed }}],
                    backgroundColor: ['#f59e0b','#3b82f6','#f43f5e','#10b981'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                }
            }
        });
    </script>
</x-app-layout>
