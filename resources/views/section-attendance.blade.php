<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Attendance Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100">

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

        <main class="flex-1 p-8 bg-white" x-data="attendanceData()">

            <div class="max-w-6xl mx-auto">
                
                <div class="flex justify-between items-start mb-8">
                    <a href="{{ route('attendance.index') }}" class="text-red-600 text-5xl hover:scale-110 transition">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </a>

                    <div class="text-center flex-1">
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">{{ $displayName }}</h2>
                        <div class="text-[#ffaf2e] font-black text-2xl italic uppercase mt-1 drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
                            Attendance Sheet
                        </div>
                    </div>

                    <div class="text-sm font-bold space-y-1 bg-white p-3 border-[3px] border-black rounded-2xl shadow-[5px_5px_0px_0px_rgba(0,0,0,1)]">
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-green-500 border-2 border-black"></span> Present</div>
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500 border-2 border-black"></span> Absent</div>
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-[#facc15] border-2 border-black"></span> Late</div>
                        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-blue-500 border-2 border-black"></span> Excused</div>
                    </div>
                </div>

                @if($canManage)
                    <div class="mb-10 p-5 border-[3px] border-black rounded-[25px] bg-gray-50 flex flex-wrap items-center justify-between shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                        
                        <div class="flex items-center gap-6">
                            <button @click="isManaging = !isManaging" 
                                class="font-black px-8 py-3 border-[3px] border-black rounded-xl transition-all shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px]"
                                :class="isManaging ? 'bg-green-400 text-black' : 'bg-gray-200 text-gray-500'">
                                <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i>
                                <span x-text="isManaging ? ' EDITING MODE' : ' VIEW MODE'"></span>
                            </button>
                            
                            <div x-show="isManaging" x-cloak class="flex items-center gap-4 animate-fade-in">
                                <span class="font-black uppercase text-sm">Select Day:</span>
                                <input type="date" x-model="selectedDate" class="border-[3px] border-black p-2 rounded-xl font-black bg-white">
                                
                                <button @click="addDateToTable()" class="bg-blue-600 text-white px-8 py-2 rounded-xl border-[3px] border-black font-black hover:bg-blue-700 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                    + ADD DATE
                                </button>
                            </div>
                        </div>

                        <button x-show="isManaging" x-cloak @click="saveAttendance()" class="bg-[#ffaf2e] text-black px-8 py-3 rounded-xl border-[3px] border-black font-black hover:bg-orange-500 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px]">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> SAVE ATTENDANCE
                        </button>

                    </div>
                    @elseif(auth()->user()->role === 'admin')
                        {{-- This part ONLY shows for the Admin --}}
                        <div class="mb-10 p-4 border-[3px] border-black rounded-[25px] bg-blue-100 flex items-center justify-center shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] text-blue-900">
                            <i class="fa-solid fa-eye text-3xl mr-4"></i>
                            <div>
                                <div class="font-black text-2xl tracking-wide uppercase">Admin View-Only Mode</div>
                                <div class="font-bold text-sm">You are viewing this attendance sheet as an administrator.</div>
                            </div>
                        </div>

                    @elseif(auth()->user()->role === 'teacher')
                        {{-- This shows for Teachers who ARE NOT assigned to this specific section --}}
                        <div class="mb-10 p-4 border-[3px] border-black rounded-[25px] bg-gray-200 flex items-center justify-center shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] text-gray-700">
                            <i class="fa-solid fa-lock text-3xl mr-4"></i>
                            <div>
                                <div class="font-black text-2xl tracking-wide uppercase">Teacher View-Only</div>
                                <div class="font-bold text-sm">You are not the assigned adviser for this section.</div>
                            </div>
                        </div>

                    @else
                        {{-- This is what the Parent sees (Optional: Leave empty to show nothing) --}}
                        <div class="mb-6">
                            <h3 class="font-black text-2xl uppercase border-b-4 border-black inline-block">Attendance Overview</h3>
                        </div>
                    @endif

                <div class="border-[3px] border-black overflow-x-auto rounded-[30px] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] bg-white">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b-[3px] border-black">
                                <th class="p-5 border-r-[3px] border-black w-1/3 text-left uppercase font-black text-2xl">Learner Name</th>
                                
                                <template x-for="day in addedDates" :key="day">
                                    <th class="border-r-[2px] border-black text-center text-lg w-16 py-4 bg-orange-50 font-black" x-text="new Date(day).getDate()"></th>
                                </template>
                                
                                <template x-if="addedDates.length === 0">
                                    <th class="p-5 text-gray-400 italic font-bold text-lg">No dates added yet...</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr class="border-b-[2px] border-black hover:bg-yellow-50/50">
                                <td class="p-5 border-r-[3px] border-black font-black text-lg text-black">
                                    {{ strtoupper($student->last_name . ', ' . $student->first_name) }}
                                </td>
                                
                                <template x-for="day in addedDates" :key="day">
                                    <td class="border-r-[2px] border-black h-16 attendance-cell"
                                        x-data="{ status: getSavedStatus('{{ $student->student_id }}', day) }"
                                        :data-student="'{{ $student->student_id }}'"
                                        :data-date="day"
                                        :data-status="status"
                                        @click="if(isManaging) status = (status + 1) % 5"
                                        :class="{
                                            'bg-white': status === 0,
                                            'bg-green-500': status === 1,
                                            'bg-red-500': status === 2,
                                            'bg-[#facc15]': status === 3,
                                            'bg-blue-500': status === 4,
                                            'cursor-pointer': isManaging
                                        }">
                                    </td>
                                </template>
                                
                                <template x-if="addedDates.length === 0">
                                    <td class="bg-white"></td>
                                </template>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="showToast" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-10"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-10"
                 class="fixed bottom-10 right-10 z-50 px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex items-center gap-4"
                 :class="toastType === 'success' ? 'bg-[#4ade80] text-black' : 'bg-red-500 text-white'">
                
                <i class="fa-solid text-2xl" :class="toastType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'"></i>
                <span class="font-black text-xl tracking-wide" x-text="toastMessage"></span>
            </div>

        </main>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendanceData', () => ({
            isManaging: false,
            selectedDate: new Date().toISOString().split('T')[0],
            
            // Toast variables
            showToast: false,
            toastMessage: '',
            toastType: 'success',
            
            // Loaded dynamically from Laravel
            addedDates: @json($existingDates ?? []),
            serverAttendance: @json($attendanceMap ?? []),
            
            addDateToTable() {
                if (!this.addedDates.includes(this.selectedDate)) {
                    this.addedDates.push(this.selectedDate);
                    this.addedDates.sort(); // Keep dates in order
                } else {
                    this.triggerToast('Date already added!', 'error');
                }
            },

            getSavedStatus(studentId, date) {
                // If we have data from the server, use it. Otherwise, default to 0 (Unset) or 1 (Present)
                if (this.serverAttendance[studentId] && this.serverAttendance[studentId][date]) {
                    return this.serverAttendance[studentId][date];
                }
                return 0; 
            },

            async saveAttendance() {
                // 1. Gather all the data from the cells
                const attendanceData = [];
                const cells = document.querySelectorAll('.attendance-cell');
                
                cells.forEach(cell => {
                    attendanceData.push({
                        student_id: cell.getAttribute('data-student'),
                        date: cell.getAttribute('data-date'),
                        status: cell.getAttribute('data-status')
                    });
                });

                if (attendanceData.length === 0) {
                    this.triggerToast('No data to save!', 'error');
                    return;
                }

                try {
                    // 2. Send to Laravel
                    const response = await fetch('{{ route("attendance.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ attendance: attendanceData })
                    });

                    if (response.ok) {
                        this.triggerToast('ATTENDANCE SAVED!', 'success');
                        this.isManaging = false; // Switch back to view mode
                    } else {
                        throw new Error('Server error');
                    }
                } catch (error) {
                    console.error(error);
                    this.triggerToast('FAILED TO SAVE', 'error');
                }
            },

            triggerToast(message, type = 'success') {
                this.toastMessage = message;
                this.toastType = type;
                this.showToast = true;
                setTimeout(() => this.showToast = false, 3000);
            }
        }));
    });
</script>

<style>
    /* Add this for the animation mentioned in your HTML */
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>