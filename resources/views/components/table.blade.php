@props(['headers'])
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50/50 dark:bg-gray-700/20 border-b border-gray-100 dark:border-gray-700/50">
                @foreach($headers as $header)
                    <th class="px-6 py-5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
            {{ $slot }}
        </tbody>
    </table>
</div>
