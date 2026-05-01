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
<body class="bg-gray-100 h-screen overflow-hidden flex flex-col">

<header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50 flex-shrink-0">
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

<div class="flex flex-1 overflow-hidden">
    <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
    <ul class="space-y-1">
        <!-- Dashboard -->
        <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>

        <!-- Student Information: ONLY for Parents -->
        @if(auth()->user()->role === 'parent')
            <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                Student Information
            </x-sidebar-link>
        @endif

        <!-- Advisory Class: ONLY for Teachers -->
        @if(auth()->user()->role === 'teacher')
            <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">
                Advisory Class
            </x-sidebar-link>
        @endif

        <!-- Student Calendar: Role-Based Routing -->
        @php
            $calendarRoute = match(auth()->user()->role) {
                'admin' => route('admin.student.participation'),
                'parent' => route('student.calendar'),
                default => route('student.calendar.index'),
            };
        @endphp
        <x-sidebar-link href="{{ $calendarRoute }}" 
            icon="fa-solid fa-calendar-days" 
            :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar*')">
            Student Calendar
        </x-sidebar-link>

        <!-- Report Card: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" 
            icon="fa-solid fa-star" 
            :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">
            Report Card
        </x-sidebar-link>
        
        <!-- Attendance: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" 
            icon="fa-solid fa-calendar-check" 
            :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">
            Attendance
        </x-sidebar-link>

        <!-- Account Management: ONLY for Admin -->
        @if(auth()->user()->role === 'admin')
            <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                Account Management
            </x-sidebar-link>
        @endif
    </ul>
</nav>

    <main class="flex-1 flex flex-col justify-between h-full overflow-y-auto bg-white">
        <div class="flex-1 p-4 flex justify-center items-center gap-8">
            
            <div class="border-2 border-black w-full max-w-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white rounded-xl overflow-hidden">
                
                <div class="flex items-center justify-between px-6 py-3 border-b-2 border-black bg-gray-50">
                    <a href="{{ route('parent.attendance', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&lt;</a>
                    <h2 class="text-3xl font-black tracking-widest uppercase">{{ $monthName }}</h2>
                    <a href="{{ route('parent.attendance', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&gt;</a>
                </div>

                <div class="grid grid-cols-7 text-center border-b-2 border-black py-2 bg-gray-100">
                    @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                        <span class="text-[#b22222] font-black text-sm">{{ $day }}</span>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 p-3 gap-2">
                    
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
                                default   => 'bg-white border-black text-gray-300 shadow-none'
                            };
                        @endphp
                        
                        <div class="{{ $statusClasses }} border-[3px] aspect-square flex items-center justify-center text-xl font-black rounded-lg transition-all 
                            {{ $status !== 'none' ? 'shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : '' }}">
                            {{ $dayNum }}
                        </div>
                    @endforeach

                </div>
            </div>

            <div class="w-56 pt-4 border-2 border-black p-4 rounded-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-gray-50">
                <h3 class="font-black text-xl mb-4 uppercase tracking-wider border-b-2 border-black pb-2">Legend</h3>
                <div class="space-y-3">
                    @php
                        $legend = [
                            ['color' => 'bg-[#4ade80]', 'label' => 'Present'],
                            ['color' => 'bg-[#ef4444]', 'label' => 'Absent'],
                            ['color' => 'bg-[#facc15]', 'label' => 'Late'],
                            ['color' => 'bg-[#60a5fa]', 'label' => 'Excused'],
                            ['color' => 'bg-white', 'label' => 'Weekend/Holiday'],
                        ];
                    @endphp
                    @foreach($legend as $item)
                        <div class="flex items-center gap-3">
                            <span class="{{ $item['color'] }} w-6 h-6 rounded-full border-[3px] border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></span>
                            <span class="font-black text-sm uppercase">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <footer class="p-6 bg-white border-t-[3px] border-black flex justify-between items-center font-black text-xl shadow-[0px_-4px_0px_0px_rgba(0,0,0,1)] relative z-10 flex-shrink-0">
            <div class="uppercase tracking-wide text-black">
                {{ $student->last_name }}, {{ $student->first_name }} 
                {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }}
            </div>
            <div class="uppercase tracking-wide text-[#ffb02e] drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
                {{ $student->grade_level }} {{ $student->section ? '- ' . $student->section->section_name : '' }}
            </div>
        </footer>
    </main>
</div>
</body>
</html>