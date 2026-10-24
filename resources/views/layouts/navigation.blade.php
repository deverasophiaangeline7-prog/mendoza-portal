<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        
        nav ul a { 
            font-size: 1.125rem !important; 
            font-weight: 500 !important;    
        }
    </style>
</head>

<!-- Wrap body in Alpine state -->
<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    @if(auth()->check() && auth()->user()->is_first_login)
        <!-- UNCLOSEABLE SECURITY POP-UP MODAL -->
        <div class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 transform transition-all relative">
                
                <!-- Logout button -->
                <form method="POST" action="{{ route('logout') }}" class="absolute top-4 right-4">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-600" title="Logout">
                        <i class="fa-solid fa-right-from-bracket text-xl"></i>
                    </button>
                </form>

                <div class="text-center mb-6 mt-2">
                    <div class="bg-orange-100 text-orange-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-lock text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Action Required</h2>
                    <p class="text-sm text-gray-500 mt-2">For your security, you must change your default password before accessing the system.</p>
                </div>

                <form method="POST" action="{{ route('user.password.update') }}">
                    @csrf
                    @method('put')

                    <!-- Current Password -->
                    <div class="mb-4 text-left" x-data="{ show: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Default Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="current_password" required class="w-full px-4 py-2 border rounded-lg focus:ring-orange-500 focus:border-orange-500 pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-[#6d0101] focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-4 text-left" x-data="{ show: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required minlength="8" class="w-full px-4 py-2 border rounded-lg focus:ring-orange-500 focus:border-orange-500 pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-[#6d0101] focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-circle-info mr-1"></i>Min. 8 characters, containing letters, numbers, and symbols.</p>
                        @error('password') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6 text-left" x-data="{ show: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" required minlength="8" class="w-full px-4 py-2 border rounded-lg focus:ring-orange-500 focus:border-orange-500 pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-[#6d0101] focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#6d0101] text-white font-bold py-3 px-4 rounded-lg hover:bg-red-800 transition shadow-lg flex justify-center items-center">
                        Update & Continue <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif
    <div class="min-h-screen flex flex-col">
        
        {{-- TOP HEADER --}}
        <header class="hero-gradient text-white py-4 px-4 sm:px-6 shadow-lg flex justify-between items-center relative z-50 shrink-0">
            <div class="flex items-center">
                
                <!-- Hamburger Menu Button (Mobile Only) -->
                <button @click="sidebarOpen = true" title="Menu" class="md:hidden text-white focus:outline-none hover:scale-110 transition-transform mr-4">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>

                <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity cursor-pointer">
                    <img src="{{ asset('images/MAILogo.png') }}"
                        class="h-10 w-10 p-1 rounded shadow"
                        alt="Logo">
                    <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight hidden sm:block">
                        Mendoza Academy, Inc.
                    </h1>
                </a>
            </div>

 <!-- Tightened the spacing classes on this parent div from space-x-6 to space-x-4 -->
<div class="flex items-center space-x-2 sm:space-x-4 text-2xl">
    
    <!-- Added -mr-2 to gently nudge the chat icon closer to the bell -->
    <a href="{{ route('messages.index') }} "title="Messages" class="relative transition inline-flex items-center p-2 -mr-2 rounded-lg {{ request()->routeIs('messages*') ? 'text-orange-400' : 'text-white hover:text-orange-400' }}">
        
        <!-- Changed text-xl to text-2xl to match the bell size perfectly -->
        <i class="fa-solid fa-comment-dots text-2xl"></i>
        
        @if(isset($unreadTotal) && $unreadTotal > 0)
            <span class="absolute -top-1 -right-1 bg-yellow-400 text-red-600 rounded-full h-4 w-4 flex items-center justify-center text-[10px] font-bold shadow-sm">
                {{ $unreadTotal }}
            </span>
        @endif
    </a>
                
                @include('components.notification-bell')

                <!-- USER PROFILE ICON -->
                <!-- Add title="Profile" to the button -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" title="Profile" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                        <i class="fa-solid fa-circle-user text-orange-400 text-3xl sm:text-4xl"></i>
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

        <div class="flex flex-1 relative overflow-hidden">
            
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false" 
                 class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm md:hidden" 
                 x-transition.opacity 
                 x-cloak>
            </div>

            {{-- SIDEBAR --}}
            <nav :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                 class="absolute inset-y-0 left-0 z-[70] w-64 bg-[#6d0101] text-white pt-4 shadow-2xl transform transition-transform duration-300 md:relative md:translate-x-0 overflow-y-auto">
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
                    <x-sidebar-link href="{{ route('teacher.information') }}"
                        icon="fa-solid fa-user-tie"
                        :active="request()->routeIs('teacher.information')">
                        Teacher Information
                    </x-sidebar-link>

                        <x-sidebar-link href="{{ route('students.index') }}"
                            icon="fa-solid fa-chalkboard-user"
                            :active="request()->routeIs('students.*')">
                            Advisory Class
                        </x-sidebar-link>
                    @endif

                    @php
                        $calendarRoute = match(auth()->user()->role) {
                            'admin', 'superadmin' => route('admin.student.participation'),
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

                    @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                        <x-sidebar-link href="{{ route('account.management') }}"
                            icon="fa-solid fa-users-gear"
                            :active="request()->routeIs(['account.management', 'admin.audit_logs', 'parent.*', 'teacher.*', 'grade.show', 'account.*'])">
                            Account Management
                        </x-sidebar-link>
                    @endif
                </ul>
            </nav>

            {{-- MAIN CONTENT --}}
            <main class="flex-1 bg-white overflow-y-auto w-full relative z-10">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set timeout to 15 minutes (15 mins * 60 seconds * 1000 milliseconds)
            const idleTimeout = 15 * 60 * 1000; 
            let idleTimer;

            function resetIdleTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(logoutUser, idleTimeout);
            }

            function logoutUser() {
                // Create a hidden form to safely POST to the logout route
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}'; 
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }

            // Reset the timer whenever the user does something
            const events = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll'];
            events.forEach(event => document.addEventListener(event, resetIdleTimer, true));

            // Start the timer initially
            resetIdleTimer();
        });
    </script>
</body>
</html>