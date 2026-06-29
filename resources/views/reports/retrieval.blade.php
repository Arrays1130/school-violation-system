<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('reports.index') }}" class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="database" class="w-3.5 h-3.5"></i>
                        Record Retrieval
                    </div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Search Records</h2>
                    <p class="text-indigo-100/70 text-xs mt-1.5">Search through all past records with advanced filters.</p>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative overflow-hidden">
            <form action="{{ route('reports.retrieval') }}" method="GET" class="space-y-6 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Date Range -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Start Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">End Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Month</label>
                        <input type="month" name="date_month" value="{{ request('date_month') }}" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm">
                    </div>

                    <!-- Identity & Logic -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Violation Type</label>
                        <select name="violation_id" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm appearance-none cursor-pointer">
                            <option value="">All Violations</option>
                            @foreach($violations as $v)
                                <option value="{{ $v->id }}" {{ request('violation_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->code }} — {{ Str::limit($v->title, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Department</label>
                        <select name="department" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm appearance-none cursor-pointer">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                     <!-- Context Search -->
                     <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Search Student</label>
                        <div class="relative group">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Search by name or ID..." class="w-full pl-11 pr-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all placeholder-gray-400 text-sm">
                        </div>
                    </div>

                    <!-- Intensity Vector -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider pl-1">Severity Level</label>
                        <select name="severity" class="w-full px-4 py-3 bg-white border border-gray-300 text-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm appearance-none cursor-pointer">
                            <option value="">All Severities</option>
                            <option value="Minor" {{ request('severity') == 'Minor' ? 'selected' : '' }}>Minor</option>
                            <option value="Major" {{ request('severity') == 'Major' ? 'selected' : '' }}>Major</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 mt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs font-medium text-slate-500 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></span>
                        <span class="text-slate-800 font-bold">{{ $cases->total() }}</span> Records Found
                    </div>
                    
                    <div class="flex w-full sm:w-auto items-center gap-3">
                        @if(request()->anyFilled(['date_from', 'date_to', 'violation_id', 'department', 'student_search', 'severity']))
                            <a href="{{ route('reports.retrieval') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-3 bg-red-50 text-red-600 font-medium rounded-xl transition-all border border-transparent hover:border-red-200 text-sm" title="Clear Filters">
                                <i data-lucide="x" class="w-4 h-4 mr-2"></i> Clear Filters
                            </a>
                        @endif
                        <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all shadow-lg shadow-blue-500/25 active:scale-95 text-sm">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Violation Type</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($cases as $case)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-800 group-hover:text-blue-600 transition-colors">{{ $case->occurred_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-slate-500">{{ $case->occurred_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-800">
                                        {{ $case->student->full_name ?? 'Unknown Student' }}
                                    </div>
                                    <div class="text-xs text-slate-500 uppercase">
                                        {{ $case->student->department ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded uppercase border border-gray-200">
                                            {{ $case->violation->code }}
                                        </span>
                                        <div class="text-sm font-medium text-slate-800 truncate max-w-[200px]" title="{{ $case->violation->title }}">
                                            {{ $case->violation->title }}
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        @if($case->violation->severity === 'Minor') bg-blue-50 text-blue-700 border border-blue-100
                                        @elseif($case->violation->severity === 'Major') bg-amber-50 text-amber-700 border border-amber-100
                                        @else bg-red-50 text-red-700 border border-red-100 @endif">
                                        {{ $case->violation->severity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                                        {{ $case->status === 'Closed' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $case->status === 'Closed' ? 'bg-green-500' : 'bg-blue-500 shadow-[0_0_5px_currentColor]' }}"></span>
                                        {{ $case->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('cases.show', $case) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('cases.print', $case) }}" target="_blank" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Print Case">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 mb-4 rounded-2xl bg-gray-50 flex items-center justify-center border border-gray-100">
                                            <i data-lucide="database" class="w-8 h-8 text-gray-300"></i>
                                        </div>
                                        <p class="text-slate-400 text-sm">No records found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($cases->hasPages())
                <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                    {{ $cases->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
