<x-app-layout>
    @section('header', 'Sanctions Report')

    <div class="space-y-6 max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-emerald-950 p-8 shadow-xl border border-emerald-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_55%)]"></div>
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl"></div>

            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('reports.index') }}"
                       class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all">
                        <i data-lucide="arrow-left" class="w-4.5 h-4.5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            Disciplinary Outcomes
                        </div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Sanctions Report</h1>
                        <p class="text-emerald-100/60 text-sm mt-1">Track imposed sanctions, compliance status, and sanction outcomes per student.</p>
                    </div>
                </div>
                <div class="text-sm text-white/50 font-medium shrink-0">As of {{ now()->format('F d, Y') }}</div>
            </div>
        </div>

        {{-- ─── Stat Cards ─── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $statCards = [
                    ['label' => 'Total Sanctions',  'value' => $totalSanctions,   'icon' => 'gavel',        'color' => 'indigo'],
                    ['label' => 'Sanction Served',  'value' => $sanctionsServed,  'icon' => 'check-circle-2','color' => 'emerald'],
                    ['label' => 'Pending Sanction', 'value' => $sanctionsPending, 'icon' => 'clock-4',      'color' => 'amber'],
                    ['label' => 'Compliance Rate',  'value' => $complianceRate.'%','icon' => 'percent',     'color' => 'blue'],
                ];
                $pal = [
                    'indigo'  => ['bg'=>'bg-indigo-50',  'icon'=>'text-indigo-600',  'border'=>'border-indigo-100',  'num'=>'text-indigo-700'],
                    'emerald' => ['bg'=>'bg-emerald-50', 'icon'=>'text-emerald-600', 'border'=>'border-emerald-100', 'num'=>'text-emerald-700'],
                    'amber'   => ['bg'=>'bg-amber-50',   'icon'=>'text-amber-600',   'border'=>'border-amber-100',   'num'=>'text-amber-700'],
                    'blue'    => ['bg'=>'bg-blue-50',    'icon'=>'text-blue-600',    'border'=>'border-blue-100',    'num'=>'text-blue-700'],
                ];
            @endphp
            @foreach($statCards as $card)
                @php $p = $pal[$card['color']]; @endphp
                <div class="bg-white border {{ $p['border'] }} rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-9 h-9 rounded-xl {{ $p['bg'] }} flex items-center justify-center mb-3">
                        <i data-lucide="{{ $card['icon'] }}" class="w-4.5 h-4.5 {{ $p['icon'] }}"></i>
                    </div>
                    <div class="text-3xl font-extrabold {{ $p['num'] }} tabular-nums">{{ $card['value'] }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Compliance Bar --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-bold text-gray-700">Overall Sanction Compliance</span>
                <span class="text-sm font-extrabold text-emerald-600">{{ $complianceRate }}%</span>
            </div>
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700"
                     style="width: {{ $complianceRate }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1.5">
                <span>{{ $sanctionsServed }} served</span>
                <span>{{ $sanctionsPending }} pending</span>
            </div>
        </div>

        {{-- ─── Filters ─── --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <form action="{{ route('reports.sanctions') }}" method="GET"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Department</label>
                    <select name="department" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Severity</label>
                    <select name="severity" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none">
                        <option value="">All Severities</option>
                        <option value="Minor"  {{ request('severity') == 'Minor'  ? 'selected' : '' }}>Minor</option>
                        <option value="Major"  {{ request('severity') == 'Major'  ? 'selected' : '' }}>Major</option>
                        <option value="Critical" {{ request('severity') == 'Critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sanction Status</label>
                    <select name="sanction_status" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none">
                        <option value="">All</option>
                        <option value="served"  {{ request('sanction_status') == 'served'  ? 'selected' : '' }}>✅ Sanction Served</option>
                        <option value="pending" {{ request('sanction_status') == 'pending' ? 'selected' : '' }}>⏳ Pending Sanction</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                </div>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                </div>

                <div class="flex gap-2 lg:col-span-5">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i> Apply Filters
                    </button>
                    @if(request()->anyFilled(['department','severity','sanction_status','date_from','date_to']))
                        <a href="{{ route('reports.sanctions') }}"
                           class="px-4 py-2.5 bg-white text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-lg flex items-center gap-2 border border-gray-200 transition-all text-sm font-medium">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ─── Table ─── --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-gray-400"></i>
                    <span class="text-sm font-bold text-gray-700">
                        {{ number_format($cases->total()) }} record{{ $cases->total() !== 1 ? 's' : '' }} found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Violation</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Offense</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sanction Imposed</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date Closed</th>
                            <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($cases as $case)
                            @php
                                $served   = $case->status === 'Closed';
                                $sevColor = match($case->violation?->severity) {
                                    'Minor'    => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'Major'    => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'Critical' => 'bg-red-50 text-red-700 border-red-100',
                                    default    => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors group">
                                {{-- Student --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shrink-0">
                                            {{ $case->student?->initials ?? '??' }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ $case->student?->full_name ?? 'Unknown' }}</div>
                                            <div class="text-[11px] text-gray-400 uppercase tracking-wide">{{ $case->student?->student_id ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Violation --}}
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-800 leading-snug max-w-[180px] truncate" title="{{ $case->violation?->title }}">
                                        {{ $case->violation?->title ?? 'N/A' }}
                                    </div>
                                    <span class="inline-flex mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $sevColor }}">
                                        {{ $case->violation?->severity ?? '—' }}
                                    </span>
                                </td>

                                {{-- Offense Level --}}
                                <td class="px-5 py-4">
                                    @php
                                        $lvl = $case->offense_level ?? 1;
                                        $sfx = match(true) {
                                            $lvl % 100 >= 11 && $lvl % 100 <= 13 => 'th',
                                            $lvl % 10 === 1 => 'st',
                                            $lvl % 10 === 2 => 'nd',
                                            $lvl % 10 === 3 => 'rd',
                                            default => 'th',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">
                                        <i data-lucide="hash" class="w-3 h-3"></i>
                                        {{ $lvl.$sfx }} Offense
                                    </span>
                                </td>

                                {{-- Sanction --}}
                                <td class="px-5 py-4 max-w-[220px]">
                                    @if($case->sanction)
                                        <div class="text-sm text-gray-700 leading-snug">{{ $case->sanction }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No sanction recorded</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center">
                                    @if($served)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-xs font-semibold">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Served
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-full text-xs font-semibold">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i> Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- Date Closed --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($case->closed_at)
                                        <div class="text-sm font-medium text-gray-700">{{ $case->closed_at->format('M d, Y') }}</div>
                                        <div class="text-[11px] text-gray-400">by {{ $case->closedByUser?->name ?? '—' }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Open</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('cases.show', $case) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-xs font-semibold hover:bg-gray-50 hover:text-indigo-700 transition-all shadow-sm">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-3">
                                            <i data-lucide="shield-off" class="w-7 h-7 text-gray-300"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-500">No sanction records found</p>
                                        <p class="text-xs text-gray-400 mt-1">Try adjusting your filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cases->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>
    </div>


</x-app-layout>
