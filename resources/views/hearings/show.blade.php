<x-app-layout>
    @section('header', 'Hearing Details')

    <div class="space-y-6 max-w-5xl mx-auto">
        {{-- Breadcrumb & Actions Panel --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <a href="{{ route('cases.show', $hearing->case) }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                        <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                            <i data-lucide="gavel" class="w-3.5 h-3.5"></i>
                            Hearing Summary
                        </div>
                        <h2 class="text-3xl font-bold text-white tracking-tight">Violation Hearing</h2>
                        <p class="text-indigo-100/70 text-sm mt-1">Accused Student: <span class="text-white font-medium">{{ $hearing->case->student->full_name }}</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('hearings.edit', $hearing) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                        Edit Hearing Setup
                    </a>
                </div>
            </div>
        </div>

        {{-- Official Digital Document Card --}}
        <div class="bg-white rounded-2xl border border-gray-150 shadow-md overflow-hidden">
            
            {{-- Document Header --}}
            <div class="bg-white px-8 py-8 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                            Hearing Record
                        </span>
                        <h1 class="text-2xl font-semibold text-slate-800 tracking-tight">
                            Violation Board Hearing
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 text-slate-500 text-sm">
                            <span class="uppercase tracking-wider">Ref ID: HR-{{ $hearing->id }}</span>
                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                            <span>Case Violation: {{ $hearing->case->violation->title }}</span>
                        </div>
                    </div>
                    
                    <div class="text-left md:text-right">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-md text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-2">
                            <i data-lucide="calendar" class="w-3 h-3"></i> Scheduled Time
                        </span>
                        <p class="text-xl font-bold text-slate-800 tracking-tight">{{ $hearing->scheduled_at->format('M d, Y') }}</p>
                        <p class="text-sm font-medium text-slate-500 mt-0.5">{{ $hearing->scheduled_at->format('h:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Document Details Block --}}
            <div class="p-8 space-y-8 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-500"></i>
                            Venue / Room
                        </h3>
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-950 shadow-inner">
                            {{ $hearing->venue }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-purple-500"></i>
                            Accompanying Participants
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($hearing->participants as $participant)
                                <span class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 shadow-sm">
                                    {{ $participant }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($hearing->notes)
                    <div class="space-y-2">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="quote" class="w-4 h-4 text-pink-500"></i>
                            Hearing Protocol Notes
                        </h3>
                        <div class="p-5 bg-indigo-50/40 rounded-xl border border-indigo-100 text-sm text-indigo-900/80 leading-relaxed italic relative">
                            <div class="absolute right-4 top-3 text-indigo-200">
                                <i data-lucide="quote" class="w-8 h-8 opacity-40"></i>
                            </div>
                            "{{ $hearing->notes }}"
                        </div>
                    </div>
                @endif

                {{-- Minutes of the Meeting --}}
                <div class="pt-8 border-t border-gray-150 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-500"></i>
                            Minutes of the Meeting (MOM)
                        </h3>
                        @if($hearing->meeting_minutes)
                            <a href="{{ route('hearings.print-mom', $hearing) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium shadow-sm transition-all duration-200">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                Print Record
                            </a>
                        @endif
                    </div>

                    @if($hearing->meeting_minutes)
                        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative group">
                            <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap font-medium">
                                {{ $hearing->meeting_minutes }}
                            </div>
                        </div>
                    @else
                        <div class="py-16 bg-white rounded-lg border border-gray-200 flex flex-col items-center justify-center text-center shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center mb-4 shadow-sm text-slate-400">
                                <i data-lucide="file-edit" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-sm font-semibold text-slate-800">No Minutes Recorded</h4>
                            <p class="text-sm text-slate-500 mt-1 max-w-sm leading-relaxed">Minutes of this official hearing session have not yet been transcribed. Click below to add documentation.</p>
                            <a href="{{ route('hearings.edit', $hearing) }}" class="mt-5 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition-all duration-200">
                                Add Meeting Minutes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
