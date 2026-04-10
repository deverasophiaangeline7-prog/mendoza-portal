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
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
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

    <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">Teacher Accounts</h2>
                        <h3 class="text-2xl font-bold text-orange-400 mt-1 italic">Teachers List</h3>
                    </div>
                    <a href="{{ route('account.management') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>

                <div class="overflow-hidden border-2 border-black rounded-lg shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-200 border-b-2 border-black">
                                <th class="px-4 py-4 border-r-2 border-black text-center font-bold text-xl w-24">No.</th>
                                <th class="px-6 py-4 border-r-2 border-black font-bold text-xl">Name</th>
                                <th class="px-6 py-4 font-bold text-xl">Advisory Class</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($teachers as $index => $teacher)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 border-r-2 border-black text-center font-bold text-lg text-gray-700">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 border-r-2 border-black font-bold text-lg">
                                    {{ $teacher->name }}
                                </td>
                                <td class="px-6 py-4 flex justify-between items-center">
                                <span class="font-bold text-lg">
                                    {{ $teacher->advisory ?? 'No Advisory' }}
                                </span>

                                <div class="flex gap-2 items-center">
                                    <button class="bg-[#34C759] text-white px-4 py-1.5 rounded-full font-bold text-sm">Edit</button>
                                    
                                    <button class="bg-[#FF9500] text-white px-4 py-1.5 rounded-full font-bold text-sm">View CV</button>

                                    <form action="{{ route('account.teacher.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1.5 rounded-full font-bold text-sm transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 font-bold text-xl">
                                    No teacher accounts found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>