<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mendoza Academy')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .hero-gradient {
            background-color: #6d0101;
        }
        [x-cloak] { display: none !important; }
        
        /* 
         * Safely force the sidebar links to be the standard larger size 
         */
        nav ul a { 
            font-size: 1.125rem !important; /* Tailwind text-lg */
            font-weight: 500 !important;    /* Tailwind font-medium */
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen">
        {{-- TOP HEADER --}}
        <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                <img src="{{ asset('images/MAILogo.png') }}"
                    class="h-10 w-10 bg-white p-1 rounded shadow"
                    alt="Logo">
                <h1 class="text-2xl font-bold uppercase tracking-tight">
                    Mendoza Academy, Inc.
                </h1>
            </a>

            <div class="flex items-center space-x-6 text-2xl">
    
            <!-- Direct link to Chat System -->
            <a href="{{ route('messages.index') }}" class="relative hover:scale-110 transition-transform focus:outline-none flex items-center">
                <i class="fa-solid fa-envelope {{ request()->routeIs('messages.index') ? 'text-[#ffaa00]' : 'text-white hover:text-[#ffaa00]' }} transition-colors duration-200"></i>
            </a>

            @include('components.notification-bell')

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                        <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
                    </button>

                    <div x-show="open"
                         x-transition
                         class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden"
                         style="display: none;"
                         x-cloak>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold">
                                <i class="fa-solid fa-right-from-bracket mr-3"></i>
                                Logout
                            </button>
                        </form>

                        <hr class="border-gray-100">

                        <button @click="open = false"
                                class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-xmark mr-3"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-screen">
            {{-- SIDEBAR --}}
            <nav class="w-64 bg-[#6d0101] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
                <ul class="space-y-1">
                    <x-sidebar-link href="{{ route('dashboard') }}"
                        icon="fa-solid fa-chart-line"
                        :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-sidebar-link>

                    @if(auth()->user()->role === 'parent')
                        <x-sidebar-link href="{{ route('student.view') }}"
                            icon="fa-solid fa-user-graduate"
                            :active="request()->routeIs('student.view')">
                            Student Information
                        </x-sidebar-link>
                    @endif

                    @if(auth()->user()->role === 'teacher')
                        <x-sidebar-link href="{{ route('students.index') }}"
                            icon="fa-solid fa-chalkboard-user"
                            :active="request()->routeIs('students.*')">
                            Advisory Class
                        </x-sidebar-link>
                    @endif

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

                    <x-sidebar-link
                        href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}"
                        icon="fa-solid fa-star"
                        :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">
                        Report Card
                    </x-sidebar-link>

                    <x-sidebar-link
                        href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}"
                        icon="fa-solid fa-calendar-check"
                        :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">
                        Attendance
                    </x-sidebar-link>

                    <x-sidebar-link
                        href="{{ route('appointments.index') }}"
                        icon="fa-solid fa-user-group" 
                        :active="request()->routeIs('appointments.*')">
                        Appointment Scheduling
                    </x-sidebar-link>

                    @if(auth()->user()->role === 'admin')
                        <x-sidebar-link href="{{ route('account.management') }}"
                            icon="fa-solid fa-users-gear"
                            :active="request()->routeIs(['account.management', 'admin.audit_logs', 'parent.*', 'teacher.*', 'grade.show', 'account.*'])">
                            Account Management
                        </x-sidebar-link>
                    @endif
                </ul>
            </nav>

            {{-- MAIN CONTENT --}}
            <main class="flex-1 bg-white">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>