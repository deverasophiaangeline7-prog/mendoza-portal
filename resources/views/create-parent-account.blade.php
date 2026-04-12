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
            <div class="relative cursor-pointer">
                <i class="fa-solid fa-envelope"></i>
                <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
            </div>
            <i class="fa-solid fa-bell cursor-pointer"></i>
            <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate w-6"></i><span>List of Students</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days w-6"></i><span>Student Calendar</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('account.management') }}" class="flex items-center p-3 space-x-3">
                        <i class="fa-solid fa-users-gear w-6"></i>
                        <span class="font-semibold">Account Management</span>
                    </a>
                </li>
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
                                    <label class="w-40 font-bold text-xl">LRN:</label>
                                    <input type="text" name="lrn" class="form-input-pill @error('lrn') border-red-600 @enderror" value="{{ old('lrn') }}" required>
                                </div>
                                @error('lrn') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Last name:</label>
                                    <input type="text" name="last_name" class="form-input-pill @error('last_name') border-red-600 @enderror" value="{{ old('last_name') }}" required>
                                </div>
                                @error('last_name') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">First name:</label>
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
                                <label class="w-40 font-bold text-xl">Gender:</label>
                                <select name="gender" class="form-input-pill bg-white cursor-pointer focus:outline-none">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Birthdate:</label>
                                <input type="date" name="birthdate" class="form-input-pill">
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl leading-tight">Grade &<br>Section:</label>
                                    <select name="advisory" class="form-input-pill bg-white cursor-pointer focus:outline-none @error('advisory') border-red-600 @enderror" required>
                                        <option value="" disabled selected>Select Grade & Section</option>
                                        <option value="Nursery - St. Mary">Nursery - St. Mary</option>
                                         <option value="Kinder - St. Bridget">Kinder - St. Bridget</option>
                                          <option value="Preparatory - St. Augustine">Preparatory - St. Augustine</option>
                                        <option value="1 - Faith">1 - Faith</option>
                                        <option value="2 - Hope">2 - Hope</option>
                                        <option value="3 - Love">3 - Love</option>
                                        <option value="4 - Grace">4 - Grace</option>
                                        <option value="5 - Light">5 - Light</option>
                                        <option value="6 - Wisdom">6 - Wisdom</option>
                                    </select>
                                </div>
                                @error('advisory') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Username:</label>
                                    <input type="text" name="username" class="form-input-pill @error('username') border-red-600 @enderror" value="{{ old('username') }}" required>
                                </div>
                                @error('username') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Password:</label>
                                    <input type="password" name="password" x-model="pw" class="form-input-pill" required>
                                </div>
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Confirm:</label>
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