<x-app-layout>
    @section('header', 'System Audit Logs')

    <div class="space-y-6">
        {{-- High-End Branded Header Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        Security & Accountability
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">System Audit Logs</h1>
                    <p class="text-indigo-100/70 text-sm mt-1 max-w-2xl leading-relaxed">Track modifications to sensitive student records, system configuration, and user roles.</p>
                </div>
            </div>
        </div>

        {{-- Analytics Data List --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-left">
                    <thead>
                        <tr class="bg-gray-50/60">
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">User (Causer)</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Module (Subject)</th>
                            <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white text-sm">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900">{{ $log->created_at->format('M d, Y') }}</span>
                                        <span class="text-[11px] text-gray-500">{{ $log->created_at->format('h:i:s A') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $log->causer->name ?? 'System / Anonymous' }}
                                    <div class="text-xs text-gray-400 font-normal">{{ $log->causer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $color = match($log->event) {
                                            'created' => 'bg-green-100 text-green-800',
                                            'updated' => 'bg-blue-100 text-blue-800',
                                            'deleted' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $color }} uppercase">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600 max-w-sm">
                                    @if($log->properties->count() > 0)
                                        <details class="cursor-pointer group">
                                            <summary class="font-medium text-indigo-600 hover:text-indigo-800 transition-colors select-none">View Details</summary>
                                            <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 overflow-x-auto">
                                                @if(isset($log->properties['old']))
                                                    <div class="mb-2">
                                                        <strong class="text-red-600">Old:</strong>
                                                        <pre class="mt-1 text-[10px] bg-red-50 p-2 rounded text-red-800">{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @endif
                                                @if(isset($log->properties['attributes']))
                                                    <div>
                                                        <strong class="text-green-600">New:</strong>
                                                        <pre class="mt-1 text-[10px] bg-green-50 p-2 rounded text-green-800">{{ json_encode($log->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-gray-400 italic">No details recorded</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No activity logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-150">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
