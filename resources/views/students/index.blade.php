<x-app-layout>
    @section('header', 'Student Records')

    <div class="space-y-6">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        Student Database
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Student Records</h1>
                    <p class="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Centralized management of student profiles, academic details, and disciplinary histories.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('students.import_form') }}" class="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold hover:bg-white/20 transition-all backdrop-blur-md flex items-center gap-2 shadow-sm hover:shadow">
                        <i data-lucide="upload-cloud" class="w-4.5 h-4.5"></i>
                        Import Data
                    </a>
                    <a href="{{ route('students.create') }}" class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                        <i data-lucide="user-plus" class="w-4.5 h-4.5"></i>
                        Add Student
                    </a>
                </div>
            </div>
        </div>


        {{-- Search & Filters --}}
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
            <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Search Records</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, or email..." 
                               class="w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department" class="w-full px-3 py-2.5 bg-white border border-gray-200 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('students.index') }}" class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Clear
                </a>
            </form>
        </div>

        {{-- Records List --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-6 py-3.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Academic Details</th>
                            <th scope="col" class="px-6 py-3.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider text-center">Incidents</th>
                            <th scope="col" class="px-6 py-3.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs border border-blue-100">
                                            {{ $student->initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-900">{{ $student->department }}</span>
                                        <span class="text-xs text-gray-400 mt-0.5">{{ $student->year_level }} — {{ $student->section }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($student->cases_count > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ $student->cases_count }} {{ Str::plural('Case', $student->cases_count) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Clear
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('cases.create', ['student_id' => $student->id]) }}" 
                                           class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-150" title="Log Violation">
                                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('students.show', $student) }}" 
                                           class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-150" title="View Profile">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('students.edit', $student) }}" 
                                           class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all duration-150" title="Edit Profile">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete('delete-student-{{ $student->id }}', 'student profile')" 
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-150" title="Delete Profile">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        <form id="delete-student-{{ $student->id }}" action="{{ route('students.destroy', $student) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                                            <i data-lucide="users" class="w-7 h-7 text-gray-400"></i>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900">No Students Found</h3>
                                        <p class="text-sm text-gray-500 mt-1 max-w-xs">Refine your search parameters or add a new student.</p>
                                        <a href="{{ route('students.create') }}" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                            Add First Student
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($students->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
