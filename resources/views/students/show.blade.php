<x-app-layout>
    @section('header', 'Student Profile')

    <div class="space-y-6" x-data="{ showMessageModal: false }">
        {{-- Profile Header Card --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 p-1 shadow-inner backdrop-blur-md">
                        <div class="w-full h-full rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                            {{ $student->initials }}
                        </div>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            Student Profile
                        </div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">{{ $student->full_name }}</h1>
                        <p class="text-indigo-100/70 text-sm mt-1 flex items-center gap-2">
                            <span>{{ $student->department }}</span>
                            <span class="w-1 h-1 rounded-full bg-indigo-400/50"></span>
                            <span>{{ $student->year_level }}</span>
                            <span class="w-1 h-1 rounded-full bg-indigo-400/50"></span>
                            <span>{{ $student->section }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('students.print', $student) }}" target="_blank" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="printer" class="w-4.5 h-4.5"></i>
                        Print
                    </a>
                    <a href="{{ route('students.edit', $student) }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                        Edit
                    </a>
                    <button @click="showMessageModal = true" class="px-5 py-2.5 bg-emerald-500 border border-emerald-400 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-emerald-400 transition-all flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4.5 h-4.5"></i>
                        Message Guardian
                    </button>
                    <a href="{{ route('cases.create', ['student_id' => $student->id]) }}" class="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4.5 h-4.5"></i>
                        Log Violation
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Details --}}
            <div class="space-y-6">
                {{-- Student Information --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Student Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Email</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $student->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Guardian</p>
                                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $student->guardian_name ?? 'Not Listed' }}</p>
                                @if($student->guardian_phone)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $student->guardian_phone }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="badge-check" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</p>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-100 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Active Enrollment
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Incident Summary Card --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Incident Summary</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-3xl font-bold {{ $offenseSummary['total'] > 2 ? 'text-red-600' : ($offenseSummary['total'] > 0 ? 'text-amber-600' : 'text-green-600') }}">
                                {{ $offenseSummary['total'] }}
                            </p>
                            <p class="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Total</p>
                        </div>
                        <div class="text-center border-x border-gray-200">
                            <p class="text-3xl font-bold text-amber-600">{{ $offenseSummary['minor'] }}</p>
                            <p class="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Minor</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-red-600">{{ $offenseSummary['major'] }}</p>
                            <p class="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Major</p>
                        </div>
                    </div>
                    {{-- Risk indicator --}}
                    @php
                        $riskLevel = $offenseSummary['total'] >= 5 ? 'High' : ($offenseSummary['total'] >= 2 ? 'Medium' : 'Low');
                        $riskColor = $offenseSummary['total'] >= 5 ? 'bg-red-50 text-red-700 border-red-200' : ($offenseSummary['total'] >= 2 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-green-50 text-green-700 border-green-200');
                    @endphp
                    <div class="mt-5 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Risk Level</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $riskColor }}">
                            {{ $riskLevel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Violation Timeline --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
                    <div class="p-5 flex items-center justify-between gap-4 border-b border-gray-100 bg-gray-50/40">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-slate-800 tracking-tight">Violation Timeline</h2>
                            <p class="text-sm text-slate-500 mt-1">Chronological record of this student’s violation cases.</p>
                        </div>
                        <a href="{{ route('cases.create', ['student_id' => $student->id]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition-all duration-200">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Add Incident
                        </a>
                    </div>

                    {{-- Timeline --}}
                    <div class="p-6">
                        @php
                            $cases = $student->cases->sortByDesc('occurred_at');
                        @endphp
                        @forelse($cases as $case)
                        <div class="flex gap-4 pb-6 last:pb-0 group">
                            {{-- Timeline line + dot --}}
                            <div class="flex flex-col items-center">
                                @php
                                    $dotColor = match($case->status) {
                                        'Pending' => 'bg-amber-500 ring-amber-100',
                                        'Hearing Scheduled' => 'bg-blue-500 ring-blue-100',
                                        'Closed' => 'bg-green-500 ring-green-100',
                                        'Endorsed to Grievance' => 'bg-red-500 ring-red-100',
                                        default => 'bg-gray-400 ring-gray-100',
                                    };
                                @endphp
                                <div class="w-3 h-3 rounded-full {{ $dotColor }} ring-4 mt-1.5 flex-shrink-0 z-10"></div>
                                @if(!$loop->last)
                                    <div class="w-0.5 flex-1 bg-gray-200 mt-1"></div>
                                @endif
                            </div>
                            {{-- Content Card --}}
                            <div class="flex-1 bg-white/90 backdrop-blur-xl rounded-2xl p-6 ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-300 hover:border-indigo-200 hover:shadow-md transition-all duration-200 -mt-0.5">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="text-sm font-semibold text-slate-800">{{ $case->violation->title }}</h4>
                                            @php
                                                $sevColor = match($case->violation->severity ?? 'Minor') {
                                                    'Minor' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                                    'Major' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                    'Critical' => 'bg-red-50 text-red-700 border-red-200',
                                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                                };
                                            @endphp
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $sevColor }}">{{ $case->violation->severity ?? 'Minor' }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 line-clamp-2">{{ $case->description }}</p>
                                        <div class="flex items-center gap-4 mt-2.5">
                                            <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                {{ $case->occurred_at->format('M d, Y') }}
                                            </span>
                                            @php
                                                $smap = [
                                                    'Pending' => 'text-amber-600',
                                                    'Closed' => 'text-green-600',
                                                    'Hearing Scheduled' => 'text-blue-600',
                                                    'Endorsed to Grievance' => 'text-red-600',
                                                ];
                                                $color = $smap[$case->status] ?? 'text-slate-500';
                                            @endphp
                                            <span class="text-[11px] font-bold uppercase tracking-wider {{ $color }}">{{ $case->status }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('cases.show', $case) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all flex-shrink-0">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center bg-white">
                            <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-4 border border-green-100">
                                <i data-lucide="shield-check" class="w-8 h-8 text-green-500"></i>
                            </div>
                            <h4 class="text-base font-semibold text-slate-800">Exemplary Conduct</h4>
                            <p class="text-sm text-slate-500 mt-1.5 max-w-xs mx-auto">
                                No violation records found. This student maintains an excellent behavioral record.
                            </p>
                            <a href="{{ route('cases.create', ['student_id' => $student->id]) }}"
                               class="mt-7 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition-all duration-200">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                Log First Incident
                            </a>
                        </div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>
        <!-- Message Modal -->
        <div x-show="showMessageModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
            <div x-show="showMessageModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showMessageModal = false"></div>
            
            <div x-show="showMessageModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
                 
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-5 h-5 text-indigo-500"></i>
                        Send Message to Guardian
                    </h3>
                    <button @click="showMessageModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <form action="{{ route('students.sendCustomMessage', $student) }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-5">
                        
                        <!-- Delivery Methods -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Delivery Method <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200/60">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="delivery_method[]" value="sms" checked class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700">Send via SMS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="delivery_method[]" value="email" checked class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700">Send via Email</span>
                                </label>
                            </div>
                        </div>

                        <!-- Message Templates -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Pre-made Templates</label>
                            <select onchange="document.getElementById('custom_message').value = this.value" 
                                    class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm bg-slate-50/50">
                                <option value="">-- Create Custom Message --</option>
                                @foreach($messageTemplates as $template)
                                    @php
                                        $parsedContent = str_replace('{{ $student->full_name }}', $student->full_name, $template->content);
                                    @endphp
                                    <option value="{{ $parsedContent }}">{{ $template->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Message Body -->
                        <div>
                            <label for="custom_message" class="block text-sm font-semibold text-slate-700 mb-2">Message <span class="text-red-500">*</span></label>
                            <textarea id="custom_message" name="message" rows="5" required
                                      class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm"
                                      placeholder="Type your message here or select a template above..."></textarea>
                            <p class="text-xs text-slate-500 mt-2">The message will be sent directly to {{ $student->guardian_name ?? 'the guardian' }}.</p>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-3">
                        <button type="button" @click="showMessageModal = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition-colors flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
