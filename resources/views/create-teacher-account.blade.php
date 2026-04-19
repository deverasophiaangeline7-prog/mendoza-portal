<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Create Teacher Account</title>
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

<body class="bg-gray-100">

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
            
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="hover:scale-110 transition-transform focus:outline-none">
                    <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
                </button>
            </form>
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
                    <p class="text-3xl font-bold text-black mt-2">Teacher</p>
                </div>

                <form action="{{ route('account.teacher.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-2 gap-x-16 gap-y-6">
                        
                        <div class="space-y-5">
                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">Last name: <span class="text-red-600">*</span></label>
                                <input type="text" name="last_name" class="form-input-pill" required>
                            </div>
                            <div class="flex items-center">
                                <label class="w-40 font-bold text-xl">First name: <span class="text-red-600">*</span></label>
                                <input type="text" name="first_name" class="form-input-pill" required>
                            </div>
                            <div x-data="{ noMiddleName: false }">
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
                            <div class="flex items-start mb-4">
                                <label class="w-40 font-bold text-xl pt-2">Username: <span class="text-red-600">*</span></label>
                                <div style="width: 490px;" class="flex flex-col">
                                    <input type="text" name="username" class="form-input-pill w-full" value="{{ old('username') }}" required>
                                    @error('username')
                                        <span class="text-red-600 text-sm mt-1 font-bold italic">This username is already taken.</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="space-y-5" x-data="{ pw: '', pw_confirm: '' }">
                                <div class="flex flex-col">
                                    <div class="flex items-center">
                                        <label class="w-40 font-bold text-xl">Password: <span class="text-red-600">*</span></label>
                                        <input type="password" name="password" x-model="pw" class="form-input-pill" required>
                                    </div>
                                    @error('password') <span class="text-red-600 text-sm ml-40 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex flex-col">
                                    <div class="flex items-center">
                                        <label class="w-40 font-bold text-xl">Confirm: <span class="text-red-600">*</span></label>
                                        <input type="password" name="password_confirmation" x-model="pw_confirm" class="form-input-pill" required>
                                    </div>
                                    <template x-if="pw_confirm !== '' && pw !== pw_confirm">
                                        <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">Passwords do not match!</span>
                                    </template>
                                </div>
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
                                <input type="date" name="birthdate" class="form-input-pill" max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}" required>
                            </div>
                            
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <label class="w-40 font-bold text-xl">Advisory: <span class="text-red-600">*</span></label>
                                    {{-- Removed 'multiple' to keep it a dropdown --}}
                                    <select name="advisory" class="form-input-pill bg-white cursor-pointer focus:outline-none @error('advisory') border-red-600 @enderror" required>
                                        <option value="" disabled selected>Select Section</option>
                                        
                                        {{-- This is the single NKP option --}}
                                        <option value="NKP">NKP (Nursery, Kinder, Prep)</option>

                                        {{-- Primary Grades --}}
                                        <option value="4">Grade 1 - Faith</option>
                                        <option value="5">Grade 2 - Hope</option>
                                        <option value="6">Grade 3 - Love</option>
                                        <option value="7">Grade 4 - Grace</option>
                                        <option value="8">Grade 5 - Light</option>
                                        <option value="9">Grade 6 - Wisdom</option>
                                    </select>
                                </div>
                                @error('advisory')
                                    <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="pt-2">
                                <label class="block font-bold text-xl mb-2 text-black">Upload CV: <span class="text-red-600">*</span></label>
                                    <div class="border-2 border-black rounded-2xl h-36 flex flex-col items-center justify-center relative hover:bg-gray-50 transition group">
                                        <input type="file" name="cv" id="cv_input" accept=".pdf" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                        <div class="text-center">
                                            <span id="cv_filename" class="text-xl font-bold block">Add attachment</span>
                                            <span id="cv_size" class="text-sm font-medium text-gray-500 block hidden"></span>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-red-700 mt-2 italic flex items-center gap-1">
                                        <i class="fa-solid fa-circle-info"></i> Note: Please upload PDF files only (Max 2MB).
                                    </p>
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
                    <script>
                        const cvInput = document.getElementById('cv_input');
                        const cvName = document.getElementById('cv_filename');
                        const cvSizeDisplay = document.getElementById('cv_size');

                        cvInput.addEventListener('change', function() {
                            if (this.files && this.files[0]) {
                                const file = this.files[0];
                                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2); 

                                // 1. Check for PDF only
                                if (file.type !== "application/pdf") {
                                    alert("Invalid file type. Please upload a PDF.");
                                    this.value = ""; 
                                    cvName.textContent = "Add attachment";
                                    return;
                                }

                                // 2. Check Size (Max 2MB)
                                if (fileSizeMB > 2) {
                                    alert("File is too large! Max size is 2MB.");
                                    this.value = "";
                                    cvName.textContent = "Add attachment";
                                    return;
                                }

                                // 3. SUCCESS: Update the UI text
                                cvName.textContent = file.name;
                                cvName.style.color = "#34C759"; // Turns the text green on success
                                
                                if(cvSizeDisplay) {
                                    cvSizeDisplay.textContent = "(" + fileSizeMB + " MB)";
                                    cvSizeDisplay.classList.remove('hidden');
                                }
                            }
                        });
                    </script>
                </form>
            </div>
        </main>
    </div>
</body>
</html>