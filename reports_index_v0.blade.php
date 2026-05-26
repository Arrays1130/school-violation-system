<x-app-layout>
    @section('header', 'Disciplinary Reports')

    <div class="space-y-6 bg-white dark:bg-gray-800">>
        {{-- High-End Branded Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                        Administrative Insights
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Reports & Analytics</h1>
                    <p class="text-indigo-100/70 text-sm mt-1 max-w-2xl leading-relaxed">Generate comprehensive student offense summaries, download high-fidelity PDF documents, and compile structural analytics.</p>
                </div>

                {{-- Action Panel --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('reports.csv', request()->all()) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4.5 h-4.5 text-emerald-400"></i>
                        Export CSV
                    </a>
                    <a href="{{ route('reports.pdf', request()->all()) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="file-down" class="w-4.5 h-4.5 text-red-400"></i>
                        Export PDF
                    </a>
                    <a href="{{ route('reports.print', request()->all()) }}" target="_blank" class="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                        <i data-lucide="printer" class="w-4.5 h-4.5"></i>
                        Print Report
                    </a>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 shadow-sm">
            <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Search Student</label>
                    <div class="relative">
                        <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Name, course or student ID..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Department</label>
                    <div class="relative">
                        <select name="department" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 appearance-none">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Current Status</label>
                    <div class="relative">
                        <select name="status" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 appearance-none">
                            <option value="">All Statuses</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Hearing Scheduled" {{ request('status') == 'Hearing Scheduled' ? 'selected' : '' }}>Hearing Scheduled</option>
                            <option value="Endorsed to Grievance" {{ request('status') == 'Endorsed to Grievance' ? 'selected' : '' }}>Endorsed</option>
                            <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </div>

                <!-- Date To -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </div>

                <!-- Month -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Month</label>
                    <input type="month" name="date_month" value="{{ request('date_month') }}" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </div>

                <div class="sm:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Apply
                    </button>
                    <a href="{{ route('reports.index') }}" class="px-4 py-2.5 bg-white text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200 transition-all duration-200" title="Clear Filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Analytics Data List --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-left">
                    <thead>
                        <tr class="bg-gray-55/60">
                            <th scope="col" class="px-6 py-4.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider">Date & Timestamp</th>
                            <th scope="col" class="px-6 py-4.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider">Student Profile</th>
                            <th scope="col" class="px-6 py-4.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider">Violation Details</th>
                            <th scope="col" class="px-6 py-4.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider text-center">Lifecycle Status</th>
                            <th scope="col" class="px-6 py-4.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider text-right font-medium">Record Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        @forelse($cases as $case)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-950">{{ $case->occurred_at->format('M d, Y') }}</span>
                                        <span class="text-[11px] text-gray-400 font-semibold mt-0.5">{{ $case->occurred_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm border border-indigo-100/50 shadow-inner">
                                            {{ $case->student?->initials ?? '??' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-950 group-hover:text-indigo-650 transition-colors">{{ $case->student->full_name ?? 'Anonymous' }}</p>
                                            <p class="text-[11px] text-gray-450 font-semibold mt-0.5 uppercase tracking-wider">{{ $case->student->department ?? 'N/A' }} Department</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-gray-950 mb-1 leading-snug">{{ $case->violation->title ?? 'Undefined Infraction' }}</div>
                                    <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                        <i data-lucide="tag-2" class="w-3.5 h-3.5 text-gray-400"></i>
                                        Category: {{ $case->violation->category ?? 'Misc' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-center">
                                    @php
                                        $smap = [
                                            'Pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Closed' => 'bg-green-50 text-green-700 border-green-200',
                                            'Hearing Scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Endorsed to Grievance' => 'bg-red-50 text-red-700 border-red-200',
                                        ];
                                        $style = $smap[$case->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $style }} shadow-sm shadow-indigo-50/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ $case->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                    <a href="{{ route('cases.print', $case) }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-xs font-semibold hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 shadow-sm">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Print Record
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-450 mb-3 shadow-inner">
                                            <i data-lucide="clipboard-x" class="w-5.5 h-5.5"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">No Disciplinary Records Found</h3>
                                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Adjust filters, search keywords, or specify different departments to generate custom ledger results.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cases->hasPages())
                <div class="px-6 py-4.5 border-t border-gray-150">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>