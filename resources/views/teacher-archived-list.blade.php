<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Archived Accounts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<!-- Notice we changed archiveModal to restoreModal here! -->
<body class="bg-white overflow-hidden" x-data="{ restoreModal: false, restoreUrl: '' }">

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

        <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                        <!-- Changed the subtitle here -->
                        <h3 class="text-2xl font-bold text-gray-500 mt-1 italic">Archived Teachers</h3>
                    </div>
                    <div class="flex gap-4">
                        <!-- This Back button now goes to the active teacher list -->
                        <a href="{{ route('teacher.list') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black">
                            <i class="fa-solid fa-arrow-left"></i> Back to Active List
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden border-2 border-black rounded-lg shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-200 border-b-2 border-black">
                                <th class="px-4 py-4 border-r-2 border-black text-center font-bold text-xl w-24">No.</th>
                                <th class="px-6 py-4 border-r-2 border-black font-bold text-xl">Name</th>
                                <th class="px-6 py-4 font-bold text-xl">Advisory Class</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            <!-- Notice we loop through $archivedTeachers here -->
                            @forelse($archivedTeachers as $index => $teacherUser)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 border-r-2 border-black text-center font-bold text-lg text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 border-r-2 border-black font-bold text-lg uppercase text-gray-500">
                                    {{ $teacherUser->teacher?->first_name ?? 'NO PROFILE' }} {{ $teacherUser->teacher?->last_name ?? '' }}
                                </td>
                                <td class="px-6 py-4 flex justify-between items-center text-gray-500">
                                    <span class="font-bold text-lg">
                                        {{ $teacherUser->teacher?->section?->section_name ?? 'No Advisory' }}
                                    </span>
                                    <div class="flex gap-2 items-center">
                                        
                                        <!-- RESTORE BUTTON -->
                                        <button type="button" 
                                            @click="restoreModal = true; restoreUrl = '{{ route('account.teacher.restore', $teacherUser->user_id) }}'" 
                                            title="Restore Account" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Restore
                                        </button>
                                        
                                        <!-- PERMANENT DELETE BUTTON -->
                                        <form action="{{ route('account.teacher.destroy', $teacherUser->user_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this account?');">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" title="Delete Permanently" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500 font-bold text-xl">No archived teachers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Restore Confirmation Modal -->
    <div x-show="restoreModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]" 
             @click.away="restoreModal = false">
            <div class="text-center">
                <i class="fa-solid fa-arrow-rotate-left text-6xl text-blue-500 mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Restore Account?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">
                    Are you sure you want to restore this teacher account? They will reappear on the active list.
                </p>
                <div class="flex flex-col gap-4">
                    <!-- The form action is dynamically injected by Alpine.js -->
                    <form :action="restoreUrl" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-blue-500 text-white font-black py-4 rounded-full border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            YES, RESTORE
                        </button>
                    </form>
                    <button @click="restoreModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-full border-2 border-black hover:bg-gray-200 transition-all">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>