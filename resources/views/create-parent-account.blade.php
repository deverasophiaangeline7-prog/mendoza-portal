<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Create Parent Account</title>
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

<body class="bg-gray-100" x-data="{ noMiddleName: false }">

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
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>

                @if(auth()->user()->role === 'parent')
                    <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                        Student Information
                    </x-sidebar-link>
                @endif

                @if(auth()->user()->role === 'teacher')
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

        <main class="flex-1 p-12 bg-white">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-5xl font-black text-black">Create an account</h2>
                    <p class="text-3xl font-bold text-black mt-2">Parent</p>
                </div>

                <form action="{{ route('account.parent.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-2 gap-x-16 gap-y-6" x-data="{ pw: '', pw_confirm: '' }">
                        
                        <div class="space-y-5">
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">LRN: <span class="text-red-600">*</span></label>
                                    
                                    <input type="text" 
                                           name="lrn" 
                                           class="form-input-pill @error('lrn') border-red-600 @enderror" 
                                           value="{{ old('lrn') }}" 
                                           required
                                           maxlength="12"
                                           inputmode="numeric"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                                           
                                </div>
                                @error('lrn') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Last name: <span class="text-red-600">*</span></label>
                                    <input type="text" name="last_name" class="form-input-pill @error('last_name') border-red-600 @enderror" value="{{ old('last_name') }}" required>
                                </div>
                                @error('last_name') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">First name: <span class="text-red-600">*</span></label>
                                    <input type="text" name="first_name" class="form-input-pill @error('first_name') border-red-600 @enderror" value="{{ old('first_name') }}" required>
                                </div>
                                @error('first_name') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Middle name:</label>
                                    <input type="text" name="middle_name" class="form-input-pill" :disabled="noMiddleName" :class="noMiddleName ? 'bg-gray-100' : ''">
                                </div>
                            </div>

                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Ext. name:</label>
                                <input type="text" name="ext_name" class="form-input-pill">
                            </div>

                            <div class="flex flex-col" x-data="{ fileError: false }">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Profile Photo:</label>
                                    <div class="flex flex-col w-full">
                                        <input type="file" 
                                            name="profile_photo" 
                                            id="profile_photo"
                                            accept=".png, .jpg, .jpeg" 
                                            class="form-input-pill bg-white py-1 transition-colors"
                                            :class="fileError ? 'border-red-600 ring-1 ring-red-600' : 'border-black'"
                                            @change="
                                                    const file = $event.target.files[0];
                                                    if (file) {
                                                        const type = file.type;
                                                        const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                                                        fileError = !validTypes.includes(type);
                                                        
                                                        if(fileError) {
                                                            $event.target.value = ''; 
                                                        }
                                                    }
                                            ">
                                        
                                        <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-wider">
                                            Max size: 2MB (.png, .jpg, .jpeg only)
                                        </p>

                                        <template x-if="fileError">
                                            <span class="text-red-600 text-sm font-bold italic mt-1">
                                                The profile photo field must be an image.
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                
                                @error('profile_photo') 
                                    <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Sex: <span class="text-red-600">*</span></label>
                                <select name="gender" class="form-input-pill bg-white cursor-pointer focus:outline-none">
                                    <option value="" disabled selected>Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Birthdate: <span class="text-red-600">*</span></label>
                                    
                                    @php
                                        // 1. Get the target year from the database
                                        $activeSy = \App\Models\SchoolYear::where('status', 'active')->first();
                                        $targetYear = now()->year; // Fallback to current year just in case

                                        if ($activeSy) {
                                            $syText = $activeSy->school_year; 
                                            preg_match('/\d{4}/', $syText, $matches);
                                            $targetYear = $matches[0] ?? now()->year; 
                                        }

                                        // 2. PANEL DEFENSE SETUP: 
                                        // Oldest allowed: 100 years ago
                                        // Youngest allowed: 3 years ago
                                        $minDate = \Carbon\Carbon::create($targetYear - 100, 1, 1)->format('Y-m-d');
                                        $maxDate = \Carbon\Carbon::create($targetYear - 3, 12, 31)->format('Y-m-d'); 
                                    @endphp

                                    <input type="date" 
                                           name="birthdate" 
                                           class="form-input-pill @error('birthdate') border-red-600 @enderror" 
                                           value="{{ old('birthdate') }}"
                                           min="{{ $minDate }}" 
                                           max="{{ $maxDate }}" 
                                           required>
                                </div>

                                @error('birthdate')
                                    <span class="text-red-600 text-[10px] font-black uppercase italic mt-1 ml-40 flex items-center">
                                        <i class="fa-solid fa-circle-exclamation mr-1 text-xs"></i>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl leading-tight">Grade &<br>Section: <span class="text-red-600">*</span></label>
                                    <select name="section_id" 
                                            class="border-2 border-black rounded-xl p-2 w-full font-bold"
                                            @change="$el.form.grade_level.value = $el.options[$el.selectedIndex].getAttribute('data-grade')">
                                        <option value="">Select Grade & Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->section_id }}" data-grade="{{ $section->grade_level }}">
                                                {{ $section->grade_level }} - {{ $section->section_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="grade_level">
                                </div>
                                @error('section_id') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Username: <span class="text-red-600">*</span></label>
                                    <input type="text" name="username" class="form-input-pill @error('username') border-red-600 @enderror" value="{{ old('username') }}" required>
                                </div>
                                @error('username') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Password: <span class="text-red-600">*</span></label>
                                    <input type="password" name="password" x-model="pw" class="form-input-pill" required>
                                </div>
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Confirm: <span class="text-red-600">*</span></label>
                                    <input type="password" name="password_confirmation" x-model="pw_confirm" class="form-input-pill" required>
                                </div>
                                <template x-if="pw_confirm !== '' && pw !== pw_confirm">
                                    <span class="text-red-600 text-sm ml-40 mt-[-10px] font-bold italic">Passwords do not match!</span>
                                </template>
                            </div>

                            <div class="flex justify-end gap-6 pt-10">
                                <a href="{{ route('account.management') }}" class="bg-[#FF3B30] text-white px-10 py-2 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition flex items-center">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-[#34C759] text-white px-10 py-2 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition">
                                    Create
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>