<x-app-layout>
    @section('header', 'Document Repository & Minutes')
    
    <div class="max-w-7xl mx-auto space-y-8">
            
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200/60 p-4 rounded-xl flex items-center gap-3 shadow-sm shadow-emerald-500/5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200/60 p-4 rounded-xl flex items-center gap-3 shadow-sm shadow-rose-500/5">
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <p class="text-sm font-semibold text-rose-800">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20" x-data="{ uploadModal: false }">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                        <i data-lucide="folder-git" class="w-3.5 h-3.5 text-indigo-400 animate-pulse"></i>
                        Minutes & Documents
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Document Repository</h2>
                    <p class="text-indigo-100/70 text-sm mt-1.5 leading-relaxed">Manage external attachments, documentary evidence, and recorded minutes.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="uploadModal = true" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 rounded-xl text-sm font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="upload-cloud" class="w-4.5 h-4.5"></i>
                        <span>Upload Document</span>
                    </button>
                    <a href="{{ route('meeting-minutes.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white/10 border border-white/10 rounded-xl text-sm font-bold text-white hover:bg-white/20 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5">
                        <i data-lucide="plus" class="w-4.5 h-4.5"></i>
                        <span>Record Minutes</span>
                    </a>
                </div>
            </div>

            {{-- Main Content Container (Embedded slightly down) --}}
            <div class="mt-8 pt-8 border-t border-white/10">
                {{-- Stats Summary --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-md flex items-center justify-between group hover:bg-white/10 transition-all duration-250">
                        <div>
                            <span class="text-xs font-bold text-indigo-200/60 uppercase tracking-widest">Total Files</span>
                            <div class="text-3xl font-black text-white mt-2">{{ $totalFiles }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/10 text-indigo-300 flex items-center justify-center shadow-inner">
                            <i data-lucide="files" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 to-indigo-950/30 p-6 flex items-center justify-between group hover:bg-indigo-500/20 transition-all duration-250">
                        <div>
                            <span class="text-xs font-bold text-indigo-300/80 uppercase tracking-widest">PDF Documents</span>
                            <div class="text-3xl font-black text-white mt-2">{{ $pdfFiles }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-200 flex items-center justify-center shadow-inner">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-md flex items-center justify-between group hover:bg-white/10 transition-all duration-250">
                        <div>
                            <span class="text-xs font-bold text-indigo-200/60 uppercase tracking-widest">Total Storage</span>
                            <div class="text-3xl font-black text-white mt-2">{{ $totalSizeMB }} <span class="text-base font-semibold text-indigo-300">MB</span></div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/10 text-indigo-300 flex items-center justify-center shadow-inner">
                            <i data-lucide="database" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload Modal --}}
            <div x-show="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="uploadModal = false"></div>
                
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-200">
                    <form action="{{ route('meeting-minutes.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-8 text-left">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                                        <i data-lucide="upload-cloud" class="w-5.5 h-5.5"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">Upload Document</h3>
                                </div>
                                <button type="button" @click="uploadModal = false" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Link to Case (Optional)</label>
                                    <div class="relative">
                                        <select name="case_id" class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-950 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                            <option value="">No Link (General Attachment)</option>
                                            @foreach($cases as $case)
                                                <option value="{{ $case->id }}">{{ $case->student->full_name }} (Case #{{ $case->id }})</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Select File</label>
                                    <input type="file" name="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-150 transition-all" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Display Name / Label</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i data-lucide="tag" class="w-4.5 h-4.5"></i>
                                        </div>
                                        <input type="text" name="label" placeholder="e.g. Sworn Statement - June 2024" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-950 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-8 py-5 border-t border-gray-150 flex flex-col sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-indigo-600 rounded-xl font-bold text-sm text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/25 transition-all duration-200">
                                Upload
                            </button>
                            <button type="button" @click="uploadModal = false" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-sm text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Repository Container --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden p-8 space-y-6">
            {{-- Search & Filter Controls --}}
            <div class="flex flex-col md:flex-row gap-4">
                <form action="{{ route('meeting-minutes.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="search" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search documents or student names..." class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        Search Records
                    </button>
                </form>
            </div>

            {{-- Files Table --}}
            <div class="overflow-hidden border border-gray-150 rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left">
                        <thead class="bg-slate-50/75">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">File Name</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Linked Case</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Uploaded By</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Size</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($records as $record)
                                <tr class="hover:bg-indigo-50/30 transition-all duration-150">
                                    <td class="px-6 py-4.5 whitespace-nowrap">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-100/50">
                                                <i data-lucide="{{ $record->type === 'text' ? 'file-text' : 'file' }}" class="w-4.5 h-4.5"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 leading-tight">{{ $record->label }}</p>
                                                <p class="text-xs text-slate-400 mt-1 font-medium">{{ $record->created_at->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap">
                                        @if($record->case)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-semibold text-slate-700 capitalize">{{ $record->case->student->full_name ?? 'N/A' }}</span>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 uppercase tracking-wider mt-1">
                                                    <i data-lucide="hash" class="w-3 h-3"></i>
                                                    Case #{{ $record->case->id }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 border border-gray-150 text-slate-500">
                                                General Repository
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap text-sm font-medium text-slate-600">
                                        {{ $record->uploader }}
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap text-xs font-semibold text-slate-500 font-mono">
                                        {{ $record->size }}
                                    </td>
                                    <td class="px-6 py-4.5 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-3">
                                            @if($record->view_url)
                                                <a href="{{ $record->view_url }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-bold text-slate-700 rounded-lg transition-colors">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    View
                                                </a>
                                            @endif
                                            @if($record->download_url)
                                                <a href="{{ $record->download_url }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-xs font-bold text-indigo-700 rounded-lg transition-colors">
                                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                                    Download
                                                </a>
                                            @endif
                                            <form action="{{ $record->delete_url }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center p-1.5 hover:bg-rose-50 border border-transparent hover:border-rose-100 text-rose-500 hover:text-rose-700 rounded-lg transition-all" onclick="return confirm('Are you sure you want to delete this file?')">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 border border-indigo-100 shadow-inner">
                                                <i data-lucide="folder-search" class="w-6.5 h-6.5 text-indigo-500 animate-bounce"></i>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800">No records or files found</p>
                                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">No matching minutes or attachments are currently in the system repository.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
