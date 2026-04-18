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
        /* Custom styles for the form inputs to match image */
        .form-input-pill {
            border: 2px solid black;
            border-radius: 0.75rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            width: 100%;
        }
    </style>
</head>

<body class="bg-gray-100" x-data="{ 
    openModal: false, 
    events: {{ json_encode($eventsData ?? []) }} 
}">

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
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li><a href="{{ route('attendance.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
                <li><a href="{{ route('account.management') }}" class="flex items-center p-3 space-x-3"><i class="fa-solid fa-users-gear w-6"></i><span class="font-semibold">Account Management</span></a></li>
            </ul>
        </nav>

        <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto">
                
                <div class="relative flex justify-center items-center mb-8">
                    <div class="text-center">
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">Report Card</h2>
                    </div>
                </div>
 
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8">
                @php
                    $grades = [
                        ['id' => 'nursery',     'level' => 'NURSERY',     'name' => 'St. Mary'],
                        ['id' => 'kinder',      'level' => 'KINDER',      'name' => 'St. Bridget'],
                        ['id' => 'preparatory', 'level' => 'PREPARATORY', 'name' => 'St. Augustine'],
                        ['id' => 'grade-1',     'level' => 'GRADE 1',     'name' => 'Faith'],
                        ['id' => 'grade-2',     'level' => 'GRADE 2',     'name' => 'Hope'],
                        ['id' => 'grade-3',     'level' => 'GRADE 3',     'name' => 'Love'],
                        ['id' => 'grade-4',     'level' => 'GRADE 4',     'name' => 'Grace'],
                        ['id' => 'grade-5',     'level' => 'GRADE 5',     'name' => 'Light'],
                        ['id' => 'grade-6',     'level' => 'GRADE 6',     'name' => 'Wisdom'],
                    ];
                @endphp
 
                @foreach($grades as $grade)
                    <button type="button" 
                        onclick="window.location.href='{{ route('reportcard.show', ['grade' => $grade['id']]) }}'"
                        class="bg-[#ffb31a] border-2 border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95">
                        
                        <span class="text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-orange-500 transition-transform" 
                            style="-webkit-text-stroke: 1.5px white;">
                            {{ $grade['level'] }}
                        </span>
                        <span class="text-xl font-medium text-black group-hover:-translate-y-1 transition-transform">
                            {{ $grade['name'] }}
                        </span>
                    </button>
                @endforeach
</div>
</main>
</div>
</body>
</html>