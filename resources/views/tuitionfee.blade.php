<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc. - Courses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">
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
<body class="bg-gray-100 flex flex-col min-h-screen">

    <header class="hero-gradient text-white py-4 px-6 shadow-md">
        <div class="container mx-auto flex flex-wrap justify-between items-center">
            
            <div class="flex items-center space-x-3">
                <div class="p-1 rounded shadow-sm">
                    <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                </div>
                <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
            </div>

            <nav class="hidden md:flex items-center space-x-8 font-medium">
                <a href="{{ url('/') }}" class="hover:text-red-200 transition" >Home</a>
                <a href="{{ url('about') }}" class="hover:text-red-200 transition">About</a>
                <a href="{{ url('tuitionfee') }}" class="nav-active px-6 py-2">Courses</a>
                <a href="{{ url('faqs') }}" class="hover:text-red-200 transition">FAQs</a>
                <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
            </nav>
        </div>
    </header>

    <main class="relative flex-grow flex items-center justify-center py-16 bg-cover bg-center" 
          style="background-image: url('{{ asset('images/tuitionfee.jpg') }}');">
        
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black/70"></div>

        <!-- Narrower max-w-lg container makes stacked cards look properly sized -->
        <div class="container mx-auto max-w-lg relative z-10 px-4 w-full">
            
            <!-- Title Section -->
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-black text-white uppercase tracking-widest drop-shadow-md">
                    Courses Offered
                </h2>
            </div>

            <!-- Courses Container (Stacked vertically, smaller gaps) -->
            <div class="flex flex-col gap-6">
                
                <!-- Pre-Elementary Card -->
                <div class="bg-gray-50 rounded-2xl py-8 px-6 shadow-xl flex flex-col items-center text-center border-t-8 border-orange-400 w-full">
                    
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-wide mb-3">
                        Pre-Elementary
                    </h3>
                    
                    <!-- Plain text on one line with separators -->
                    <div class="text-base md:text-lg font-bold text-slate-700 whitespace-nowrap">
                        Nursery <span class="text-gray-400 mx-2">|</span> Kindergarten <span class="text-gray-400 mx-2">|</span> Preparatory
                    </div>
                </div>

                <!-- Elementary Card -->
                <div class="bg-gray-50 rounded-2xl py-8 px-6 shadow-xl flex flex-col items-center text-center border-t-8 border-red-700 w-full">
                    
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-wide mb-3">
                        Elementary
                    </h3>
                    
                    <!-- Plain text -->
                    <div class="text-base md:text-lg font-bold text-slate-700">
                        Grades 1 - 6
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-white py-4 border-t border-gray-200 mt-auto">
        <div class="container mx-auto flex flex-wrap justify-center gap-8 text-red-800 font-bold">
            <div class="flex items-center space-x-2">
                <i class="fab fa-facebook text-blue-600 text-xl"></i>
                <span>Mendoza Academy Inc</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09452415916</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09081482052</span>
            </div>
        </div>
    </footer>

</body>
</html>