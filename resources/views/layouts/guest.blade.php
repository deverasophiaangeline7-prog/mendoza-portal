<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Mendoza Academy') }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .hero-gradient {
                background: linear-gradient(to right, #d32f2f, #8b0000);
            }
            .nav-active {
                background-color: #ffb74d;
                color: #fff;
                border-radius: 9999px;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased m-0 p-0 flex flex-col h-screen overflow-hidden">
        
        <header class="hero-gradient text-white py-4 px-6 shadow-lg z-50">
            <div class="container mx-auto flex flex-wrap justify-between items-center">
                
                <div class="flex items-center space-x-3">
                    <div class="p-1 rounded shadow-sm">
                        <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
                </div>

                <nav class="hidden md:flex items-center space-x-8 font-medium">
                    <a href="{{ url('/') }}" class="hover:text-red-200 transition px-6 py-2">Home</a>
                    <a href="#" class="hover:text-red-200 transition">About</a>
                    <a href="#" class="hover:text-red-200 transition">Tuition Fees</a>
                    <a href="#" class="hover:text-red-200 transition">FAQs</a>
                    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
                </nav>
            </div>
        </header>

        <div class="relative flex-1 bg-cover bg-center flex flex-col items-center justify-center overflow-hidden" 
             style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">
            
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <div class="relative z-10 w-full flex flex-col items-center px-4 py-4 justify-center h-full">
                
                <div class="text-center mb-4">
                    <h2 class="text-white text-4xl md:text-7xl font-black uppercase leading-none drop-shadow-2xl italic tracking-tighter">
                        STUDENT MONITORING<br><span class="text-white">SYSTEM</span>
                    </h2>
                </div>

                <div class="mt-8 w-full flex flex-col items-center">
                    {{ $slot }}
                 </div>

                <p class="text-white/80 text-[10px] mt-8 uppercase font-bold tracking-[0.2em]">
                    © 2026 Mendoza Academy, Inc. | School Monitoring System
                </p>
            </div>
        </div>
    </body>
</html>