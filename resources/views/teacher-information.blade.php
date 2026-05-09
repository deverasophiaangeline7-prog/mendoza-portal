<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Teacher Information</title>
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

<body class="bg-gray-100 h-screen overflow-hidden flex flex-col" x-data="{ passwordModal: false }">

    <header class="hero-gradient text-white py-3 px-6 shadow-md flex justify-between items-center relative z-50 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight italic">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <x-top-icon-button><i class="fa-solid fa-envelope"></i></x-top-icon-button>
            <x-top-icon-button><i class="fa-solid fa-bell"></i></x-top-icon-button>
            
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                    <i class="fa-solid fa-circle-user text-[#ffb31a] text-4xl shadow-sm"></i>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden" style="display: none;" x-cloak>
                    
                    <button @click="passwordModal = true; open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-bold cursor-pointer">
                        <i class="fa-solid fa-key mr-3 text-yellow-500"></i>Change Password
                    </button>
                    <hr class="border-gray-100">

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket mr-3"></i>Logout
                        </button>
                    </form>
                    <hr class="border-gray-100">
                    <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark mr-3"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>

                @if(auth()->user()->role === 'parent')
                    <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                        Student Information
                    </x-sidebar-link>
                @endif

                @if(auth()->user()->role === 'teacher')
                    <x-sidebar-link href="{{ route('teacher.information') }}" icon="fa-solid fa-address-card" :active="request()->routeIs('teacher.information')">
                        Teacher Information
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">
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

                @if(auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                        Account Management
                    </x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 bg-white p-10 relative overflow-y-auto custom-scrollbar">
            
            <div class="max-w-4xl mx-auto w-full mt-4">
                
                <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12 pl-4">
                    
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="w-44 h-44 bg-[#b91c1c] border-[4px] border-black rounded-[2rem] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] overflow-hidden flex items-center justify-center flex-shrink-0 rotate-[-2deg]">
                            @if(auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-chalkboard-user text-7xl text-white"></i>
                            @endif
                        </div>

                        <div class="text-center md:text-left">
                            <h2 class="text-6xl font-black uppercase italic tracking-tighter leading-none text-black">
                                {{ auth()->user()->name }}
                            </h2>
                            <div class="font-bold text-gray-400 mt-4 italic uppercase tracking-widest text-2xl">
                                FACULTY MEMBER
                            </div>
                        </div>
                    </div>

                    <button @click="passwordModal = true" class="bg-[#111] text-white font-black uppercase tracking-widest px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-800 active:translate-y-1 active:shadow-none transition-all flex items-center gap-2 flex-shrink-0">
                        <i class="fa-solid fa-key text-yellow-400"></i> Change Password
                    </button>

                </div>

                <div class="bg-white border-[5px] border-black p-10 rounded-[3rem] shadow-[20px_20px_0px_0px_rgba(0,0,0,1)] mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
                        
                        <div class="space-y-10">
                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Teacher ID / Username</label>
                                <p class="text-3xl font-black uppercase italic">{{ auth()->user()->username ?? auth()->user()->lrn ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Email Address</label>
                                <p class="text-3xl font-black uppercase italic">{{ auth()->user()->email ?? 'NOT ASSIGNED' }}</p>
                            </div>
                        </div>

                        <div class="space-y-10">
                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Account Status</label>
                                <p class="text-3xl font-black uppercase italic text-green-600">{{ auth()->user()->status ?? 'ACTIVE' }}</p>
                            </div>

                            <div>
                                <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Account Role</label>
                                <p class="text-3xl font-black uppercase italic">{{ auth()->user()->role ?? 'TEACHER' }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="passwordModal" 
         x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        
        <div @click.away="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="absolute top-4 right-6 text-3xl font-black text-gray-400 hover:text-red-600 transition-colors">&times;</button>
            
            <h2 class="text-3xl font-black mb-6 uppercase tracking-tight text-center text-black italic">Change Password</h2>
            
            <form action="{{ route('password.update') }}" method="POST" 
                  @submit.prevent="if(newPassword === confirmPassword && currentPassword !== newPassword) $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-8">
                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Current Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="current_password" x-model="currentPassword" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-red-400 pr-10">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">New Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="password" x-model="newPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors pr-10"
                                   :class="(currentPassword !== '' && newPassword !== '' && currentPassword === newPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <p x-show="currentPassword !== '' && newPassword !== '' && currentPassword === newPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Cannot be the same as current password
                        </p>
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" x-model="confirmPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors pr-10"
                                   :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Passwords do not match
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" 
                            :disabled="(confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)"
                            :class="((confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600 active:translate-y-1 active:shadow-none'"
                            class="bg-[#34C759] text-white font-black uppercase tracking-wider px-6 py-4 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center justify-center text-xl">
                        <i class="fa-solid fa-check mr-2"></i> Update Password
                    </button>
                    <button type="button" @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="w-full bg-gray-100 text-black font-black py-4 rounded-xl border-[3px] border-black hover:bg-gray-200 transition-all text-lg uppercase">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>