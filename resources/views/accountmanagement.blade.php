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
        <x-top-icon-button>
            <i class="fa-solid fa-envelope relative">
                <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
            </i>
        </x-top-icon-button>
        
        <x-top-icon-button>
            <i class="fa-solid fa-bell"></i>
        </x-top-icon-button>
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
            </button>

            <div x-show="open" 
                 x-transition 
                 class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden"
                 style="display: none;"
                 x-cloak>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold">
                        <i class="fa-solid fa-right-from-bracket mr-3"></i>
                        Logout
                    </button>
                </form>

                <hr class="border-gray-100">

                <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-xmark mr-3"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</header>

    <div class="flex h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
    <ul class="space-y-1">
        <!-- Dashboard: Accessible to all -->
        <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line">
            Dashboard
        </x-sidebar-link>

        <!--Hidden from Admin, Visible to Teacher -->
        @if(auth()->user()->role !== 'admin')
            <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('students.index')">
                Advisory Class
            </x-sidebar-link>
        @endif

        <!-- Student Calendar: Dynamic Route based on Role -->
        <x-sidebar-link href="{{ auth()->user()->role === 'admin' ? route('admin.student.participation') : route('student.calendar.index') }}" 
            icon="fa-solid fa-calendar-days" 
            :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar.index')">
            Student Calendar
        </x-sidebar-link>

        <!-- Academic Features -->
        <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star">
            Report Card
        </x-sidebar-link>
        
        <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check">
            Attendance
        </x-sidebar-link>

        <!-- Account Management: Admin Only -->
        @if(auth()->user()->role === 'admin')
            <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                Account Management
            </x-sidebar-link>
        @endif
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
                    
                    <div class="relative" x-data="{ listOpen: false }" @click.away="listOpen = false">
    <button @click="listOpen = !listOpen" class="bg-[#ffb72b] hover:bg-yellow-500 text-black text-2xl font-bold py-5 px-12 rounded-full border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all">
        List of accounts
        <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="listOpen ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="listOpen" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-cloak 
         class="absolute top-full mt-4 left-0 w-full bg-white border-2 border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
        
        <a href="{{ route('teacher.list') }}" class="block px-6 py-4 text-xl font-bold border-b-2 border-black hover:bg-yellow-100 transition-colors">
            Teacher Accounts
        </a>
        <a href="{{ route('parent.list') }}" class="block px-6 py-4 text-xl font-bold border-b-2 border-black hover:bg-yellow-100 transition-colors">
            Parent Accounts
        </a>
    </div>
</div>

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
                            <a href="{{ route('teacher.create') }}" class="block px-6 py-4 text-xl font-bold border-b-2 border-black hover:bg-yellow-100 transition-colors">Teacher Account</a>
                            <a href="{{ route('parent.create') }}" class="block px-6 py-4 text-xl font-bold hover:bg-yellow-100 transition-colors">Parent Account</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 max-w-3xl mx-auto mt-8 mb-12">
                    
                        <a href="{{ route('admin.audit_logs') }}" 
                        class="w-full bg-blue-500 hover:bg-blue-600 text-black text-2xl font-bold py-5 rounded-full border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all flex items-center justify-center text-center">
                            View Audit Logs
                        </a>
                    
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