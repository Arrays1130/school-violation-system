<x-app-layout>
    @section('header', 'Rules & Regulations')

    <div class="space-y-6">
        {{-- MODERN PRISM HEADER --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        Policy Management
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Rules & Regulations</h1>
                    <p class="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage violation guidelines, categorize offenses, and establish standardized disciplinary severity classifications.</p>
                </div>
                
                {{-- Action Panel --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('violations.create') }}" class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4.5 h-4.5"></i>
                        Add Rule Category
                    </a>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-4 border border-gray-200/80 shadow-sm mb-6">
            <form method="GET" action="{{ route('violations.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="search" class="w-4.5 h-4.5"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search rules, keywords, or codes..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                </div>
                
                <div class="sm:w-56 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="layers" class="w-4.5 h-4.5"></i>
                    </div>
                    <select name="category" class="w-full pl-10 pr-10 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    <span>Filter</span>
                </button>
            </form>
        </div>

        {{-- Categories List --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200">
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Violation Rule / Code</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center whitespace-nowrap">Category Class</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center whitespace-nowrap">Severity Level</th>
                            <th scope="col" class="px-6 py-5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($violations as $violation)
                            <tr class="hover:bg-slate-50/60 transition-colors duration-200 group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 rounded-lg font-bold text-xs border border-slate-300/50 uppercase tracking-wider shadow-sm">
                                                {{ $violation->code }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ $violation->title }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <p class="text-[11px] text-gray-500 font-medium">Identifier: <span class="text-gray-400">#{{ str_pad($violation->id, 4, '0', STR_PAD_LEFT) }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 border border-gray-200 text-gray-600">
                                        {{ $violation->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($violation->severity === 'Major')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 border border-rose-200 text-rose-700 shadow-sm">
                                            <span class="relative flex h-2 w-2">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                            </span>
                                            Major Severity
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Minor Severity
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('violations.edit', $violation) }}" 
                                           class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-sm transition-all duration-200" title="Edit Rule">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete('delete-violation-{{ $violation->id }}', 'violation type')" 
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:shadow-sm transition-all duration-200" title="Delete Rule">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        <form id="delete-violation-{{ $violation->id }}" action="{{ route('violations.destroy', $violation) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 max-w-sm mx-auto">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 mb-4 shadow-inner">
                                            <i data-lucide="shield-question" class="w-7 h-7"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-gray-900">No Guidelines Found</h3>
                                        <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">We couldn't find any rules matching your search. Create a rule type to instantiate the standard handbook registry.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($violations->hasPages())
                <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/50">
                    {{ $violations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
