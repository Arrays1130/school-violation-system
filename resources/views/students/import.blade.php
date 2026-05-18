<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <a href="{{ route('students.index') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Students</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-gray-900 dark:text-white font-medium">Import Students</span>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Students</h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Upload a CSV or Excel file to bulk import students.</p>
                </div>

                <div class="p-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Excel File
                            </label>
                            <div class="flex items-center justify-center w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i data-lucide="upload-cloud" class="w-10 h-10 mb-3 text-gray-400"></i>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">XLSX, XLS or CSV</p>
                                    </div>
                                    <input id="dropzone-file" type="file" name="file" class="hidden" accept=".xlsx,.xls,.csv" required />
                                </label>
                            </div>
                            <div id="file-name" class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center hidden"></div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Instructions</h3>
                            <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
                                <li>The file must have a header row.</li>
                                <li>Required columns: <strong>full_name, email, department, year_level, section</strong></li>
                                <li>Optional columns: <strong>guardian_name, guardian_email, guardian_phone</strong></li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('students.index') }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all shadow-sm">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Import Students
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('dropzone-file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameDisplay = document.getElementById('file-name');
            if (fileName) {
                fileNameDisplay.textContent = 'Selected file: ' + fileName;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
