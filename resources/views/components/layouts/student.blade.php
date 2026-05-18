<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Student Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-slate-800">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-lg border-b border-white/20 shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:flex">
                            <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('student.dashboard') ? 'border-blue-500 text-blue-900' : 'border-transparent text-slate-500 hover:text-blue-600 hover:border-blue-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="{{ route('student.violations') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('student.violations') ? 'border-blue-500 text-blue-900' : 'border-transparent text-slate-500 hover:text-blue-600 hover:border-blue-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Violations
                            </a>
                            <a href="{{ route('student.notifications.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('student.notifications.*') ? 'border-blue-500 text-blue-900' : 'border-transparent text-slate-500 hover:text-blue-600 hover:border-blue-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Notifications
                                @if(auth('student')->user()->unreadNotifications->count() > 0)
                                    <span class="ml-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm animate-pulse">
                                        {{ auth('student')->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- User Info & Logout -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                        <div class="flex items-center gap-3">
                            <!-- Profile Link -->
                            <a href="{{ route('student.profile.edit') }}" class="text-right hidden md:block group hover:opacity-80 transition-opacity">
                                <div class="text-sm font-bold text-slate-700 leading-tight group-hover:text-blue-600 transition-colors">{{ auth('student')->user()->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ auth('student')->user()->student_id }}</div>
                            </a>
                            <a href="{{ route('student.profile.edit') }}" class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                                {{ substr(auth('student')->user()->full_name ?? 'S', 0, 1) }}
                            </a>
                        </div>
                        
                        <div class="h-6 w-px bg-slate-200 mx-2"></div>

                        <form method="POST" action="{{ route('student.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-slate-500 hover:text-red-600 transition-colors flex items-center gap-1 group">
                                <span>Logout</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="pt-16">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                const studentId = "{{ auth('student')->id() }}";
                
                window.Echo.private(`student.${studentId}`)
                    .listen('ViolationRecorded', (e) => {
                        console.log('New violation alert received:', e);
                        
                        // Sound alert
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.volume = 0.4;
                        audio.play().catch(err => console.log('Audio blocked'));

                        Swal.fire({
                            title: 'System Notification',
                            html: `
                                <div class="text-left mt-2">
                                    <p class="text-sm text-slate-500 mb-3 text-center">A new disciplinary record has been added to your profile.</p>
                                    <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                            <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">New Violation</p>
                                        </div>
                                        <p class="text-sm font-bold text-slate-900">${e.violation_title}</p>
                                        <p class="text-xs text-slate-500 mt-1">Severity: <span class="font-bold text-slate-700">${e.severity}</span></p>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-4 text-center italic">Please review your dashboard for details and next steps.</p>
                                </div>
                            `,
                            icon: 'warning',
                            showConfirmButton: true,
                            confirmButtonText: 'View My Records',
                            confirmButtonColor: '#2563eb',
                            timer: 15000,
                            timerProgressBar: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('student.violations') }}";
                            }
                        });
                    });
            }
        });
    </script>
</body>
</html>
