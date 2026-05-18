<x-app-layout>
    @section('header', 'Schedule Hearing')

    <div class="max-w-4xl mx-auto space-y-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('cases.show', $case) }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                        Hearing Setup
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Schedule Hearing</h2>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-500/20 border border-indigo-500/30 text-indigo-200 text-[11px] font-bold uppercase tracking-wider">
                            Case #{{ str_pad($case->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="text-indigo-100/70 text-sm font-medium">{{ $case->violation->title }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('hearings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="case_id" value="{{ $case->id }}">
            
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Date & Time *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="calendar-clock" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                @error('scheduled_at') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Venue *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="map-pin" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="venue" value="{{ old('venue', 'Guidance Office') }}" required placeholder="e.g. Dean's Office, Room 101"
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                @error('venue') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Participants (Optional)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="users" class="w-4.5 h-4.5"></i>
                            </div>
                            <input type="text" name="participants" value="{{ old('participants') }}" placeholder="e.g. Dean of Discipline, Parent, Guidance Counselor"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            <p class="text-xs text-gray-500 mt-2">Separate names with commas.</p>
                            @error('participants') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Notes / Instructions</label>
                        <textarea name="notes" rows="3" placeholder="Additional instructions for the student..."
                            class="w-full p-4 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                                <i data-lucide="file-check-2" class="w-4.5 h-4.5"></i>
                            </div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Meeting Minutes (Initial)</label>
                        </div>
                        <textarea name="meeting_minutes" rows="5" 
                            class="w-full p-4 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400" placeholder="Official minutes of the hearing (can be added later)...">{{ old('meeting_minutes') }}</textarea>
                        <p class="text-xs text-gray-500 mt-2">You may leave this blank and formulate it securely during or after the hearing.</p>
                        @error('meeting_minutes') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('cases.show', $case) }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-4.5 h-4.5"></i>
                        Schedule Hearing
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
