<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Audit Logs</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
    </style>
</head>
<body class="bg-gray-100">

    <!-- TOP HEADER -->
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

    <div class="flex h-[calc(100vh-72px)] overflow-hidden">
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

    <main class="p-8 flex-1 overflow-y-auto bg-gray-100">
    <div class="max-w-7xl mx-auto">
        
        <!-- HEADER ROW: Title on Left, Buttons on Right -->
        <div class="flex justify-between items-start mb-10">
            
            <!-- 1. THE TEXT: Locked to Top Left -->
            <div class="text-left">
                <h2 class="text-5xl font-black text-black uppercase tracking-tight" style="text-shadow: 2px 2px 0px #f59e0b;">
                    System Audit Logs
                </h2>
                <h3 class="text-xl font-bold text-gray-600 mt-2 italic leading-none">
                    A complete record of all sensitive system actions.
                </h3>
            </div>

            <!-- 2. THE BUTTONS: Aligned to the Top Right -->
            <!-- We use pt-2 (padding-top) to nudge the buttons down so they center perfectly with the big H2 text -->
            <div class="flex items-center gap-4 pt-2" x-data="{ showSearch: false }">
                
                <!-- Expanding Search Form -->
                <form action="{{ route('admin.audit_logs') }}" method="GET" 
                      x-show="showSearch" 
                      x-cloak
                      @click.outside="showSearch = false"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 scale-95 translate-x-10"
                      x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                      class="flex items-center gap-3">
                    
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search name, action, or date..." 
                               class="w-80 pl-12 pr-4 py-3 border-[3px] border-black rounded-full font-bold shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all">
                    </div>

                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-black font-black px-6 py-3 rounded-full border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all uppercase text-sm">
                        GO
                    </button>
                </form>

                <!-- Search Toggle Button -->
                <button x-show="!showSearch" @click="showSearch = true" x-cloak
                        class="bg-blue-500 hover:bg-blue-600 text-black w-14 h-14 rounded-full border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-center hover:scale-110 transition active:shadow-none active:translate-x-[2px] active:translate-y-[2px]">
                    <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                </button>

                <!-- Back Button -->
                <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </button>
            </div>
        </div>

        <!-- 3. THE TABLE: Starts below the header -->
        <div class="bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#f59e0b] border-b-[3px] border-black text-black uppercase">
                <tr>
                    <th class="p-4 border-r-[3px] border-black w-48 text-center font-black text-lg">Date & Time</th>
                    <th class="p-4 border-r-[3px] border-black w-64 font-black text-lg">User</th>
                    <th class="p-4 border-r-[3px] border-black w-64 text-center font-black text-lg">Action</th>
                    <th class="p-4 font-black text-lg">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y-[3px] divide-black">
                @forelse($logs as $log)
                    <tr> <!-- CRITICAL FIX: ADDED THE TR TAG -->
                        <td class="p-4 border-r-[3px] border-black font-bold text-sm text-center text-gray-800">
                            {{ $log->created_at->format('M d, Y') }} <br>
                            <span class="text-red-600">{{ $log->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="p-4 border-r-[3px] border-black font-black uppercase text-blue-600 break-all">
                            {{ $log->user?->username ?? 'SYSTEM' }}
                        </td>
                        <td class="p-4 border-r-[3px] border-black font-bold text-center">
                            <span class="bg-gray-200 border-2 border-black px-3 py-1 rounded-full text-xs uppercase tracking-wider font-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] inline-block">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-gray-800 text-lg">
                            {{ $log->description }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-gray-500 font-bold text-2xl uppercase italic">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
            </table>
            <div class="mt-8">
        {{ $logs->appends(['search' => $search])->links() }}
    </div>
    </div>
</main>
</body>
</html>