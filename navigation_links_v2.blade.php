<div class="space-y-1">
    @if(auth()->user()->isDean())
    <a href="{{ route('dean.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('dean.dashboard') ? 'bg-blue-600 text-white' : 'text-g
<truncated 2766 bytes>