<x-app-layout>
    @section('header', 'Modify Student Profile')

    <div class="max-w-3xl mx-auto space-y-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('students.show', $student) }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Edit Student Profile</h2>
                    <p class="text-indigo-100/70 text-sm mt-1">Updating Profile: <span class="text-white font-medium">{{ $student->full_name }}</span></p>
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <form action="{{ route('students.update', $student) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8 space-y-8">
                    {{-- Identity --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                            <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Student Identification</h3>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="user-circle" class="w-4.5 h-4.5"></i>
                                </div>
                                <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}" required 
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Year Level</label>
                                <div class="relative">
                                    <select name="year_level" required 
                                        class="w-full pl-4 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                        @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                                            <option value="{{ $year }}" {{ old('year_level', $student->year_level) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Section</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="hash" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="section" value="{{ old('section', $student->section) }}" required 
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                </div>
                            </div>
                        </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="at-sign" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="email" name="email" value="{{ old('email', $student->email) }}" placeholder="student@example.com"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('email') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" placeholder="e.g. 09XX XXX XXXX"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                    @error('phone') <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Department</label>
                            <div class="relative">
                                <select name="department" required 
                                    class="w-full pl-4 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    @foreach(['CBAE', 'CTE', 'CCJE', 'HS', 'CCE'] as $dept)
                                        <option value="{{ $dept }}" {{ old('department', $student->department) == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4.5 h-4.5"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Emergency --}}
                    <div class="space-y-5 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                            <i data-lucide="shield" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Emergency Contact</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Guardian Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="user-check" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" placeholder="e.g. Jane Doe"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Guardian Phone</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}" placeholder="e.g. +63 912 345 6789"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                    <button type="button" onclick="confirmDelete('delete-student-form', 'student profile')" class="text-sm font-bold text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Delete Student
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                        <i data-lucide="save" class="w-4.5 h-4.5"></i>
                        Save Changes
                    </button>
                </div>
            </form>
            
            <form id="delete-student-form" action="{{ route('students.destroy', $student) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
