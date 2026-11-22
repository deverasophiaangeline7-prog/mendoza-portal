<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $siteSettings['school_name'] ?? config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="{{ asset($siteSettings['favicon_path'] ?? 'images/favicon.ico') }}" type="image/x-icon">

        <style>
            :root {
                --color-primary: {{ $siteSettings['primary_color'] ?? '#6d0101' }};
                --color-accent: {{ $siteSettings['accent_color'] ?? '#d4af37' }};
                --color-navbar-bg: {{ $siteSettings['navbar_bg'] ?? '#6d0101' }};
                --color-navbar-text: {{ $siteSettings['navbar_text'] ?? '#ffffff' }};
            }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    </body>
</html>
