<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc. | Parent Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    </style>
</head>
<body class="bg-gray-100" x-data="{ 
    currentMonth: {{ now()->month - 1 }}, 
    currentYear: {{ now()->year }}, 
    selectedDate: {{ now()->day }},
    monthNames: ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'],
    events: {{ json_encode($eventsData) }}
}">

<header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <i class="fa-solid fa-envelope relative cursor-pointer">
                <span class="absolute -top-2 -right-2 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-red-700">1</span>
            </i>
            <i class="fa-solid fa-bell cursor-pointer"></i>
            
            <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
            </button>

            <div x-show="open" 
                 x-transition 
                 class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden"
                 style="display: none;">
                
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
    <nav class="w-64 bg-[#b91c1c] text-white pt-4">
        <ul class="space-y-1">
            <li class="bg-orange-400 mx-2 rounded-lg">
                <a href="#" class="flex items-center p-3 space-x-3"><i class="fa-solid fa-chart-line"></i><span class="font-semibold">Dashboard</span></a>
            </li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate"></i><span>Student Information</span></a></li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days"></i><span>Student Calendar</span></a></li>
            <li class="{{ request()->routeIs('parent.reportcard') ? 'bg-orange-400' : '' }} mx-2 rounded-lg">
                <a href="{{ route('parent.reportcard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-star w-6"></i>
                    <span>Report Card</span>
                </a>
            </li>
            <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet"></i><span>Tuition Fee</span></a></li>
            <li><a href="{{ route('parent.attendance') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check"></i><span>Attendance</span></a></li>
        </ul>
    </nav>

    <main class="flex-1 p-8 bg-white">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-extrabold tracking-tight uppercase">Welcome, Parent</h2>
    </div>

    <div class="relative w-full h-80 bg-orange-400 rounded-3xl p-6 shadow-lg border-2 border-black mb-12">
        <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-orange-300 relative overflow-hidden flex items-center justify-center">
            @if($announcementImages->count() > 0)
                <div class="absolute inset-0">
                    <img src="{{ asset('storage/' . $announcementImages->first()->image_path) }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="text-center text-gray-400 italic font-bold">No Active Announcements</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-12" x-data="{ 
        currentMonth: {{ now()->month - 1 }}, 
        currentYear: {{ now()->year }},
        selectedDate: {{ now()->day }},
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        get daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
        get startDay() { return new Date(this.currentYear, this.currentMonth, 1).getDay(); }
    }">

        <div>
            <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">SCHOOL CALENDAR</h3>
            <div class="bg-[#d97706] rounded-[40px] p-6 border-[3px] border-black shadow-lg">
                
                <div class="flex justify-between items-center mb-4 px-2">
                    <button @click="currentMonth === 0 ? (currentMonth = 11, currentYear--) : currentMonth--" class="text-white text-3xl hover:scale-125 transition">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="text-center">
                        <span class="text-white text-5xl font-black italic tracking-tighter block" style="text-shadow: 2px 2px 0px #800000;" x-text="monthNames[currentMonth]"></span>
                        <span class="text-white text-2xl font-black tracking-tighter" x-text="currentYear"></span>
                    </div>
                    <button @click="currentMonth === 11 ? (currentMonth = 0, currentYear++) : currentMonth++" class="text-white text-3xl hover:scale-125 transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="bg-white rounded-2xl p-4 border-2 border-black">
                    <div class="calendar-grid mb-4">
                        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
                            <span class="text-[#b91c1c] text-center font-black text-sm">{{ $day }}</span>
                        @endforeach
                    </div>

                    <div class="calendar-grid">
                        <template x-for="blank in startDay">
                            <div class="aspect-square"></div>
                        </template>

                        <template x-for="day in daysInMonth">
                        <button @click="selectedDate = day"
                                class="aspect-square flex items-center justify-center rounded-lg border-2 font-black text-xl transition-all relative"
                                :class="{
                                    /* State: Selected Date */
                                    'bg-red-500 text-white border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] scale-110 z-10': selectedDate === day,
                                    
                                    /* State: Not Selected */
                                    'bg-white text-black border-gray-200 hover:bg-orange-100': selectedDate !== day,
                                    
                                    /* State: Has Event (Red Ring indicator) */
                                    'ring-2 ring-red-600 ring-offset-1': events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0')]
                                }"
                                x-text="day">
                        </button>
                    </template>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">EVENTS</h3>
            
            <div class="bg-white rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[420px] flex flex-col justify-center text-center">
                
                <template x-if="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')]">
                    <div class="space-y-6">
                        <p class="font-black text-lg text-gray-800 uppercase">Name of the event:</p>
                        <h4 class="text-red-600 text-4xl font-black uppercase leading-tight" 
                            x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].name"></h4>
                        
                        <p class="font-black text-lg text-gray-800 uppercase">Time:</p>
                        <p class="text-red-600 text-2xl font-black italic" 
                            x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].time"></p>
                        
                        <div class="mt-4 border-t pt-4 border-dashed border-black">
                            <p class="font-black text-gray-800 uppercase text-sm">Description:</p>
                            <p class="font-normal italic text-red-600" 
                               x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].ps"></p>
                        </div>
                    </div>
                </template>

                <template x-if="!events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')]">
                    <div class="flex flex-col items-center">
                         <i class="fa-solid fa-calendar-xmark text-6xl text-gray-200 mb-4"></i>
                         <p class="text-gray-400 text-2xl font-black italic">
                            No events scheduled for this day.
                        </p>
                    </div>
                </template>

            </div>
        </div>
    </div>
</main>
</div>
</body>
</html>