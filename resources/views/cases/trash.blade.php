<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <a href="{{ route('students.index') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Students</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-gray-900 dark:text-white font-medium">Trash Bin</span>
        </div>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <i data-lucide="trash-2" class="w-8 h-8 text-gray-400"></i>
                Trash Bin
            </h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">Recover deleted students or individual violation records.</p>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-1 mb-6 p-1 bg-gray-100 dark:bg-gray-900/50 rounded-xl w-fit">
            <a href="{{ route('students.trash') }}" class="px-6 py-2.5 text-sm font-bold rounded-lg transition-all {{ request()->routeIs('students.trash') ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Deleted Students
            </a>
            <a href="{{ route('cases.trash') }}" class="px-6 py-2.5 text-sm font-bold rounded-lg transition-all {{ request()->routeIs('cases.trash') ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Deleted Violation Records
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Violation</th>
                            <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deleted At</th>
                            <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($cases as $case)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $case->violation->code }} — {{ $case->violation->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">
                                        {{ Str::limit($case->description, 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $case->student->full_name ?? 'Restoration Required' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $case->student->department ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $case->deleted_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form id="restore-case-{{ $case->id }}" action="{{ route('cases.restore', $case->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="button" 
                                                    onclick="confirmAction('restore-case-{{ $case->id }}', {
                                                        title: 'Restore Violation Record?',
                                                        text: 'This record will be added back to the student\'s violation history.',
                                                        icon: 'info',
                                                        confirmButtonText: 'Yes, restore record'
                                                    })"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 rounded-lg text-sm font-bold transition-all border border-blue-100 dark:border-blue-800">
                                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                                Restore Record
                                            </button>
                                        </form>
                                        <form id="force-delete-case-{{ $case->id }}" action="{{ route('cases.force-delete', $case->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmAction('force-delete-case-{{ $case->id }}', {title: 'Permanently Delete Record?', text: 'This cannot be undone. The violation record will be erased forever.', icon: 'warning', confirmButtonText: 'Yes, delete permanently', confirmButtonColor: '#dc2626'})" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 rounded-lg text-sm font-bold transition-all border border-red-100 dark:border-red-800">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                Delete Permanently
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mb-4 text-gray-300">
                                            <i data-lucide="file-x" class="w-10 h-10"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No deleted violations</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                            Violation records you delete will appear here for recovery.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cases->hasPages())
                <div class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
