<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Report Cards - Mendoza Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden">

    <!-- HEADER -->
    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-[60]">
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
        <!-- SIDEBAR -->
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line">
                    Dashboard
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('admin.student.participation') }}" icon="fa-solid fa-calendar-days">
                    Student Calendar
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star">
                    Report Card
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check">
                    Attendance
                </x-sidebar-link>

                <!-- Highlight Account Management since Archives belong here -->
                <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="true">
                    Account Management
                </x-sidebar-link>
            </ul>
        </nav>

        <!-- MAIN CONTENT (ARCHIVE LIST) -->
        <main class="flex-1 bg-gray-100 overflow-y-auto p-8 relative pb-32">
            
            <div class="max-w-5xl mx-auto mt-4">
                <!-- Header & Back Button -->
                <div class="flex justify-between items-center mb-8">
                    <a href="{{ route('account.management') }}" class="bg-white border-4 border-black text-black font-bold py-3 px-6 rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Accounts
                    </a>
                    
                    <div class="text-right">
                        <h1 class="text-4xl font-black text-black uppercase tracking-tighter">Archived Records</h1>
                        <h2 class="text-2xl font-bold text-red-600">SY {{ $schoolYear->school_year }}</h2>
                    </div>
                </div>

                <!-- The Interactive Student List -->
                <div class="bg-white border-4 border-black rounded-3xl p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]" x-data="{ search: '' }">
                    
                    <!-- Real-time Search Bar -->
                    <div class="mb-6 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-4 text-gray-400 text-xl"></i>
                        <input type="text" x-model="search" placeholder="Search student by name..." class="w-full border-4 border-black rounded-xl pl-12 pr-4 py-3 font-bold text-lg focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all">
                    </div>

                    <!-- Student Table -->
                    <div class="overflow-hidden border-4 border-black rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#ffb72b] border-b-4 border-black">
                                    <th class="p-4 font-black uppercase text-lg border-r-4 border-black">LRN</th>
                                    <th class="p-4 font-black uppercase text-lg border-r-4 border-black">Student Name</th>
                                    <th class="p-4 font-black uppercase text-lg border-r-4 border-black">Prev Section</th>
                                    <th class="p-4 font-black uppercase text-lg text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    <tr class="border-b-4 border-black last:border-0 hover:bg-yellow-50 transition-colors" x-show="search === '' || '{{ strtolower($student->last_name . ' ' . $student->first_name) }}'.includes(search.toLowerCase())">
                                        <td class="p-4 font-bold border-r-4 border-black">{{ $student->lrn }}</td>
                                        <td class="p-4 font-bold border-r-4 border-black uppercase">{{ $student->last_name }}, {{ $student->first_name }}</td>
                                        <td class="p-4 font-bold border-r-4 border-black">
                                            {{ isset($histories[$student->student_id]) ? $histories[$student->student_id]->section_name : 'N/A' }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="{{ route('archives.reportcards.showStudent', ['student_id' => $student->student_id, 'school_year_id' => $schoolYear->id]) }}" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all inline-block">
                                                View Old Grades
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-8 text-center font-bold text-gray-500">No grades were recorded during this school year.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>
    </div>

</body>
</html>