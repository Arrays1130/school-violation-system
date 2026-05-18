{{-- Section: Overview --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">Overview</p>
    <div class="space-y-0.5">
        @if(auth()->user()->isDean())
        <a href="{{ route('dean.dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('dean.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] {{ request()->routeIs('dean.dashboard') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Dashboard</span>
        </a>
        @else
        <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="layout-dashboard" class="w-[18px] h-[18px] {{ request()->routeIs('dashboard') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Dashboard</span>
        </a>
        @endif
    </div>
</div>

{{-- Section: Management --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">Management</p>
    <div class="space-y-0.5">
        <a href="{{ route('students.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('students.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="graduation-cap" class="w-[18px] h-[18px] {{ request()->routeIs('students.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Students</span>
        </a>

        <a href="{{ route('cases.index') }}" class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('cases.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <span class="flex items-center gap-3">
                <i data-lucide="folder-open" class="w-[18px] h-[18px] {{ request()->routeIs('cases.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
                <span>Violation Cases</span>
            </span>
            @php $openCases = \App\Models\StudentCase::whereNotIn('status', ['Closed', 'Dismissed'])->count(); @endphp
            @if($openCases > 0)
                <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full text-[10px] font-bold {{ request()->routeIs('cases.*') ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $openCases }}</span>
            @endif
        </a>

        <a href="{{ route('violations.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('violations.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="shield-alert" class="w-[18px] h-[18px] {{ request()->routeIs('violations.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Rules & Violations</span>
        </a>
    </div>
</div>

{{-- Section: Records --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">Records</p>
    <div class="space-y-0.5">
        <a href="{{ route('handbooks.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('handbooks.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="book-open" class="w-[18px] h-[18px] {{ request()->routeIs('handbooks.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Handbook</span>
        </a>

        <a href="{{ route('meeting-minutes.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('meeting-minutes.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="clipboard-list" class="w-[18px] h-[18px] {{ request()->routeIs('meeting-minutes.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Meeting Minutes</span>
        </a>

        <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20' : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-lucide="bar-chart-3" class="w-[18px] h-[18px] {{ request()->routeIs('reports.*') ? '' : 'text-gray-400 group-hover:text-indigo-600' }} transition-colors"></i>
            <span>Reports</span>
        </a>
    </div>
</div>

{{-- Section: System --}}
<div class="mb-6">
    <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">System</p>
    <div class="space-y-0.5">
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
