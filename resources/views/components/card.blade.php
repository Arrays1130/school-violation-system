@props(['title' => null, 'description' => null, 'padding' => 'p-6 sm:p-8', 'icon' => null])
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700/50']) }}>
    @if($title)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($icon) <div class="text-gray-400 dark:text-gray-500">{!! $icon !!}</div> @endif
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if($description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</p>
                    @endif
                </div>
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
