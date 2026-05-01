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
        .form-input-pill {
            border: 2px solid black;
            border-radius: 0.75rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            width: 100%;
        }
    </style>
</head>

<body class="bg-gray-100" x-data="{ openModal: false }">

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

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h2 class="text-4xl font-black text-black uppercase">Account Management</h2>
                <h3 class="text-3xl font-black text-orange-400 italic uppercase" style="-webkit-text-stroke: 1.5px black;">
                    {{ str_replace('-', ' ', $grade) }} - {{ $section->section_name ?? 'General' }}
                </h3>
            </div>

            <div class="border-2 border-black rounded-lg overflow-hidden bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-200 border-b-2 border-black text-xl font-bold">
                        <tr>
                            <th class="p-4 border-r-2 border-black text-center w-24">No.</th>
                            <th class="p-4 border-r-2 border-black w-40">LRN</th>
                            <th class="p-4">Learner</th>
                            <th class="p-4 w-32 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Male</td></tr>
                        @foreach($males as $index => $student)
                            <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                                <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                                <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                                <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="p-4 text-center"><button class="bg-green-500 text-white px-4 py-1 rounded-lg font-bold border border-black/20">Edit</button></td>
                            </tr>
                        @endforeach

                        <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Female</td></tr>
                        @foreach($females as $index => $student)
                            <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                                <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                                <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                                <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="p-4 text-center"><button class="bg-green-500 text-white px-4 py-1 rounded-lg font-bold border border-black/20">Edit</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>