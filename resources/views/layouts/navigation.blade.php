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
        <!-- NEO-BRUTALIST SECURITY POP-UP MODAL -->
        <div class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <!-- Alpine component handling visibility and password strength -->
            <div x-data="passwordStrengthMeter()" class="bg-white rounded-3xl border-[3px] border-black shadow-[8px_8px_0_0_rgba(0,0,0,1)] w-full max-w-md p-8 relative">
                
                <h2 class="text-3xl font-black italic uppercase text-center mb-6 tracking-wide">Change Password</h2>
                
                <p class="text-sm font-bold text-gray-600 text-center mb-6 uppercase tracking-wider">
                    <i class="fa-solid fa-lock mr-1"></i> Security action required
                </p>

                <!-- Global Error Alert (e.g. Current Password is wrong) -->
                @if($errors->any())
                    <div class="bg-red-100 border-2 border-red-500 text-red-700 px-4 py-3 rounded-xl mb-6 font-bold text-sm shadow-[4px_4px_0_0_rgba(239,68,68,1)]">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> 
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('user.password.update') }}">
                    @csrf
                    @method('put')

                    <!-- Current Password -->
                    <div class="mb-5 text-left">
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wider">Current Password</label>
                        <div class="relative">
                            <input :type="show1 ? 'text' : 'password'" name="current_password" required 
                                class="w-full px-4 py-3 border-2 border-black rounded-xl focus:bg-blue-50 focus:outline-none transition-colors font-bold text-lg pr-12">
                            <button type="button" @click="show1 = !show1" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-black focus:outline-none">
                                <i class="fa-solid text-xl" :class="show1 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-2 text-left">
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wider">New Password</label>
                        <div class="relative">
                            <input :type="show2 ? 'text' : 'password'" name="password" required x-model="password" @input="checkStrength"
                                class="w-full px-4 py-3 border-2 border-black rounded-xl focus:bg-blue-50 focus:outline-none transition-colors font-bold text-lg pr-12">
                            <button type="button" @click="show2 = !show2" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-black focus:outline-none">
                                <i class="fa-solid text-xl" :class="show2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Live Password Strength Meter -->
                    <div class="flex items-center justify-between mb-5 border-2 border-black rounded-xl px-4 py-2 bg-gray-50">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-700">Strength:</span>
                        <div class="flex gap-1 flex-1 mx-4">
                            <div class="h-2 flex-1 rounded-full border border-black" :class="strength >= 1 ? (strength == 1 ? 'bg-red-500' : 'bg-yellow-400') : 'bg-gray-200'"></div>
                            <div class="h-2 flex-1 rounded-full border border-black" :class="strength >= 2 ? (strength == 3 ? 'bg-green-500' : 'bg-yellow-400') : 'bg-gray-200'"></div>
                            <div class="h-2 flex-1 rounded-full border border-black" :class="strength >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                        </div>
                        <span class="text-xs font-black uppercase tracking-wider w-12 text-right" 
                              :class="{'text-red-500': strength == 1, 'text-yellow-600': strength == 2, 'text-green-600': strength == 3}"
                              x-text="strengthText"></span>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-8 text-left">
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wider">Confirm New Password</label>
                        <div class="relative">
                            <input :type="show3 ? 'text' : 'password'" name="password_confirmation" required 
                                class="w-full px-4 py-3 border-2 border-black rounded-xl focus:bg-blue-50 focus:outline-none transition-colors font-bold text-lg pr-12">
                            <button type="button" @click="show3 = !show3" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-black focus:outline-none">
                                <i class="fa-solid text-xl" :class="show3 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <!-- Optional Logout Button -->
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-modal').submit();" 
                           class="w-1/3 bg-white text-black font-black uppercase tracking-wider py-3 rounded-xl border-2 border-black text-center shadow-[4px_4px_0_0_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0_0_rgba(0,0,0,1)] transition-all flex items-center justify-center">
                            Cancel
                        </a>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="w-2/3 bg-[#86efac] text-black font-black uppercase tracking-wider py-3 rounded-xl border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0_0_rgba(0,0,0,1)] transition-all flex items-center justify-center">
                            <i class="fa-solid fa-check mr-2"></i> Update
                        </button>
                    </div>
                </form>

                <!-- Hidden Logout Form for the Cancel button -->
                <form id="logout-form-modal" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Alpine.js Logic for the Password Strength Meter -->
        <script>
            function passwordStrengthMeter() {
                return {
                    show1: false, show2: false, show3: false,
                    password: '',
                    strength: 0,
                    strengthText: 'NONE',
                    checkStrength() {
                        let score = 0;
                        if (!this.password) {
                            this.strength = 0;
                            this.strengthText = 'NONE';
                            return;
                        }
                        
                        // Rule 1: Length >= 8
                        if (this.password.length >= 8) score++;
                        // Rule 2: Contains both letters and numbers
                        if (/[a-zA-Z]/.test(this.password) && /[0-9]/.test(this.password)) score++;
                        // Rule 3: Contains special symbols
                        if (/[^a-zA-Z0-9]/.test(this.password)) score++;

                        this.strength = score;
                        
                        if (score === 1) this.strengthText = 'WEAK';
                        else if (score === 2) this.strengthText = 'MID';
                        else if (score === 3) this.strengthText = 'STRONG';
                        else this.strengthText = 'WEAK';
                    }
                }
            }
        </script>
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
                        :active="request()->routeIs([
                            'account.management', 
                            'admin.audit_logs', 
                            'parent.*', 
                            'teacher.*', 
                            'grade.show', 
                            'account.*', 
                            'admin.list', 
                            'admin.archived', 
                            'admin.create'
                        ])">
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