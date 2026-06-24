<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'I-Link CST') }} - @yield('title')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body class="font-sans antialiased text-slate-800 bg-slate-900 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-[100dvh] flex flex-col relative overflow-hidden">
            
            <!-- Dynamic Background -->
            <div class="fixed inset-0 z-0">
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed scale-105" 
                     style="background-image: url('{{ asset('images/bg_user.jpg') }}'); filter: blur(8px) brightness(0.4);"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-indigo-950/80 via-slate-900/95 to-black/95"></div>
                <!-- Premium Glow Effects -->
                <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] mix-blend-screen pointer-events-none"></div>
                <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen pointer-events-none"></div>
            </div>

            <!-- Header -->
            <header class="relative z-10 py-6 px-4 sm:px-6 lg:px-8 border-b border-white/10 bg-white/5 backdrop-blur-md">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center overflow-hidden shadow-lg shadow-black/20 ring-1 ring-white/20">
                            <img src="{{ asset('brand_logo.png') }}" alt="Logo" class="w-9 h-9 object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                            <span class="hidden font-bold text-indigo-600">Logo</span>
                        </div>
                        <div>
                            <h1 class="text-lg md:text-xl font-bold text-white tracking-tight leading-tight">I-Link College</h1>
                            <p class="text-indigo-300 text-[10px] md:text-xs font-semibold tracking-wider uppercase">Admissions Portal</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="relative z-10 flex-grow py-8 md:py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center w-full">
                <div class="w-full @yield('max-width', 'max-w-4xl') mx-auto">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="relative z-10 py-6 text-center border-t border-white/5 bg-black/20 backdrop-blur-md">
                <p class="text-xs font-medium text-slate-400">
                    &copy; {{ date('Y') }} I-Link College of Science and Technology. All rights reserved.
                </p>
            </footer>
        </div>

        <script>
            // Initialize Lucide icons
            lucide.createIcons();
        </script>
    </body>
</html>
