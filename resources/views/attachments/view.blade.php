<x-app-layout>
    {{-- Load docx-preview libs BEFORE Alpine initialises this page --}}
    <script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview/dist/docx-preview.js"></script>

    <div class="max-w-5xl mx-auto py-8 px-4" x-data="{
        loading: true,
        error: false,
        errorMessage: '',
        fileExt: '{{ strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}',
        renderDoc() {
            const ext = this.fileExt;
            if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) {
                this.loading = false; // image handled by <img> tag
                return;
            }
            if (ext === 'pdf') {
                this.loading = false; // PDF handled by <iframe>
                return;
            }
            if (['doc','docx'].includes(ext)) {
                const container = this.$refs.docxContainer;
                const url = '{{ route('attachments.download', $attachment) }}';
                fetch(url)
                    .then(r => { if (!r.ok) throw new Error('Could not load file (HTTP ' + r.status + ')'); return r.arrayBuffer(); })
                    .then(buf => docx.renderAsync(buf, container))
                    .then(() => { this.loading = false; })
                    .catch(err => { this.error = true; this.loading = false; this.errorMessage = err.message; });
                return;
            }
            // Unsupported format
            this.error = true;
            this.loading = false;
            this.errorMessage = 'Preview is not available for .' + ext + ' files. Please download the file instead.';
        }
    }" x-init="renderDoc()">

        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20 mb-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <a href="{{ url()->previous() }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0">
                        <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-indigo-400"></i>
                            Document Viewer
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight leading-tight max-w-xl">{{ $attachment->label ?? $attachment->file_name }}</h2>
                        <div class="flex items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="file" class="w-4 h-4 text-indigo-400"></i>
                                Format: .{{ strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('attachments.download', $attachment) }}"
                       class="inline-flex items-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="download" class="w-4.5 h-4.5 mr-1.5"></i>
                        Download Document
                    </a>
                </div>
            </div>
        </div>

        {{-- Preview Container --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden relative border-l-4 border-indigo-600" style="min-height: 600px;">

            {{-- Loading --}}
            <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-10">
                <div class="w-10 h-10 border-4 border-slate-100 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
                <p class="text-sm text-slate-500 font-bold tracking-tight">Preparing document preview...</p>
            </div>

            {{-- Error --}}
            <div x-show="error" class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center z-10 bg-white" style="display:none;" x-cloak>
                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                    <i data-lucide="file-x" class="w-7 h-7 text-indigo-500"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Preview unavailable</h3>
                <p class="text-sm text-slate-400 mb-6 font-medium" x-text="errorMessage"></p>
                <a href="{{ route('attachments.download', $attachment) }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl transition-all active:scale-95 shadow-sm shadow-indigo-650/20">
                    Download File Instead
                </a>
            </div>

            {{-- Image Preview --}}
            @if(in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp','bmp','svg']))
                <div class="p-8 flex items-center justify-center bg-slate-50/50">
                    <img src="{{ route('attachments.download', $attachment) }}" alt="{{ $attachment->label ?? $attachment->file_name }}" class="max-w-full rounded-2xl shadow-sm border border-gray-200/85">
                </div>

            {{-- PDF Preview --}}
            @elseif(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) === 'pdf')
                <iframe src="{{ route('attachments.download', $attachment) }}" class="w-full" style="height: 80vh; border: none;"></iframe>

            {{-- DOCX Preview (rendered by JS above) --}}
            @else
                <div x-ref="docxContainer" x-show="!loading && !error" class="p-6 lg:p-10 prose max-w-none overflow-x-auto" style="display:none;"></div>
            @endif
        </div>
    </div>

    <style>
        .docx-wrapper { background: transparent !important; padding: 0 !important; }
        .docx { box-shadow: none !important; margin-bottom: 0 !important; background: white !important; padding: 0 !important; width: 100% !important; }
    </style>
</x-app-layout>
