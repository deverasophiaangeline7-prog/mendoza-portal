<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Mendoza Academy') }}</title>
        
        <!-- Scripts and Fonts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Added Alpine.js for the mobile sidebar state -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            [x-cloak] { display: none !important; }
        </style>
    </head>
    
    <!-- Added x-data to control the sidebar state globally -->
    <body class="bg-gray-100 min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">

        <header class="hero-gradient text-white py-4 px-6 shadow-lg z-50 relative sticky top-0">
            <div class="container mx-auto flex flex-wrap justify-between items-center">
                
                <div class="flex items-center space-x-4">
                    <!-- Hamburger Menu Button (Mobile Only) -->
                    <button @click="sidebarOpen = true" class="md:hidden text-white focus:outline-none hover:scale-110 transition-transform">
                        <i class="fa-solid fa-bars text-3xl"></i>
                    </button>

                    <!-- Logo -->
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                        <div class="p-1 rounded shadow-sm">
                            <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                        </div>
                        <h1 class="text-xl md:text-2xl font-bold tracking-tight uppercase hidden sm:block">Mendoza Academy, Inc.</h1>
                    </a>
                </div>

                <!-- Desktop Navigation (Hidden on Mobile) -->
                <nav class="hidden md:flex items-center space-x-8 font-medium">
                    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
                        Home 
                    </a>
                    <a href="{{ url('about') }}" class="{{ request()->is('about') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
                        About 
                    </a>
                    <a href="{{ url('tuitionfee') }}" class="{{ request()->is('tuitionfee') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
                        Courses 
                    </a>
                    <a href="{{ url('faqs') }}" class="{{ request()->is('faqs') ? 'nav-active px-6 py-2' : 'hover:text-red-200 transition' }}">
                        FAQs 
                    </a>
                    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">
                        SMS / LOG IN 
                    </a>
                </nav>
            </div>
        </header>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm md:hidden" 
             @click="sidebarOpen = false" 
             x-transition.opacity 
             x-cloak>
        </div>

        <!-- Mobile Slide-out Sidebar -->
        <nav :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             class="fixed inset-y-0 left-0 z-[70] w-72 bg-white shadow-2xl transform transition-transform duration-300 md:hidden flex flex-col border-r-[3px] border-black" 
             x-cloak>
            
            <!-- Sidebar Header -->
            <div class="p-5 border-b-[3px] border-black flex justify-between items-center bg-gray-100">
                <span class="font-black text-lg uppercase text-black">Menu</span>
                <button @click="sidebarOpen = false" class="text-black hover:text-red-600 transition-colors">
                    <i class="fa-solid fa-xmark text-3xl"></i>
                </button>
            </div>

            <!-- Sidebar Links -->
            <div class="flex flex-col font-bold text-lg text-black mt-2">
                <a href="{{ url('/') }}" class="px-6 py-4 border-b border-gray-200 hover:bg-gray-100 transition-colors {{ request()->is('/') ? 'bg-orange-100 text-orange-800' : '' }}">Home</a>
                <a href="{{ url('about') }}" class="px-6 py-4 border-b border-gray-200 hover:bg-gray-100 transition-colors {{ request()->is('about') ? 'bg-orange-100 text-orange-800' : '' }}">About Us</a>
                <a href="{{ url('tuitionfee') }}" class="px-6 py-4 border-b border-gray-200 hover:bg-gray-100 transition-colors {{ request()->is('tuitionfee') ? 'bg-orange-100 text-orange-800' : '' }}">Courses</a>
                <a href="{{ url('faqs') }}" class="px-6 py-4 border-b border-gray-200 hover:bg-gray-100 transition-colors {{ request()->is('faqs') ? 'bg-orange-100 text-orange-800' : '' }}">FAQs</a>
                
                <a href="{{ route('login') }}" class="px-6 py-3 mt-6 mx-4 text-center bg-[#FB923C] text-red-900 rounded-full shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:brightness-95 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all">
                    SMS / LOG IN <i class="fa-solid fa-arrow-right-to-bracket ml-2"></i>
                </a>
            </div>
        </nav>

        <!-- unique content -->
        <main class="flex-grow flex flex-col relative z-10">
            {{ $slot }}
        </main>

        <footer class="bg-white py-2 border-t border-gray-200 mt-auto z-40 relative">
            <div class="container mx-auto flex items-center justify-center">
                <div class="flex flex-wrap justify-center gap-6 text-red-800 font-bold text-sm">
                    <a href="https://www.facebook.com/JoyofFaithIntegratedSchool" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-2 hover:opacity-80 transition-opacity cursor-pointer">
                        <i class="fab fa-facebook text-blue-600 text-lg"></i>
                        <span>Mendoza Academy, Inc.</span>
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