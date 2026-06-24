<x-app-layout>
    @section('header', 'System Audit Logs')

    @inject('formatter', 'App\Services\AuditLogFormatter')

    <div class="space-y-6" x-data="auditLogPage()">
        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <a href="{{ route('reports.index') }}" class="mt-1 w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all backdrop-blur-md shrink-0">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            Security & Accountability
                        </div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">System Audit Logs</h1>
                        <p class="text-indigo-100/70 text-sm mt-1 max-w-2xl leading-relaxed">Track modifications to sensitive student records, system configuration, and user roles.</p>
                    </div>
                </div>

                <a href="{{ route('reports.audit-logs.export', request()->all()) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 transition-all shrink-0">
                    <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i>
                    Export CSV
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total (filtered)</p>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Today</p>
                <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['today']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm p-4">
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Created</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ number_format($stats['created']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4">
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Updated</p>
                <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ number_format($stats['updated']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-100 shadow-sm p-4 col-span-2 md:col-span-1">
                <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider">Deleted</p>
                <p class="text-2xl font-extrabold text-red-700 mt-1">{{ number_format($stats['deleted']) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <form action="{{ route('reports.audit-logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="User, email, record ID..."
                               class="w-full pl-9 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Action</label>
                    <select name="event" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                        <option value="">All Actions</option>
                        <option value="created" @selected(request('event') === 'created')>Created</option>
                        <option value="updated" @selected(request('event') === 'updated')>Updated</option>
                        <option value="deleted" @selected(request('event') === 'deleted')>Deleted</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Module</label>
                    <select name="subject_type" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                        <option value="">All Modules</option>
                        @foreach($subjectTypes as $type => $label)
                            <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">User</label>
                    <select name="causer_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('causer_id') == $user->id)>
                                {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                </div>

                <div class="lg:col-span-1 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition-colors shadow-sm">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'event', 'subject_type', 'causer_id', 'date_from', 'date_to']))
                        <a href="{{ route('reports.audit-logs') }}" class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg border border-gray-300 transition-colors" title="Clear filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-left">
                    <thead>
                        <tr class="bg-gray-50/60">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Module</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Summary</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white text-sm">
                        @forelse($logs as $log)
                            @php
                                $changedFields = $formatter->changedFields($log);
                                $subjectUrl = $formatter->subjectUrl($log);
                                $summary = $formatter->changeSummary($log);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800">{{ $log->created_at->format('M d, Y') }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $log->created_at->format('h:i:s A') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 text-xs font-bold">
                                            {{ strtoupper(substr($log->causer->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-800">{{ $log->causer->name ?? 'System' }}</div>
                                            <div class="text-xs text-slate-400">{{ $log->causer->email ?? 'Automated action' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 {{ $formatter->eventColor($log->event ?? '') }} uppercase">
                                        <i data-lucide="{{ $formatter->eventIcon($log->event ?? '') }}" class="w-3 h-3"></i>
                                        {{ $log->event ?? 'unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $formatter->subjectLabel($log) }}</div>
                                    @if($log->subject_id)
                                        @if($subjectUrl)
                                            <a href="{{ $subjectUrl }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">#{{ $log->subject_id }} &rarr;</a>
                                        @else
                                            <span class="text-xs text-slate-400">#{{ $log->subject_id }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    @if(count($changedFields) > 0)
                                        <p class="text-slate-600 text-xs leading-relaxed">{{ $summary }}</p>
                                    @else
                                        <span class="text-slate-400 italic text-xs">{{ $log->description ?: 'No field changes' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if(count($changedFields) > 0)
                                        <button type="button"
                                                @click="openDetail(@js([
                                                    'id' => $log->id,
                                                    'timestamp' => $log->created_at->format('M d, Y h:i:s A'),
                                                    'user' => $log->causer->name ?? 'System',
                                                    'email' => $log->causer->email ?? '',
                                                    'event' => $log->event,
                                                    'module' => $formatter->subjectLabel($log),
                                                    'recordId' => $log->subject_id,
                                                    'fields' => $changedFields,
                                                ]))"
                                                class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium text-xs transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            View
                                        </button>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="clipboard-list" class="w-10 h-10 text-gray-300 mb-3"></i>
                                        <p class="font-medium">No audit logs found</p>
                                        <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or check back later.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-150 bg-gray-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        {{-- Detail Modal --}}
        <div x-show="detailOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6"
             @keydown.escape.window="detailOpen = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="detailOpen = false"></div>

            <div class="relative max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 overflow-hidden mt-8">
                <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-indigo-50/50">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1">Audit Entry</p>
                            <h3 class="text-lg font-bold text-slate-900" x-text="'#' + (detail?.id ?? '')"></h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="detail?.timestamp"></p>
                        </div>
                        <button type="button" @click="detailOpen = false" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b border-slate-100 text-sm">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">User</p>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="detail?.user"></p>
                        <p class="text-xs text-slate-400" x-text="detail?.email"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Action / Module</p>
                        <p class="font-semibold text-slate-800 mt-0.5 capitalize" x-text="detail?.event"></p>
                        <p class="text-xs text-slate-500"><span x-text="detail?.module"></span> <span x-show="detail?.recordId" x-text="'#' + detail?.recordId"></span></p>
                    </div>
                </div>

                <div class="px-6 py-5 max-h-[50vh] overflow-y-auto">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Field Changes</p>
                    <template x-if="detail?.fields?.length">
                        <div class="space-y-3">
                            <template x-for="field in detail.fields" :key="field.field">
                                <div class="rounded-xl border border-slate-100 overflow-hidden">
                                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-100">
                                        <span class="text-xs font-bold text-slate-700" x-text="field.label"></span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                                        <div class="px-3 py-2.5">
                                            <p class="text-[10px] font-bold text-red-500 uppercase mb-1">Before</p>
                                            <p class="text-xs text-slate-700 break-words" x-text="field.old ?? '—'"></p>
                                        </div>
                                        <div class="px-3 py-2.5 bg-emerald-50/30">
                                            <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">After</p>
                                            <p class="text-xs text-slate-700 break-words" x-text="field.new ?? '—'"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="button" @click="detailOpen = false"
                            class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function auditLogPage() {
            return {
                detailOpen: false,
                detail: null,
                openDetail(data) {
                    this.detail = data;
                    this.detailOpen = true;
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
