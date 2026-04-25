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
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-user-graduate">
                    List of Students
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-calendar-days">
                    Student Calendar
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*')">
                    Report Card
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*')">
                    Attendance
                </x-sidebar-link>
                
                {{-- Automatically hidden from teachers/parents on the backend --}}
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.*') || request()->routeIs('teacher.*') || request()->routeIs('parent.*')">
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

                <form action="{{ route('account.parent.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-x-16 gap-y-6" x-data="{ pw: '', pw_confirm: '' }">
                        
                        <div class="space-y-5">
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">LRN: <span class="text-red-600">*</span></label>
                                    <input type="text" name="lrn" class="form-input-pill @error('lrn') border-red-600 @enderror" value="{{ old('lrn') }}" required>
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
                                <div class="ml-40 mt-2 flex items-center gap-2">
                                    <input type="checkbox" id="no_middle" name="no_middle" x-model="noMiddleName" class="w-5 h-5 accent-red-700">
                                    <label for="no_middle" class="font-bold text-lg cursor-pointer">No middle name</label>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Ext. name:</label>
                                <input type="text" name="ext_name" class="form-input-pill">
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

                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Birthdate: <span class="text-red-600">*</span></label>
                                <input type="date" name="birthdate" class="form-input-pill" required>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl leading-tight">Grade &<br>Section: <span class="text-red-600">*</span></label>
                                    <select name="section_id" id="section_select" class="form-input-pill bg-white cursor-pointer focus:outline-none @error('section_id') border-red-600 @enderror" required>
                                        <option value="" disabled selected>Select Grade & Section</option>
                                        <option value="1">Nursery - St. Mary</option>
                                        <option value="2">Kinder - St. Bridget</option>
                                        <option value="3">Preparatory - St. Augustine</option>
                                        <option value="4">Grade 1 - Faith</option>
                                        <option value="5">Grade 2 - Hope</option>
                                        <option value="6">Grade 3 - Love</option>
                                        <option value="7">Grade 4 - Grace</option>
                                        <option value="8">Grade 5 - Light</option>
                                        <option value="9">Grade 6 - Wisdom</option>
                                    </select>
                                </div>
                                @error('section_id') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                                
                                <input type="hidden" name="advisory" id="advisory_hidden">
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

    <script>
        // Logic to extract just the Grade text for the hidden 'advisory' field
        document.getElementById('section_select').addEventListener('change', function() {
            const fullText = this.options[this.selectedIndex].text;
            // Gets 'Nursery' or 'Grade 1' from the string
            document.getElementById('advisory_hidden').value = fullText.split(' - ')[0];
        });
    </script>
</body>
</html>