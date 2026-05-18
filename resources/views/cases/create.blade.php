<x-app-layout>
    @section('header', 'Log Violation Case')

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ 
        violation_id: '{{ old('violation_id', $prefilledViolationId ?? '') }}',
        student_id: '{{ old('student_id', $student?->id ?? '') }}',
        sanction: '',
        level: '',
        isLoading: false,
        
        async fetchPolicy() {
            if(!this.violation_id) {
                this.sanction = '';
                this.level = '';
                return;
            }
            this.isLoading = true;
            try {
                let url = `{{ url('/api/get-sanction-info') }}?violation_id=${this.violation_id}`;
                if (this.student_id) url += `&student_id=${this.student_id}`;
                const res = await fetch(url);
                const data = await res.json();
                this.sanction = data.sanction ?? data.first_offense ?? '';
                this.level = data.severity ?? '';
            } catch(e) {
                console.error('Failed to retrieve policy details.');
            } finally {
                this.isLoading = false;
            }
        }
    }" x-init="if(violation_id) fetchPolicy()">

        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('cases.index') }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Log Violation Case</h2>
                    <p class="text-indigo-100/70 text-sm mt-1">Record a new student disciplinary incident</p>
                </div>
            </div>
        </div>

        <form action="{{ route('cases.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            
            {{-- Main Form Data --}}
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-8 space-y-8">
                        {{-- Subject Selection --}}
                        <div class="space-y-3">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Student Information</label>
                            <div class="relative">
                                <select name="student_id" x-model="student_id" @change="fetchPolicy()" required 
                                    class="w-full pl-11 pr-10 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">Select Student...</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" 
                                            {{ (old('student_id') == $s->id || ($student && $student->id == $s->id)) ? 'selected' : '' }}>
                                            {{ $s->full_name }} ({{ $s->department }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Violation Selection --}}
                        <div class="space-y-3 pt-6 border-t border-gray-100">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Violation Details</label>
                            <div class="relative">
                                <select name="violation_id" x-model="violation_id" @change="fetchPolicy()" required 
                                    class="w-full pl-11 pr-10 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">Select Violation...</option>
                                    @foreach($violations as $v)
                                        <option value="{{ $v->id }}">{{ $v->code }} — {{ $v->title }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="space-y-3 pt-6 border-t border-gray-100">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Incident Details / Description</label>
                            <textarea name="description" rows="4" placeholder="Describe the incident in detail..." required 
                                class="w-full p-4 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Date & Time of Incident</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required 
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Witnesses</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="users" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="witness" value="{{ old('witness') }}" placeholder="Witness Name" 
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('cases.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Cancel</a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                            <i data-lucide="save" class="w-4.5 h-4.5"></i>
                            Log Violation Case
                        </button>
                    </div>
                </div>
            </div>

            {{-- Policy Panel (Clean) --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 overflow-hidden relative">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50/50 rounded-full blur-3xl -z-10"></div>
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-5">Policy Information</h3>
                    
                    <div x-show="!violation_id" class="py-8 text-center opacity-60">
                        <i data-lucide="fingerprint" class="w-8 h-8 mx-auto mb-3 text-gray-400"></i>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Select violation to view details</p>
                    </div>

                    <div x-show="violation_id" x-cloak class="space-y-6 animate-in fade-in duration-300">
                        <div>
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Severity Level</p>
                            <div class="flex items-center gap-3">
                                <span :class="level === 'Major' ? 'bg-rose-50 text-rose-700 border-rose-200' : (level === 'Critical' ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-indigo-50 text-indigo-700 border-indigo-200')"
                                      class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider border">
                                    <span x-text="level || 'Analyzing...'"></span>
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Institutional Sanction</p>
                            <div class="p-4 bg-gray-50/50 rounded-xl text-sm font-medium text-gray-700 leading-relaxed italic border border-gray-200">
                                <span x-text="sanction || 'Retrieving sanction protocols...'"></span>
                            </div>
                        </div>

                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100/50">
                            <div class="flex gap-3">
                                <i data-lucide="info" class="w-4.5 h-4.5 text-indigo-600 flex-shrink-0 mt-0.5"></i>
                                <p class="text-xs font-medium text-indigo-800 leading-relaxed">
                                    This sanction is based on the Student Handbook standard for first-time offenders.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 rounded-2xl p-6 border border-amber-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Compliance Warning
                        </h4>
                        <p class="text-sm font-medium leading-relaxed text-amber-700">
                            All incident logs are subject to institutional audit. Ensure factual accuracy before authorization.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
