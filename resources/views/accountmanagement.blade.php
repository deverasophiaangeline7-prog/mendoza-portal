<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Account Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-white overflow-hidden" x-data="{ finalizeModal: false }">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <div class="relative cursor-pointer">
                <i class="fa-solid fa-envelope"></i>
                <span class="absolute -top-2 -right-2 bg-yellow-400 text-black text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-red-700">1</span>
            </div>
            <i class="fa-solid fa-bell cursor-pointer"></i>
            
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="hover:scale-110 transition-transform focus:outline-none">
                    <i class="fa-solid fa-circle-user text-yellow-500 text-4xl"></i>
                </button>
            </form>
        </div>
    </header>

    <div class="flex h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate w-6"></i><span>List of Students</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days w-6"></i><span>Student Calendar</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="#" class="flex items-center p-3 space-x-3 text-white">
                        <i class="fa-solid fa-users-gear w-6"></i>
                        <span class="font-bold">Account Management</span>
                    </a>
                </li>
            </ul>
        </nav>

        <main class="flex-1 bg-white relative p-8 flex flex-col items-center justify-center">
            
        <div class="absolute top-20 w-full max-w-md">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border-2 border-green-600 text-green-700 font-bold rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                        <span><i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}</span>
                        <button @click="$el.parentElement.remove()" class="ml-4 hover:text-green-900">&times;</button>
                    </div>
                @endif
        </div>
            <div class="absolute top-6 right-8">
                <div class="inline-flex items-center border-2 border-black rounded-lg px-4 py-1 font-bold bg-white cursor-pointer hover:bg-gray-50 transition-colors">
                    <span>SY 2025 - 2026</span>
                    <i class="fa-solid fa-chevron-down ml-3 text-sm"></i>
                </div>
            </div>

            <div class="text-center mb-16">
                <h2 class="text-6xl font-black text-gray-900 mb-2">Account Management</h2>
                <h3 class="text-4xl font-bold text-gray-800 uppercase tracking-widest">SY 2025 – 2026</h3>
            </div>

            <div class="w-full max-w-4xl">
                <div class="flex flex-wrap justify-center items-start gap-12">
                    
                    <a href="#" class="bg-[#ffb72b] hover:bg-yellow-500 text-black text-2xl font-bold py-5 px-12 rounded-full border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                        List of accounts
                    </a>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="bg-[#ffb72b] hover:bg-yellow-500 text-black text-2xl font-bold py-5 px-12 rounded-full border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all">
                            Create an account
                            <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-cloak 
                             class="absolute top-full mt-4 left-0 w-full bg-white border-2 border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
                            <a href="#" class="block px-6 py-4 text-xl font-bold border-b-2 border-black hover:bg-yellow-100 transition-colors">Teacher Account</a>
                            <a href="#" class="block px-6 py-4 text-xl font-bold hover:bg-yellow-100 transition-colors">Parent Account</a>
                        </div>
                    </div>

                    <div class="w-full flex justify-center mt-4">
                        <button @click="finalizeModal = true" class="bg-[#4caf50] hover:bg-green-600 text-black text-2xl font-bold py-5 px-16 rounded-full border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            Finalize School Year
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="finalizeModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]" 
             @click.away="finalizeModal = false">
            <div class="text-center">
                <i class="fa-solid fa-triangle-exclamation text-6xl text-red-600 mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Are you sure?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">
                    Finalizing will archive all records for <span class="font-bold text-black">SY 2025-2026</span>. This action cannot be undone.
                </p>
                <div class="flex flex-col gap-4">
                    <form action="{{ route('finalize.year') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-[#4caf50] text-black font-black py-4 rounded-full border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-green-500 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            YES, FINALIZE YEAR
                        </button>
                    </form>
                    <button @click="finalizeModal = false" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-full border-2 border-black hover:bg-gray-200 transition-all">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>