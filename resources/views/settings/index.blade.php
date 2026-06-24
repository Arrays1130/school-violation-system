<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="sliders" class="w-6 h-6 text-indigo-600"></i>
                System Settings
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Manage global system configurations and defaults.</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    
                    <div class="max-w-xl">
                        <div class="mb-6">
                            <label for="current_academic_year" class="block text-sm font-bold text-slate-700 mb-2">Current Academic Year</label>
                            <p class="text-xs text-slate-500 mb-3">This sets the default academic year for new student registrations. Existing students will remain in their originally assigned academic year to preserve historical accuracy.</p>
                            
                            @php
                                $currentYear = date('Y');
                                $years = [];
                                for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                                    $years[] = "SY " . $i . "-" . ($i + 1);
                                }
                            @endphp
                            
                            <select id="current_academic_year" 
                                   name="current_academic_year" 
                                   class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm appearance-none"
                                   required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ old('current_academic_year', $currentAcademicYear) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('current_academic_year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 py-2 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-2">End-of-Year Processing</h2>
                <p class="text-sm text-slate-500 mb-6">Archive all currently <strong>Closed</strong> cases to clear out your dashboard for the new academic year. Archived cases will no longer appear in the main dashboard statistics but can always be found in the <strong>Record Retrieval</strong> page.</p>
                
                <form id="archiveForm" action="{{ route('settings.archive-cases') }}" method="POST">
                    @csrf
                    
                    <div class="max-w-xl">
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 mb-6 flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <h3 class="text-sm font-bold text-amber-800">Warning</h3>
                                <p class="text-xs text-amber-700 mt-1">Make sure you have officially closed all cases for the previous academic year before archiving. Only cases with a "Closed" status will be archived.</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button type="button" onclick="confirmArchive()" class="inline-flex items-center gap-2 justify-center rounded-xl border border-transparent bg-slate-800 py-2 px-4 text-sm font-bold text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                                Archive All Closed Cases
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmArchive() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to archive ALL Closed cases. This action will remove them from your active dashboard.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e293b', // slate-800
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, archive them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('archiveForm').submit();
                }
            })
        }
    </script>
</x-app-layout>
