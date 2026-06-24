<x-app-layout>
    @section('header', $handbook->title)

    <div class="space-y-8 max-w-4xl mx-auto">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-5">
                    <a href="{{ route('handbooks.index') }}" class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-400"></i>
                            Institutional Policy
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight leading-tight max-w-xl">{{ $handbook->title }}</h2>
                        <div class="flex items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-4 h-4 text-indigo-400"></i>
                                Updated {{ $handbook->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('handbooks.edit', $handbook) }}"
                       class="inline-flex items-center px-4 py-2.5 bg-white/10 border border-white/10 rounded-xl text-xs font-bold text-white hover:bg-white/20 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-1.5 text-amber-400"></i>
                        Edit Policy
                    </a>
                    <button onclick="window.print()"
                            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
                        Print Policy
                    </button>
                </div>
            </div>
        </div>

        {{-- Official Policy Document Card --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden border-l-4 border-indigo-650 border-l-4 border-indigo-600">
            
            {{-- Document Narrative Content --}}
            <div class="p-8 md:p-10 space-y-8 bg-white">
                <div class="prose max-w-none text-slate-700 leading-relaxed text-sm font-medium space-y-4 whitespace-pre-wrap pl-6 border-l-2 border-indigo-100 font-serif italic text-base">
                    {!! nl2br(e($handbook->content)) !!}
                </div>

                {{-- Attachment Panel --}}
                @if($handbook->attachment)
                    <div class="pt-8 border-t border-gray-100 space-y-4">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i data-lucide="file-check-2" class="w-4.5 h-4.5 text-indigo-500 animate-pulse"></i>
                            Reference Documents
                        </h3>
                        <div class="bg-slate-50/50 border border-gray-150 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i data-lucide="file-text" class="w-6.5 h-6.5 text-indigo-655 text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Policy Document</h4>
                                    <p class="text-xs text-slate-400 mt-1 font-medium">Portable Document Format (.pdf)</p>
                                </div>
                            </div>
                            <a href="{{ $handbook->attachment }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-655/20 transition-all duration-200 hover:-translate-y-0.5 shrink-0">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                Download PDF
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Policy Authentication Footer --}}
            <div class="px-8 py-5 bg-slate-50 border-t border-gray-150 flex items-center justify-between text-slate-400">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4.5 h-4.5 text-indigo-655 text-indigo-500"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Officially Approved Policy Guideline</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
