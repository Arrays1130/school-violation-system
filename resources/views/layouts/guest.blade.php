<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'I-Link CST') }} — Student Violation Management System</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])

        <style>
            body { font-family: 'Inter', system-ui, sans-serif; }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 flex flex-col sm:justify-center items-center min-h-screen py-12">
        <div class="w-full sm:max-w-md px-8 py-8 bg-white shadow-sm overflow-hidden sm:rounded-2xl border border-gray-200">
            <div class="mb-8 text-center flex flex-col items-center">
                <div class="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center mb-4 border border-blue-100 shadow-sm">
                    <img class="w-10 h-10 object-contain" src="{{ asset('brand_logo.png') }}" alt="Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'text-blue-600 font-bold text-xl\'>IC</span>';">
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">I-Link CST</h2>
                <p class="text-sm text-gray-500 mt-2">Discipline Management Platform</p>
            </div>

            {{ $slot }}
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} I-Link College of Science and Technology
        </div>
    </body>
</html>
