<x-app-layout>
    <div class="space-y-8 max-w-7xl mx-auto">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-indigo-400 animate-pulse"></i>
                        University Guidelines
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Handbooks & Policies</h2>
                    <p class="text-indigo-100/70 text-sm mt-1.5 leading-relaxed">Manage institutional guidelines and student conduct protocols.</p>
                </div>
                <a href="{{ route('handbooks.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 rounded-xl text-sm font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5 self-start md:self-auto shrink-0">
                    <i data-lucide="plus" class="w-4.5 h-4.5"></i>
                    <span>Add Document</span>
                </a>
            </div>
        </div>

        {{-- Upgraded Search Bar --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200/80">
            <form method="GET" action="{{ route('handbooks.index') }}" class="flex flex-col md:flex-row gap-3 items-center">
                <div class="flex-1 relative w-full">
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search regulation titles, codes or contents..."
                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                    >
                </div>
                
                <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all duration-200 w-full md:w-auto shrink-0">
                    Search Policies
                </button>
            </form>
        </div>

        {{-- Premium Card Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($handbooks as $handbook)
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden hover:shadow-md hover:border-indigo-200 transition-all duration-250 group">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-inner">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                            </div>
                            
                            @if($handbook->attachment)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-xl text-[10px] font-bold border border-emerald-100 uppercase tracking-wide">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    PDF Attachment
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-50 text-gray-500 rounded-xl text-[10px] font-bold border border-gray-200 uppercase tracking-wide">
                                    Text Only
                                </span>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            <h3 class="text-sm font-bold text-slate-850 group-hover:text-indigo-600 transition-all duration-200 line-clamp-2">
                                <a href="{{ route('handbooks.show', $handbook) }}">{{ $handbook->title }}</a>
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 flex items-center gap-1 uppercase tracking-wider">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-300"></i>
                                Updated {{ $handbook->updated_at->diffForHumans() }}
                            </p>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-xl border border-slate-100 line-clamp-3 font-medium">
                            "{{ Str::limit(strip_tags($handbook->content), 120) }}"
                        </p>
                    </div>

                    <div class="px-6 py-4.5 bg-slate-50/70 border-t border-gray-150 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-450"></i>
                            <span>{{ $handbook->updated_at->format('M d, Y') }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('handbooks.show', $handbook) }}" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 flex items-center justify-center transition-all" title="View">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('handbooks.edit', $handbook) }}" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 flex items-center justify-center transition-all" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <form id="delete-handbook-{{ $handbook->id }}" action="{{ route('handbooks.destroy', $handbook) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-handbook-{{ $handbook->id }}', 'handbook document')" 
                                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 flex items-center justify-center transition-all"
                                        title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 bg-white rounded-2xl border border-gray-200 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center mb-4 shadow-sm text-slate-400">
                        <i data-lucide="book-open" class="w-6.5 h-6.5 text-indigo-500 animate-bounce"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800">No Handbooks Found</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm leading-relaxed">We couldn't find any policies matching your search criteria. Try a different query or add a new handbook.</p>
                    <a href="{{ route('handbooks.create') }}" class="mt-5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/25 transition-all duration-200">
                        Add Document
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination Block --}}
        @if($handbooks->hasPages())
            <div class="bg-white border border-gray-150 rounded-2xl px-6 py-4.5 shadow-sm">
                {{ $handbooks->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
