<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mendoza Academy') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased h-screen overflow-hidden bg-gray-100">
    
    <!-- Include your main navigation -->
     <div class="flex h-screen">
    @include('layouts.navigation')

    <!-- The content from your chat-system.blade.php will be yielded here -->
    <main class="flex-1 h-full overflow-hidden">
            @yield('content')
    </main>

</body>
</html>