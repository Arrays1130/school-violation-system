<x-app-layout>
    @section('header', 'Violations')

    <div class="space-y-6">
        {{-- Premium Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                        Student Disciplinary Board
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Violation Records</h1>
                    <p class="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage and track student violation cases, monitor disciplinary actions, and streamline institutional resolution processes.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('cases.create') }}" class="group relative px-6 py-3 bg-white text-indigo-950 rounded-xl text-sm font-bold shadow-lg shadow-black/20 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-white via-indigo-50 to-white opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <i data-lucide="plus-circle" class="w-4.5 h-4.5 relative z-10 text-indigo-600"></i>
                        <span class="relative z-10">New Record</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Overview Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            {{-- Total --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-slate-100 to-transparent opacity-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center shadow-inner group-hover:bg-slate-100 transition-colors">
                        <i data-lucide="folder-open" class="w-5.5 h-5.5 text-slate-600"></i>
                    </div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">Total</span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($summary['total']) }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">All recorded cases</p>
                </div>
            </div>
            
            {{-- Pending --}}
            <div class="bg-white rounded-2xl border border-amber-200/60 shadow-sm p-6 hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-amber-50 to-transparent opacity-80 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center shadow-inner group-hover:bg-amber-100 transition-colors">
                        <i data-lucide="clock" class="w-5.5 h-5.5 text-amber-600"></i>
                    </div>
                    <span class="text-[10px] font-extrabold text-amber-500 uppercase tracking-widest bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">Pending</span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($summary['pending']) }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Awaiting action</p>
                </div>
            </div>

            {{-- Hearing Scheduled --}}
            <div class="bg-white rounded-2xl border border-blue-200/60 shadow-sm p-6 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-50 to-transparent opacity-80 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center shadow-inner group-hover:bg-blue-100 transition-colors">
                        <i data-lucide="calendar-check" class="w-5.5 h-5.5 text-blue-600"></i>
                    </div>
                    <span class="text-[10px] font-extrabold text-blue-500 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">Hearing</span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($summary['hearing']) }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Scheduled hearings</p>
                </div>
            </div>

            {{-- Closed --}}
            <div class="bg-white rounded-2xl border border-emerald-200/60 shadow-sm p-6 hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-50 to-transparent opacity-80 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shadow-inner group-hover:bg-emerald-100 transition-colors">
                        <i data-lucide="check-circle-2" class="w-5.5 h-5.5 text-emerald-600"></i>
                    </div>
                    <span class="text-[10px] font-extrabold text-emerald-500 uppercase tracking-widest bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">Resolved</span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($summary['closed']) }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Cases closed</p>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-4 border border-gray-200/80 shadow-sm mb-6">
            <form method="GET" action="{{ route('cases.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="search" class="w-4.5 h-4.5"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student name or violation..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                </div>
                
                <div class="sm:w-56 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="layers" class="w-4.5 h-4.5"></i>
                    </div>
                    <select name="status" class="w-full pl-10 pr-10 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\StudentCase::STATUSES as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('cases.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Records List --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200">
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Date & Time</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Student Details</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Violation Type</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center whitespace-nowrap">Current Status</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($cases as $case)
                            <tr class="hover:bg-slate-50/60 transition-colors duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900">{{ $case->occurred_at->format('M d, Y') }}</span>
                                            <span class="text-[11px] font-semibold text-gray-500 mt-0.5">{{ $case->occurred_at->format('h:i A') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-blue-100 text-indigo-700 flex items-center justify-center font-bold text-sm border border-indigo-200 shadow-sm">
                                            {{ $case->student?->initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $case->student->full_name ?? 'Anonymous' }}</p>
                                            <p class="text-[11px] font-medium text-gray-500">{{ $case->student->department ?? 'Unassigned' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col max-w-xs">
                                        <span class="text-sm font-bold text-gray-900 truncate">{{ $case->violation->title ?? 'Undefined Infraction' }}</span>
                                        <span class="text-[11px] font-medium text-gray-500 mt-0.5 truncate">{{ $case->violation->category ?? 'General' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center">
                                    @php
                                        $smap = [
                                            'Pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Closed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Hearing Scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Endorsed to Grievance' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                        $style = $smap[$case->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                        
                                        $dotMap = [
                                            'Pending' => 'bg-amber-500',
                                            'Closed' => 'bg-emerald-500',
                                            'Hearing Scheduled' => 'bg-blue-500',
                                            'Endorsed to Grievance' => 'bg-rose-500',
                                        ];
                                        $dotStyle = $dotMap[$case->status] ?? 'bg-gray-500';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border shadow-sm {{ $style }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
                                        {{ $case->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('cases.show', $case) }}" 
                                           class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-sm transition-all duration-200" title="View Details">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('cases.edit', $case) }}" 
                                           class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 hover:shadow-sm transition-all duration-200" title="Edit Case">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete('delete-case-{{ $case->id }}', 'disciplinary record')" 
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:shadow-sm transition-all duration-200" title="Delete Case">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        <form id="delete-case-{{ $case->id }}" action="{{ route('cases.destroy', $case) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 max-w-sm mx-auto">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 mb-4 shadow-inner">
                                            <i data-lucide="clipboard-list" class="w-7 h-7"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-gray-900">No Records Found</h3>
                                        <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">No disciplinary records match your criteria. Adjust your filters or log a new case.</p>
                                        <a href="{{ route('cases.create') }}" class="mt-5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            Log First Case
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($cases->hasPages())
                <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/50">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
