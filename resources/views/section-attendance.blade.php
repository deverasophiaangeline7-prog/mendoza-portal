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

<body class="bg-gray-100" x-data="{ 
    isManaging: false, 
    isPublishing: false,
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
                <li><a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('attendance.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
                @if(auth()->user()->role == 'admin')
                    <li><a href="{{ route('account.management') }}" class="flex items-center p-3 space-x-3"><i class="fa-solid fa-users-gear w-6"></i><span class="font-semibold">Account Management</span></a></li>
                @endif
            </ul>
        </nav>

       <main class="flex-1 p-6 bg-white" 
    x-data="{ 
        isManaging: false, 
        selectedDate: new Date().toISOString().split('T')[0], 
        addedDates: [], // This starts empty and holds the days you add
        
        addDateToTable() {
            // Get just the day number from the selected date (e.g., '2024-03-15' -> 15)
            let dateObj = new Date(this.selectedDate);
            let dayNumber = dateObj.getDate();
            
            // Only add if it's not already there
            if (!this.addedDates.includes(dayNumber)) {
                this.addedDates.push(dayNumber);
                // Optional: Sort them so they stay in order (1, 2, 3...)
                this.addedDates.sort((a, b) => a - b);
            } else {
                alert('This date is already in the table!');
            }
        }
    }">

    <div class="max-w-6xl mx-auto" x-data="{ selectedDate: new Date().toISOString().split('T')[0] }">
        
        <div class="flex justify-between items-start mb-6">
            <a href="{{ route('attendance.index') }}" class="text-red-600 text-4xl hover:scale-110 transition">
                <i class="fa-solid fa-circle-xmark"></i>
            </a>

            <div class="text-center flex-1">
                <h2 class="text-3xl font-bold text-black uppercase">{{ $displayName }}</h2>
                <div class="text-orange-500 font-bold text-xl italic uppercase mt-1">Attendance Sheet</div>
            </div>

            <div class="text-sm font-bold space-y-1 bg-gray-50 p-2 border border-black rounded">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500 border border-black"></span> Present</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500 border border-black"></span> Absent</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-400 border border-black"></span> Late</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 border border-black"></span> Excused</div>
            </div>
        </div>

    <div class="max-w-6xl mx-auto">
        
        <div class="mb-6 p-4 border-2 border-black rounded-xl bg-gray-50 flex flex-wrap items-center gap-6 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            
            <button @click="isManaging = !isManaging" 
                class="font-black px-6 py-2 border-2 border-black rounded-lg transition-all"
                :class="isManaging ? 'bg-green-400 text-black' : 'bg-gray-200 text-gray-500'">
                <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i>
                <span x-text="isManaging ? 'Editing Mode' : 'View Mode'"></span>
            </button>
            
            <div x-show="isManaging" x-cloak class="flex items-center gap-3 animate-fade-in">
                <span class="font-bold uppercase text-sm">Select School Day:</span>
                <input type="date" x-model="selectedDate" class="border-2 border-black p-1 rounded font-bold">
                
                <button @click="addDateToTable()" class="bg-blue-600 text-white px-4 py-1 rounded border-2 border-black font-bold hover:bg-blue-700 active:translate-y-0.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    + Add to Table
                </button>
            </div>
        </div>

        <div class="border-2 border-black overflow-x-auto rounded-lg">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-black">
                        <th class="p-3 border-r-2 border-black w-1/3 text-left uppercase font-black">Learner Name</th>
                        
                        <template x-for="day in addedDates" :key="day">
                            <th class="border-r border-black text-center text-xs w-10 py-3 bg-orange-50 font-bold" x-text="day"></th>
                        </template>

                        <template x-if="addedDates.length === 0">
                            <th class="p-3 text-gray-400 italic font-normal text-sm">No dates added yet...</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="border-b border-black hover:bg-gray-50">
                        <td class="p-3 border-r-2 border-black font-medium text-sm">{{ $student['name'] }}</td>
                        
                        <template x-for="day in addedDates" :key="day">
                            <td class="border-r border-black h-12 transition-all"
                                x-data="{ status: 0 }"
                                @click="if(isManaging) status = (status + 1) % 5"
                                :class="{
                                    'bg-white': status === 0,
                                    'bg-green-500': status === 1,
                                    'bg-red-500': status === 2,
                                    'bg-yellow-400': status === 3,
                                    'bg-blue-500': status === 4,
                                    'cursor-pointer': isManaging
                                }">
                            </td>
                        </template>

                        <template x-if="addedDates.length === 0">
                            <td class="bg-gray-50"></td>
                        </template>
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