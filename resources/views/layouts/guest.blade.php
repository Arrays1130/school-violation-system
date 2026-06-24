<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center bg-cover bg-center relative" 
             style="background-image: url('{{ asset('images/bg_user.jpg') }}')">
            
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/20 z-0"></div>

            <div class="z-10 w-full sm:max-w-md px-8 py-10 bg-white/40 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/40">
                <div class="mb-6 flex flex-col items-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 overflow-hidden border-2 border-white shadow-sm">
                        <img src="{{ asset('brand_logo.png') }}" alt="Logo" class="w-16 h-16 object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                        <span class="hidden text-xl font-bold text-blue-600">Logo</span>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
