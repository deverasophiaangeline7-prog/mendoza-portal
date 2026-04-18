<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy</title>
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

<body class="bg-gray-100" x-data="{ openModal: false }">

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
                <button type="submit" class="hover:scale-110 transition-transform focus:outline-none">
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
                <li class="bg-orange-400 mx-2 rounded-lg"><a href="{{ route('account.management') }}" class="flex items-center p-3 space-x-3"><i class="fa-solid fa-users-gear w-6"></i><span class="font-semibold">Account Management</span></a></li>
            </ul>
        </nav>

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h2 class="text-4xl font-black text-black uppercase">Account Management</h2>
                <h3 class="text-3xl font-black text-orange-400 italic uppercase" style="-webkit-text-stroke: 1.5px black;">
                    {{ str_replace('-', ' ', $grade) }} - {{ $section->section_name ?? 'General' }}
                </h3>
            </div>

            <div class="border-2 border-black rounded-lg overflow-hidden bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-200 border-b-2 border-black text-xl font-bold">
                        <tr>
                            <th class="p-4 border-r-2 border-black text-center w-24">No.</th>
                            <th class="p-4 border-r-2 border-black w-40">LRN</th>
                            <th class="p-4">Learner</th>
                            <th class="p-4 w-32 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Male</td></tr>
                        @foreach($males as $index => $student)
                            <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                                <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                                <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                                <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="p-4 text-center"><button class="bg-green-500 text-white px-4 py-1 rounded-lg font-bold border border-black/20">Edit</button></td>
                            </tr>
                        @endforeach

                        <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Female</td></tr>
                        @foreach($females as $index => $student)
                            <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                                <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                                <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                                <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="p-4 text-center"><button class="bg-green-500 text-white px-4 py-1 rounded-lg font-bold border border-black/20">Edit</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>