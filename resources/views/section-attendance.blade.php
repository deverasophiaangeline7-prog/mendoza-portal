@extends('layouts.navigation')

@section('title', 'Attendance Sheet')

@section('content')
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<main class="flex-1 p-4 sm:p-8 bg-white min-h-screen relative" x-data="attendanceData()">
    <div class="max-w-6xl mx-auto">
        
        <!-- HEADER SECTION -->
        <div class="flex flex-col lg:flex-row justify-between items-center lg:items-start gap-6 mb-8">
            <!-- Back Button -->
            <div class="w-full lg:w-auto flex justify-center lg:justify-start order-2 lg:order-1">
                <a href="{{ route('attendance.index') }}" class="text-red-600 text-5xl hover:scale-110 transition">
                    <i class="fa-solid fa-circle-xmark"></i>
                </a>
            </div>

            <!-- Title -->
            <div class="text-center flex-1 order-1 lg:order-2 w-full">
                <h2 class="text-3xl sm:text-4xl font-black text-black uppercase tracking-tight">{{ $displayName }}</h2>
                <div class="text-[#b26905] font-black text-xl sm:text-2xl italic uppercase mt-1 drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
                    Attendance Sheet
                </div>
            </div>

            <!-- Legend -->
            <div class="w-full lg:w-auto flex justify-center lg:justify-end order-3 lg:order-3">
                <div class="text-sm font-bold bg-white p-3 border-[3px] border-black rounded-2xl shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] grid grid-cols-2 gap-x-6 gap-y-2 lg:grid-cols-1 lg:gap-1 lg:space-y-1">
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-green-500 border-2 border-black"></span> Present</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500 border-2 border-black"></span> Absent</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-[#facc15] border-2 border-black"></span> Late</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-blue-500 border-2 border-black"></span> Excused</div>
                </div>
            </div>
        </div>

        @if($canManage)
            <!-- CONTROL PANEL -->
            <div class="mb-10 p-4 border-[3px] border-black rounded-[25px] bg-gray-50 flex flex-col xl:flex-row items-center justify-between gap-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] overflow-x-auto">
                
                <!-- Left Side: Actions (Forced into one row) -->
                <div class="flex flex-row items-center gap-3 w-max">
                    <button @click="isManaging = !isManaging" 
                        class="font-black px-5 py-2.5 border-[3px] border-black rounded-xl transition-all shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] whitespace-nowrap"
                        :class="isManaging ? 'bg-green-400 text-black' : 'bg-gray-200 text-gray-500'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i>
                        <span x-text="isManaging ? ' EDITING MODE' : ' VIEW MODE'"></span>
                    </button>
                    
                    <div x-show="isManaging" x-cloak class="flex flex-row items-center gap-3 animate-fade-in">
                        <span class="font-black uppercase text-sm whitespace-nowrap hidden lg:inline">Select Day:</span>
                        <input type="date" 
                                 x-model="selectedDate" 
                                    @change="
                                        if(selectedDate) {
                                            const day = new Date(selectedDate).getUTCDay();
                                            if(day === 0 || day === 6) {
                                                triggerToast('Weekends are not allowed! Please select a weekday.', 'error');
                                                selectedDate = '';
                                            }
                                        }
                                    "
                               class="border-[3px] border-black p-2 rounded-xl font-black bg-white cursor-pointer w-[140px] sm:w-auto">
                        
                        <button @click="addDateToTable()" class="bg-blue-600 text-white px-4 sm:px-6 py-2.5 rounded-xl border-[3px] border-black font-black hover:bg-blue-700 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap">
                            + ADD DATE
                        </button>

                        <button @click="deleteDateFromTable()" class="bg-red-600 text-white px-4 sm:px-6 py-2.5 rounded-xl border-[3px] border-black font-black hover:bg-red-700 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap">
                            - DELETE DATE
                        </button>
                    </div>
                </div>

                <!-- Right Side: Save Button -->
                <button x-show="isManaging" x-cloak @click="saveAttendance()" class="w-full xl:w-auto bg-[#b26905] text-black px-6 py-2.5 rounded-xl border-[3px] border-black font-black hover:bg-amber-700 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] whitespace-nowrap mt-4 xl:mt-0">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> SAVE ATTENDANCE
                </button>
            </div>
        @elseif(auth()->user()->role === 'admin')
            <div class="mb-10 p-4 border-[3px] border-black rounded-[25px] bg-blue-100 flex items-center justify-center shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] text-blue-900">
                <i class="fa-solid fa-eye text-3xl mr-4"></i>
                <div>
                    <div class="font-black text-2xl tracking-wide uppercase">Admin View-Only Mode</div>
                    <div class="font-bold text-sm">You are viewing this attendance sheet as an administrator.</div>
                </div>
            </div>
        @elseif(auth()->user()->role === 'teacher')
            <div class="mb-10 p-4 border-[3px] border-black rounded-[25px] bg-gray-200 flex items-center justify-center shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] text-gray-700">
                <i class="fa-solid fa-lock text-3xl mr-4"></i>
                <div>
                    <div class="font-black text-2xl tracking-wide uppercase">Teacher View-Only</div>
                    <div class="font-bold text-sm">You are not the assigned adviser for this section.</div>
                </div>
            </div>
        @else
            <div class="mb-6">
                <h3 class="font-black text-2xl uppercase border-b-4 border-black inline-block">Attendance Overview</h3>
            </div>
        @endif

        <!-- PAGINATION CONTROLS -->
        <div class="flex justify-between items-center mb-6 bg-white border-[3px] border-black rounded-[20px] p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            @if(isset($currentPage) && isset($totalPages))
                @if($currentPage <$totalPages)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="font-black text-black hover:text-blue-600 transition">
                        <i class="fa-solid fa-arrow-left"></i> OLDER DATES
                    </a>
                @else
                    <span class="font-black text-gray-400 cursor-not-allowed"><i class="fa-solid fa-arrow-left"></i> OLDER DATES</span>
                @endif

                <span class="font-black uppercase text-lg sm:text-xl text-center px-4">Page {{ $currentPage }} of {{$totalPages }}</span>

                @if($currentPage > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="font-black text-black hover:text-blue-600 transition">
                        NEWER DATES <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @else
                    <span class="font-black text-gray-400 cursor-not-allowed">NEWER DATES <i class="fa-solid fa-arrow-right"></i></span>
                @endif
            @else
                <span class="font-black text-gray-400 text-center w-full">Pagination not available</span>
            @endif
        </div>

        <div class="border-[3px] border-black overflow-hidden rounded-[30px] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] bg-white">
            <table class="w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-gray-100 border-b-[3px] border-black">
                        <th class="p-4 sm:p-5 border-r-[3px] border-black w-[40%] text-left uppercase font-black text-xl sm:text-2xl truncate">Learner Name</th>
                        
                        <template x-for="day in addedDates" :key="day">
                            <th class="border-r-[2px] last:border-r-0 border-black text-center text-base sm:text-lg py-4 bg-amber-700 font-black truncate" 
                                x-text="new Date(day).toLocaleDateString('en-US', { timeZone: 'UTC', month: 'short', day: 'numeric' })">
                            </th>
                        </template>
                        
                        <template x-if="addedDates.length === 0">
                            <th class="p-5 text-gray-400 italic font-bold text-lg text-center">No dates added yet...</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="border-b-[2px] border-black hover:bg-yellow-50/50">
                        <td class="p-4 sm:p-5 border-r-[3px] border-black font-black text-base sm:text-lg text-black truncate">
                            {{ strtoupper($student->last_name . ', ' .$student->first_name) }}
                        </td>
                        
                        <template x-for="day in addedDates" :key="day">
                            <td class="border-r-[2px] last:border-r-0 border-black h-12 sm:h-16 attendance-cell"
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
         class="fixed bottom-10 right-4 sm:right-10 z-[100] px-6 sm:px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex items-center gap-4"
         :class="toastType === 'success' ? 'bg-[#4ade80] text-black' : 'bg-red-500 text-white'">
        
        <i class="fa-solid text-2xl" :class="toastType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'"></i>
        <span class="font-black text-lg sm:text-xl tracking-wide" x-text="toastMessage"></span>
    </div>

</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('attendanceData', () => ({
        isManaging: false,
        selectedDate: new Date().toISOString().split('T')[0],
        showToast: false,
        toastMessage: '',
        toastType: 'success',
        addedDates: @json($existingDates ?? []),
        serverAttendance: @json($attendanceMap ?? []),
        
        addDateToTable() {
            if (!this.selectedDate) {
                this.triggerToast('Please select a date first!', 'error');
                return;
            }

            const day = new Date(this.selectedDate).getUTCDay();
            if (day === 0 || day === 6) {
                this.triggerToast('Cannot add weekends to the attendance sheet!', 'error');
                this.selectedDate = '';
                return;
            }

            if (!this.addedDates.includes(this.selectedDate)) {
                this.addedDates.push(this.selectedDate);
                this.addedDates.sort(); 
            } else {
                this.triggerToast('Date already added!', 'error');
            }
        },

        async deleteDateFromTable() {
            if (!this.selectedDate) {
                this.triggerToast('Please select a date to delete!', 'error');
                return;
            }

            if (!this.addedDates.includes(this.selectedDate)) {
                this.triggerToast('This date is not on the sheet!', 'error');
                return;
            }

            if (!confirm(`Are you sure you want to completely remove ${this.selectedDate}? This will also delete saved records for this day.`)) {
                return;
            }

            // Instantly remove it from the frontend array so the column disappears
            this.addedDates = this.addedDates.filter(d => d !== this.selectedDate);

            // Fetch request to actually delete from database
            try {
                const response = await fetch('/attendance/delete-date', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        date: this.selectedDate,
                        student_ids: @json($students->pluck('student_id'))
                    })
                });

                if (response.ok) {
                    this.triggerToast('Date permanently deleted!', 'success');
                } else {
                    // Only happens if the date was added but not saved yet, which is fine
                    this.triggerToast('Date removed from view (Unsaved).', 'success');
                }
            } catch (error) {
                console.error(error);
                this.triggerToast('Date removed from view.', 'success');
            }
        },

        getSavedStatus(studentId, date) {
            if (this.serverAttendance[studentId] && this.serverAttendance[studentId][date]) {
                return this.serverAttendance[studentId][date];
            }
            return 0; 
        },

        async saveAttendance() {
            const attendanceData = [];
            const cells = document.querySelectorAll('.attendance-cell');
            let hasIncompleteData = false;
            
            cells.forEach(cell => {
                const status = cell.getAttribute('data-status');
                
                if (status === '0' || status === 0 || !status) {
                    hasIncompleteData = true;
                }

                attendanceData.push({
                    student_id: cell.getAttribute('data-student'),
                    date: cell.getAttribute('data-date'),
                    status: status
                });
            });

            if (hasIncompleteData) {
                this.triggerToast('Please complete all attendance fields before saving!', 'error');
                return;
            }

            if (attendanceData.length === 0) {
                this.triggerToast('No data to save!', 'error');
                return;
            }

            try {
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
                    this.isManaging = false; 
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
@endsection