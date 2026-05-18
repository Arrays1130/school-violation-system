<x-app-layout>
    @section('header', 'Add Violation Type')

    <div class="max-w-3xl mx-auto space-y-6">
        {{-- MODERN PRISM HEADER --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <a href="{{ route('violations.index') }}" class="flex-shrink-0 w-12 h-12 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-white/70 hover:text-white transition-all backdrop-blur-md group shadow-lg shadow-black/20">
                        <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                            <i data-lucide="file-plus-2" class="w-3.5 h-3.5"></i>
                            Creation Wizard
                        </div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">New Violation Type</h1>
                        <p class="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Define a new handbook rule and standard sanction.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <form action="{{ route('violations.store') }}" method="POST">
                @csrf
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Violation Code</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="hash" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. V-001" required 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Violation Category</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="layers" class="w-4.5 h-4.5"></i>
                                </div>
                                <select name="category" required 
                                    class="w-full pl-10 pr-10 py-3 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">Select Category...</option>
                                    @foreach(['Academic', 'Behavioral', 'Administrative', 'Security', 'General'] as $cat)
                                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Violation Title</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="type" class="w-4.5 h-4.5"></i>
                            </div>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="Official violation title..." required 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Standard Sanction</label>
                        <textarea name="description" rows="4" placeholder="Define the standard sanction for this violation..." required 
                            class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Severity Level</label>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['Minor', 'Major'] as $level)
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="{{ $level }}" class="hidden peer" {{ old('severity', 'Minor') == $level ? 'checked' : '' }}>
                                    <div class="px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-xl text-center transition-all peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:shadow-sm group-hover:bg-gray-50">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($level === 'Major')
                                                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500 peer-checked:text-indigo-600"></i>
                                            @else
                                                <i data-lucide="info" class="w-4 h-4 text-emerald-500 peer-checked:text-indigo-600"></i>
                                            @endif
                                            <p class="text-sm font-bold text-gray-600 peer-checked:text-indigo-700 uppercase tracking-wider">{{ $level }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <a href="{{ route('violations.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2 hover:-translate-y-0.5">
                        <i data-lucide="save" class="w-4.5 h-4.5"></i>
                        Add Violation Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
