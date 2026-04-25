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
    </style>
</head>

<body class="bg-gray-100 h-screen overflow-hidden flex flex-col" x-data="{ open: false }">

    <header class="hero-gradient text-white py-3 px-6 shadow-md flex justify-between items-center relative z-50 flex-shrink-0">
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
            <x-top-icon-button><i class="fa-solid fa-bell"></i></x-top-icon-button>
            <i class="fa-solid fa-circle-user text-[#ffb31a] text-4xl"></i>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line">Dashboard</x-sidebar-link>
                <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-user-graduate" :active="true">List of Students</x-sidebar-link>
                <x-sidebar-link href="#" icon="fa-solid fa-calendar-days">Student Calendar</x-sidebar-link>
                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star">Report Card</x-sidebar-link>
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check">Attendance</x-sidebar-link>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear">Account Management</x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 bg-white flex flex-col p-12 relative overflow-y-auto">
            
            <a href="javascript:history.back()" class="absolute top-6 left-8 text-gray-400 hover:text-red-600 transition flex items-center font-bold">
                <i class="fa-solid fa-arrow-left-long mr-2"></i> Back
            </a>

            <div class="max-w-4xl mx-auto w-full mt-8">
                
                <div class="flex items-center space-x-8 mb-10 pl-4">
                    <div class="w-40 h-40 rounded-full border-2 border-transparent overflow-hidden relative bg-[#def4ff] flex-shrink-0 shadow-inner">
                        <div class="absolute bottom-0 w-full h-1/3 bg-[#8cc63f] rounded-t-[50%]"></div>
                        <i class="fa-solid fa-cloud text-white absolute top-8 left-1/2 transform -translate-x-1/2 text-4xl opacity-80"></i>
                    </div>

                    <div class="flex flex-col text-black">
                        <h2 class="text-3xl font-black uppercase tracking-wide">
                            {{ $student->last_name }}, {{ $student->first_name }}, {{ $student->middle_initial ?? '' }}
                        </h2>
                        <h3 class="text-xl font-bold uppercase mt-3">
                            LRN: {{ $student->lrn }}
                        </h3>
                        <h3 class="text-xl font-bold uppercase mt-1">
                            {{ $student->grade_level }} {{ $student->section->section_name ?? '' }}
                        </h3>
                    </div>
                </div>

                <div class="bg-[#d48112] border-[3px] border-black p-8">
                    <div class="grid grid-cols-2 gap-y-8 gap-x-12 text-black">
                        
                        <div class="space-y-8">
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Birthdate:</div>
                                <div class="text-xl font-medium pl-8">
                                    {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'dd/mm/yyyy' }}
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Latest Enrolment:</div>
                                <div class="text-xl font-medium pl-8">
                                    SY {{ $student->enrollment_year ?? '2025-2026' }}
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Adviser:</div>
                                <div class="text-xl font-medium pl-8">
                                    {{ $student->adviser_name ?? 'Surname, First Name, MI' }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Mother's Maiden Name:</div>
                                <div class="text-xl font-medium pl-8">
                                    {{ $student->mother_name ?? 'Surname, First Name, MI' }}
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Father's Name:</div>
                                <div class="text-xl font-medium pl-8">
                                    {{ $student->father_name ?? 'Surname, First Name, MI' }}
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-2xl tracking-wide mb-1">Guardian's Name:</div>
                                <div class="text-xl font-medium pl-8">
                                    {{ $student->guardian_name ?? 'Surname, First Name, MI' }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</body>
</html>