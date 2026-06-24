<x-app-layout>
    @section('header', 'Trash Bin')

    <div class="container mx-auto px-4 py-6 max-w-7xl">
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 font-medium transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
            <a href="{{ route('students.index') }}" class="hover:text-indigo-600 font-medium transition-colors">Students</a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
            <span class="text-slate-800 font-bold">Trash Bin</span>
        </div>

        {{-- Hero Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 to-red-950 px-6 py-5 shadow-xl shadow-red-900/10 mb-6 border border-red-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(244,63,94,0.15),_transparent_50%)]"></div>
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i data-lucide="trash-2" class="w-20 h-20 text-white"></i>
            </div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                        <i data-lucide="archive" class="w-3 h-3"></i> Recovery Zone
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1">
                        Trash Bin
                    </h1>
                    <p class="text-red-100 text-xs sm:text-sm font-medium max-w-xl">
                        Recover deleted students or permanently remove them from the system.
                    </p>
                </div>
            </div>
        </div>

        {{-- Modern Tabs --}}
        <div class="flex items-center gap-2 mb-8 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-sm w-fit">
            <a href="{{ route('students.trash') }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all flex items-center gap-2 {{ request()->routeIs('students.trash') ? 'bg-rose-600 text-white shadow-md shadow-rose-500/20' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                Deleted Students
            </a>
            <a href="{{ route('cases.trash') }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all flex items-center gap-2 {{ request()->routeIs('cases.trash') ? 'bg-rose-600 text-white shadow-md shadow-rose-500/20' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                <i data-lucide="folder-x" class="w-4 h-4"></i>
                Deleted Cases
            </a>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Student Details</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Deleted At</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-sm font-bold shadow-sm">
                                            {{ $student->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-800">
                                                {{ $student->full_name }}
                                            </div>
                                            <div class="text-xs font-medium text-slate-500 mt-0.5">
                                                {{ $student->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-50 border border-slate-200/60 text-xs font-semibold text-slate-600">
                                        {{ trim($student->department) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                        <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                                        {{ $student->deleted_at->format('M d, Y h:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form id="restore-student-{{ $student->id }}" action="{{ route('students.restore', $student->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="button" 
                                                    onclick="confirmAction('restore-student-{{ $student->id }}', {
                                                        title: 'Restore Student?',
                                                        text: 'This will bring the student and all their violation records back to the active list.',
                                                        icon: 'info',
                                                        confirmButtonText: 'Yes, Restore Student'
                                                    })"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-emerald-100 hover:border-emerald-500 shadow-sm hover:shadow-emerald-500/20">
                                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                                Restore
                                            </button>
                                        </form>
                                        <form id="force-delete-student-{{ $student->id }}" action="{{ route('students.force-delete', $student->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction('force-delete-student-{{ $student->id }}', {
                                                        title: 'Permanently Delete Student?', 
                                                        text: 'This cannot be undone. The student and all records will be erased forever.', 
                                                        icon: 'warning', 
                                                        confirmButtonText: 'Yes, Delete Permanently', 
                                                        confirmButtonColor: '#e11d48'
                                                    })" 
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-rose-100 hover:border-rose-600 shadow-sm hover:shadow-rose-600/20">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-24 h-24 bg-slate-50 border border-slate-100 rounded-3xl flex items-center justify-center mb-6 shadow-sm">
                                            <i data-lucide="wind" class="w-12 h-12 text-slate-300"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-800 mb-2">Trash is empty</h3>
                                        <p class="text-sm font-medium text-slate-500 max-w-sm mx-auto">
                                            No deleted students found. Students you delete will appear here for recovery.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
                <div class="bg-slate-50/50 border-t border-slate-100 px-6 py-4">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
