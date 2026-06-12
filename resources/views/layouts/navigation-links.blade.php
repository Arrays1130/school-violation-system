{{-- Section: Overview --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Overview</p>
    <div class="space-y-0.5">
        @if(auth()->user()->isDean())
        <a href="{{ route('dean.dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('dean.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] {{ request()->routeIs('dean.dashboard') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Dashboard</span>
        </a>
        @else
        <a href="{{ route('dashboard') }}" 
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }} accessible-button"
           aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
            <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] {{ request()->routeIs('dashboard') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors" aria-hidden="true"></i>
            <span>Dashboard</span>
            <span class="sr-only">{{ request()->routeIs('dashboard') ? '(current page)' : '' }}</span>
        </a>
        @endif
    </div>
</div>

{{-- Section: Management --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Management</p>
    <div class="space-y-0.5">
        {{-- Students Group --}}
        @php $studentsActive = request()->routeIs('students.*'); @endphp
        <a href="{{ route('students.index') }}" 
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ $studentsActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }} accessible-button"
           aria-current="{{ $studentsActive ? 'page' : 'false' }}">
            <i data-lucide="graduation-cap" class="w-[18px] h-[18px] {{ $studentsActive ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors" aria-hidden="true"></i>
            <span>Students</span>
            <span class="sr-only">{{ $studentsActive ? '(current page)' : '' }}</span>
        </a>
        @if($studentsActive && !auth()->user()->isDean())
        <div class="ml-4 pl-3 border-l-2 border-indigo-200 space-y-0.5">
            <a href="{{ route('students.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-150 {{ request()->routeIs('students.index') ? 'text-indigo-700 bg-indigo-50' : 'text-gray-500 hover:text-indigo-600 hover:bg-slate-50' }}">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                All Students
            </a>
            <a href="{{ route('students.trash') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-150 {{ request()->routeIs('students.trash') ? 'text-red-700 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-red-50' }}">
                <span class="flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Trash Bin
                </span>
                @php
                    $trashedStudents = \Illuminate\Support\Facades\Cache::remember(
                        'nav.trashed_students.all',
                        now()->addSeconds(30),
                        fn () => \App\Models\Student::onlyTrashed()->count()
                    );
                @endphp
                @if($trashedStudents > 0)
                    <span class="min-w-[18px] h-4.5 px-1.5 flex items-center justify-center rounded-full text-[11px] font-bold bg-red-100 text-red-700">{{ $trashedStudents }}</span>
                @endif
            </a>
        </div>
        @endif

        {{-- Cases Group --}}
        @php
            $casesActive = request()->routeIs('cases.*');
            $user = auth()->user();
            $isDean = $user?->isDean() ?? false;
            $openCases = \Illuminate\Support\Facades\Cache::remember(
                'nav.open_cases.' . ($isDean ? ('dean.' . $user->department) : 'all'),
                now()->addSeconds(30),
                function () use ($isDean, $user) {
                    $q = \App\Models\StudentCase::query()->whereNotIn('status', ['Closed', 'Dismissed']);
                    if ($isDean) {
                        $q->forUser($user);
                    }
                    return $q->count();
                }
            );
        @endphp
        <a href="{{ route('cases.index') }}" class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ $casesActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <span class="flex items-center gap-3">
                <i data-lucide="folder-open" class="w-[18px] h-[18px] {{ $casesActive ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
                <span>Violation Cases</span>
            </span>
            @if($openCases > 0)
                <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full text-[11px] font-bold {{ $casesActive ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $openCases }}</span>
            @endif
        </a>
        @if($casesActive && !auth()->user()->isDean())
        <div class="ml-4 pl-3 border-l-2 border-indigo-200 space-y-0.5">
            <a href="{{ route('cases.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-150 {{ request()->routeIs('cases.index') ? 'text-indigo-700 bg-indigo-50' : 'text-gray-500 hover:text-indigo-600 hover:bg-slate-50' }}">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                All Cases
            </a>
            <a href="{{ route('cases.trash') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-150 {{ request()->routeIs('cases.trash') ? 'text-red-700 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-red-50' }}">
                <span class="flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    Trash Bin
                </span>
                @php
                    $trashedCases = \Illuminate\Support\Facades\Cache::remember(
                        'nav.trashed_cases.all',
                        now()->addSeconds(30),
                        fn () => \App\Models\StudentCase::onlyTrashed()->count()
                    );
                @endphp
                @if($trashedCases > 0)
                    <span class="min-w-[18px] h-4.5 px-1.5 flex items-center justify-center rounded-full text-[11px] font-bold bg-red-100 text-red-700">{{ $trashedCases }}</span>
                @endif
            </a>
        </div>
        @endif

        <a href="{{ route('violations.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('violations.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="shield-alert" class="w-[18px] h-[18px] {{ request()->routeIs('violations.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Rules & Violations</span>
        </a>
    </div>
</div>

{{-- Section: Records --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Records</p>
    <div class="space-y-0.5">
        <a href="{{ route('handbooks.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('handbooks.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="book-open" class="w-[18px] h-[18px] {{ request()->routeIs('handbooks.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Handbook</span>
        </a>

        <a href="{{ route('meeting-minutes.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('meeting-minutes.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="clipboard-list" class="w-[18px] h-[18px] {{ request()->routeIs('meeting-minutes.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Meeting Minutes</span>
        </a>

        <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reports.index') || request()->routeIs('reports.system') || request()->routeIs('reports.sanctions') || request()->routeIs('reports.print') || request()->routeIs('reports.pdf') || request()->routeIs('reports.csv') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="bar-chart-3" class="w-[18px] h-[18px] {{ request()->routeIs('reports.index') || request()->routeIs('reports.system') || request()->routeIs('reports.sanctions') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Reports</span>
        </a>

        <a href="{{ route('reports.retrieval') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reports.retrieval') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="database" class="w-[18px] h-[18px] {{ request()->routeIs('reports.retrieval') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Record Retrieval</span>
        </a>
    </div>
</div>

{{-- Section: System --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">System</p>
    <div class="space-y-0.5">
        @can('viewAny', App\Models\User::class)
        <a href="{{ route('users.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="users" class="w-[18px] h-[18px] {{ request()->routeIs('users.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>User Accounts</span>
        </a>
        @endcan

        @if(auth()->user()->isSuperAdmin() || auth()->user()->isDean())
        <a href="{{ route('reports.audit-logs') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reports.audit-logs') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="shield-check" class="w-[18px] h-[18px] {{ request()->routeIs('reports.audit-logs') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Audit Logs</span>
        </a>
        @endif

        <a href="{{ route('ai-assistant.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('ai-assistant.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="sparkles" class="w-[18px] h-[18px] {{ request()->routeIs('ai-assistant.*') ? '' : 'text-purple-500 group-hover:text-purple-600' }} transition-colors"></i>
            <span>AI Assistant</span>
            <span class="ml-auto px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider {{ request()->routeIs('ai-assistant.*') ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700' }}">Beta</span>
        </a>

        <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="settings" class="w-[18px] h-[18px] {{ request()->routeIs('profile.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Settings</span>
        </a>
    </div>
</div>
