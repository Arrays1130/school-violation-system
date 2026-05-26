<x-app-layout>
    @section('header', 'Case Details')

    <div class="space-y-6">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <a href="{{ route('students.show', $case->student) }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                        <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                            <i data-lucide="folder-open" class="w-3.5 h-3.5"></i>
                            Disciplinary Record
                        </div>
                        <h2 class="text-3xl font-bold text-white tracking-tight">Violation Case #{{ str_pad($case->id, 4, '0', STR_PAD_LEFT) }}</h2>
                        <p class="text-indigo-100/70 text-sm mt-1">Student Respondent: <span class="text-white font-medium">{{ $case->student->full_name }}</span></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('cases.edit', $case) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('cases.print', $case) }}" target="_blank" class="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                        <i data-lucide="printer" class="w-4.5 h-4.5"></i>
                        Print
                    </a>
                </div>
            </div>
        </div>

        {{-- ═══ CASE LIFECYCLE PIPELINE ═══ --}}
        @php
            $stages = ['Pending', 'Hearing Scheduled', 'Hearing', 'Closed'];
            $currentIndex = array_search($case->status, $stages);
            if ($currentIndex === false) $currentIndex = 0; // Fallback
            $isEndorsed = $case->status === 'Endorsed to Grievance';
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-8 mb-8 relative overflow-hidden">
            @if($isEndorsed)
                <div class="flex items-center justify-center gap-4 py-4">
                    <div class="w-14 h-14 rounded-full bg-rose-50 border-4 border-rose-100 flex items-center justify-center shadow-inner">
                        <i data-lucide="arrow-up-right" class="w-6 h-6 text-rose-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-rose-700">Endorsed to Grievance Committee</h3>
                        <p class="text-sm font-medium text-rose-500 mt-0.5">This case has been escalated and is no longer in standard processing.</p>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-100 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-emerald-400 rounded-full transition-all duration-1000" style="width: {{ count($stages) > 1 ? ($currentIndex / (count($stages) - 1)) * 100 : 0 }}%"></div>
                    
                    @foreach($stages as $i => $stage)
                        <div class="flex flex-col items-center relative z-10 group">
                            @if($i < $currentIndex)
                                {{-- Completed --}}
                                <div class="w-12 h-12 rounded-full bg-emerald-50 border-4 border-emerald-400 flex items-center justify-center shadow-md shadow-emerald-500/10">
                                    <i data-lucide="check" class="w-5 h-5 text-emerald-600"></i>
                                </div>
                            @elseif($i === $currentIndex)
                                {{-- Active --}}
                                <div class="w-12 h-12 rounded-full bg-indigo-600 border-4 border-white flex items-center justify-center shadow-lg shadow-indigo-600/30">
                                    <span class="w-3.5 h-3.5 rounded-full bg-white animate-pulse"></span>
                                </div>
                            @else
                                {{-- Future --}}
                                <div class="w-12 h-12 rounded-full bg-white border-4 border-gray-200 flex items-center justify-center group-hover:border-gray-300 transition-colors">
                                    <span class="w-3.5 h-3.5 rounded-full bg-gray-200 group-hover:bg-gray-300 transition-colors"></span>
                                </div>
                            @endif
                            <p class="text-xs font-bold mt-3 text-center whitespace-nowrap {{ $i <= $currentIndex ? ($i === $currentIndex ? 'text-indigo-700' : 'text-emerald-700') : 'text-gray-400' }}">
                                {{ $stage }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Summary --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Status & Violation Card --}}
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                                <i data-lucide="file-warning" class="w-5 h-5 text-red-500"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $case->violation->title }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $case->violation->category }} — Code: {{ $case->violation->code }}</p>
                            </div>
                        </div>
                        @php
                            $smap = [
                                'Pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Closed' => 'bg-green-50 text-green-700 border-green-200',
                                'Hearing Scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Endorsed to Grievance' => 'bg-red-50 text-red-700 border-red-200',
                            ];
                            $style = $smap[$case->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $style }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $case->status }}
                        </span>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Incident Description</h4>
                            <div class="bg-gray-50 rounded-lg p-4 text-gray-700 text-sm leading-relaxed border border-gray-100">
                                {{ $case->description }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Date & Time</h4>
                                <div class="flex items-center gap-2 text-gray-900 text-sm">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                    {{ $case->occurred_at->format('F d, Y') }}
                                </div>
                                <div class="flex items-center gap-2 text-gray-500 text-sm mt-1 ml-6">
                                    {{ $case->occurred_at->format('h:i A') }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Witnesses</h4>
                                <div class="flex items-center gap-2 text-gray-900 text-sm">
                                    <i data-lucide="user-check" class="w-4 h-4 text-gray-400"></i>
                                    {{ $case->witness ?? 'No Witness Logged' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hearing Protocol Hub --}}
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Case Hearings</h3>
                        @if($case->status !== 'Closed')
                            <a href="{{ route('hearings.create', ['case' => $case->id]) }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800 transition-colors flex items-center gap-2">
                                <i data-lucide="gavel" class="w-4 h-4"></i>
                                Schedule Hearing
                            </a>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse($case->hearings as $hearing)
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-blue-200 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Hearing #{{ $loop->iteration }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $hearing->scheduled_at->format('M d, Y') }} at {{ $hearing->scheduled_at->format('h:i A') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-xs font-medium text-gray-600">
                                        {{ $hearing->location ?? "Dean's Office" }}
                                    </span>
                                    <a href="{{ route('hearings.show', $hearing) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View Hearing">
                                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-10 text-center">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="calendar-x" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 text-sm font-medium">No Hearings Scheduled</p>
                                <p class="text-gray-400 text-xs mt-1">Schedule a hearing to begin the disciplinary process.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right Column: Case Info & Quick Actions --}}
            <div class="space-y-6">
                {{-- Quick Actions --}}
                @if($case->status !== 'Closed')
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50/50 rounded-full blur-3xl -z-10"></div>
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-5">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($case->status === 'Pending')
                            <a href="{{ route('hearings.create', ['case' => $case->id]) }}" class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl text-sm font-bold shadow-sm shadow-blue-500/5 hover:bg-blue-100 hover:-translate-y-0.5 transition-all duration-200">
                                <i data-lucide="calendar-plus" class="w-4.5 h-4.5"></i>
                                Schedule Hearing
                            </a>
                        @endif
                        <form action="{{ route('cases.endorse', $case) }}" method="POST" onsubmit="return confirm('Endorse this case to the Grievance Committee?')">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-sm font-bold shadow-sm shadow-amber-500/5 hover:bg-amber-100 hover:-translate-y-0.5 transition-all duration-200">
                                <i data-lucide="arrow-up-right" class="w-4.5 h-4.5"></i>
                                Endorse to Grievance
                            </button>
                        </form>
                        <form action="{{ route('cases.close', $case) }}" method="POST" onsubmit="return confirm('Close this case? This action marks it as resolved.')">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold shadow-sm shadow-emerald-500/5 hover:bg-emerald-100 hover:-translate-y-0.5 transition-all duration-200">
                                <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                                Close Case
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Student Info --}}
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-6">Student Information</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-lg border border-blue-100">
                            {{ $case->student?->initials }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $case->student->full_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $case->student->id_number }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Department</span>
                            <span class="text-sm font-medium text-gray-900">{{ $case->student->department }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Previous Incidents</span>
                            <span class="text-sm font-semibold {{ ($offenseSummary['total'] - 1) > 0 ? 'text-red-600' : 'text-green-600' }}">{{ max(0, $offenseSummary['total'] - 1) }} Cases</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5">
                            <span class="text-sm text-gray-500">Status</span>
                            <span class="text-sm font-medium text-green-600">Enrolled</span>
                        </div>
                    </div>

                    <a href="{{ route('students.show', $case->student) }}" class="mt-6 w-full py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
                        View Student Profile
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                </div>

                {{-- Severity --}}
                <div class="bg-red-50 rounded-lg border border-red-100 p-6">
                    <h3 class="text-xs font-semibold text-red-800 uppercase tracking-wider mb-4">Violation Severity</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-red-600 mb-1">Severity Level</p>
                            <p class="text-lg font-semibold text-red-900">{{ $case->violation->severity ?? 'Major' }} Offense</p>
                        </div>
                        <div class="pt-4 border-t border-red-200/50">
                            <p class="text-xs text-red-600 mb-1">Offense Count for Student</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-red-900">{{ $offenseSummary['total'] }}</p>
                                <p class="text-xs text-red-600">total ({{ $offenseSummary['minor'] }} minor, {{ $offenseSummary['major'] }} major)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
