<x-app-layout>
    @section('header', 'Student Profile')

    <div class="space-y-6">
        {{-- Profile Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 p-1 shadow-inner backdrop-blur-md">
                        <div class="w-full h-full rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                            {{ $student->initials }}
                        </div>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            Student Profile
                        </div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">{{ $student->full_name }}</h1>
                        <p class="text-indigo-100/70 text-sm mt-1 flex items-center gap-2">
                            <span>{{ $student->department }}</span>
                            <span class="w-1 h-1 rounded-full bg-indigo-400/50"></span>
                            <span>{{ $student->year_level }}</span>
                            <span class="w-1 h-1 rounded-full bg-indigo-400/50"></span>
                            <span>{{ $student->section }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('students.print', $student) }}" target="_blank" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="printer" class="w-4.5 h-4.5"></i>
                        Print
                    </a>
                    <a href="{{ route('students.edit', $student) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                        Edit
                    </a>
                    <a href="{{ route('cases.create', ['student_id' => $student->id]) }}" class="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4.5 h-4.5"></i>
                        Log Violation
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Details --}}
            <div class="space-y-6">
                {{-- Student Information --}}
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5">Student Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Email</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $student->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Guardian</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $student->guardian_name ?? 'Not Listed' }}</p>
                                @if($student->guardian_phone)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $student->guardian_phone }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="badge-check" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</p>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-100 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Active Enrollment
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Incident Summary Card --}}
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5">Incident Summary</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-3xl font-bold {{ $offenseSummary['total'] > 2 ? 'text-red-600' : ($offenseSummary['total'] > 0 ? 'text-amber-600' : 'text-green-600') }}">
                                {{ $offenseSummary['total'] }}
                            </p>
                            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mt-1">Total</p>
                        </div>
                        <div class="text-center border-x border-gray-200">
                            <p class="text-3xl font-bold text-amber-600">{{ $offenseSummary['minor'] }}</p>
                            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mt-1">Minor</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-red-600">{{ $offenseSummary['major'] }}</p>
                            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mt-1">Major</p>
                        </div>
                    </div>
                    {{-- Risk indicator --}}
                    @php
                        $riskLevel = $offenseSummary['total'] >= 5 ? 'High' : ($offenseSummary['total'] >= 2 ? 'Medium' : 'Low');
                        $riskColor = $offenseSummary['total'] >= 5 ? 'bg-red-50 text-red-700 border-red-200' : ($offenseSummary['total'] >= 2 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-green-50 text-green-700 border-green-200');
                    @endphp
                    <div class="mt-5 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Risk Level</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $riskColor }}">
                            {{ $riskLevel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Violation Timeline --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Violation Timeline</h2>
                    <a href="{{ route('cases.create', ['student_id' => $student->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add Incident
                    </a>
                </div>

                {{-- Timeline --}}
                <div class="relative">
                    @forelse($student->cases->sortByDesc('occurred_at') as $case)
                        <div class="flex gap-4 pb-6 last:pb-0 group">
                            {{-- Timeline line + dot --}}
                            <div class="flex flex-col items-center">
                                @php
                                    $dotColor = match($case->status) {
                                        'Pending' => 'bg-amber-500 ring-amber-100',
                                        'Hearing Scheduled' => 'bg-blue-500 ring-blue-100',
                                        'Closed' => 'bg-green-500 ring-green-100',
                                        'Endorsed to Grievance' => 'bg-red-500 ring-red-100',
                                        default => 'bg-gray-400 ring-gray-100',
                                    };
                                @endphp
                                <div class="w-3 h-3 rounded-full {{ $dotColor }} ring-4 mt-1.5 flex-shrink-0 z-10"></div>
                                @if(!$loop->last)
                                    <div class="w-0.5 flex-1 bg-gray-200 mt-1"></div>
                                @endif
                            </div>
                            {{-- Content Card --}}
                            <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm p-5 hover:border-blue-200 hover:shadow-md transition-all duration-200 -mt-0.5">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $case->violation->title }}</h4>
                                            @php
                                                $sevColor = match($case->violation->severity ?? 'Minor') {
                                                    'Minor' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                                    'Major' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                    'Critical' => 'bg-red-50 text-red-700 border-red-200',
                                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                                };
                                            @endphp
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $sevColor }}">{{ $case->violation->severity ?? 'Minor' }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 line-clamp-2">{{ $case->description }}</p>
                                        <div class="flex items-center gap-4 mt-2.5">
                                            <span class="text-[11px] font-medium text-gray-400 flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                {{ $case->occurred_at->format('M d, Y') }}
                                            </span>
                                            @php
                                                $smap = [
                                                    'Pending' => 'text-amber-600',
                                                    'Closed' => 'text-green-600',
                                                    'Hearing Scheduled' => 'text-blue-600',
                                                    'Endorsed to Grievance' => 'text-red-600',
                                                ];
                                                $color = $smap[$case->status] ?? 'text-gray-500';
                                            @endphp
                                            <span class="text-[11px] font-bold uppercase tracking-wider {{ $color }}">{{ $case->status }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('cases.show', $case) }}" class="p-2 text-gray-300 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all flex-shrink-0">
                                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg border border-dashed border-gray-300 p-12 text-center">
                            <div class="w-16 h-16 rounded-xl bg-green-50 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="shield-check" class="w-8 h-8 text-green-400"></i>
                            </div>
                            <h4 class="text-base font-semibold text-gray-900">Exemplary Conduct</h4>
                            <p class="text-sm text-gray-500 mt-1.5 max-w-xs mx-auto">No disciplinary records found. This student maintains an excellent behavioral record.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
