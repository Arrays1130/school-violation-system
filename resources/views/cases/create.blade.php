<x-app-layout>
    @section('header', 'Log Violation Case')

    <script>
        window.__students  = {!! json_encode($students->map(fn($s) => [
            'id'         => (string)$s->id,
            'name'       => (string)$s->full_name,
            'dept'       => (string)$s->department,
            'dept_short' => (string)$s->department_shortcut,
            'section'    => (string)$s->section,
            'year'       => (string)$s->year_level,
            'initials'   => (string)$s->initials,
            'cases'      => (int)$s->cases->count(),
        ])) !!};
        window.__violations = {!! json_encode($violations->map(fn($v) => [
            'id'       => (string)$v->id,
            'code'     => (string)$v->code,
            'title'    => (string)$v->title,
            'severity' => (string)$v->severity,
        ])) !!};
        window.__prefilled = @if($student) {
            id:       '{{ $student->id }}',
            name:     '{{ addslashes($student->full_name) }}',
            dept:     '{{ addslashes($student->department) }}',
            dept_short:'{{ $student->department_shortcut }}',
            section:  '{{ addslashes($student->section) }}',
            year:     '{{ addslashes($student->year_level) }}',
            initials: '{{ $student->initials }}',
            cases:    {{ $student->cases->count() }},
        } @else null @endif;
    </script>

    <style>
        [x-cloak]{display:none!important}
        @keyframes dropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
        .drop-in{animation:dropIn .18s ease forwards}
        @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .fade-up{animation:fadeUp .22s ease forwards}
    </style>

    <div class="max-w-5xl mx-auto pb-12" x-data="{
        prefilled: window.__prefilled,

        /* student */
        sid: '{{ old('student_id', $student?->id ?? '') }}',
        student: window.__prefilled ?? null,
        sOpen: false,
        sQ: '',
        students: window.__students,
        get sFiltered(){
            const q=this.sQ.toLowerCase();
            return q ? this.students.filter(s=>s.name.toLowerCase().includes(q)||s.dept.toLowerCase().includes(q)) : this.students;
        },
        pickStudent(s){ this.sid=s.id; this.student=s; this.sQ=''; this.sOpen=false; this.loadSanction(); },
        clearStudent(){ this.sid=''; this.student=null; this.sanction=''; this.severity=''; },

        /* violation */
        vid: '{{ old('violation_id', $prefilledViolationId ?? '') }}',
        violation: null,
        vOpen: false,
        vQ: '',
        violations: window.__violations,
        get vFiltered(){
            const q=this.vQ.toLowerCase();
            return q ? this.violations.filter(v=>v.code.toLowerCase().includes(q)||v.title.toLowerCase().includes(q)) : this.violations;
        },
        pickViolation(v){ this.vid=v.id; this.violation=v; this.vQ=''; this.vOpen=false; this.loadSanction(); },
        clearViolation(){ this.vid=''; this.violation=null; this.sanction=''; this.severity=''; },

        /* sanction */
        sanction: '',
        severity: '',
        loading: false,
        async loadSanction(){
            if(!this.vid){ this.sanction=''; this.severity=''; return; }
            this.loading=true;
            try{
                let url=`{{ url('/api/get-sanction-info') }}?violation_id=\${this.vid}`;
                if(this.sid) url+=`&student_id=\${this.sid}`;
                const d=await(await fetch(url)).json();
                this.sanction=d.sanction??d.first_offense??'';
                this.severity=d.severity??(this.violation?.severity??'');
            }catch(e){ this.severity=this.violation?.severity??''; }
            finally{ this.loading=false; }
        },
        get sevClass(){
            if(this.severity==='Critical') return {badge:'bg-red-100 text-red-700 border-red-200',dot:'bg-red-500',bar:'from-red-500 to-rose-600'};
            if(this.severity==='Major')    return {badge:'bg-orange-100 text-orange-700 border-orange-200',dot:'bg-orange-500',bar:'from-orange-500 to-amber-500'};
            return {badge:'bg-yellow-100 text-yellow-700 border-yellow-200',dot:'bg-yellow-500',bar:'from-yellow-500 to-amber-400'};
        },
    }" x-init="
        if(vid){ const v=violations.find(x=>x.id==vid); if(v){ violation=v; loadSanction(); } }
    ">

        {{-- PAGE HEADER --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 mb-8 shadow-2xl border border-white/5">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_0%,rgba(99,102,241,0.25),transparent_65%)]"></div>
            <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>
            <div class="relative flex items-center gap-4">
                <a href="{{ route('cases.index') }}"
                   class="group flex-shrink-0 w-11 h-11 rounded-2xl bg-white/8 border border-white/10 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/15 transition-all duration-200">
                    <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <p class="text-indigo-300/70 text-xs font-semibold uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-400"></i>
                        Disciplinary Record
                    </p>
                    <h1 class="text-white text-2xl font-bold tracking-tight">Log Violation Case</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('cases.store') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id"   x-model="sid">
            <input type="hidden" name="violation_id" x-model="vid">

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                {{-- ═══════════════ MAIN (3/5) ═══════════════ --}}
                <div class="lg:col-span-3 space-y-4">

                    {{-- ── STUDENT ── --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
                        {{-- Section label --}}
                        <div class="px-5 pt-5 pb-3 flex items-center gap-2 border-b border-gray-100">
                            <span class="w-5 h-5 rounded-md bg-indigo-100 flex items-center justify-center shrink-0">
                                <i data-lucide="user" class="w-3 h-3 text-indigo-600"></i>
                            </span>
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Student</span>
                            <span class="text-rose-400 text-xs font-bold ml-0.5">*</span>
                        </div>

                        <div class="p-4">
                            {{-- Prefilled: ID card --}}
                            <template x-if="prefilled">
                                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-slate-800 to-indigo-900 p-4 flex items-center gap-4">
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_50%,rgba(99,102,241,0.2),transparent_70%)]"></div>
                                    {{-- Avatar --}}
                                    <div class="relative flex-shrink-0 w-14 h-14 rounded-xl bg-indigo-600/40 border border-indigo-400/30 flex items-center justify-center text-xl font-black text-white">
                                        <span x-text="student?.initials"></span>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-400 border-2 border-slate-800 flex items-center justify-center">
                                            <i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>
                                        </div>
                                    </div>
                                    {{-- Info --}}
                                    <div class="relative flex-1 min-w-0">
                                        <p class="text-white font-bold text-base truncate" x-text="student?.name"></p>
                                        <p class="text-indigo-200/60 text-xs mt-0.5 truncate" x-text="student?.dept"></p>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <span class="px-2 py-0.5 rounded-md bg-white/10 text-white/70 text-[10px] font-semibold" x-text="student?.year"></span>
                                            <span class="px-2 py-0.5 rounded-md bg-white/10 text-white/70 text-[10px] font-semibold" x-text="student?.section"></span>
                                            <template x-if="student?.cases > 0">
                                                <span class="px-2 py-0.5 rounded-md bg-rose-500/30 text-rose-300 text-[10px] font-bold"
                                                    x-text="`${student.cases} prior case${student.cases>1?'s':''}`"></span>
                                            </template>
                                            <template x-if="student?.cases == 0">
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">Clean record</span>
                                            </template>
                                        </div>
                                    </div>
                                    {{-- Lock --}}
                                    <div class="relative flex-shrink-0">
                                        <i data-lucide="lock" class="w-4 h-4 text-indigo-300/60"></i>
                                    </div>
                                </div>
                            </template>

                            {{-- Free pick --}}
                            <template x-if="!prefilled">
                                <div>
                                    {{-- Selected card --}}
                                    <div x-show="student" x-cloak class="mb-3 fade-up">
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50 border border-indigo-200">
                                            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shrink-0" x-text="student?.initials"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate" x-text="student?.name"></p>
                                                <p class="text-xs text-gray-500 truncate" x-text="student?.dept"></p>
                                            </div>
                                            <button type="button" @click="clearStudent()"
                                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 hover:border-rose-300 hover:bg-rose-50 flex items-center justify-center text-gray-400 hover:text-rose-500 transition-all shrink-0">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Dropdown trigger --}}
                                    <div x-show="!student" class="relative" @click.outside="sOpen=false">
                                        <button type="button" @click="sOpen=!sOpen"
                                            class="w-full flex items-center gap-3 px-3.5 py-3 border-2 rounded-xl text-sm transition-all text-left bg-gray-50 hover:bg-white"
                                            :class="sOpen?'border-indigo-500 bg-white ring-4 ring-indigo-500/10':'border-gray-200'">
                                            <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                                            <span class="text-gray-400 flex-1">Search student by name or department…</span>
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="sOpen?'rotate-180':''"></i>
                                        </button>

                                        <div x-show="sOpen" x-cloak class="absolute z-50 w-full mt-1 bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden drop-in">
                                            <div class="flex items-center gap-2 p-2.5 border-b border-gray-100 bg-gray-50">
                                                <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>
                                                <input type="text" x-model="sQ" placeholder="Type to filter…"
                                                    class="flex-1 bg-transparent text-sm outline-none text-gray-800 placeholder-gray-400" autofocus>
                                                <button type="button" x-show="sQ" @click="sQ=''" class="text-gray-400 hover:text-gray-600">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                            <div class="max-h-56 overflow-y-auto divide-y divide-gray-50">
                                                <template x-for="s in sFiltered" :key="s.id">
                                                    <button type="button" @mousedown.prevent="pickStudent(s)"
                                                        class="w-full flex items-center gap-3 px-3.5 py-2.5 text-left hover:bg-slate-50 transition-colors"
                                                        :class="sid==s.id?'bg-indigo-50':''">
                                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
                                                            :class="sid==s.id?'bg-indigo-600 text-white':'bg-indigo-100 text-indigo-600'"
                                                            x-text="s.initials"></div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="s.name"></p>
                                                            <p class="text-[10px] text-gray-400 truncate" x-text="s.dept_short"></p>
                                                        </div>
                                                        <template x-if="s.cases>0">
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-500 shrink-0" x-text="`${s.cases}×`"></span>
                                                        </template>
                                                    </button>
                                                </template>
                                                <div x-show="sFiltered.length===0" class="py-8 text-center text-xs text-gray-400">
                                                    No students found
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── VIOLATION ── --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
                        <div class="px-5 pt-5 pb-3 flex items-center gap-2 border-b border-gray-100">
                            <span class="w-5 h-5 rounded-md bg-rose-100 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-alert" class="w-3 h-3 text-rose-600"></i>
                            </span>
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Violation</span>
                            <span class="text-rose-400 text-xs font-bold ml-0.5">*</span>
                        </div>

                        <div class="p-4 space-y-3">
                            {{-- Selected violation pill --}}
                            <div x-show="violation" x-cloak class="fade-up">
                                <div class="flex items-center gap-3 p-3 rounded-xl border"
                                    :class="severity==='Critical'?'bg-red-50 border-red-200':severity==='Major'?'bg-orange-50 border-orange-200':'bg-yellow-50 border-yellow-200'">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[9px] font-black shrink-0"
                                        :class="severity==='Critical'?'bg-red-100 text-red-700':severity==='Major'?'bg-orange-100 text-orange-700':'bg-yellow-100 text-yellow-700'"
                                        x-text="violation?.code"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate" x-text="violation?.title"></p>
                                        <span class="inline-flex items-center gap-1 mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                            :class="sevClass.badge" x-text="severity"></span>
                                    </div>
                                    <button type="button" @click="clearViolation()"
                                        class="w-7 h-7 rounded-lg bg-white border border-gray-200 hover:border-rose-300 hover:bg-rose-50 flex items-center justify-center text-gray-400 hover:text-rose-500 transition-all shrink-0">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Dropdown --}}
                            <div class="relative" @click.outside="vOpen=false">
                                <button type="button" @click="vOpen=!vOpen"
                                    class="w-full flex items-center gap-3 px-3.5 py-3 border-2 rounded-xl text-sm transition-all text-left bg-gray-50 hover:bg-white"
                                    :class="vOpen?'border-rose-500 bg-white ring-4 ring-rose-500/10':'border-gray-200'">
                                    <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                                    <span class="text-gray-400 flex-1" x-text="violation?'Change violation…':'Search violation by code or title…'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="vOpen?'rotate-180':''"></i>
                                </button>

                                <div x-show="vOpen" x-cloak class="absolute z-50 w-full mt-1 bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden drop-in">
                                    <div class="flex items-center gap-2 p-2.5 border-b border-gray-100 bg-gray-50">
                                        <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>
                                        <input type="text" x-model="vQ" placeholder="Type violation code or title…"
                                            class="flex-1 bg-transparent text-sm outline-none text-gray-800 placeholder-gray-400" autofocus>
                                        <button type="button" x-show="vQ" @mousedown.prevent="vQ=''" class="text-gray-400 hover:text-gray-600">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-56 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="v in vFiltered" :key="v.id">
                                            <button type="button" @mousedown.prevent="pickViolation(v)"
                                                class="w-full flex items-start gap-3 px-3.5 py-3 text-left hover:bg-slate-50 transition-colors"
                                                :class="vid==v.id?'bg-rose-50':''">
                                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[9px] font-black shrink-0 mt-0.5"
                                                    :class="v.severity==='Major'||v.severity==='Critical'?'bg-rose-100 text-rose-700':'bg-yellow-100 text-yellow-700'"
                                                    x-text="v.code"></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="v.title"></p>
                                                    <span class="inline-flex mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                                        :class="v.severity==='Major'||v.severity==='Critical'?'bg-rose-100 text-rose-600':'bg-yellow-100 text-yellow-600'"
                                                        x-text="v.severity"></span>
                                                </div>
                                                <template x-if="vid==v.id">
                                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-rose-500 mt-1 shrink-0"></i>
                                                </template>
                                            </button>
                                        </template>
                                        <div x-show="vFiltered.length===0" class="py-8 text-center text-xs text-gray-400">No violations found</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sanction preview --}}
                            <div x-show="vid" x-cloak class="fade-up">
                                <div x-show="loading" class="flex items-center gap-2 text-xs text-gray-400 py-2 px-1">
                                    <span class="w-3.5 h-3.5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></span>
                                    Loading sanction details…
                                </div>
                                <div x-show="!loading" class="flex items-stretch gap-3">
                                    <div class="flex-1 flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" :class="sevClass.dot"></div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Severity</p>
                                            <p class="text-sm font-bold text-slate-800 mt-0.5" x-text="severity||'—'"></p>
                                        </div>
                                    </div>
                                    <div class="flex-[2] flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                        <i data-lucide="gavel" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sanction</p>
                                            <p class="text-xs font-semibold text-slate-700 mt-0.5 leading-snug" x-text="sanction||'—'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── INCIDENT DETAILS ── --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center gap-2 border-b border-gray-100">
                            <span class="w-5 h-5 rounded-md bg-amber-100 flex items-center justify-center shrink-0">
                                <i data-lucide="file-pen-line" class="w-3 h-3 text-amber-600"></i>
                            </span>
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Incident Details</span>
                        </div>

                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Description <span class="text-rose-400">*</span>
                                </label>
                                <textarea name="description" rows="4" required
                                    placeholder="Describe the incident — what happened, where, any relevant context…"
                                    class="w-full px-3.5 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 resize-none leading-relaxed outline-none">{{ old('description') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Date & Time <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                            <i data-lucide="calendar-clock" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="datetime-local" name="occurred_at" required
                                            value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"
                                            class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Witness <span class="text-gray-300 font-normal normal-case">(optional)</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                            <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="text" name="witness" value="{{ old('witness') }}"
                                            placeholder="e.g. Prof. Santos"
                                            class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SUBMIT ── --}}
                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ route('cases.index') }}"
                           class="text-sm font-semibold text-gray-400 hover:text-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 hover:-translate-y-0.5 hover:shadow-indigo-500/30 transition-all duration-200">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            Log Violation Case
                        </button>
                    </div>
                </div>

                {{-- ═══════════════ SIDEBAR (2/5) ═══════════════ --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Policy panel --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 border-b border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Policy Details</p>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="!vid" class="p-5 text-center py-10">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="fingerprint" class="w-6 h-6 text-gray-400"></i>
                            </div>
                            <p class="text-xs font-semibold text-gray-400">Select a violation to view policy details</p>
                        </div>

                        {{-- Loaded state --}}
                        <div x-show="vid" x-cloak class="p-5 space-y-4 fade-up">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Severity Level</p>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border" :class="sevClass.badge">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="sevClass.dot"></span>
                                        <span x-text="severity||'Loading…'"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Expected Sanction</p>
                                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                                    <p class="text-xs font-semibold text-gray-700 leading-relaxed italic" x-text="sanction||'—'"></p>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 px-0.5">Based on Student Handbook — first offense</p>
                            </div>
                        </div>
                    </div>

                    {{-- Compliance notice --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                                <i data-lucide="triangle-alert" class="w-4 h-4 text-amber-600"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-amber-800 mb-1">Compliance Warning</p>
                                <p class="text-xs text-amber-700 leading-relaxed">All incident logs are subject to institutional audit. Ensure factual accuracy before authorization.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Quick tips --}}
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-4 space-y-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Quick Guide</p>
                        <div class="space-y-2.5">
                            @foreach([['user','Select the student involved in the incident'],['shield-alert','Choose the specific violation from the handbook'],['file-pen-line','Write a factual, detailed description'],['calendar-clock','Set the exact date and time of occurrence']] as [$icon, $tip])
                            <div class="flex items-start gap-2.5">
                                <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="{{ $icon }}" class="w-3 h-3 text-gray-500"></i>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">{{ $tip }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
