<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="auth-page"
    style="margin:0;padding:0;min-height:100dvh;height:100%;background-color:#7eb8e6;"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VioTrack') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
        <style>
            html.auth-page,
            body.auth-page {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: 100dvh !important;
                height: 100% !important;
                background-color: #7eb8e6 !important;
                overflow-x: hidden !important;
            }

            .auth-bg-layer {
                position: fixed !important;
                inset: 0 !important;
                width: 100vw !important;
                height: 100dvh !important;
                min-height: 100dvh !important;
                z-index: 0 !important;
                overflow: hidden !important;
                pointer-events: none !important;
                background-color: #7eb8e6;
                background-image: url('{{ asset('images/login-campus-bg.png') }}');
                background-size: cover !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
            }

            .auth-bg-layer img {
                display: none !important;
            }
        </style>
    </head>
    <body class="auth-page" style="margin:0;padding:0;min-height:100dvh;width:100%;">
        <div class="auth-bg-layer" aria-hidden="true">
            <img src="{{ asset('images/login-campus-bg.png') }}" alt="" />
        </div>

        <div class="relative z-10 flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <div class="w-full sm:max-w-md overflow-hidden rounded-[2rem] border border-white/60 bg-white/80 px-8 py-10 shadow-2xl backdrop-blur-md">
                <div class="mb-6 flex flex-col items-center">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-blue-100 shadow-sm">
                        <img src="{{ asset('brand_logo.png') }}" alt="Logo" class="h-16 w-16 object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" />
                    </div>
                </div>

                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-xs text-white drop-shadow-md">
                &copy; {{ date('Y') }} I-Link College of Science and Technology
            </div>
        </div>
    </body>
</html>
