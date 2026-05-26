<x-app-layout>
    @section('header', 'User Management')

    <div class="space-y-6">
        {{-- High-End Branded Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        System Administration
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">User Management</h1>
                    <p class="text-indigo-100/70 text-sm mt-1 max-w-2xl leading-relaxed">Manage system accounts, roles, and access permissions for administrators and deans.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Add New User
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800 font-semibold shadow-sm">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 px-5 py-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 font-semibold shadow-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Search & Filter Bar --}}
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
            <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Search Users</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email address..."
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="sm:w-52">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Filter by Role</label>
                    <div class="relative">
                        <select name="role" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 appearance-none">
                            <option value="">All Roles</option>
                            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="dean" {{ request('role') === 'dean' ? 'selected' : '' }}>Dean</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-white text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200 transition-all duration-200" title="Clear Filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-left">
                    <thead>
                        <tr class="bg-gray-50/60">
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Department</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Created</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($users as $user)
                            @php
                                $initials = collect(explode(' ', $user->name))
                                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                    ->take(2)
                                    ->implode('');

                                $roleConfig = match($user->role) {
                                    'super_admin' => ['label' => 'Super Admin', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                                    'admin'       => ['label' => 'Admin',       'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
                                    'dean'        => ['label' => 'Dean',        'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                                    default       => ['label' => ucfirst($user->role), 'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
                                };

                                $avatarColor = match($user->role) {
                                    'super_admin' => 'bg-purple-50 text-purple-600 border-purple-100',
                                    'admin'       => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                    'dean'        => 'bg-blue-50 text-blue-600 border-blue-100',
                                    default       => 'bg-gray-50 text-gray-600 border-gray-100',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl {{ $avatarColor }} flex items-center justify-center font-bold text-sm border shadow-inner shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-950 group-hover:text-indigo-650 transition-colors">{{ $user->name }}</p>
                                            <p class="text-[11px] text-gray-450 font-semibold mt-0.5">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $roleConfig['class'] }} shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ $roleConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->department)
                                        <span class="text-sm text-gray-700">{{ $user->department }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No department</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-700">{{ $user->created_at->format('M d, Y') }}</span>
                                        <span class="text-[11px] text-gray-400 mt-0.5">{{ $user->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition-all duration-200 shadow-sm"
                                           title="Edit User">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </a>

                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}? This action cannot be undone.')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-500 rounded-xl text-xs font-semibold hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition-all duration-200 shadow-sm"
                                                        title="Delete User">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-300 bg-gray-50 border border-gray-100 cursor-not-allowed" title="Cannot delete your own account">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                                You
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-400 mb-3 shadow-inner">
                                            <i data-lucide="users-x" class="w-5 h-5"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">No Users Found</h3>
                                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Try adjusting your search or filters, or add a new user account.</p>
                                        <a href="{{ route('users.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-500 transition-all">
                                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                            Add New User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
