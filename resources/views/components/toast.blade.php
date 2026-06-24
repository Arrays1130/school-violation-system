@if(session()->has('success') || session()->has('error'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 4000)"
         x-show="show"
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 shadow-xl rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
        <div class="p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if(session('success'))
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
                    @else
                        <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
                    @endif
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ session('success') ?? session('error') }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false" class="bg-white dark:bg-gray-800 rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
