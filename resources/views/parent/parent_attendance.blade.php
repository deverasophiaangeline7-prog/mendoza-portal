@extends('layouts.navigation')

@section('title', 'Parent Dashboard - Attendance')

@section('content')
<div class="flex-1 flex flex-col justify-between h-full min-h-screen bg-white">
    <!-- Updated container: Stacks vertically on mobile, side-by-side on large screens -->
    <div class="flex-1 p-4 flex flex-col lg:flex-row justify-center items-center lg:items-start gap-8 mt-4 md:mt-8">
        
        <!-- Calendar Section -->
        <div class="border-[3px] border-black w-full max-w-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white rounded-xl overflow-hidden">
            
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b-[3px] border-black bg-gray-50">
                <a href="{{ route('parent.attendance', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" class="text-3xl sm:text-4xl font-black cursor-pointer hover:-translate-x-1 transition-transform">&lt;</a>
                <h2 class="text-2xl sm:text-3xl font-black tracking-widest uppercase">{{ $monthName }}</h2>
                <a href="{{ route('parent.attendance', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" class="text-3xl sm:text-4xl font-black cursor-pointer hover:translate-x-1 transition-transform">&gt;</a>
            </div>

            <div class="grid grid-cols-7 text-center border-b-[3px] border-black py-2 bg-gray-100">
                @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                    <!-- Added responsive text sizing to prevent overlap on narrow screens -->
                    <span class="text-[#b22222] font-black text-[10px] sm:text-sm">{{ $day }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 p-2 sm:p-3 gap-1 sm:gap-2"
                 x-data="{ 
                    attendanceData: {{ json_encode($rawAttendance) }},
                    getClass(status) {
                        if (status === 'present') return 'bg-[#4ade80] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'absent') return 'bg-[#ef4444] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'late') return 'bg-[#facc15] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'excused') return 'bg-[#60a5fa] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        return 'bg-white border-black text-gray-300 shadow-none';
                    },
                    init() {
                        setInterval(() => {
                            fetch('{{ route('attendance.fetch') }}?month={{ $currentDate->month }}&year={{ $currentDate->year }}&t=' + Date.now())
                                .then(res => res.json())
                                .then(data => { this.attendanceData = { ...data }; })
                                .catch(err => console.error('Error fetching attendance:', err));
                        }, 5000); // 5 seconds
                    }
                 }">
                
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="aspect-square border-none"></div>
                @endfor

                @foreach($days as $dayNum => $status)
                    @php
                        // Calculate the exact YYYY-MM-DD for this specific calendar square
                        $dateString = $currentDate->copy()->day($dayNum)->format('Y-m-d');
                    @endphp
                    <!-- Alpine dynamically changes the class based on the live database status -->
                    <div class="border-2 sm:border-[3px] aspect-square flex items-center justify-center text-sm sm:text-xl font-black rounded-lg transition-all"
                         :class="getClass(attendanceData['{{ $dateString }}'] || 'none')">
                        {{ $dayNum }}
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Legend Section -->
        <div class="w-full max-w-xl lg:w-56 border-[3px] border-black p-4 rounded-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-gray-50">
            <h3 class="font-black text-xl mb-4 uppercase tracking-wider border-b-[3px] border-black pb-2 text-center lg:text-left">Legend</h3>
            
            <!-- Wraps legend items on mobile so they don't break the container -->
            <div class="flex flex-row flex-wrap lg:flex-col justify-center gap-4 lg:space-y-3 lg:gap-0">
                @php
                    $legend = [
                        ['color' => 'bg-[#4ade80]', 'label' => 'Present'],
                        ['color' => 'bg-[#ef4444]', 'label' => 'Absent'],
                        ['color' => 'bg-[#facc15]', 'label' => 'Late'],
                        ['color' => 'bg-[#60a5fa]', 'label' => 'Excused'],
                        ['color' => 'bg-white', 'label' => 'Weekend/Holiday'],
                    ];
                @endphp
                @foreach($legend as $item)
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="{{ $item['color'] }} w-5 h-5 sm:w-6 sm:h-6 rounded-full border-[3px] border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></span>
                        <span class="font-black text-xs sm:text-sm uppercase">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Added flex-col for narrow screens to prevent long names from crashing into sections -->
    <footer class="p-4 sm:p-6 bg-white border-t-[3px] border-black flex flex-col md:flex-row justify-between items-center gap-2 font-black text-lg sm:text-xl shadow-[0px_-4px_0px_0px_rgba(0,0,0,1)] relative z-10 flex-shrink-0 text-center md:text-left">
        <div class="uppercase tracking-wide text-black">
            {{ $student->last_name }}, {{ $student->first_name }} 
            {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }}
        </div>
        <div class="uppercase tracking-wide text-[#ffb02e] drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
            {{ $student->grade_level }} {{ $student->section ? '- ' . $student->section->section_name : '' }}
        </div>
    </footer>
</div>
@endsection