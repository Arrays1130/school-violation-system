<x-app-layout>
    @section('header', 'Edit Violation Case')

    <div class="max-w-3xl mx-auto space-y-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('cases.show', $case) }}" class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Edit Case Record</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-500/20 border border-indigo-500/30 text-indigo-200 text-xs font-bold">
                            #{{ str_pad($case->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="text-indigo-100/70 text-xs font-medium">Updating details for {{ $case->student->full_name }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <form action="{{ route('cases.update', $case) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8 space-y-8">
                    {{-- Status --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Case Status</label>
                        <div class="relative">
                            <select name="status" required 
                                class="w-full pl-4 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                @foreach(\App\Models\StudentCase::STATUSES as $status)
                                    <option value="{{ $status }}" {{ old('status', $case->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Narrative --}}
                    <div class="space-y-2 pt-6 border-t border-gray-100">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Incident Narrative</label>
                        <textarea name="description" rows="5" required placeholder="Provide a detailed description of the incident..."
                            class="w-full p-4 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400">{{ old('description', $case->description) }}</textarea>
                    </div>

                    {{-- Logistics --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Date & Time of Incident</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', $case->occurred_at->format('Y-m-d\TH:i')) }}" required 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Witnesses (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="users" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="witness" value="{{ old('witness', $case->witness) }}" placeholder="e.g. John Doe, Prof. Smith"
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                    <button type="button" onclick="confirmDelete('delete-case-form', 'case record')" class="text-sm font-bold text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Delete Record
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="save" class="w-4.5 h-4.5"></i>
                        Save Changes
                    </button>
                </div>
            </form>
            
            <form id="delete-case-form" action="{{ route('cases.destroy', $case) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
