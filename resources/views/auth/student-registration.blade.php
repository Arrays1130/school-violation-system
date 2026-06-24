<x-public-layout>
    @section('title', 'Student Registration')

    <div class="space-y-8 animate-[fade-in-up_0.5s_ease-out]">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        New Enrollment
                    </div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Student Registration</h2>
                    <p class="text-indigo-100/70 text-xs mt-1.5">Register a new student profile in the system.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('student.register.send_otp') }}" method="POST">
            @csrf
            
            <div class="bg-white/95 backdrop-blur-xl rounded-2xl ring-1 ring-slate-200/50 shadow-2xl overflow-hidden">
                <div class="p-5 sm:p-8 space-y-8 sm:space-y-10">
                    {{-- Section 1: Identification --}}
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-indigo-100">
                                <i data-lucide="user" class="w-4.5 h-4.5"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Student Identification</h3>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Full Name *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="type" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Last Name, First Name M.I." required 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                @error('full_name') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Year Level *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="graduation-cap" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <select name="year_level" required 
                                        class="w-full pl-10 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                        <option value="">Select Level...</option>
                                        @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                                            <option value="{{ $year }}" {{ old('year_level') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                    @error('year_level') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Section *</label>
                                <div class="relative">
                                    <select name="section" required 
                                        class="w-full pl-10 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                        <option value="">Select Section...</option>
                                        @foreach(['A', 'B', 'C'] as $sec)
                                            <option value="{{ $sec }}" {{ old('section') == $sec ? 'selected' : '' }}>Section {{ $sec }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="users" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                    </div>
                                    @error('section') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Academic Year</label>
                            <div class="relative">
                                @php
                                    $currentYearNum = date('Y');
                                    $years = [];
                                    for ($i = $currentYearNum - 5; $i <= $currentYearNum + 5; $i++) {
                                        $years[] = "SY " . $i . "-" . ($i + 1);
                                    }
                                @endphp
                                <select name="academic_year" required 
                                    class="w-full pl-10 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ old('academic_year', $currentAcademicYear ?? '') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                </div>
                            </div>
                            @error('academic_year') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-500 mt-1.5">Defaults to the global system setting.</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Department *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="building-2" class="w-4.5 h-4.5"></i>
                                </div>
                                <select name="department" required 
                                    class="w-full pl-10 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                    <option value="">Select Department...</option>
                                    @foreach([
                                        'College Of Business And Accounting Education' => 'CBAE', 
                                        'Bachelor Of Technical Vocational Teachers Education' => 'CTE', 
                                        'Bachelor Of Science In Criminology' => 'CCJE', 
                                        'Bachelor Of Science In Information System' => 'CCE'
                                    ] as $value => $label)
                                        <option value="{{ $value }}" {{ old('department') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                                @error('department') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Connectivity --}}
                    <div class="space-y-6 pt-8 border-t border-gray-100">
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                                <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Contact Information</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Email Address *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="at-sign" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="student@ilinkcst.edu.ph" required pattern=".+@ilinkcst\.edu\.ph$" title="Please use your institutional @ilinkcst.edu.ph email address."
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('email') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                    <p class="text-[10px] text-indigo-500 mt-1 font-semibold">Must be an @ilinkcst.edu.ph email.</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('phone') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Emergency --}}
                    <div class="space-y-6 pt-8 border-t border-gray-100">
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                                <i data-lucide="shield-alert" class="w-4.5 h-4.5"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Emergency Contact</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Guardian Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="user-check" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Legal Guardian Name"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('guardian_name') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Guardian Phone</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="+63 912 345 6789"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('guardian_phone') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Guardian Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" placeholder="e.g. guardian@example.com"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('guardian_email') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-5 sm:px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4.5 h-4.5"></i>
                        Send OTP Verification
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <style>
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-public-layout>
