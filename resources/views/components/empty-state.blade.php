@props(['title', 'description', 'icon' => 'folder-open'])
<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 text-gray-400 dark:text-gray-500">
        <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">{{ $title }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">{{ $description }}</p>
    @if(isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
