<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen" x-data="{ isManaging: false, openAddModal: false, passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <button class="hover:scale-110 transition-transform">
                <i class="fa-solid fa-envelope relative">
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
                </i>
            </button>
            <button class="hover:scale-110 transition-transform"><i class="fa-solid fa-bell"></i></button>
            
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                    <i class="fa-solid fa-circle-user text-orange-300 text-4xl"></i>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden" style="display: none;" x-cloak>
                    
                    @if(auth()->user()->role === 'teacher')
                        <button @click="passwordModal = true; open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-bold">
                            <i class="fa-solid fa-key mr-3 text-gray-400"></i> Change Password
                        </button>
                        <hr class="border-gray-100">
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold"><i class="fa-solid fa-right-from-bracket mr-3"></i>Logout</button>
                    </form>
                    <hr class="border-gray-100">
                    <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-xmark mr-3"></i>Cancel</button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1">
        
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
                @if(auth()->user()->role === 'parent')
                    <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">Student Information</x-sidebar-link>
                @endif
                @if(auth()->user()->role === 'teacher')
                    <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">Advisory Class</x-sidebar-link>
                @endif
                @php
                    $calendarRoute = match(auth()->user()->role) {
                        'admin' => route('admin.student.participation'),
                        'parent' => route('student.calendar'),
                        default => route('student.calendar.index'),
                    };
                @endphp
                <x-sidebar-link href="{{ $calendarRoute }}" icon="fa-solid fa-calendar-days" :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar*')">Student Calendar</x-sidebar-link>
                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">Report Card</x-sidebar-link>
                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">Attendance</x-sidebar-link>
                @if(auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">Account Management</x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 p-8 bg-white flex flex-col items-center">
            
            <h2 class="text-6xl font-black text-black uppercase tracking-tighter mb-8 drop-shadow-[4px_4px_0px_rgba(255,255,255,1)] text-center">
                ADVISORY CLASS
            </h2>

            @if(auth()->user()->role === 'admin')    
                <div class="text-center mb-6">
                    <h2 class="text-4xl font-black text-black uppercase tracking-tight">
                        TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ $totalStudents ?? \App\Models\Student::count() }}</span>
                    </h2>
                </div>
            @endif

            <div class="text-center mb-12">
                <h2 class="text-3xl font-black text-black tracking-tight uppercase">Select Section:</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 w-full max-w-6xl justify-items-center">
                @foreach($sections as $section)
                    <div class="relative w-full max-w-[350px]">
                        
                        <form action="{{ route('sections.destroy', $section->id ?? $section->section_id) }}" method="POST" x-show="isManaging" x-cloak class="absolute -top-3 -right-3 z-50">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete {{ $section->grade_level }} - {{ $section->section_name }}?')" class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-black shadow-sm animate-bounce hover:bg-red-700 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fa-solid fa-minus text-lg"></i>
                            </button>
                        </form>

                        <button type="button" 
                            onclick="window.location.href='/students/section/{{ $section->id ?? $section->section_id }}'"
                            class="w-full bg-[#ffb31a] border-[3px] border-black rounded-[40px] py-8 flex flex-col items-center group transition-all shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            
                            <span class="text-4xl font-black text-white tracking-widest uppercase mb-1" style="-webkit-text-stroke: 2px black;">
                                {{ is_numeric($section->grade_level) ? 'GRADE ' . $section->grade_level : $section->grade_level }}
                            </span>
                            <span class="text-xl font-bold text-black uppercase italic tracking-wider">{{ $section->section_name }}</span>
                        </button>
                    </div>
                @endforeach
            </div>

            @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="flex justify-center items-center space-x-12 mt-12">
                    <button @click="openAddModal = true" class="flex items-center text-green-600 font-black text-xl hover:scale-110 transition-transform">
                        <span class="mr-2 text-2xl">+</span> Add a section
                    </button>
                    
                    <button @click="isManaging = !isManaging" 
                        class="flex items-center font-black text-xl hover:scale-110 transition-transform"
                        :class="isManaging ? 'text-gray-500' : 'text-red-600'">
                        <span class="mr-2 text-2xl" x-text="isManaging ? 'x' : '-'"></span> 
                        <span x-text="isManaging ? 'Cancel Editing' : 'Delete a section'"></span>
                    </button>
                </div>
            @endif

        </main>
    </div>

    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
        <div @click.away="openAddModal = false" class="bg-white border-[3px] border-black rounded-[30px] p-8 w-full max-w-md shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-black uppercase text-black">New Section</h2>
                <button @click="openAddModal = false" class="text-gray-400 hover:text-red-600 text-2xl"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('sections.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Grade Level</label>
                    <select name="grade_level" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 bg-white cursor-pointer appearance-none">
                        <option value="" disabled selected>Select Grade Level</option>
                        <option value="NURSERY">Nursery</option>
                        <option value="KINDER">Kinder</option>
                        <option value="PREPARATORY">Preparatory</option>
                        <option value="1">Grade 1</option>
                        <option value="2">Grade 2</option>
                        <option value="3">Grade 3</option>
                        <option value="4">Grade 4</option>
                        <option value="5">Grade 5</option>
                        <option value="6">Grade 6</option>
                    </select>
                </div>

                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Section Name</label>
                    <input type="text" name="section_name" placeholder="e.g. FAITH" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" @click="openAddModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4">Cancel</button>
                    <button type="submit" class="bg-[#8cc63f] text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-[#9ee047] active:translate-y-1 active:shadow-none transition-all">
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->role === 'teacher')
    <div x-show="passwordModal" x-transition:opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="passwordModal = false" class="bg-white border-[4px] border-black rounded-[2.5rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative" x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }">
            <button @click="passwordModal = false" class="absolute top-6 right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>
            <h2 class="text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>
            <form action="{{ route('user.password.update') }}" method="POST" @submit.prevent="if(newPassword === confirmPassword && currentPassword !== '') $el.submit()">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" required class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all @error('current_password') border-red-500 bg-red-50 focus:ring-red-400 @else border-black focus:ring-green-400 bg-white @enderror">
                        @error('current_password')<p class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>@enderror
                    </div>
                    <div><label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label><input type="password" name="password" x-model="newPassword" required class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all bg-white"></div>
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" x-model="confirmPassword" required class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all" :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-green-400 bg-white'">
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" x-transition class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> Passwords do not match!</p>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">Cancel</button>
                    <button type="submit" :disabled="currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword" :class="(currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword) ? 'opacity-50 cursor-not-allowed' : 'hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none'" class="bg-[#22C55E] text-white font-black py-3 px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2"><i class="fa-solid fa-check"></i> UPDATE</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</body>
</html>