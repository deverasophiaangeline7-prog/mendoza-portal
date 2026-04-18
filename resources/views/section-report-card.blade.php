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

        <main class="flex-1 p-6 bg-white">
    <div class="max-w-6xl mx-auto">
        
        <div class="relative flex justify-center items-center mb-8">
            <h2 class="text-3xl font-black text-black uppercase tracking-tight">
                {{ $displayName }}
            </h2>
            
            <a href="{{ route('reportcard.index') }}" class="absolute right-0 text-red-600 text-5xl hover:scale-110 transition">
                <i class="fa-solid fa-circle-xmark"></i>
            </a>
        </div>

        <div class="overflow-hidden border-[1.5px] border-black rounded-sm shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-[1.5px] border-black">
                        <th class="p-3 border-r-[1.5px] border-black w-20 text-center font-bold text-xl">No.</th>
                        <th class="p-3 text-center uppercase font-bold text-xl">Learner</th>
                        <th class="w-32"></th> </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-300 border-b-[1.5px] border-black">
                        <td class="p-2 px-4 font-bold text-lg border-r-[1.5px] border-black" colspan="3">Male</td>
                    </tr>
                    
                    @php $mCount = 1; @endphp
                    @foreach(collect($students)->where('gender', 'Male') as $index => $student)
                    <tr class="border-b-[1.5px] border-black last:border-b-0">
                        <td class="p-3 text-center font-bold text-xl border-r-[1.5px] border-black">
                            {{ $mCount++ }}
                        </td>
                        <td class="p-3 px-4 font-semibold text-lg border-r-[1.5px] border-black">
                            {{ $student['name'] }}
                        </td>
                        <td class="p-3 text-center">
                            <a href="{{ route('reportcard.student', ['grade' => $grade, 'student' => $index]) }}" 
                               class="bg-[#ffb31a] hover:bg-orange-500 text-white px-8 py-1.5 rounded-2xl font-black text-xl shadow-md transition inline-block">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach

                    <tr class="bg-gray-300 border-y-[1.5px] border-black">
                        <td class="p-2 px-4 font-bold text-lg border-r-[1.5px] border-black" colspan="3">Female</td>
                    </tr>

                    @php $fCount = 1; @endphp
                    @foreach(collect($students)->where('gender', 'Female') as $index => $student)
                    <tr class="border-b-[1.5px] border-black last:border-b-0">
                        <td class="p-3 text-center font-bold text-xl border-r-[1.5px] border-black">
                            {{ $fCount++ }}
                        </td>
                        <td class="p-3 px-4 font-semibold text-lg border-r-[1.5px] border-black">
                            {{ $student['name'] }}
                        </td>
                        <td class="p-3 text-center">
                            <a href="{{ route('reportcard.student', ['grade' => $grade, 'student' => $index]) }}" 
                               class="bg-[#ffb31a] hover:bg-orange-500 text-white px-8 py-1.5 rounded-2xl font-black text-xl shadow-md transition inline-block">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
</body>
</html>