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

<main class="flex-1 p-8 bg-white min-h-screen relative" x-data="attendanceData()">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-start mb-8">
            <a href="{{ route('attendance.index') }}" class="text-red-600 text-5xl hover:scale-110 transition">
                <i class="fa-solid fa-circle-xmark"></i>
            </a>

            <div class="text-center flex-1">
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">{{ $displayName }}</h2>
                <div class="text-[#b26905] font-black text-2xl italic uppercase mt-1 drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
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

                <button x-show="isManaging" x-cloak @click="saveAttendance()" class="bg-[#b26905] text-black px-8 py-3 rounded-xl border-[3px] border-black font-black hover:bg-amber-700 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px]">
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
            {{-- This is what the Parent sees --}}
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
                            <th class="border-r-[2px] border-black text-center text-lg w-16 py-4 bg-amber-700 font-black" x-text="new Date(day).getDate()"></th>
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
         class="fixed bottom-10 right-10 z-[100] px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex items-center gap-4"
         :class="toastType === 'success' ? 'bg-[#4ade80] text-black' : 'bg-red-500 text-white'">
        
        <i class="fa-solid text-2xl" :class="toastType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'"></i>
        <span class="font-black text-xl tracking-wide" x-text="toastMessage"></span>
    </div>

</main>

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
@endsection