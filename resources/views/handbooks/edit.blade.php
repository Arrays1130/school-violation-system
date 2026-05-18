<x-app-layout>
    @section('header', 'Edit Handbook Entry')

    <div class="max-w-3xl mx-auto space-y-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('handbooks.show', $handbook) }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5 text-indigo-400"></i>
                        Edit Handbook Entry
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Edit Policy Entry</h2>
                    <p class="text-indigo-100/70 text-sm mt-1">Updating Entry: <span class="text-white font-medium">{{ $handbook->title }}</span></p>
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
            <form action="{{ route('handbooks.update', $handbook) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8 space-y-8">
                    {{-- Basic Details --}}
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                            <i data-lucide="bookmark" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Policy Details</h3>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Title *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="type" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="title" value="{{ old('title', $handbook->title) }}" placeholder="e.g. Student Code of Conduct" required autofocus
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-950 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                            @error('title') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Content & Description *</label>
                            <textarea name="content" rows="12" required placeholder="Describe the rule in detail..."
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-950 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 leading-relaxed font-mono text-xs">{{ old('content', $handbook->content) }}</textarea>
                            @error('content') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">External Document Link (URL)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="link" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="url" name="attachment" value="{{ old('attachment', $handbook->attachment) }}" placeholder="https://example.com/policy.pdf" 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-950 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                            <p class="text-xs text-gray-400 mt-2 font-medium">Optional: Paste a link to a Google Doc or hosted PDF document.</p>
                            @error('attachment') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50 border-t border-gray-150 flex items-center justify-end gap-3">
                    <a href="{{ route('handbooks.show', $handbook) }}" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="save" class="w-4.5 h-4.5"></i>
                        Update Policy
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
