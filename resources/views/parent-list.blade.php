<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        /* Custom styles for the form inputs to match image */
        .form-input-pill {
            border: 2px solid black;
            border-radius: 0.75rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            width: 100%;
        }
    </style>
</head>

<body class="bg-gray-100" x-data="{ 
    openModal: false, 
    events: {{ json_encode($eventsData ?? []) }} 
}">

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

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-user-graduate">
                    List of Students
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-calendar-days">
                    Student Calendar
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*')">
                    Report Card
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*')">
                    Attendance
                </x-sidebar-link>
                
                {{-- Automatically hidden from teachers/parents on the backend --}}
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.*') || request()->routeIs('teacher.*') || request()->routeIs('parent.*')">
                        Account Management
                    </x-sidebar-link>
                @endif
            </ul>
        </nav>

    <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                        <h3 class="text-4xl font-bold text-orange-400 mt-1 italic">Parents</h3>
                    </div>
                    <a href="{{ route('account.management') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
 
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8">
                @php
                    $grades = [
                        ['id' => 'nursery',     'level' => 'NURSERY',     'name' => 'St. Mary'],
                        ['id' => 'kinder',      'level' => 'KINDER',      'name' => 'St. Bridget'],
                        ['id' => 'preparatory', 'level' => 'PREPARATORY', 'name' => 'St. Augustine'],
                        ['id' => 'grade-1',     'level' => 'GRADE 1',     'name' => 'Faith'],
                        ['id' => 'grade-2',     'level' => 'GRADE 2',     'name' => 'Hope'],
                        ['id' => 'grade-3',     'level' => 'GRADE 3',     'name' => 'Love'],
                        ['id' => 'grade-4',     'level' => 'GRADE 4',     'name' => 'Grace'],
                        ['id' => 'grade-5',     'level' => 'GRADE 5',     'name' => 'Light'],
                        ['id' => 'grade-6',     'level' => 'GRADE 6',     'name' => 'Wisdom'],
                    ];
                @endphp
 
                @foreach($grades as $grade)
                    <button type="button" 
                        onclick="window.location.href='{{ route('grade.show', ['grade' => $grade['id']]) }}'"
                        class="bg-[#ffb31a] border-2 border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95">
                        
                        <span class="text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-orange-500 transition-transform" 
                            style="-webkit-text-stroke: 1.5px white;">
                            {{ $grade['level'] }}
                        </span>
                        <span class="text-xl font-medium text-black group-hover:-translate-y-1 transition-transform">
                            {{ $grade['name'] }}
                        </span>
                    </button>
                @endforeach
</div>
</main>
</div>
</body>
</html>