<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

    <style>
        .hero-gradient {
            background: linear-gradient(to right, #d32f2f, #8b0000);
        }
        [x-cloak] { display: none !important; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    </style>
</head>
<body class="bg-gray-100">

<header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <i class="fa-solid fa-envelope relative cursor-pointer">
                <span class="absolute -top-2 -right-2 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-red-700">1</span>
            </i>
            <i class="fa-solid fa-bell cursor-pointer"></i>
            
            <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
            </button>

            <div x-show="open" 
                 x-transition 
                 class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden"
                 style="display: none;">
                
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
    <nav class="w-64 bg-[#b91c1c] text-white pt-4">
        <ul class="space-y-1">
            <li>
                <a href="/dashboard#" class="flex items-center p-3 space-x-3">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="font-semibold">Dashboard</span>
                </a>
            </li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate"></i><span>List of Students</span></a></li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days"></i><span>Student Calendar</span></a></li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star"></i><span>Report Card</span></a></li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet"></i><span>Tuition Fee</span></a></li>
            <li class = "bg-orange-400 mx-2 rounded-lg"><a href="{{ route('parent.attendance') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check"></i><span>Attendance</span></a></li>
        </ul>
    </nav>
    <main class="flex-1 flex flex-col">
    <div class="flex-1 p-8 flex justify-center items-start gap-12 bg-white">
        
        <div class="border-2 border-black w-full max-w-2xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white rounded-xl overflow-hidden">
            
            <div class="flex items-center justify-between px-6 py-4 border-b-2 border-black bg-gray-50">
                <span class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&lt;</span>
                <h2 class="text-5xl font-black tracking-widest uppercase">{{ $monthName }}</h2>
                <span class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&gt;</span>
            </div>

            <div class="grid grid-cols-7 text-center border-b-2 border-black py-3 bg-gray-100">
                @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                    <span class="text-[#b22222] font-black text-lg">{{ $day }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 p-4 gap-2">
                
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="aspect-square border-none"></div>
                @endfor

                @foreach($days as $dayNum => $status)
                    @php
                        $statusClasses = match($status) {
                            'present' => 'bg-[#4ade80] border-black text-black',
                            'absent'  => 'bg-[#ef4444] border-black text-black',
                            'late'    => 'bg-[#facc15] border-black text-black',
                            'excused' => 'bg-[#60a5fa] border-black text-black',
                            'holiday' => 'bg-[#9ca3af] border-black text-black',
                            default   => 'bg-white border-black text-gray-300 shadow-none' // Unmarked days
                        };
                    @endphp
                    
                    <div class="{{ $statusClasses }} border-[3px] aspect-square flex items-center justify-center text-2xl font-black rounded-lg transition-all 
                        {{ $status !== 'none' ? 'shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : '' }}">
                        {{ $dayNum }}
                    </div>
                @endforeach

            </div>
        </div>

        <div class="w-64 pt-4 border-2 border-black p-6 rounded-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-gray-50">
            <h3 class="font-black text-2xl mb-6 uppercase tracking-wider border-b-2 border-black pb-2">Legend</h3>
            <div class="space-y-4">
                @php
                    $legend = [
                        ['color' => 'bg-[#4ade80]', 'label' => 'Present'],
                        ['color' => 'bg-[#ef4444]', 'label' => 'Absent'],
                        ['color' => 'bg-[#facc15]', 'label' => 'Late'],
                        ['color' => 'bg-[#60a5fa]', 'label' => 'Excused'],
                        ['color' => 'bg-white', 'label' => 'No Class', 'outline' => true],
                    ];
                @endphp
                @foreach($legend as $item)
                    <div class="flex items-center gap-4">
                        <span class="{{ $item['color'] }} w-8 h-8 rounded-full border-[3px] border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></span>
                        <span class="font-black text-lg uppercase">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="p-8 bg-white border-t-[3px] border-black flex justify-between items-center font-black text-2xl shadow-[0px_-4px_0px_0px_rgba(0,0,0,1)] relative z-10">
        <div class="uppercase tracking-wide text-black">
            {{ $student->last_name }}, {{ $student->first_name }} 
            {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }}
        </div>
        <div class="uppercase tracking-wide text-[#ffb02e] drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
            {{ $student->grade_level }} {{ $student->section ? '- ' . $student->section->section_name : '' }}
        </div>
    </footer>
</main>