<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            [x-cloak] { display: none !important; }
            
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8fafc; /* slate-50 */
            }

            ::-webkit-scrollbar { width: 6px; height: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
            ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>
    </head>
    <body class="antialiased text-slate-900" x-data="{ sidebarOpen: false }" x-init="setTimeout(() => { lucide.createIcons(); }, 50)">
        <div class="h-screen flex overflow-hidden">

            <!-- ===================== SIDEBAR ===================== -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-50 w-66 bg-white border-r border-slate-100 flex flex-col transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none shrink-0">
                
                <!-- Logo Header -->
                <div class="px-6 py-6 flex-shrink-0 border-b border-slate-100/80 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <img class="w-9 h-9 object-contain shrink-0 rounded-lg shadow-sm" src="{{ asset('brand_logo.png') }}" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-650 flex items-center justify-center text-white shadow-md shadow-indigo-500/10 shrink-0" style="display: none;">
                            <i data-lucide="shield" class="w-5.5 h-5.5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-slate-900 font-extrabold text-[15px] tracking-tight leading-none uppercase">I-Link CST</p>
                            <p class="text-slate-400 text-[9px] font-bold uppercase tracking-wider mt-1.5">Disciplinary System</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-900 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 overflow-y-auto px-4.5 py-6 no-scrollbar">
                    @include('layouts.navigation-links')
                </nav>

                {{-- User Panel --}}
                <div class="p-4 border-t border-slate-100/80">
                    <div class="flex items-center gap-3.5 p-2 rounded-xl bg-slate-50 border border-slate-100/50 hover:bg-slate-100/40 transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/80 flex items-center justify-center text-indigo-700 font-extrabold text-xs shrink-0 shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-800 text-sm font-extrabold truncate leading-tight">{{ Auth::user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                @csrf
                                <button type="submit" class="text-slate-450 text-[10px] font-bold uppercase tracking-wide hover:text-indigo-600 transition-colors flex items-center gap-1">
                                    <i data-lucide="log-out" class="w-3 h-3"></i>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- Mobile Overlay -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

            <!-- ===================== MAIN AREA ===================== -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50/50">

                <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-6 sm:px-8 lg:px-10 sticky top-0 z-30 shrink-0">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-50 transition-colors border border-slate-100">
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                        <div class="text-base font-bold text-slate-800">
                            @yield('header')
                        </div>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-6 sm:p-8 lg:p-10 no-scrollbar">
                    <div class="max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>

            </div>
        </div>

        @stack('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            });

            function confirmDelete(formId, label) {
                Swal.fire({
                    title: 'Confirm Deletion?',
                    text: 'Are you sure you want to permanently delete this ' + (label || 'record') + '? This action is irreversible.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48', // rose-600
                    cancelButtonColor: '#64748b', // slate-500
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'rounded-2xl border border-slate-100 shadow-xl',
                        confirmButton: 'px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-sm shadow-sm transition-all duration-150',
                        cancelButton: 'px-5 py-2.5 bg-slate-500 hover:bg-slate-600 text-white rounded-xl font-bold text-sm shadow-sm transition-all duration-150 ml-3',
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            }
        </script>
    </body>
</html>
