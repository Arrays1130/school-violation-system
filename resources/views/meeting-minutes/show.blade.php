<x-app-layout>
    @section('header', $meetingMinute->title)

    <div class="max-w-4xl mx-auto space-y-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-5">
                    <a href="{{ route('meeting-minutes.index') }}" class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                            Official Record #{{ $meetingMinute->id }}
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight leading-tight">{{ $meetingMinute->title }}</h2>
                        <div class="flex flex-wrap items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-indigo-400"></i>
                                {{ $meetingMinute->meeting_date->format('F d, Y • h:i A') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-4 h-4 text-indigo-400"></i>
                                {{ $meetingMinute->venue }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('meeting-minutes.edit', $meetingMinute) }}"
                       class="inline-flex items-center px-4 py-2.5 bg-white/10 border border-white/10 rounded-xl text-xs font-bold text-white hover:bg-white/20 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-1.5 text-amber-400"></i>
                        Edit Record
                    </a>
                    <button onclick="window.print()"
                            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
                        Print Minutes
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden border-l-4 border-indigo-600">
            <div class="p-8 space-y-8">

                {{-- Associated Case --}}
                @if($meetingMinute->case)
                    <div class="bg-slate-50/50 rounded-2xl border border-gray-150 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center border border-gray-200 shadow-sm">
                                <i data-lucide="user-check" class="w-6 h-6 text-indigo-655 text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Involved Student</p>
                                <h4 class="text-base font-extrabold text-slate-800 capitalize mt-1.5">{{ $meetingMinute->case->student->full_name }}</h4>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3.5 py-1.5 bg-indigo-600/10 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-150">
                                Case #{{ $meetingMinute->case->id }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500 font-mono">{{ $meetingMinute->case->student->student_id }}</span>
                        </div>
                    </div>
                @endif

                {{-- Minutes Content --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i data-lucide="file-text" class="w-4.5 h-4.5"></i>
                        </div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Proceedings & Official Findings</h3>
                    </div>
                    
                    <div class="bg-slate-50/30 rounded-2xl border border-slate-100 p-8">
                        <div class="whitespace-pre-wrap text-slate-700 leading-relaxed text-base border-l-2 border-indigo-200 pl-6 font-serif italic">
                            {{ $meetingMinute->content }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-slate-50 px-8 py-5 border-t border-gray-150 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-750 flex items-center justify-center text-white font-extrabold text-xs">
                        {{ substr($meetingMinute->creator->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none">Recorded By</p>
                        <p class="text-sm font-bold text-slate-700 mt-1">{{ $meetingMinute->creator->name ?? 'System Administrator' }}</p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    Last updated {{ $meetingMinute->updated_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
