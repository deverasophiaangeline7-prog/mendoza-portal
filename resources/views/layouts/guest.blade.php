<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Mendoza Academy') }}</title>
        
        <!-- Scripts and Fonts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .hero-gradient {
            background-color: #6d0101;
            }
            .nav-active {
                background-color: #b26905;
                color: #f1f5f9;
                border-radius: 9999px;
            }
        </style>
    </head>
    <body class="bg-gray-100 min-h-screen flex flex-col">

        <header class="hero-gradient text-white py-4 px-6 shadow-lg z-50 relative">
            <div class="container mx-auto flex flex-wrap justify-between items-center">
                
                <a href="{{ url('/') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                    <div class="p-1 rounded shadow-sm">
                        <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
                </a>

                <nav class="hidden md:flex items-center space-x-8 font-medium">
    
    <a href="{{ url('/') }}"class="{{ request()->is('/') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
       Home </a>
    <a href="{{ url('about') }}"class="{{ request()->is('about') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
       About </a>
    <a href="{{ url('tuitionfee') }}"class="{{ request()->is('tuitionfee') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
       Courses </a>
    <a href="{{ url('faqs') }}"class="{{ request()->is('faqs') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
       FAQs </a>
    <a href="{{ route('login') }}"class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">
       SMS / LOG IN </a>
    </nav>
            </div>
        </header>

        <!-- unique cintent-->
        {{ $slot }}

        <footer class="bg-white py-2 border-t border-gray-200 mt-auto z-50 relative">
        <div class="container mx-auto flex items-center justify-center">
            
            <div class="flex flex-wrap justify-center gap-6 text-red-800 font-bold text-sm">
            <a href="https://www.facebook.com/JoyofFaithIntegratedSchool" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-2 hover:opacity-80 transition-opacity cursor-pointer">
                <i class="fab fa-facebook text-blue-600 text-lg"></i>
                <span>(Formerly) Joy of Faith Integrated School</span>
            </a>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09452415916</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09081482052</span>
            </div>
        </div>

    </div>
</footer>

    </body>
</html>