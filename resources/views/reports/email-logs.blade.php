<x-app-layout>
    <div class="px-8 py-8">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('reports.index') }}" class="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-5.5 h-5.5"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        System Activity
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Email Logs</h2>
                    <p class="text-indigo-100/70 text-sm mt-1.5">Monitor and audit outgoing automated notifications and institutional correspondence.</p>
                </div>
            </div>
        </div>

        {{-- Integrated Search Filter --}}
        <div class="bg-white rounded-lg p-5 mb-6 shadow-sm border border-gray-200">
            <form action="{{ route('reports.email-logs') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Search Recipient</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Search by recipient email or subject..." 
                            class="w-full pl-9 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm"
                            autocomplete="off"
                        >
                    </div>
                </div>
                
                <div class="w-full md:w-auto flex gap-3">
                    <button type="submit" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition-colors shadow-sm">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Execute Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('reports.email-logs') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-lg border border-gray-300 transition-colors" title="Clear Search">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Logs Data Grid --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $log->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                            <i data-lucide="mail" class="w-4 h-4"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->recipient }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 font-medium">{{ $log->subject }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 inline-flex text-xs font-medium rounded-md
                                        {{ $log->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button onclick="viewEmailDetails({{ $log->id }})" class="text-indigo-600 hover:text-indigo-900 transition-colors inline-flex items-center gap-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="inbox" class="w-8 h-8 text-gray-300 mb-3"></i>
                                        <p>No email logs found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Detail Modal --}}
    <div id="emailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Email Contents</h3>
                        <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="px-6 py-6">
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">To</h4>
                                <p id="modal-recipient" class="text-sm font-medium text-gray-900"></p>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Subject</h4>
                                <p id="modal-subject" class="text-sm font-medium text-gray-900"></p>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Message Body</h4>
                                <div id="modal-body" class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-700 whitespace-pre-wrap font-mono"></div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            Close Window
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const logsData = @json($logs->items());

        function viewEmailDetails(id) {
            const log = logsData.find(l => l.id === id);
            if(log) {
                document.getElementById('modal-recipient').textContent = log.recipient;
                document.getElementById('modal-subject').textContent = log.subject;
                document.getElementById('modal-body').innerHTML = log.body;
                
                document.getElementById('emailModal').classList.remove('hidden');
            }
        }

        function closeModal() {
            document.getElementById('emailModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
