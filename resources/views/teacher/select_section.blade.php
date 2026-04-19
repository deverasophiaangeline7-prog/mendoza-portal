<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Attendance Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <div class="relative cursor-pointer">
                <i class="fa-solid fa-envelope"></i>
                <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
            </div>
            <i class="fa-solid fa-bell cursor-pointer"></i>
            
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="hover:scale-110 transition-transform focus:outline-none">
                    <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
                </button>
            </form>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate w-6"></i><span>List of Students</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days w-6"></i><span>Student Calendar</span></a></li>
                <li><a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('attendance.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
            </ul>
        </nav>

        <main class="flex-1 p-12 bg-white flex flex-col items-center">
            <h2 class="text-5xl font-black text-black mb-12 uppercase">ATTENDANCE</h2>
            <h3 class="text-3xl font-black text-black mb-12 ">Select Section:</h2>
            
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-12 gap-x-14 w-full max-w-6xl justify-items-center">
                @foreach($sections as $section)
                    @php 
                        $slug = strtolower(str_replace([' ', 'garten'], ['', ''], $section->grade_level)); 
                    @endphp
                    
                    <a href="{{ route('attendance.show', $slug) }}" 
                    class="bg-[#ffaf2e] w-[350px] py-8 rounded-[40px] border-[2px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-y-0.5 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all text-center">
                        
                        <div class="font-black text-4xl uppercase tracking-tighter text-black" 
                            style="text-shadow: 2px 2px 0 #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
                            {{ str_replace('garten', '', $section->grade_level) }}
                        </div>

                        <div class="font-bold text-xl text-black mt-1">
                            {{ $section->section_name }}
                        </div>
                    </a>
                @endforeach
            </div>
        </main>
    </div>
</body>
</html>