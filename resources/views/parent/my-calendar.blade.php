<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc. | Parent Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

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
                Student Information
            </x-sidebar-link>
            
            <x-sidebar-link href="{{ route('student.calendar') }}" 
                icon="fa-solid fa-calendar-days" 
                :active="request()->routeIs('student.calendar')">
                Student Calendar
            </x-sidebar-link>
            
            <x-sidebar-link href="{{ route('parent.reportcard') }}" icon="fa-solid fa-star" :active="request()->routeIs('parent.reportcard')">
                Report Card
            </x-sidebar-link>
            
            <x-sidebar-link href="{{ route('parent.attendance') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('parent.attendance')">
                Attendance
            </x-sidebar-link>
        </ul>
    </nav>
    
    <main class="p-8">
    <h2 class="text-3xl font-black uppercase mb-8">Student Calendar</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($events as $event)
        <div class="bg-white border-[3px] border-black p-6 rounded-3xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
            <div class="mb-4">
                <h4 class="text-2xl font-black text-red-600 uppercase">{{ $event->event_title }}</h4>
                <p class="font-bold text-gray-400 italic text-sm">
                    {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                </p>
            </div>

            <div class="mt-4 pt-4 border-t-2 border-dashed border-black">
                <p class="text-[10px] font-black uppercase text-gray-400 mb-3 tracking-widest">{{ $student->first_name }} {{ $student->last_name }} Assigned Role(s):</p>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($event->participants as $participation)
                        <span class="bg-green-400 text-black border-2 border-black px-4 py-2 rounded-xl font-black uppercase text-xs shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            <i class="fa-solid fa-star mr-1"></i> 
                            {{ $participation->role ?: 'General Participant' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
            <div class="col-span-full text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-300">
                <p class="text-gray-400 font-bold italic">No event partcipation yet.</p>
            </div>
        @endforelse
    </div>
</main>
</div>
</body>
</html>