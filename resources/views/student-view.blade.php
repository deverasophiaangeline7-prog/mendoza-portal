<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    </style>
</head>

<body class="bg-gray-100 h-screen overflow-hidden flex flex-col" x-data="{ open: false }">

    <!-- Top Header -->
    <header class="hero-gradient text-white py-3 px-6 shadow-md flex justify-between items-center relative z-50 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight italic">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <x-top-icon-button><i class="fa-solid fa-envelope"></i></x-top-icon-button>
            <x-top-icon-button><i class="fa-solid fa-bell"></i></x-top-icon-button>
            <i class="fa-solid fa-circle-user text-[#ffb31a] text-4xl shadow-sm"></i>
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

        <!-- Main Content Area -->
        <main class="flex-1 bg-white p-10 relative overflow-y-auto custom-scrollbar">
            
            <div class="max-w-4xl mx-auto w-full mt-4">
                
                <!-- Student Identity Header -->
                <div class="flex flex-col md:flex-row items-center gap-8 mb-12 pl-4">
                    <!-- Profile Avatar Box -->
                    <div class="w-44 h-44 bg-orange-400 border-[4px] border-black rounded-[2rem] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] overflow-hidden flex items-center justify-center flex-shrink-0 rotate-[-2deg]">
                        @if($student->parent && $student->parent->profile_photo_path)
                            <img src="{{ asset('storage/' . $student->parent->profile_photo_path) }}" 
                                class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user-graduate text-7xl text-black"></i>
                        @endif
                    </div>

                    <div class="text-center md:text-left">
                        <h2 class="text-6xl font-black uppercase italic tracking-tighter leading-none text-black">
                            {{ $student->last_name }}, {{ $student->first_name }} {{ $student->ext_name ?? '' }}
                        </h2>
                        <!-- Your liked gray text style applied here -->
                        <div class="font-bold text-gray-400 mt-4 italic uppercase tracking-widest text-2xl">
                            {{ $student->grade_level }} - {{ $student->section->section_name ?? 'NO SECTION' }}
                        </div>
                    </div>
                </div>

                <!-- Main Information Box -->
                <div class="bg-white border-[5px] border-black p-10 rounded-[3rem] shadow-[20px_20px_0px_0px_rgba(0,0,0,1)] mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
                        
                        <!-- Column 1 -->
                        <div class="space-y-10">
                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Learner Reference Number (LRN)</label>
                                <p class="text-3xl font-black uppercase italic">{{ $student->lrn }}</p>
                            </div>

                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Class Adviser</label>
                                <p class="text-3xl font-black uppercase italic">{{ $student->adviser_name ?? 'NOT ASSIGNED' }}</p>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-10">
                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Biological Sex</label>
                                <p class="text-3xl font-black uppercase italic">{{ $student->gender }}</p>
                            </div>

                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Date of Birth</label>
                                <p class="text-3xl font-black uppercase italic">{{ \Carbon\Carbon::parse($student->birth_date)->format('F d, Y') }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>