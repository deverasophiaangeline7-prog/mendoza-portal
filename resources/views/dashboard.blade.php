<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    </style>
</head>

<body class="bg-gray-100" x-data="{ 
    openModal: false, 
    selectedDate: {{ now()->day }}, 
    isEditing: false,
    tempName: '',
    tempStartTime: '', 
    tempEndTime: '',
    tempPs: '',
    currentMonth: {{ now()->month - 1 }}, 
    currentYear: {{ now()->year }},
    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    events: {{ json_encode($eventsData) ?: '{}' }},

    get daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
    get startDay() { return new Date(this.currentYear, this.currentMonth, 1).getDay(); },
    
    nextMonth() { if(this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; } else { this.currentMonth++; } },
    prevMonth() { if(this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; } else { this.currentMonth--; } },

    formatTime(time) {
        if (!time) return 'No time set';
        let parts = time.split(':');
        let hours = parseInt(parts[0]);
        let minutes = parts[1];
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12; 
        return `${hours}:${minutes} ${ampm}`;
    },

    getDateKey(day) {
        return `${this.currentYear}-${(this.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    },

    // ---> THIS IS THE NEW PART YOU NEED TO ADD <---
    get minTimeLimit() {
        let today = new Date();
        if (this.currentYear === today.getFullYear() && 
            this.currentMonth === today.getMonth() && 
            this.selectedDate === today.getDate()) {
            
            let hh = today.getHours().toString().padStart(2, '0');
            let mm = today.getMinutes().toString().padStart(2, '0');
            return `${hh}:${mm}`;
        }
        return null; 
    }
}">

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
        <!-- Dashboard -->
        <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>

        <!-- Student Information: ONLY for Parents -->
        @if(auth()->user()->role === 'parent')
            <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                Student Information
            </x-sidebar-link>
        @endif

        <!-- Advisory Class: ONLY for Teachers -->
        @if(auth()->user()->role === 'teacher')
            <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">
                Advisory Class
            </x-sidebar-link>
        @endif

        <!-- Student Calendar: Role-Based Routing -->
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

        <!-- Report Card: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" 
            icon="fa-solid fa-star" 
            :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">
            Report Card
        </x-sidebar-link>
        
        <!-- Attendance: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" 
            icon="fa-solid fa-calendar-check" 
            :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">
            Attendance
        </x-sidebar-link>

        <!-- Account Management: ONLY for Admin -->
        @if(auth()->user()->role === 'admin')
            <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                Account Management
            </x-sidebar-link>
        @endif
    </ul>
</nav>

        <main class="flex-1 p-8 bg-white">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-extrabold">Welcome, Admin!</h2>
                <a href="{{ route('announcement-images.archived') }}" title="View Archives">
                    <i class="fa-solid fa-box-archive text-3xl text-orange-800 opacity-80 hover:opacity-100 transition cursor-pointer"></i>
                </a>
            </div>

            <div class="relative w-full h-80 bg-orange-400 rounded-3xl p-6 shadow-lg border-2 border-black mb-12" 
                 x-data="{ activeSlide: 0, slidesCount: {{ $announcementImages->count() }} }">
                
                <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-orange-300 relative overflow-hidden flex items-center justify-center">
                    @if($announcementImages->count() > 0)
                        @foreach($announcementImages as $index => $image)
                            <div x-show="activeSlide === {{ $index }}" x-transition:enter="transition ease-out duration-500" class="absolute inset-0">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                                @if($image->caption)
                                    <div class="absolute bottom-4 left-4 bg-black/60 text-white px-4 py-2 rounded-xl backdrop-blur-sm">{{ $image->caption }}</div>
                                @endif
                            </div>
                        @endforeach
                        <button @click="activeSlide = activeSlide === 0 ? slidesCount - 1 : activeSlide - 1" class="absolute left-4 z-20 bg-white/40 hover:bg-white/80 p-2 rounded-full shadow-md"><i class="fa-solid fa-chevron-left"></i></button>
                        <button @click="activeSlide = activeSlide === slidesCount - 1 ? 0 : activeSlide + 1" class="absolute right-4 z-20 bg-white/40 hover:bg-white/80 p-2 rounded-full shadow-md"><i class="fa-solid fa-chevron-right"></i></button>
                    @else
                        <div class="text-center text-gray-400 italic font-bold">No Active Announcement Images</div>
                    @endif

                    <div class="absolute top-4 right-6 z-30" x-data="{ openDropdown: false }">
                        <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="bg-white/90 px-3 py-1 rounded-lg font-black shadow-sm focus:outline-none uppercase text-sm">
                            Edit <i class="fa-solid fa-caret-down ml-1"></i>
                        </button>
                        <div x-show="openDropdown" x-transition x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-2xl border py-2 text-right">
                            <button @click="openModal = true; openDropdown = false" class="block w-full px-4 py-2 font-bold hover:bg-gray-100 uppercase text-xs">Add New</button>
                            @if($announcementImages->count() > 0)
                                @foreach($announcementImages as $index => $image)
                                    <form x-show="activeSlide === {{ $index }}" action="{{ route('announcement-images.archive', $image->image_id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="block w-full px-4 py-2 text-red-600 font-bold hover:bg-gray-100 uppercase text-xs border-t">Archive Current</button>
                                    </form>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-12" x-data="{ 
                currentMonth: {{ now()->month - 1 }}, 
                currentYear: {{ now()->year }},
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                get daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
                get startDay() { return new Date(this.currentYear, this.currentMonth, 1).getDay(); },
                nextMonth() { if(this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; } else { this.currentMonth++; } },
                prevMonth() { if(this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; } else { this.currentMonth--; } }
            }">

                <div>
                    <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">SCHOOL CALENDAR</h3>
                    <div class="bg-[#d97706] rounded-[40px] p-6 border-[3px] border-black shadow-lg">
                        <div class="flex justify-between items-center mb-4 px-2">
                            <button @click="prevMonth()" class="text-white text-3xl hover:scale-125 transition">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <div class="text-center">
                                <span class="text-white text-5xl font-black italic tracking-tighter block uppercase leading-none" 
                                    style="text-shadow: 3px 3px 0px #800000;" 
                                    x-text="monthNames[currentMonth]"></span>
                                <span class="text-white text-2xl font-black tracking-tighter" x-text="currentYear"></span>
                            </div>
                            <button @click="nextMonth()" class="text-white text-3xl hover:scale-125 transition">
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
                    
                    <div x-show="events[getDateKey(selectedDate)] && !isEditing" x-cloak x-transition 
                         class="bg-white rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[420px] relative flex flex-col justify-center text-center">
                        
                        <div class="absolute top-6 right-8 flex flex-col items-end space-y-1">
                            <button @click="
                                let key = getDateKey(selectedDate);
                                tempName = events[key].name; 
                                tempStartTime = events[key].start_time; // Ensure this matches index()
                                tempEndTime = events[key].end_time;     // Ensure this matches index()
                                tempPs = events[key].ps;
                                isEditing = true; 
                            " class="text-green-500 font-black text-lg hover:scale-110 transition">+ Edit</button>
                            
                            <button @click="
                                if(confirm('Delete this event?')) {
                                    let key = getDateKey(selectedDate);
                                    fetch('/calendar/delete/' + key, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(res => res.ok && delete events[key]);
                                }
                            " class="text-red-600 font-black text-lg hover:scale-110 transition">- Delete</button>
                        </div>

                        <div class="space-y-6">
                            <p class="font-black text-lg text-gray-800 uppercase">Event Name:</p>
                            <h4 class="text-red-600 text-4xl font-black uppercase leading-tight" x-text="events[getDateKey(selectedDate)]?.name"></h4>
                            
                            <p class="font-black text-lg text-gray-800 uppercase">Time:</p>
                            <p class="text-red-600 text-2xl font-black italic">
                                <span x-text="formatTime(events[getDateKey(selectedDate)]?.start_time)"></span>
                                <span class="text-black not-italic mx-2">-</span>
                                <span x-text="formatTime(events[getDateKey(selectedDate)]?.end_time)"></span>
                            </p>
                            
                            <div class="mt-4 border-t pt-4 border-dashed border-black">
                                <p class="font-black text-gray-800">PS: 
                                    <span class="font-normal italic text-red-600" x-text="events[getDateKey(selectedDate)]?.ps || 'No description'"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div x-show="!events[getDateKey(selectedDate)] && !isEditing" x-cloak x-transition
                         class="bg-white rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[420px] flex flex-col justify-center text-center">
                        <p class="text-gray-400 text-2xl font-black italic mb-4">No events for <span x-text="monthNames[currentMonth]"></span> <span x-text="selectedDate"></span></p>
                        <button @click="isEditing = true; tempName=''; tempStartTime=''; tempEndTime=''; tempPs='';" 
                                class="text-green-500 text-3xl font-black italic hover:scale-110 transition">+ Add an event</button>
                    </div>

                    <div x-show="isEditing" x-cloak x-transition 
                        class="bg-white rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[420px] flex flex-col justify-center text-left space-y-4">
                        <h4 class="text-xl font-black uppercase border-b-2 border-black pb-2 text-red-800">
                            Set Event: <span x-text="monthNames[currentMonth]"></span> <span x-text="selectedDate"></span>
                        </h4>
                        
                        <label class="block font-black text-xs uppercase text-gray-400">Event Name</label>
                        <input type="text" x-model="tempName" class="w-full p-2 border-2 border-black rounded-lg">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-black text-xs uppercase text-gray-400">Start Time</label>
                                <input type="time" x-model="tempStartTime" class="w-full p-2 border-2 border-black rounded-lg font-bold">
                            </div>
                            <div>
                                <label class="block font-black text-xs uppercase text-gray-400">Finish Time</label>
                                <input type="time" 
                                    x-model="tempEndTime" 
                                    :min="tempStartTime" 
                                    @change="if(tempStartTime && tempEndTime < tempStartTime) { tempEndTime = tempStartTime; triggerNotification('Finish time cannot be earlier than the start time.'); }"
                                    class="w-full p-2 border-2 border-black rounded-lg font-bold">
                            </div>
                        </div>

                        <label class="block font-black text-xs uppercase text-gray-400">Notes (PS)</label>
                        <textarea x-model="tempPs" class="w-full p-2 border-2 border-black rounded-lg"></textarea>
                        
                        <div class="flex space-x-2 pt-2 w-full">
                            <button @click="isEditing = false" class="bg-gray-200 px-4 py-2 rounded-lg font-black flex-1 border-2 border-black hover:bg-gray-300 transition">CANCEL</button>
                            <button @click="
                                fetch('{{ route('calendar.store') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({
                                        start_date: getDateKey(selectedDate),
                                        event_title: tempName,
                                        start_time: tempStartTime,
                                        end_time: tempEndTime,
                                        description: tempPs
                                    })
                                })
                                .then(res => {
                                    if(res.ok) {
                                        events[getDateKey(selectedDate)] = { 
                                            name: tempName, 
                                            start_time: tempStartTime, 
                                            end_time: tempEndTime, 
                                            ps: tempPs 
                                        };
                                        isEditing = false;
                                    }
                                })" 
                                class="bg-red-600 text-white px-4 py-2 rounded-lg font-black flex-1 border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-red-700 transition">
                                SAVE
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="openModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100]" x-cloak>
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border-4 border-orange-400">
            <h3 class="text-2xl font-black mb-6 text-red-800 uppercase italic">Upload New Image</h3>
            <form action="{{ route('announcement-images.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block font-bold text-gray-700 mb-2 uppercase text-xs">Select File</label>
                    <input type="file" name="image" required class="block w-full text-sm border-2 border-dashed p-4 rounded-xl cursor-pointer hover:border-orange-400 transition">
                </div>
                <div class="mb-6">
                    <label class="block font-bold text-gray-700 mb-2 uppercase text-xs">Caption</label>
                    <input type="text" name="caption" placeholder="Ex: School Holiday" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-red-600 outline-none">
                </div>
                <div class="flex space-x-3">
                    <button type="button" @click="openModal = false" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl">CANCEL</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-red-700 text-white font-bold rounded-xl shadow-lg">UPLOAD</button>
                </div>
            </form>

        </div>
    </div>

<div x-show="showNotification" 
     x-cloak 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-10"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-10"
     class="fixed bottom-10 right-10 bg-red-600 text-white font-black px-6 py-4 rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] border-2 border-black z-[200] flex items-center space-x-3">
    <i class="fa-solid fa-circle-exclamation text-2xl"></i>
    <span x-text="notificationMessage"></span>
</div>

</body>
</html>