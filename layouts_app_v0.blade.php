<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Theme toggle button -->
        <script src="{{ Vite::asset('resources/js/theme.js') }}" defer></script>
    </head>
    <body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <nav class="flex items-center justify-between p-4">
            <h1 class="text-xl font-bold">Viotrack Dashboard</h1>
            <button id="themeToggle" class="p-2 focus:outline-none">
                <svg id="themeIcon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"></svg>
            </button>
        </nav>
        <main>
            @yield('content')
        </main>
    </body>
</html>
<truncated 3694 bytes>